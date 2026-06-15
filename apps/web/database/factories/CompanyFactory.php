<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'corporate_name' => fake()->company(),
            'trade_name' => fake()->company(),
            'document' => fake()->unique()->numerify('##############'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'active' => true,
        ];
    }
}
