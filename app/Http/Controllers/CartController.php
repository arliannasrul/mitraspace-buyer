<?php

namespace App\Http\Controllers;

use App\Services\SellerApiService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected SellerApiService $sellerApi;

    public function __construct(SellerApiService $sellerApi)
    {
        $this->sellerApi = $sellerApi;
    }

    public function index()
    {
        $cart  = session('cart', []);
        $total = $this->calculateTotal($cart);

        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('auth.google')->with('error', 'Silakan login terlebih dahulu untuk mulai belanja.');
        }

        $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'nullable|integer|min:1|max:99',
        ]);

        $products  = $this->sellerApi->getProducts();
        $productId = (int) $request->input('product_id');
        $product   = collect($products)->firstWhere('id', $productId);

        if (!$product) {
            return back()->with('error', 'Produk tidak ditemukan.');
        }

        $quantity = (int) $request->input('quantity', 1);
        $cart     = session('cart', []);

        if (isset($cart[$productId])) {
            $newQty = $cart[$productId]['quantity'] + $quantity;
            // Jangan melebihi stok
            $newQty = min($newQty, (int) ($product['stock'] ?? 99));
            $cart[$productId]['quantity'] = $newQty;
        } else {
            $cart[$productId] = [
                'id'       => $product['id'],
                'name'     => $product['name'],
                'price'    => (float) $product['price'],
                'weight'   => (float) ($product['weight'] ?? 0.5),
                'image'    => $product['image_url'] ?? null,
                'stock'    => (int) ($product['stock'] ?? 0),
                'quantity' => $quantity,
            ];
        }

        session(['cart' => $cart]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $this->cartCount($cart),
                'message' => "'{$product['name']}' ditambahkan ke keranjang.",
            ]);
        }

        return back()->with('success', "'{$product['name']}' berhasil ditambahkan ke keranjang!");
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer|min:0|max:99',
        ]);

        $cart      = session('cart', []);
        $productId = (int) $request->input('product_id');
        $quantity  = (int) $request->input('quantity');

        if ($quantity === 0) {
            unset($cart[$productId]);
        } elseif (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = min($quantity, $cart[$productId]['stock']);
        }

        session(['cart' => $cart]);

        return response()->json([
            'success'  => true,
            'count'    => $this->cartCount($cart),
            'total'    => $this->calculateTotal($cart),
            'subtotal' => isset($cart[$productId]) ? $cart[$productId]['price'] * $cart[$productId]['quantity'] : 0,
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);

        $cart      = session('cart', []);
        $productId = (int) $request->input('product_id');
        unset($cart[$productId]);
        session(['cart' => $cart]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $this->cartCount($cart),
                'total'   => $this->calculateTotal($cart),
            ]);
        }

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('catalog.index')->with('success', 'Keranjang dikosongkan.');
    }

    // ---- Helpers ----

    protected function calculateTotal(array $cart): float
    {
        return array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
    }

    protected function cartCount(array $cart): int
    {
        return array_sum(array_column($cart, 'quantity'));
    }
}
