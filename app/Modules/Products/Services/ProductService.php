<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * جلب المنتجات حسب المعرفات.
     */
    public function getProductsByIds(array $productIds): Collection
    {
        return Product::whereIn('id', $productIds)->get();
    }

    /**
     * التحقق من وجود جميع المنتجات المطلوبة.
     */
    public function allProductsExist(array $productIds): bool
    {
        $existingCount = Product::whereIn('id', $productIds)
            ->count();

        return $existingCount === count(array_unique($productIds));
    }
}