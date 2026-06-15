<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->uuid('client_reference')->nullable();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('sales_representative_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('price_table_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 50);
            $table->string('status', 50);
            $table->decimal('subtotal', 15, 2);
            $table->json('discounts')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->string('source', 50);
            $table->dateTime('order_date');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'order_number']);
            $table->unique(['company_id', 'client_reference']);
            $table->index(['company_id', 'status', 'order_date']);
            $table->index(['sales_representative_id', 'order_date']);
            $table->index(['customer_id', 'order_date']);
            $table->index(['company_id', 'updated_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2);
            $table->json('discounts')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->timestamps();

            $table->index(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
