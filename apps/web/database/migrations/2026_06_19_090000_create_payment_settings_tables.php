<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'active', 'sort_order']);
        });

        Schema::create('payment_terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->json('installment_days');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'active', 'sort_order']);
        });

        $now = now();
        $companies = DB::table('companies')->pluck('id');
        $methods = [
            ['code' => 'boleto', 'name' => 'Boleto', 'sort_order' => 10],
            ['code' => 'avista', 'name' => 'À vista', 'sort_order' => 20],
            ['code' => 'pix', 'name' => 'PIX', 'sort_order' => 30],
            ['code' => 'cartao', 'name' => 'Cartão', 'sort_order' => 40],
        ];
        $terms = [
            ['code' => 'avista', 'name' => 'À vista', 'installment_days' => [0], 'sort_order' => 10],
            ['code' => '7', 'name' => '7 dias', 'installment_days' => [7], 'sort_order' => 20],
            ['code' => '15', 'name' => '15 dias', 'installment_days' => [15], 'sort_order' => 30],
            ['code' => '30', 'name' => '30 dias', 'installment_days' => [30], 'sort_order' => 40],
            ['code' => '15/30', 'name' => '15/30 dias', 'installment_days' => [15, 30], 'sort_order' => 50],
            ['code' => '15/30/45', 'name' => '15/30/45 dias', 'installment_days' => [15, 30, 45], 'sort_order' => 60],
            ['code' => '15/30/45/60', 'name' => '15/30/45/60 dias', 'installment_days' => [15, 30, 45, 60], 'sort_order' => 70],
            ['code' => '15/30/45/60/75', 'name' => '15/30/45/60/75 dias', 'installment_days' => [15, 30, 45, 60, 75], 'sort_order' => 80],
            ['code' => '15/30/45/60/75/90', 'name' => '15/30/45/60/75/90 dias', 'installment_days' => [15, 30, 45, 60, 75, 90], 'sort_order' => 90],
        ];

        foreach ($companies as $companyId) {
            DB::table('payment_methods')->insert(array_map(fn (array $method): array => $method + [
                'company_id' => $companyId,
                'description' => null,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $methods));

            DB::table('payment_terms')->insert(array_map(fn (array $term): array => [
                ...$term,
                'installment_days' => json_encode($term['installment_days']),
                'company_id' => $companyId,
                'description' => null,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], $terms));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_terms');
        Schema::dropIfExists('payment_methods');
    }
};
