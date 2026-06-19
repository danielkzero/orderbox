<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_terms', function (Blueprint $table): void {
            $table->decimal('minimum_order_amount', 15, 2)
                ->default(0)
                ->after('installment_days');
        });
    }

    public function down(): void
    {
        Schema::table('payment_terms', function (Blueprint $table): void {
            $table->dropColumn('minimum_order_amount');
        });
    }
};
