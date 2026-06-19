<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\OrderDocumentSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderDocumentSetting> */
class OrderDocumentSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'columns' => OrderDocumentSetting::DEFAULT_COLUMNS,
            'image_size' => 'medium',
            'item_order' => 'insertion_asc',
            'show_customer_address' => true,
            'show_commercial_terms' => true,
            'show_notes' => true,
            'show_subtotal' => true,
            'show_total_quantity' => false,
            'show_total_weight' => false,
            'show_total' => true,
        ];
    }
}
