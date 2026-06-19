<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_representative_price_table', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sales_representative_id')->constrained()->restrictOnDelete();
            $table->foreignId('price_table_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['sales_representative_id', 'price_table_id'], 'sr_price_table_unique');
        });

        Schema::create('order_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('channel', 20);
            $table->string('recipient')->nullable();
            $table->string('status', 20);
            $table->text('details')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'order_id', 'created_at']);
        });

        $now = now();
        DB::table('sales_representatives')
            ->where('active', true)
            ->orderBy('id')
            ->eachById(function (object $representative) use ($now): void {
                $rows = DB::table('price_tables')
                    ->where('company_id', $representative->company_id)
                    ->where('active', true)
                    ->pluck('id')
                    ->map(fn (int $priceTableId): array => [
                        'sales_representative_id' => $representative->id,
                        'price_table_id' => $priceTableId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->all();

                if ($rows !== []) {
                    DB::table('sales_representative_price_table')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
        Schema::dropIfExists('sales_representative_price_table');
    }
};
