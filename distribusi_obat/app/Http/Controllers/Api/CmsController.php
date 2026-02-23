<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Profile;
use App\Models\Contact;
use App\Models\OrganizationStructure;
use App\Models\Gallery;
use App\Models\GalleryFile;
use App\Models\GeneralFile;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CmsController extends Controller {

    // --- Akses Publik (Landing Page) ---
    public function getLandingPageData() {
        try {
            return response()->json([
                'profiles' => Profile::where('active', 1)->get()->keyBy('key'),
                'contacts' => Contact::where('active', 1)->get()->keyBy('key'),

                // Mengambil postingan dengan kategori yang mengandung kata 'Berita'
                'news' => Post::with('category')
                    ->whereHas('category', function($q) {
                        $q->where('name', 'LIKE', '%Berita%');
                    })
                    ->where('status', 1) // Pastikan hanya yang PUBLISHED
                    ->where('active', 1)
                    ->latest()
                    ->take(3)
                    ->get(),

                // Mengambil postingan dengan kategori yang mengandung kata 'Kegiatan'
                'activities' => Post::with('category')
                    ->whereHas('category', function($q) {
                        $q->where('name', 'LIKE', '%Kegiatan%');
                    })
                    ->where('status', 1)
                    ->where('active', 1)
                    ->latest()
                    ->take(3)
                    ->get(),

                'organization' => OrganizationStructure::where('active', 1)
                    ->orderBy('order', 'asc')
                    ->get(),

                'gallery' => Gallery::with('files') // Tambahkan with('files') agar data foto muncul
                ->where('active', 1)
                ->latest()
                ->take(6)
                ->get(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // --- Manajemen Berita & Kegiatan (Admin) ---
    public function indexPosts() {
        // PENTING: Menggunakan with(['category']) agar p.category.name tidak null di JS
        return Post::with(['category', 'author'])->where('active', 1)->latest()->get();
    }

    public function showPost($id) {
        return Post::with('category')->findOrFail($id);
    }

    /**
     * SIMPAN POSTINGAN BARU (Fix Error "Type field is required")
     */
    public function storePost(Request $request) {
        // PERBAIKAN: Validasi menggunakan post_category_id, bukan type
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'post_category_id' => 'required|exists:post_categories,id',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:0,1'
        ]);

        return DB::transaction(function() use ($request) {
            $path = $request->hasFile('image') ? $request->file('image')->store('posts', 'public') : null;

            $post = Post::create([
                'user_id' => auth()->id(),
                'post_category_id' => $request->post_category_id,
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . time(),
                'content' => $request->content,
                'image' => $path,
                'status' => $request->status,
                'active' => 1
            ]);

            // Ambil nama kategori untuk log yang lebih jelas
            $category = PostCategory::find($request->post_category_id);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Membuat postingan baru [{$category->name}] - {$request->title}"
            ]);

            return response()->json(['message' => 'Konten berhasil disimpan'], 201);
        });
    }

    /**
     * UPDATE POSTINGAN
     */
    public function updatePost(Request $request, $id) {
        $post = Post::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'post_category_id' => 'required|exists:post_categories,id',
            'status' => 'required'
        ]);

        return DB::transaction(function() use ($request, $post) {
            $data = $request->only(['title', 'content', 'post_category_id', 'status']);

            if ($request->hasFile('image')) {
                if($post->image) Storage::disk('public')->delete($post->image);
                $data['image'] = $request->file('image')->store('posts', 'public');
            }

            $post->update($data);

            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Mengupdate postingan - {$post->title}"]);
            return response()->json(['message' => 'Konten diperbarui']);
        });
    }

    /**
     * HAPUS POSTINGAN (Soft Delete via Active field)
     */
    public function deletePost($id) {
        $post = Post::findOrFail($id);
        $title = $post->title;

        // Sesuai DBML: kita set active = 0 (Archive)
        $post->update(['active' => 0]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "CMS: Mengarsipkan postingan - {$title}"
        ]);

        return response()->json(['message' => 'Konten berhasil diarsipkan']);
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

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => "CMS: Memperbarui konten profil bagian " . strtoupper($request->key)
                ]);

                return response()->json(['message' => 'Profil berhasil diperbarui']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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

    /**
     * List semua kontak untuk Admin
     */
    public function indexContacts() {
        return Contact::where('active', 1)->latest()->get();
    }

    /**
     * Simpan Kontak Baru
     */
    public function storeContact(Request $request) {
        $request->validate([
            'key' => 'required|unique:contacts,key',
            'title' => 'required',
            'value' => 'required',
            'image' => 'nullable|image|max:1024'
        ]);

        return DB::transaction(function() use ($request) {
            $path = $request->hasFile('image') ? $request->file('image')->store('contacts', 'public') : null;

            Contact::create([
                'key' => strtolower($request->key),
                'title' => $request->title,
                'value' => $request->value,
                'image' => $path,
                'active' => 1
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Menambah kontak baru - {$request->title}"
            ]);

            return response()->json(['message' => 'Konten kontak berhasil ditambah']);
        });
    }

    /**
     * Update Kontak
     */
    public function updateContact(Request $request, $id) {
        $contact = Contact::findOrFail($id);

        $request->validate([
            'key' => "required|unique:contacts,key,{$id}",
            'title' => 'required',
            'value' => 'required',
            'image' => 'nullable|image|max:1024'
        ]);

        return DB::transaction(function() use ($request, $contact) {
            $data = $request->only(['key', 'title', 'value']);

            if ($request->hasFile('image')) {
                if($contact->image) Storage::disk('public')->delete($contact->image);
                $data['image'] = $request->file('image')->store('contacts', 'public');
            }

            $contact->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Memperbarui data kontak {$contact->title}"
            ]);

            return response()->json(['message' => 'Data kontak diperbarui']);
        });
    }


    /**
     * Hapus Kontak (Soft Delete)
     */
    public function deleteContact($id) {
        $contact = Contact::findOrFail($id);
        $contact->update(['active' => 0]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "CMS: Menghapus kontak {$contact->title}"
        ]);

        return response()->json(['message' => 'Kontak berhasil dihapus']);
    }

    /**
     * Mengambil semua kategori post untuk dropdown
     */
    public function indexPostCategories() {
        try {
            // withCount akan otomatis menambahkan atribut 'posts_count' pada tiap objek kategori
            $categories = PostCategory::withCount('posts')
                ->where('active', 1)
                ->latest()
                ->get();

            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function storePostCategory(Request $request) {
        $request->validate(['name' => 'required|string|unique:post_categories,name']);

        return DB::transaction(function() use ($request) {
            $category = PostCategory::create([
                'name' => $request->name,
                'active' => 1
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Menambah kategori postingan baru - {$category->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil ditambahkan'], 201);
        });
    }

    /**
     * Update nama kategori
     */
    public function updatePostCategory(Request $request, $id) {
        $request->validate(['name' => "required|string|unique:post_categories,name,{$id}"]);

        return DB::transaction(function() use ($request, $id) {
            $category = PostCategory::findOrFail($id);
            $oldName = $category->name;
            $category->update(['name' => $request->name]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Mengubah kategori postingan dari {$oldName} menjadi {$request->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil diperbarui']);
        });
    }

    /**
     * Hapus kategori (Soft Delete via active field)
     */
    public function deletePostCategory($id) {
        return DB::transaction(function() use ($id) {
            $category = PostCategory::findOrFail($id);

            // Proteksi: Jangan hapus jika masih ada post yang menggunakannya
            if ($category->posts()->count() > 0) {
                return response()->json(['message' => 'Gagal! Kategori ini masih digunakan oleh beberapa postingan.'], 422);
            }

            $name = $category->name;
            $category->update(['active' => 0]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Menghapus kategori postingan - {$name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil dihapus']);
        });
    }

    public function indexGalleries() {
        return Gallery::with(['files'])->where('active', 1)->latest()->get();
    }

    public function storeGallery(Request $request) {
        $request->validate(['title' => 'required|string|max:255']);
        return DB::transaction(function() use ($request) {
            $gallery = Gallery::create(['title' => $request->title]);

            // Handle Multiple Uploads jika ada
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('galleries', 'public');
                    $type = Str::contains($file->getMimeType(), 'video') ? 'video' : 'image';

                    GalleryFile::create([
                        'gallery_id' => $gallery->id,
                        'file_path' => 'storage/'.$path,
                        'file_type' => $type
                    ]);
                }
            }

            AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Membuat album galeri baru - {$request->title}"]);
            return response()->json(['message' => 'Galeri berhasil dibuat']);
        });
    }

    public function deleteGallery($id) {
        $gallery = Gallery::findOrFail($id);
        $gallery->update(['active' => 0]); // Soft delete
        AuditLog::create(['user_id' => auth()->id(), 'action' => "CMS: Menghapus album galeri - {$gallery->title}"]);
        return response()->json(['message' => 'Galeri dihapus']);
    }

    /**
     * List file untuk Admin
     */
    public function indexGeneralFiles() {
        return GeneralFile::with('creator')->where('active', 1)->latest()->get();
    }

    /**
     * Simpan file baru
     */
    public function storeGeneralFile(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,docx,doc,xlsx|max:5120' // Maks 5MB
        ]);

        return DB::transaction(function() use ($request) {
            $path = $request->file('file')->store('general_files', 'public');

            $file = GeneralFile::create([
                'name' => $request->name,
                'file_path' => $path,
                'active' => 1
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "CMS: Mengunggah dokumen umum - {$request->name}"
            ]);

            return response()->json(['message' => 'Dokumen berhasil diunggah']);
        });
    }

    /**
     * Hapus file (Soft Delete)
     */
    public function deleteGeneralFile($id) {
        $file = GeneralFile::findOrFail($id);
        $file->update(['active' => 0]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "CMS: Menghapus dokumen - {$file->name}"
        ]);

        return response()->json(['message' => 'Dokumen berhasil dihapus']);
    }
}