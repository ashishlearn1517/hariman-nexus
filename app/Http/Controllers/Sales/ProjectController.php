<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        return view('sales.projects.index', [
            'projects' => Project::query()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('expected_delivery_time', 'like', "%{$search}%")
                        ->orWhere('awarded_to', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => Project::statusOptions(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProject($request);

        Project::create($validated);

        return redirect()
            ->route('sales.projects.index')
            ->with('status', 'project-created');
    }

    public function edit(Project $project): View
    {
        return view('sales.projects.edit', [
            'project' => $project,
            'statuses' => Project::statusOptions(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validatedProject($request));

        return redirect()
            ->route('sales.projects.index')
            ->with('status', 'project-saved');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProject(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'expected_delivery_time' => ['nullable', 'string', 'max:255'],
            'awarded_to' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Project::statusOptions()))],
        ]);
    }
}
