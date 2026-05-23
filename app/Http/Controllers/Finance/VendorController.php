<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('finance.vendors.index', [
            'vendors' => Vendor::query()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('vendor_code', 'like', "%{$search}%")
                        ->orWhere('vendor_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('tax_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => Vendor::statusOptions(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $vendor = Vendor::create(array_merge($this->validatedVendor($request), [
                'vendor_code' => $this->nextVendorCode(),
                'created_by' => auth()->id(),
            ]));

            ActivityLogger::log('vendors', 'created', auth()->user()->name.' created Vendor '.$vendor->vendor_code.'.');
        });

        return redirect()
            ->route('finance.vendors.index')
            ->with('status', 'vendor-created');
    }

    public function edit(Vendor $vendor): View
    {
        return view('finance.vendors.edit', [
            'vendor' => $vendor,
            'statuses' => Vendor::statusOptions(),
        ]);
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update($this->validatedVendor($request));
        ActivityLogger::log('vendors', 'updated', auth()->user()->name.' updated Vendor '.$vendor->vendor_code.'.');

        return redirect()
            ->route('finance.vendors.index')
            ->with('status', 'vendor-updated');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        if ($vendor->expenses()->exists()) {
            return redirect()
                ->route('finance.vendors.index')
                ->with('status', 'vendor-delete-blocked');
        }

        $code = $vendor->vendor_code;
        $vendor->delete();
        ActivityLogger::log('vendors', 'archived', auth()->user()->name.' archived Vendor '.$code.'.');

        return redirect()
            ->route('finance.vendors.index')
            ->with('status', 'vendor-deleted');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedVendor(Request $request): array
    {
        return $request->validate([
            'vendor_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(array_keys(Vendor::statusOptions()))],
        ]);
    }

    private function nextVendorCode(): string
    {
        $highest = Vendor::withTrashed()
            ->select('vendor_code')
            ->where('vendor_code', 'like', 'VEN%')
            ->lockForUpdate()
            ->get()
            ->map(fn (Vendor $vendor) => (int) preg_replace('/\D+/', '', $vendor->vendor_code))
            ->max() ?? 0;

        return 'VEN'.str_pad((string) ($highest + 1), 3, '0', STR_PAD_LEFT);
    }
}
