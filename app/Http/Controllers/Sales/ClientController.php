<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('sales.clients.index', [
            'clients' => Client::query()
                ->with('project')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('client_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('client_type', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('project', fn ($projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"));
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'projects' => Project::query()
                ->where('status', Project::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(),
            'types' => Client::typeOptions(),
            'statuses' => Client::statusOptions(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedClient($request);

        DB::transaction(function () use ($validated): void {
            $generated = $this->generateClientCode($validated['client_type']);

            Client::create(array_merge($validated, [
                'client_code' => $generated['code'],
                'sequence_no' => $generated['sequence'],
            ]));
        });

        return redirect()
            ->route('sales.clients.index')
            ->with('status', 'client-created');
    }

    public function edit(Client $client): View
    {
        return view('sales.clients.edit', [
            'client' => $client->load('project'),
            'projects' => Project::query()
                ->where(function ($query) use ($client): void {
                    $query->where('status', Project::STATUS_ACTIVE)
                        ->orWhere('id', $client->project_id);
                })
                ->orderBy('name')
                ->get(),
            'types' => Client::typeOptions(),
            'statuses' => Client::statusOptions(),
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validatedClient($request));

        return redirect()
            ->route('sales.clients.index')
            ->with('status', 'client-saved');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedClient(Request $request): array
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'client_type' => ['required', 'string', Rule::in(array_keys(Client::typeOptions()))],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9]+$/'],
            'address' => ['required', 'string', 'max:2000'],
            'tax_applicable' => ['nullable', 'boolean'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', Rule::in(array_keys(Client::statusOptions()))],
        ]);

        $validated['tax_applicable'] = (bool) ($validated['tax_applicable'] ?? false);
        $validated['tax_percent'] = $validated['tax_applicable']
            ? ($validated['tax_percent'] ?? 0)
            : 0;

        return $validated;
    }

    /**
     * @return array{code: string, sequence: int}
     */
    private function generateClientCode(string $type): array
    {
        $year = now()->year;
        $prefix = $type === Client::TYPE_ABROAD ? 'AC' : 'LC';

        $highestSequence = Client::query()
            ->where('client_type', $type)
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->max('sequence_no') ?? 0;

        do {
            $highestSequence++;
            $clientCode = $prefix.'-'.$year.'-'.str_pad((string) $highestSequence, 4, '0', STR_PAD_LEFT);
        } while (Client::query()->where('client_code', $clientCode)->exists());

        return [
            'code' => $clientCode,
            'sequence' => $highestSequence,
        ];
    }
}
