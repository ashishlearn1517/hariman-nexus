<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('sales.services.index', [
            'services' => Service::query()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('short_name', 'like', "%{$search}%")
                        ->orWhere('long_name', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => Service::statusOptions(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($this->validatedService($request));

        return redirect()
            ->route('sales.services.index')
            ->with('status', 'service-created');
    }

    public function edit(Service $service): View
    {
        return view('sales.services.edit', [
            'service' => $service,
            'statuses' => Service::statusOptions(),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validatedService($request));

        return redirect()
            ->route('sales.services.index')
            ->with('status', 'service-saved');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedService(Request $request): array
    {
        return $request->validate([
            'short_name' => ['required', 'string', 'max:80'],
            'long_name' => ['required', 'string', 'max:255'],
            'default_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(array_keys(Service::statusOptions()))],
        ]);
    }
}
