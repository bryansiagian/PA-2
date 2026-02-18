<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Gallery;
use App\Models\OrganizationStructure;
use App\Models\AuditLog; // Pastikan Model AuditLog di-import
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CmsController extends Controller {

    // --- Akses Publik (Landing Page) ---
    public function getLandingPageData() {
        return response()->json([
            'profiles' => Profile::all()->keyBy('key'),
            'news' => Post::where('type', 'news')->latest()->take(3)->get(),
            'activities' => Post::where('type', 'activity')->latest()->take(3)->get(),
            'gallery' => Gallery::latest()->take(8)->get(),
            'organization' => OrganizationStructure::orderBy('order', 'asc')->get()
        ]);
    }

    // --- Manajemen Berita & Kegiatan (Admin) ---
    public function indexPosts() { return Post::latest()->get(); }

    public function storePost(Request $request) {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'type' => 'required',
            'image' => 'image|max:2048'
        ]);

        return DB::transaction(function() use ($request) {
            $path = $request->hasFile('image') ? $request->file('image')->store('posts', 'public') : null;

            $post = Post::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . time(),
                'content' => $request->content,
                'type' => $request->type,
                'image' => $path,
                'user_id' => auth()->id()
            ]);

            // CATAT KE AUDIT LOG
            $tipe = $request->type == 'news' ? 'Berita' : 'Kegiatan';
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Mempublikasikan {$tipe} baru - {$request->title}"
            ]);

            return response()->json(['message' => 'Konten berhasil dipublikasikan']);
        });
    }

    public function deletePost($id) {
        return DB::transaction(function() use ($id) {
            $post = Post::findOrFail($id);
            $judul = $post->title;
            $tipe = $post->type == 'news' ? 'Berita' : 'Kegiatan';

            if($post->image) Storage::disk('public')->delete($post->image);
            $post->delete();

            // CATAT KE AUDIT LOG
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Menghapus {$tipe} - {$judul}"
            ]);

            return response()->json(['message' => 'Konten dihapus']);
        });
    }

    // --- Manajemen Profil (Update History, Visi Misi, dll) ---
    public function updateProfile(Request $request) {
        $request->validate([
            'key' => 'required',
            'title' => 'required',
            'content' => 'required'
        ]);

        try {
            return DB::transaction(function() use ($request) {
                $profile = Profile::updateOrCreate(
                    ['key' => $request->key],
                    [
                        'title' => $request->title,
                        'content' => $request->content
                    ]
                );

                // CATAT KE AUDIT LOG
                $sectionName = strtoupper(str_replace('_', ' ', $request->key));
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => "CMS: Memperbarui konten profil bagian {$sectionName}"
                ]);

                return response()->json([
                    'message' => 'Konten ' . $request->key . ' berhasil diperbarui'
                ], 200);
            });

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- Manajemen Struktur Organisasi ---
    public function indexOrg() { return OrganizationStructure::orderBy('order')->get(); }

    public function storeOrg(Request $request) {
        $request->validate([
            'name' => 'required',
            'position' => 'required',
            'photo' => 'nullable|image|max:2048'
        ]);

        return DB::transaction(function() use ($request) {
            $path = $request->hasFile('photo') ? $request->file('photo')->store('org', 'public') : null;

            $org = OrganizationStructure::create([
                'name' => $request->name,
                'position' => $request->position,
                'photo' => $path,
                'order' => $request->order ?? 0
            ]);

            // CATAT KE AUDIT LOG
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Menambah struktur organisasi - {$request->name} ({$request->position})"
            ]);

            return response()->json(['message' => 'Struktur organisasi diperbarui']);
        });
    }

    // Jika ingin menghapus struktur organisasi juga perlu log
    public function deleteOrg($id) {
        return DB::transaction(function() use ($id) {
            $org = OrganizationStructure::findOrFail($id);
            $nama = $org->name;

            if($org->photo) Storage::disk('public')->delete($org->photo);
            $org->delete();

            // CATAT KE AUDIT LOG
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Menghapus pengurus organisasi - {$nama}"
            ]);

            return response()->json(['message' => 'Data pengurus dihapus']);
        });
    }
}