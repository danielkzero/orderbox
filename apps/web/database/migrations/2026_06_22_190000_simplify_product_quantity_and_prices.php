<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'minimum_quantity')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->decimal('minimum_quantity', 15, 3)->default(1)->after('base_price');
                $table->decimal('quantity_multiple', 15, 3)->nullable()->after('minimum_quantity');
                $table->boolean('allows_fractional_quantity')->default(false)->after('quantity_multiple');
            });
        }

        $duplicates = DB::table('product_prices')
            ->select('product_id', 'price_table_id')
            ->groupBy('product_id', 'price_table_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $keepId = DB::table('product_prices')
                ->where('product_id', $duplicate->product_id)
                ->where('price_table_id', $duplicate->price_table_id)
                ->orderBy('minimum_quantity')
                ->orderBy('id')
                ->value('id');

            DB::table('product_prices')
                ->where('product_id', $duplicate->product_id)
                ->where('price_table_id', $duplicate->price_table_id)
                ->where('id', '<>', $keepId)
                ->delete();
        }

        $indexes = collect(Schema::getIndexes('product_prices'))->pluck('name');

        Schema::table('product_prices', function (Blueprint $table) use ($indexes): void {
            if (! $indexes->contains('product_prices_product_id_migration_idx')) {
                $table->index('product_id', 'product_prices_product_id_migration_idx');
            }
            if (! $indexes->contains('product_prices_price_table_id_migration_idx')) {
                $table->index('price_table_id', 'product_prices_price_table_id_migration_idx');
            }
        });

        $indexes = collect(Schema::getIndexes('product_prices'))->pluck('name');
        Schema::table('product_prices', function (Blueprint $table) use ($indexes): void {
            if ($indexes->contains('product_price_tier_unique')) {
                $table->dropUnique('product_price_tier_unique');
            }
            if ($indexes->contains('price_table_product_qty_idx')) {
                $table->dropIndex('price_table_product_qty_idx');
            }
            if (Schema::hasColumn('product_prices', 'minimum_quantity')) {
                $table->dropColumn('minimum_quantity');
            }
        });

        $indexes = collect(Schema::getIndexes('product_prices'))->pluck('name');
        Schema::table('product_prices', function (Blueprint $table) use ($indexes): void {
            if (! $indexes->contains('product_price_table_unique')) {
                $table->unique(['product_id', 'price_table_id'], 'product_price_table_unique');
            }
            if (! $indexes->contains('price_table_product_idx')) {
                $table->index(['price_table_id', 'product_id'], 'price_table_product_idx');
            }
        });

        $indexes = collect(Schema::getIndexes('product_prices'))->pluck('name');
        Schema::table('product_prices', function (Blueprint $table) use ($indexes): void {
            if ($indexes->contains('product_prices_product_id_migration_idx')) {
                $table->dropIndex('product_prices_product_id_migration_idx');
            }
            if ($indexes->contains('product_prices_price_table_id_migration_idx')) {
                $table->dropIndex('product_prices_price_table_id_migration_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table): void {
            $table->index('product_id', 'product_prices_product_id_rollback_idx');
            $table->index('price_table_id', 'product_prices_price_table_id_rollback_idx');
        });

        Schema::table('product_prices', function (Blueprint $table): void {
            $table->dropUnique('product_price_table_unique');
            $table->dropIndex('price_table_product_idx');
            $table->decimal('minimum_quantity', 15, 3)->default(1);
        });

        Schema::table('product_prices', function (Blueprint $table): void {
            $table->unique(['product_id', 'price_table_id', 'minimum_quantity'], 'product_price_tier_unique');
            $table->index(['price_table_id', 'product_id', 'minimum_quantity'], 'price_table_product_qty_idx');
        });

        Schema::table('product_prices', function (Blueprint $table): void {
            $table->dropIndex('product_prices_product_id_rollback_idx');
            $table->dropIndex('product_prices_price_table_id_rollback_idx');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['minimum_quantity', 'quantity_multiple', 'allows_fractional_quantity']);
        });
    }
};
