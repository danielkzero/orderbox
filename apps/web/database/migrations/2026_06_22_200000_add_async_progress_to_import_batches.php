<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_batches', function (Blueprint $table): void {
            $table->string('storage_path')->nullable()->after('original_filename');
            $table->unsignedInteger('processed_rows')->default(0)->after('total_rows');
            $table->timestamp('started_at')->nullable()->after('errors');
        });
    }

    public function down(): void
    {
        Schema::table('import_batches', function (Blueprint $table): void {
            $table->dropColumn(['storage_path', 'processed_rows', 'started_at']);
        });
    }
};
