<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('color', 80)->nullable()->after('description');
            $table->decimal('weight_kg', 10, 3)->nullable()->after('color');
            $table->decimal('length_cm', 10, 2)->nullable()->after('weight_kg');
            $table->decimal('width_cm', 10, 2)->nullable()->after('length_cm');
            $table->decimal('height_cm', 10, 2)->nullable()->after('width_cm');
            $table->decimal('base_price', 15, 2)->nullable()->after('height_cm');
            $table->string('stock_status', 30)->default('InStock')->after('available_stock');
            $table->timestamp('published_at')->nullable()->after('stock_status');

            $table->index(['company_id', 'stock_status']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['company_id', 'stock_status']);
            $table->dropColumn([
                'color',
                'weight_kg',
                'length_cm',
                'width_cm',
                'height_cm',
                'base_price',
                'stock_status',
                'published_at',
            ]);
        });
    }
};
