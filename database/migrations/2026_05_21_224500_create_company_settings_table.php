<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('singleton_key')->default('default')->unique();
            $table->string('company_name');
            $table->string('company_email')->nullable();
            $table->string('company_phone_country', 3)->nullable();
            $table->string('company_phone_code', 10)->nullable();
            $table->string('company_phone_local', 30)->nullable();
            $table->string('company_location_country', 3)->nullable();
            $table->string('company_location')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_registration_number')->nullable();
            $table->string('company_logo_path')->nullable();
            $table->string('company_logo_web_path')->nullable();
            $table->string('payment_label')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('bank_details')->nullable();
            $table->string('payment_qr_path')->nullable();
            $table->string('payment_qr_web_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
