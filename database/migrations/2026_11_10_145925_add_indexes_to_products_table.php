
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('created_at', 'products_created_at_index');

            $table->index(
                ['category', 'created_at'],
                'products_category_created_at_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_created_at_index');

            $table->dropIndex('products_category_created_at_index');
        });
    }
};
