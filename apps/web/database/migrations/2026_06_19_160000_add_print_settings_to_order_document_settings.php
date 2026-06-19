<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_document_settings', function (Blueprint $table): void {
            $table->json('print_columns')->nullable()->after('columns');
            $table->string('print_image_size', 20)->default('medium')->after('image_size');
            $table->string('print_margin', 20)->default('standard')->after('item_order');
            $table->boolean('print_customer_address')->default(true)->after('show_customer_address');
            $table->boolean('print_commercial_terms')->default(true)->after('show_commercial_terms');
            $table->boolean('print_notes')->default(true)->after('show_notes');
            $table->boolean('print_subtotal')->default(true)->after('show_subtotal');
            $table->boolean('print_total_quantity')->default(false)->after('show_total_quantity');
            $table->boolean('print_total_weight')->default(false)->after('show_total_weight');
            $table->boolean('print_total')->default(true)->after('show_total');
        });
    }

    public function down(): void
    {
        Schema::table('order_document_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'print_columns',
                'print_image_size',
                'print_margin',
                'print_customer_address',
                'print_commercial_terms',
                'print_notes',
                'print_subtotal',
                'print_total_quantity',
                'print_total_weight',
                'print_total',
            ]);
        });
    }
};
