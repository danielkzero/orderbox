<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\PaymentTerm;
use Illuminate\Database\Seeder;

class PaymentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(fn (Company $company) => $this->seedCompany($company));
    }

    public function seedCompany(Company $company): void
    {
        collect([
            ['code' => 'boleto', 'name' => 'Boleto', 'sort_order' => 10],
            ['code' => 'avista', 'name' => 'À vista', 'sort_order' => 20],
            ['code' => 'pix', 'name' => 'PIX', 'sort_order' => 30],
            ['code' => 'cartao', 'name' => 'Cartão', 'sort_order' => 40],
        ])->each(fn (array $method) => PaymentMethod::query()->updateOrCreate(
            ['company_id' => $company->id, 'code' => $method['code']],
            $method + ['active' => true],
        ));

        collect([
            ['code' => 'avista', 'name' => 'À vista', 'installment_days' => [0], 'sort_order' => 10],
            ['code' => '7', 'name' => '7 dias', 'installment_days' => [7], 'sort_order' => 20],
            ['code' => '15', 'name' => '15 dias', 'installment_days' => [15], 'sort_order' => 30],
            ['code' => '30', 'name' => '30 dias', 'installment_days' => [30], 'sort_order' => 40],
            ['code' => '15/30', 'name' => '15/30 dias', 'installment_days' => [15, 30], 'sort_order' => 50],
            ['code' => '15/30/45', 'name' => '15/30/45 dias', 'installment_days' => [15, 30, 45], 'sort_order' => 60],
            ['code' => '15/30/45/60', 'name' => '15/30/45/60 dias', 'installment_days' => [15, 30, 45, 60], 'sort_order' => 70],
            ['code' => '15/30/45/60/75', 'name' => '15/30/45/60/75 dias', 'installment_days' => [15, 30, 45, 60, 75], 'sort_order' => 80],
            ['code' => '15/30/45/60/75/90', 'name' => '15/30/45/60/75/90 dias', 'installment_days' => [15, 30, 45, 60, 75, 90], 'sort_order' => 90],
        ])->each(fn (array $term) => PaymentTerm::query()->updateOrCreate(
            ['company_id' => $company->id, 'code' => $term['code']],
            $term + ['active' => true],
        ));
    }
}
