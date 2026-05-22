<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numbering_settings', function (Blueprint $table) {
            $table->id();
            $table->string('singleton_key')->default('default')->unique();
            $table->string('separator', 3)->default('-');
            $table->unsignedTinyInteger('padding')->default(4);
            $table->boolean('include_year_for_clients')->default(true);
            $table->boolean('include_year_for_invoices')->default(true);
            $table->boolean('include_year_for_quotations')->default(true);
            $table->string('local_client_prefix', 10)->default('LC');
            $table->string('abroad_client_prefix', 10)->default('AC');
            $table->string('product_prefix', 10)->default('PROD');
            $table->string('invoice_prefix', 10)->default('INV');
            $table->string('quotation_prefix', 10)->default('QUO');
            $table->unsignedInteger('next_local_client_number')->default(1);
            $table->unsignedInteger('next_abroad_client_number')->default(1);
            $table->unsignedInteger('next_product_number')->default(1);
            $table->unsignedInteger('next_invoice_number')->default(1);
            $table->unsignedInteger('next_quotation_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_settings');
    }
};
