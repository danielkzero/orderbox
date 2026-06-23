<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region_price_table', function (Blueprint $table): void {
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_table_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['region_id', 'price_table_id']);
        });

        DB::table('price_tables')
            ->whereNotNull('region_id')
            ->orderBy('id')
            ->each(function (object $priceTable): void {
                DB::table('region_price_table')->insertOrIgnore([
                    'region_id' => $priceTable->region_id,
                    'price_table_id' => $priceTable->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('price_tables', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::table('regions', function (Blueprint $table): void {
            $table->dropUnique(['company_id', 'name']);
            $table->unique(['company_id', 'state', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('price_tables', function (Blueprint $table): void {
            $table->foreignId('region_id')->nullable()->after('company_id')->constrained('regions')->nullOnDelete();
        });

        DB::table('region_price_table')
            ->orderBy('price_table_id')
            ->orderBy('region_id')
            ->get()
            ->groupBy('price_table_id')
            ->each(function ($links, int $priceTableId): void {
                DB::table('price_tables')
                    ->where('id', $priceTableId)
                    ->update(['region_id' => $links->first()->region_id]);
            });

        Schema::dropIfExists('region_price_table');

        Schema::table('regions', function (Blueprint $table): void {
            $table->dropUnique(['company_id', 'state', 'name']);
            $table->unique(['company_id', 'name']);
        });
    }
};
