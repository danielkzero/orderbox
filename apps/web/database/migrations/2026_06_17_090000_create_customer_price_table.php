<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_price_table', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('price_table_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['customer_id', 'price_table_id']);
            $table->index(['price_table_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_price_table');
    }
};
