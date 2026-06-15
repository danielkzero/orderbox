<?php

namespace Tests\Feature\Database;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\HydradigitalDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HydradigitalDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_an_operational_hydradigital_dataset(): void
    {
        $company = Company::factory()->create();
        User::factory()->create([
            'company_id' => $company->id,
            'email' => 'danikzero@hotmail.com',
            'role' => 'Admin',
        ]);

        $this->seed(HydradigitalDemoSeeder::class);
        $this->seed(HydradigitalDemoSeeder::class);

        $company->refresh();

        $this->assertSame('hydradigital', $company->trade_name);
        $this->assertSame(4, $company->users()->count());
        $this->assertSame(6, Customer::query()->whereBelongsTo($company)->count());
        $this->assertSame(8, Product::query()->whereBelongsTo($company)->count());
        $this->assertSame(4, Order::query()->whereBelongsTo($company)->count());
        $this->assertSame(8, Order::query()->withCount('items')->get()->sum('items_count'));
    }
}
