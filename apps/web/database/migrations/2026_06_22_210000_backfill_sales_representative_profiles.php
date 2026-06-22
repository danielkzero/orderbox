<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('users')
                ->where('role', 'SalesRepresentative')
                ->orderBy('id')
                ->get(['id', 'company_id', 'active', 'created_at', 'updated_at'])
                ->each(function (object $user): void {
                    $exists = DB::table('sales_representatives')
                        ->where('company_id', $user->company_id)
                        ->where('user_id', $user->id)
                        ->exists();

                    if ($exists) {
                        return;
                    }

                    DB::table('sales_representatives')->insert([
                        'company_id' => $user->company_id,
                        'user_id' => $user->id,
                        'code' => $this->availableCode((int) $user->company_id, (int) $user->id),
                        'active' => (bool) $user->active,
                        'created_at' => $user->created_at ?? now(),
                        'updated_at' => $user->updated_at ?? now(),
                    ]);
                });
        });
    }

    public function down(): void
    {
        // Perfis podem receber carteira, pedidos e tabelas após a criação.
        // O rollback não remove dados operacionais.
    }

    private function availableCode(int $companyId, int $userId): string
    {
        $base = 'REP-USR-'.$userId;
        $code = $base;
        $suffix = 2;

        while (DB::table('sales_representatives')
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->exists()) {
            $code = $base.'-'.$suffix;
            $suffix++;
        }

        return $code;
    }
};
