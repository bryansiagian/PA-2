<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use App\Models\StockLog;
use App\Models\AuditLog; // Pastikan model ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DrugController extends Controller
{
    /**
     * Menampilkan semua daftar obat beserta kategorinya.
     */
    public function index() {
        try {
            return Drug::with('category')->latest()->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal di server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * MENDAFTARKAN OBAT BARU (Store).
     * Digunakan oleh Admin/Operator untuk menambah produk ke katalog.
     */
    public function store(Request $request) {
        $request->validate([
            'name'        => 'required|string|max:255',
            'sku'         => 'required|string|unique:drugs,sku',
            'category_id' => 'required|exists:categories,id',
            'unit'        => 'required|string',
            'min_stock'   => 'required|integer|min:0',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Validasi gambar
        ]);

        try {
            return DB::transaction(function() use ($request) {
                $path = null;
                // Handle Upload Gambar jika ada
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('drugs', 'public');
                }

                // 1. Buat Record Obat
                $drug = Drug::create([
                    'name'        => $request->name,
                    'sku'         => $request->sku,
                    'category_id' => $request->category_id,
                    'unit'        => $request->unit,
                    'min_stock'   => $request->min_stock,
                    'stock'       => $request->stock,
                    'image'       => $path ? 'storage/'.$path : null, // Simpan path publik
                ]);

                // 2. Jika ada stok awal, catat di StockLog
                if ($request->stock > 0) {
                    StockLog::create([
                        'drug_id'  => $drug->id,
                        'user_id'  => auth()->id(),
                        'type'     => 'in',
                        'quantity' => $request->stock,
                        'reference'=> 'Initial Stock (New Registration)'
                    ]);
                }

                // 3. Catat Audit Log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "CREATE DRUG: Mendaftarkan obat baru {$drug->name} (SKU: {$drug->sku})"
                ]);

                return response()->json(['message' => 'Obat baru berhasil ditambahkan', 'data' => $drug], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambah obat: ' . $e->getMessage()], 500);
        }
    }

    /**
     * UPDATE DATA OBAT (Info katalog).
     */
    public function update(Request $request, $id) {
        $drug = Drug::findOrFail($id);

        $request->validate([
            'name'        => 'required|string',
            'sku'         => 'required|unique:drugs,sku,'.$id,
            'category_id' => 'required|exists:categories,id',
            'unit'        => 'required|string',
            'min_stock'   => 'required|integer',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $data = $request->all();

            // Handle Update Gambar
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($drug->image) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $drug->image));
                }
                $path = $request->file('image')->store('drugs', 'public');
                $data['image'] = 'storage/'.$path;
            }

            $drug->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "UPDATE DRUG: Mengubah informasi obat {$drug->name}"
            ]);

            return response()->json(['message' => 'Data obat berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * UPDATE STOK (Stock-In).
     */
    public function updateStock(Request $request) {
        $validator = Validator::make($request->all(), [
            'drug_id'  => 'required|exists:drugs,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function() use ($request) {
                $drug = Drug::findOrFail($request->drug_id);
                $drug->increment('stock', $request->quantity);

                StockLog::create([
                    'drug_id'  => $drug->id,
                    'user_id'  => auth()->id(),
                    'type'     => 'in',
                    'quantity' => $request->quantity,
                    'reference'=> 'Manual Stock-In Entry'
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "STOCK-IN: Menambah {$request->quantity} unit obat {$drug->name}"
                ]);

                return response()->json(['message' => 'Stok ' . $drug->name . ' Berhasil Diperbarui']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id) {
        return Drug::with('category')->findOrFail($id);
    }

    public function destroy($id) {
        try {
            $drug = Drug::findOrFail($id);
            $name = $drug->name;

            // Hapus gambar dari storage jika ada
            if ($drug->image) {
                Storage::disk('public')->delete(str_replace('storage/', '', $drug->image));
            }

            $drug->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "DELETE DRUG: Menghapus obat {$name} dari sistem"
            ]);

            return response()->json(['message' => 'Obat berhasil dihapus dari sistem']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
}