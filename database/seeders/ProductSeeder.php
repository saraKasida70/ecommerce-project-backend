<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Products\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop',
                'description' => 'High performance laptop',
                'price' => 999.99,
                'category' => 'Electronics',
            ],
            [
                'name' => 'Mouse',
                'description' => 'Wireless mouse',
                'price' => 29.99,
                'category' => 'Electronics',
            ],
            [
                'name' => 'Keyboard',
                'description' => 'Mechanical keyboard',
                'price' => 79.99,
                'category' => 'Electronics',
            ],
            [
                'name' => 'Monitor',
                'description' => '27 inch monitor',
                'price' => 299.99,
                'category' => 'Electronics',
            ],
            [
                'name' => 'Headphones',
                'description' => 'Noise cancelling headphones',
                'price' => 149.99,
                'category' => 'Electronics',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Created ' . count($products) . ' products');
    }
}
