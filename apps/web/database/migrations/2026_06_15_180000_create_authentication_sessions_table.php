<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authentication_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 20);
            $table->boolean('active_slot')->nullable();
            $table->char('session_key_hash', 64)->nullable()->unique();
            $table->string('web_session_id')->nullable();
            $table->foreignId('personal_access_token_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'channel', 'active_slot']);
            $table->index(['company_id', 'user_id', 'channel']);
            $table->index('last_activity_at');
            $table->index('revoked_at');
        });

        Schema::create('authentication_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 20);
            $table->boolean('remember')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'channel', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authentication_challenges');
        Schema::dropIfExists('authentication_sessions');
    }
};
