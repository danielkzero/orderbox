<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentMethod> */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Boleto', 'PIX', 'Cartão', 'Transferência', 'Dinheiro']).' '.fake()->unique()->numberBetween(1, 999);

        return [
            'company_id' => Company::factory(),
            'code' => str($name)->slug()->toString(),
            'name' => $name,
            'description' => fake()->sentence(),
            'sort_order' => 0,
            'active' => true,
        ];
    }
}
