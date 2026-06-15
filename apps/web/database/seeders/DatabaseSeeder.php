<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        $company = Company::query()->firstOrCreate(
            ['document' => env('COMPANY_DOCUMENT', '00000000000000')],
            [
                'corporate_name' => env('COMPANY_NAME', 'OrderBox Development'),
                'trade_name' => env('COMPANY_NAME', 'OrderBox Development'),
                'email' => $email,
                'active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'company_id' => $company->id,
                'name' => env('ADMIN_NAME', 'OrderBox Admin'),
                'password' => $password,
                'role' => 'Admin',
                'active' => true,
            ],
        );
    }
}
