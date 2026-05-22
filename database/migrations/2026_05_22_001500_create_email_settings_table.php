<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('singleton_key')->default('default')->unique();
            $table->string('mail_host');
            $table->unsignedSmallInteger('mail_port');
            $table->string('mail_encryption', 10);
            $table->string('mail_username');
            $table->text('mail_password')->nullable();
            $table->string('mail_from_address');
            $table->string('mail_from_name');
            $table->string('mail_cc_address')->nullable();
            $table->string('test_email_recipient')->nullable();
            $table->string('invoice_email_subject');
            $table->text('invoice_email_body');
            $table->string('reminder_email_subject');
            $table->text('reminder_email_body');
            $table->string('overdue_email_subject');
            $table->text('overdue_email_body');
            $table->string('quotation_email_subject');
            $table->text('quotation_email_body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
