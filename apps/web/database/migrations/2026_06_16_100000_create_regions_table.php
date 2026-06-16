<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->char('state', 2)->nullable();
            $table->string('city')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'active', 'name']);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->foreignId('region_id')->nullable()->after('company_id')->constrained('regions')->nullOnDelete();
        });

        Schema::table('sales_representatives', function (Blueprint $table): void {
            $table->foreignId('region_id')->nullable()->after('company_id')->constrained('regions')->nullOnDelete();
        });

        Schema::table('price_tables', function (Blueprint $table): void {
            $table->foreignId('region_id')->nullable()->after('company_id')->constrained('regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('price_tables', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::table('sales_representatives', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::dropIfExists('regions');
    }
};
