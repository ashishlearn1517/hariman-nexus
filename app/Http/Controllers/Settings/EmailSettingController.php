<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmailSettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.email.edit', [
            'emailSetting' => EmailSetting::current(),
            'encryptionOptions' => EmailSetting::encryptionOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $emailSetting = EmailSetting::current();
        $validated = $this->validatedEmailSetting($request, $emailSetting->exists);

        if (($validated['mail_password'] ?? '') === '') {
            unset($validated['mail_password']);
        }

        $emailSetting->fill(array_merge($validated, [
            'singleton_key' => EmailSetting::SINGLETON_KEY,
        ]));
        $emailSetting->save();

        return redirect()
            ->route('settings.email.edit')
            ->with('status', 'email-saved');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedEmailSetting(Request $request, bool $hasExistingSettings): array
    {
        return $request->validate([
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'integer', 'between:1,65535'],
            'mail_encryption' => ['required', 'string', Rule::in(array_keys(EmailSetting::encryptionOptions()))],
            'mail_username' => ['required', 'email', 'max:255'],
            'mail_password' => [$hasExistingSettings ? 'nullable' : 'required', 'string', 'max:1000'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'mail_cc_address' => ['nullable', 'email', 'max:255'],
            'test_email_recipient' => ['nullable', 'email', 'max:255'],
            'invoice_email_subject' => ['required', 'string', 'max:255'],
            'invoice_email_body' => ['required', 'string'],
            'reminder_email_subject' => ['required', 'string', 'max:255'],
            'reminder_email_body' => ['required', 'string'],
            'overdue_email_subject' => ['required', 'string', 'max:255'],
            'overdue_email_body' => ['required', 'string'],
            'quotation_email_subject' => ['required', 'string', 'max:255'],
            'quotation_email_body' => ['required', 'string'],
        ]);
    }
}
