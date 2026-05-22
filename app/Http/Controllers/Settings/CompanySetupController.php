<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanySetupController extends Controller
{
    public function edit(): View
    {
        return view('settings.company.edit', [
            'company' => CompanySetting::current(),
            'countries' => CompanySetting::countryOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone_country' => ['nullable', 'string', Rule::in(array_keys(CompanySetting::countryOptions()))],
            'company_phone_local' => ['nullable', 'digits_between:6,15'],
            'company_location_country' => ['nullable', 'string', Rule::in(array_keys(CompanySetting::countryOptions()))],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_registration_number' => ['nullable', 'string', 'max:255'],
            'payment_label' => ['nullable', 'string', 'max:255'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'bank_details' => ['nullable', 'string', 'max:3000'],
            'company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'payment_qr' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $countries = CompanySetting::countryOptions();
        $company = CompanySetting::current();

        $phoneCountry = $validated['company_phone_country'] ?? null;
        $locationCountry = $validated['company_location_country'] ?? null;

        $company->fill([
            'singleton_key' => CompanySetting::SINGLETON_KEY,
            'company_name' => $validated['company_name'],
            'company_email' => $validated['company_email'] ?? null,
            'company_phone_country' => $phoneCountry,
            'company_phone_code' => $phoneCountry ? $countries[$phoneCountry]['code'] : null,
            'company_phone_local' => $validated['company_phone_local'] ?? null,
            'company_location_country' => $locationCountry,
            'company_location' => $locationCountry ? $countries[$locationCountry]['label'] : null,
            'website' => $validated['website'] ?? null,
            'tax_registration_number' => $validated['tax_registration_number'] ?? null,
            'payment_label' => $validated['payment_label'] ?? null,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'bank_details' => $validated['bank_details'] ?? null,
        ]);

        if ($request->hasFile('company_logo')) {
            $company->fill($this->storeUpload($request, 'company_logo', 'company-logo'));
        }

        if ($request->hasFile('payment_qr')) {
            $company->fill($this->storeUpload($request, 'payment_qr', 'payment-qr'));
        }

        $company->save();

        return redirect()
            ->route('settings.company.edit')
            ->with('status', 'company-saved');
    }

    /**
     * @return array<string, string>
     */
    private function storeUpload(Request $request, string $field, string $baseName): array
    {
        $uploadDirectory = public_path('uploads/settings');
        File::ensureDirectoryExists($uploadDirectory);

        foreach (glob($uploadDirectory.DIRECTORY_SEPARATOR.$baseName.'.*') ?: [] as $existingFile) {
            File::delete($existingFile);
        }

        $file = $request->file($field);
        $extension = $file->extension();
        $filename = $baseName.'.'.$extension;
        $file->move($uploadDirectory, $filename);

        $prefix = $field === 'company_logo' ? 'company_logo' : 'payment_qr';

        return [
            $prefix.'_path' => $uploadDirectory.DIRECTORY_SEPARATOR.$filename,
            $prefix.'_web_path' => 'uploads/settings/'.$filename,
        ];
    }
}
