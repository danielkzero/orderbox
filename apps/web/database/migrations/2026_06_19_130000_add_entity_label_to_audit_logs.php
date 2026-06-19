<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->string('entity_label', 255)->nullable()->after('entity_id');
            $table->index(['company_id', 'entity_label']);
        });

        DB::table('audit_logs')
            ->where('entity_type', 'Order')
            ->orderBy('id')
            ->eachById(function (object $log): void {
                $orderNumber = DB::table('orders')
                    ->where('company_id', $log->company_id)
                    ->where('id', $log->entity_id)
                    ->value('order_number');

                if ($orderNumber) {
                    DB::table('audit_logs')
                        ->where('id', $log->id)
                        ->update(['entity_label' => $orderNumber]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex(['company_id', 'entity_label']);
            $table->dropColumn('entity_label');
        });
    }
};
