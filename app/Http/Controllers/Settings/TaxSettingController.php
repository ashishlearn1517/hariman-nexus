<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\TaxSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxSettingController extends Controller
{
    public function index(): View
    {
        return view('settings.taxes.index', [
            'taxes' => TaxSetting::query()
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'statuses' => TaxSetting::statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedTax($request);
        $validated['is_default'] = $request->boolean('is_default');

        DB::transaction(function () use ($validated): void {
            if ($validated['is_default']) {
                TaxSetting::query()->update(['is_default' => false]);
                $validated['status'] = TaxSetting::STATUS_ACTIVE;
            }

            TaxSetting::create($validated);
        });

        return redirect()
            ->route('settings.taxes.index')
            ->with('status', 'tax-created');
    }

    public function edit(TaxSetting $tax): View
    {
        return view('settings.taxes.edit', [
            'tax' => $tax,
            'statuses' => TaxSetting::statusOptions(),
        ]);
    }

    public function update(Request $request, TaxSetting $tax): RedirectResponse
    {
        $validated = $this->validatedTax($request);
        $validated['is_default'] = $request->boolean('is_default');

        DB::transaction(function () use ($tax, $validated): void {
            if ($validated['is_default']) {
                TaxSetting::query()
                    ->whereKeyNot($tax->id)
                    ->update(['is_default' => false]);

                $validated['status'] = TaxSetting::STATUS_ACTIVE;
            }

            $tax->update($validated);
        });

        return redirect()
            ->route('settings.taxes.index')
            ->with('status', 'tax-saved');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedTax(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', Rule::in(array_keys(TaxSetting::statusOptions()))],
        ]);
    }
}
