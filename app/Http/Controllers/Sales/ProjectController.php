<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('sales.projects.index', [
            'projects' => Project::query()
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'statuses' => Project::statusOptions(),
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
