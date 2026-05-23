<?php

use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();
            $table->unsignedInteger('sequence_no')->default(0);
            $table->date('expense_date');
            $table->foreignIdFor(ExpenseCategory::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Project::class)->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('vendor_name')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
