<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table): void {
            $table->unsignedSmallInteger('level')->default(1)->after('name');
            $table->string('coverage_type', 30)->default('municipalities')->after('state');
        });

        Schema::create('region_municipalities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('ibge_code', 7);
            $table->string('name');
            $table->char('state', 2);
            $table->string('microregion_name')->nullable();
            $table->string('mesoregion_name')->nullable();
            $table->timestamps();

            $table->unique(['region_id', 'ibge_code']);
            $table->index(['ibge_code', 'state']);
        });

        Schema::table('customer_addresses', function (Blueprint $table): void {
            $table->string('municipality_ibge_code', 7)->nullable()->after('state');
            $table->index('municipality_ibge_code');
        });

        DB::table('regions')
            ->whereNull('city')
            ->update(['coverage_type' => 'state_remainder', 'level' => 2]);
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table): void {
            $table->dropIndex(['municipality_ibge_code']);
            $table->dropColumn('municipality_ibge_code');
        });

        Schema::dropIfExists('region_municipalities');

        Schema::table('regions', function (Blueprint $table): void {
            $table->dropColumn(['level', 'coverage_type']);
        });
    }
};
