<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('payment_method');
            $table->string('receipt_web_path')->nullable()->after('receipt_path');
            $table->string('receipt_original_name')->nullable()->after('receipt_web_path');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_path',
                'receipt_web_path',
                'receipt_original_name',
            ]);
        });
    }
};
