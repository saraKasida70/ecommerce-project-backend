<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Create a separate cache key for each page and category.
        $filters = $request->only(['category', 'page']);
        ksort($filters);

        $cacheKey = 'products:index:' . md5(json_encode($filters));

        // Cache-Aside: retrieve from Redis or query MySQL.
        $cachedProducts = Cache::tags(['products'])->remember(
            $cacheKey,
            now()->addMinutes(10),
            function () use ($request) {

                $query = Product::query();

                if ($request->filled('category')) {
                    $query->where(
                        'category',
                        $request->input('category')
                    );
                }

                $products = $query->latest()->paginate(10);

                return [
                    'data' => $products->items(),
                    'meta' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ],
                ];
            }
        );

        $payload = [
            'message' => 'Products retrieved successfully.',
            'data' => $cachedProducts['data'],
            'meta' => $cachedProducts['meta'],
        ];

        // Create the JSON response.
        $response = response()->json($payload);

        // Generate ETag based on response content.
        $etag = hash('sha256', $response->getContent());

        $response->setEtag($etag);

        // Browser caching with revalidation.
        $response->headers->set(
            'Cache-Control',
            'private, no-cache, must-revalidate'
        );

        // Return 304 if the content has not changed.
        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
            'category' => ['required', 'string', 'max:255'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product = Product::create($validated);

        // Invalidate all cached product listings.
        Cache::tags(['products'])->flush();

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }
}
