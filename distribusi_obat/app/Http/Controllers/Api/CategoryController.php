<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        return response()->json(Category::withCount('drugs')->get());
    }

    public function store(Request $request) {
        $request->validate(['name' => 'required|unique:categories,name']);
        Category::create($request->all());
        return response()->json(['message' => 'Kategori berhasil ditambahkan']);
    }

    public function show($id) {
        return Category::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $cat = Category::findOrFail($id);
        $request->validate(['name' => 'required|unique:categories,name,'.$id]);
        $cat->update($request->all());
        return response()->json(['message' => 'Kategori berhasil diubah']);
    }

    public function destroy($id) {
        $cat = Category::findOrFail($id);
        // Proteksi: Jangan hapus jika kategori masih dipakai oleh obat
        if($cat->drugs()->count() > 0) {
            return response()->json(['message' => 'Gagal! Kategori ini masih digunakan oleh beberapa obat.'], 422);
        }
        $cat->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}