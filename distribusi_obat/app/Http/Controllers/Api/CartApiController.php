<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product; // Pastikan menggunakan model Product
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function index() {
        // Ambil isi keranjang user yang sedang login
        // Relasi diubah dari drug ke product
        return Cart::with('product.category')->where('user_id', auth()->id())->get();
    }

    public function store(Request $request) {
        // 1. Validasi input
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        // 2. Cek apakah item sudah ada di keranjang user
        $cart = Cart::where('user_id', auth()->id())
                    ->where('product_id', $request->product_id) // Gunakan product_id
                    ->first();

        if ($cart) {
            $cart->increment('quantity');
        } else {
            // 3. Simpan data baru
            Cart::create([
                'user_id'    => auth()->id(),
                'product_id' => $request->product_id, // Pastikan variabel ini benar
                'quantity'   => 1
            ]);
        }

        return response()->json(['message' => 'Berhasil ditambah ke keranjang']);
    }

    public function update(Request $request, $id) {
        // Eager load relasi product
        $cart = Cart::with('product')->where('user_id', auth()->id())->findOrFail($id);

        // Validasi: Apakah jumlah yang diminta melebihi stok yang ada?
        if ($request->quantity > $cart->product->stock) {
            return response()->json([
                'message' => 'Gagal: Stok tersedia hanya ' . $cart->product->stock . ' unit.',
                'max_stock' => $cart->product->stock
            ], 422);
        }

        $cart->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Kuantitas diperbarui']);
    }

    public function destroy($id) {
        // $id di sini akan otomatis menangani UUID jika dikirimkan dari frontend
        Cart::where('user_id', auth()->id())->findOrFail($id)->delete();
        return response()->json(['message' => 'Item dihapus']);
    }

    public function clear() {
        Cart::where('user_id', auth()->id())->delete();
        return response()->json(['message' => 'Keranjang dikosongkan']);
    }
}