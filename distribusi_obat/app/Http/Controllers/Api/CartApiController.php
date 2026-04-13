<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartApiController extends Controller
{
    public function index()
    {
        return Cart::with('product.category')
            ->where('user_id', auth()->id())
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        return DB::transaction(function () use ($request) {

            // 🔒 LOCK PRODUK (anti bentrok multi user)
            $product = Product::where('id', $request->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            // ❗ VALIDASI STOK HABIS
            if ($product->stock <= 0) {
                return response()->json([
                    'message' => 'Stok produk habis, tidak bisa ditambahkan ke keranjang'
                ], 422);
            }

            $cart = Cart::where('user_id', auth()->id())
                ->where('product_id', $request->product_id)
                ->first();

            if ($cart) {

                // ❗ CEK JANGAN MELEBIHI STOK
                if ($cart->quantity + 1 > $product->stock) {
                    return response()->json([
                        'message' => 'Jumlah melebihi stok tersedia',
                        'max_stock' => $product->stock
                    ], 422);
                }

                $cart->increment('quantity');

            } else {

                Cart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $request->product_id,
                    'quantity' => 1
                ]);
            }

            return response()->json([
                'message' => 'Berhasil ditambah ke keranjang'
            ]);
        });
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($request, $id) {

            $cart = Cart::with('product')
                ->where('user_id', auth()->id())
                ->findOrFail($id);

            $product = Product::where('id', $cart->product_id)
                ->lockForUpdate()
                ->first();

            // ❗ STOK HABIS
            if ($product->stock <= 0) {
                return response()->json([
                    'message' => 'Stok produk habis'
                ], 422);
            }

            // ❗ MELEBIHI STOK
            if ($request->quantity > $product->stock) {
                return response()->json([
                    'message' => 'Stok hanya tersedia ' . $product->stock,
                    'max_stock' => $product->stock
                ], 422);
            }

            $cart->update([
                'quantity' => $request->quantity
            ]);

            return response()->json([
                'message' => 'Kuantitas diperbarui'
            ]);
        });
    }

    public function destroy($id)
    {
        Cart::where('user_id', auth()->id())
            ->findOrFail($id)
            ->delete();

        return response()->json(['message' => 'Item dihapus']);
    }

    public function clear()
    {
        Cart::where('user_id', auth()->id())->delete();

        return response()->json(['message' => 'Keranjang dikosongkan']);
    }
}
