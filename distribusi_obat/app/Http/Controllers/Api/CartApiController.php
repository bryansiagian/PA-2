<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function index() {
        // Ambil isi keranjang user yang sedang login
        return Cart::with('drug.category')->where('user_id', auth()->id())->get();
    }

    public function store(Request $request) {
        $request->validate(['drug_id' => 'required|exists:drugs,id']);

        // Cek apakah item sudah ada di keranjang user
        $cart = Cart::where('user_id', auth()->id())
                    ->where('drug_id', $request->drug_id)
                    ->first();

        if ($cart) {
            $cart->increment('quantity');
        } else {
            Cart::create([
                'user_id' => auth()->id(),
                'drug_id' => $request->drug_id,
                'quantity' => 1
            ]);
        }

        return response()->json(['message' => 'Berhasil ditambah ke keranjang database']);
    }

    public function update(Request $request, $id) {
        $cart = Cart::with('drug')->where('user_id', auth()->id())->findOrFail($id);

        // Validasi: Apakah jumlah yang diminta melebihi stok yang ada?
        if ($request->quantity > $cart->drug->stock) {
            return response()->json([
                'message' => 'Gagal: Stok tersedia hanya ' . $cart->drug->stock . ' unit.',
                'max_stock' => $cart->drug->stock
            ], 422);
        }

        $cart->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Kuantitas diperbarui']);
    }

    public function destroy($id) {
        Cart::where('user_id', auth()->id())->findOrFail($id)->delete();
        return response()->json(['message' => 'Item dihapus']);
    }

    public function clear() {
        Cart::where('user_id', auth()->id())->delete();
        return response()->json(['message' => 'Keranjang dikosongkan']);
    }
}