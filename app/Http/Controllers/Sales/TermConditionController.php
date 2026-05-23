<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\TermCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TermConditionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('sales.terms.index', [
            'terms' => TermCondition::query()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => TermCondition::statusOptions(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TermCondition::create($this->validatedTerm($request));

        return redirect()
            ->route('sales.terms.index')
            ->with('status', 'term-created');
    }

    public function edit(TermCondition $term): View
    {
        return view('sales.terms.edit', [
            'term' => $term,
            'statuses' => TermCondition::statusOptions(),
        ]);
    }

    public function update(Request $request, TermCondition $term): RedirectResponse
    {
        $term->update($this->validatedTerm($request));

        return redirect()
            ->route('sales.terms.index')
            ->with('status', 'term-saved');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedTerm(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys(TermCondition::statusOptions()))],
        ]);
    }
}
