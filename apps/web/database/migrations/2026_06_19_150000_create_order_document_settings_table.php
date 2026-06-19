<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_document_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('columns');
            $table->string('image_size', 20)->default('medium');
            $table->string('item_order', 30)->default('insertion_asc');
            $table->boolean('show_customer_address')->default(true);
            $table->boolean('show_commercial_terms')->default(true);
            $table->boolean('show_notes')->default(true);
            $table->boolean('show_subtotal')->default(true);
            $table->boolean('show_total_quantity')->default(false);
            $table->boolean('show_total_weight')->default(false);
            $table->boolean('show_total')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_document_settings');
    }
};
