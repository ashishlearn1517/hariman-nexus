<?php

use App\Models\EmailSetting;
use App\Models\User;

function validEmailSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'mail_host' => 'smtp.gmail.com',
        'mail_port' => 465,
        'mail_encryption' => 'ssl',
        'mail_username' => 'sender@hariman.com',
        'mail_password' => 'app-password',
        'mail_from_address' => 'sender@hariman.com',
        'mail_from_name' => 'Hariman Nexus',
        'mail_cc_address' => 'accounts@hariman.com',
        'test_email_recipient' => 'test@hariman.com',
        'invoice_email_subject' => 'Invoice {invoice_no}',
        'invoice_email_body' => 'Dear {client_name}, invoice {invoice_no} is attached.',
        'reminder_email_subject' => 'Reminder {invoice_no}',
        'reminder_email_body' => 'Dear {client_name}, this is a reminder.',
        'overdue_email_subject' => 'Overdue {invoice_no}',
        'overdue_email_body' => 'Dear {client_name}, this invoice is overdue.',
        'quotation_email_subject' => 'Quotation {quotation_no}',
        'quotation_email_body' => 'Dear {client_name}, quotation {quotation_no} is attached.',
    ], $overrides);
}

test('email settings page requires authentication', function () {
    $response = $this->get('/settings/email');

    $response->assertRedirect(route('login', absolute: false));
});

test('authenticated users can view email settings page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/email');

    $response->assertOk();
    $response->assertSee('Email Settings');
    $response->assertSee('SMTP Account');
    $response->assertSee('Email Templates');
});

test('smtp and template fields are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/email', []);

    $response->assertSessionHasErrors([
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'invoice_email_subject',
        'invoice_email_body',
    ]);
});

test('authenticated users can save email settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/email', validEmailSettingsPayload());

    $response->assertRedirect(route('settings.email.edit', absolute: false));
    $setting = EmailSetting::first();

    expect($setting)->not->toBeNull()
        ->and($setting->mail_host)->toBe('smtp.gmail.com')
        ->and($setting->mail_password)->toBe('app-password')
        ->and($setting->invoice_email_subject)->toBe('Invoice {invoice_no}');
});

test('blank password keeps the saved password when updating email settings', function () {
    $user = User::factory()->create();
    EmailSetting::create(array_merge(EmailSetting::defaults(), [
        'mail_host' => 'smtp.gmail.com',
        'mail_username' => 'sender@hariman.com',
        'mail_password' => 'old-password',
        'mail_from_address' => 'sender@hariman.com',
    ]));

    $response = $this->actingAs($user)->patch('/settings/email', validEmailSettingsPayload([
        'mail_host' => 'smtp.hostinger.com',
        'mail_password' => '',
    ]));

    $response->assertRedirect(route('settings.email.edit', absolute: false));
    $setting = EmailSetting::first();

    expect($setting->mail_host)->toBe('smtp.hostinger.com')
        ->and($setting->mail_password)->toBe('old-password');
});
