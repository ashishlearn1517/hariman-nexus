<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_code')->unique();
            $table->unsignedInteger('sequence_no');
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('client_type')->index();
            $table->string('email');
            $table->string('phone', 30);
            $table->text('address');
            $table->boolean('tax_applicable')->default(false);
            $table->decimal('tax_percent', 10, 2)->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
