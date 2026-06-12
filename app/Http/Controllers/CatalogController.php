<?php

namespace App\Http\Controllers;

use App\Services\SellerApiService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected SellerApiService $sellerApi) {}

    public function index(Request $request)
    {
        $products = $this->sellerApi->getProducts();

        // Filter by category if requested
        $category = $request->query('category');
        if ($category) {
            $products = array_filter($products, fn($p) => ($p['category'] ?? '') === $category);
            $products = array_values($products);
        }

        // Search
        $search = $request->query('search');
        if ($search) {
            $products = array_filter($products, fn($p) =>
                stripos($p['name'] ?? '', $search) !== false ||
                stripos($p['description'] ?? '', $search) !== false
            );
            $products = array_values($products);
        }

        $categories = array_unique(array_column($this->sellerApi->getProducts(), 'category'));

        return view('catalog.index', compact('products', 'categories', 'category', 'search'));
    }

    public function show(int $id)
    {
        $products = $this->sellerApi->getProducts();
        $product  = collect($products)->firstWhere('id', $id);

        if (!$product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $related = collect($products)
            ->filter(fn($p) => $p['id'] !== $id && ($p['category'] ?? '') === ($product['category'] ?? ''))
            ->take(4)
            ->values()
            ->toArray();

        return view('catalog.show', compact('product', 'related'));
    }
}
