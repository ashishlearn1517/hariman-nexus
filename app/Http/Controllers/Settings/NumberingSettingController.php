<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\NumberingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NumberingSettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.numbering.edit', [
            'numbering' => NumberingSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'separator' => ['required', 'string', 'max:3'],
            'padding' => ['required', 'integer', 'min:2', 'max:10'],
            'local_client_prefix' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'abroad_client_prefix' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'product_prefix' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'invoice_prefix' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'quotation_prefix' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'next_local_client_number' => ['required', 'integer', 'min:1'],
            'next_abroad_client_number' => ['required', 'integer', 'min:1'],
            'next_product_number' => ['required', 'integer', 'min:1'],
            'next_invoice_number' => ['required', 'integer', 'min:1'],
            'next_quotation_number' => ['required', 'integer', 'min:1'],
        ]);

        foreach (['local_client_prefix', 'abroad_client_prefix', 'product_prefix', 'invoice_prefix', 'quotation_prefix'] as $field) {
            $validated[$field] = strtoupper($validated[$field]);
        }

        $validated['include_year_for_clients'] = $request->boolean('include_year_for_clients');
        $validated['include_year_for_invoices'] = $request->boolean('include_year_for_invoices');
        $validated['include_year_for_quotations'] = $request->boolean('include_year_for_quotations');

        $numbering = NumberingSetting::current();
        $numbering->fill(array_merge($validated, [
            'singleton_key' => NumberingSetting::SINGLETON_KEY,
        ]));
        $numbering->save();

        return redirect()
            ->route('settings.numbering.edit')
            ->with('status', 'numbering-saved');
    }
}
