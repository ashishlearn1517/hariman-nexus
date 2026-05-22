<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        return view('settings.currencies.index', [
            'currencies' => Currency::query()
                ->orderByDesc('is_default')
                ->orderBy('code')
                ->paginate(10)
                ->withQueryString(),
            'currencyOptions' => Currency::currencyOptions(),
            'statuses' => Currency::statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedCurrency($request);
        $validated['code'] = strtoupper($validated['code']);
        $validated['name'] = Currency::currencyOptions()[$validated['code']]['name'];
        $validated['symbol'] = Currency::currencyOptions()[$validated['code']]['symbol'];
        $validated['is_default'] = $request->boolean('is_default');

        DB::transaction(function () use ($validated): void {
            if ($validated['is_default']) {
                Currency::query()->update(['is_default' => false]);
                $validated['status'] = Currency::STATUS_ACTIVE;
                $validated['exchange_rate'] = 1;
            }

            Currency::create($validated);
        });

        return redirect()
            ->route('settings.currencies.index')
            ->with('status', 'currency-created');
    }

    public function edit(Currency $currency): View
    {
        return view('settings.currencies.edit', [
            'currency' => $currency,
            'currencyOptions' => Currency::currencyOptions(),
            'statuses' => Currency::statusOptions(),
        ]);
    }

    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $validated = $this->validatedCurrency($request, $currency);
        $validated['code'] = strtoupper($validated['code']);
        $validated['name'] = Currency::currencyOptions()[$validated['code']]['name'];
        $validated['symbol'] = Currency::currencyOptions()[$validated['code']]['symbol'];
        $validated['is_default'] = $request->boolean('is_default');

        DB::transaction(function () use ($currency, $validated): void {
            if ($validated['is_default']) {
                Currency::query()
                    ->whereKeyNot($currency->id)
                    ->update(['is_default' => false]);

                $validated['status'] = Currency::STATUS_ACTIVE;
                $validated['exchange_rate'] = 1;
            }

            $currency->update($validated);
        });

        return redirect()
            ->route('settings.currencies.index')
            ->with('status', 'currency-saved');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCurrency(Request $request, ?Currency $currency = null): array
    {
        if ($request->filled('code')) {
            $request->merge(['code' => strtoupper((string) $request->input('code'))]);
        }

        return $request->validate([
            'code' => [
                'required',
                'string',
                'size:3',
                Rule::in(array_keys(Currency::currencyOptions())),
                Rule::unique('currencies', 'code')->ignore($currency),
            ],
            'exchange_rate' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'string', Rule::in(array_keys(Currency::statusOptions()))],
        ]);
    }
}
