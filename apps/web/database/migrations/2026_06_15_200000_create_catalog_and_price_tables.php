<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'parent_id', 'name']);
            $table->index(['company_id', 'active', 'name']);
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->string('external_id', 100)->nullable();
            $table->string('sku', 100);
            $table->string('barcode', 50)->nullable();
            $table->string('image_url')->nullable();
            $table->string('name');
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->decimal('available_stock', 15, 3)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'active', 'name']);
            $table->index(['company_id', 'category_id']);
            $table->index(['company_id', 'updated_at']);
        });

        Schema::create('price_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('price_table_id')->constrained()->restrictOnDelete();
            $table->decimal('price', 15, 2);
            $table->decimal('minimum_quantity', 15, 3)->default(1);
            $table->timestamps();

            $table->unique(['product_id', 'price_table_id', 'minimum_quantity'], 'product_price_tier_unique');
            $table->index(['price_table_id', 'product_id', 'minimum_quantity'], 'price_table_product_qty_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('price_tables');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
