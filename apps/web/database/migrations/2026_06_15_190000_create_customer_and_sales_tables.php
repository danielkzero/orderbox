<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->uuid('client_reference')->nullable();
            $table->string('corporate_name');
            $table->string('trade_name')->nullable();
            $table->string('document', 20);
            $table->string('state_registration', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'document']);
            $table->unique(['company_id', 'client_reference']);
            $table->index(['company_id', 'active', 'corporate_name']);
            $table->index(['company_id', 'updated_at']);
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('type', 50);
            $table->string('zip_code', 10);
            $table->string('street');
            $table->string('number', 20);
            $table->string('complement')->nullable();
            $table->string('district');
            $table->string('city');
            $table->char('state', 2);
            $table->string('country', 100)->default('Brasil');
            $table->boolean('default_address')->default(false);
            $table->timestamps();

            $table->index(['customer_id', 'type', 'default_address']);
        });

        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->boolean('primary_contact')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['customer_id', 'active', 'primary_contact']);
        });

        Schema::create('sales_representatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('code', 50);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
            $table->unique(['company_id', 'code']);
        });

        Schema::create('customer_representatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('sales_representative_id')->constrained()->restrictOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['customer_id', 'sales_representative_id'], 'cr_customer_rep_unique');
            $table->index(['sales_representative_id', 'customer_id'], 'cr_rep_customer_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_representatives');
        Schema::dropIfExists('sales_representatives');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
