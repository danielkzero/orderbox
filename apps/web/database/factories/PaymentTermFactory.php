<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\PaymentTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentTerm> */
class PaymentTermFactory extends Factory
{
    public function definition(): array
    {
        $days = fake()->randomElement([[0], [7], [15], [30], [15, 30], [30, 60, 90]]);
        $code = implode('-', $days);

        return [
            'company_id' => Company::factory(),
            'code' => $code,
            'name' => $days === [0] ? 'À vista' : implode('/', $days).' dias',
            'installment_days' => $days,
            'minimum_order_amount' => fake()->randomElement([0, 100, 300, 500]),
            'description' => fake()->sentence(),
            'sort_order' => 0,
            'active' => true,
        ];
    }
}
