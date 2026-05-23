<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('numbering_settings', function (Blueprint $table) {
            $table->boolean('include_year_for_expenses')->default(true)->after('include_year_for_quotations');
            $table->string('expense_prefix', 10)->default('EXP')->after('quotation_prefix');
            $table->unsignedInteger('next_expense_number')->default(1)->after('next_quotation_number');
        });
    }

    public function down(): void
    {
        Schema::table('numbering_settings', function (Blueprint $table) {
            $table->dropColumn([
                'include_year_for_expenses',
                'expense_prefix',
                'next_expense_number',
            ]);
        });
    }
};
