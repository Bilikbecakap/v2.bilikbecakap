<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminProjectsController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::query()
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                $query->byStatus($request->status);
            })
            ->when($request->category && $request->category !== 'all', function ($query) use ($request) {
                $query->byCategory($request->category);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'status', 'category']),
            'statuses' => Project::getStatuses(),
            'categories' => Project::getCategories(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Projects/Create', [
            'statuses' => Project::getStatuses(),
            'categories' => Project::getCategories(),
            'technologies' => Project::getTechnologies(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:' . implode(',', array_keys(Project::getStatuses())),
            'category' => 'required|in:' . implode(',', Project::getCategories()),
            'technologies' => 'required|array',
            'technologies.*' => 'required|in:' . implode(',', Project::getTechnologies()),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('projects', 'public');
        }

        if ($request->hasFile('gallery')) {
            $galleryImages = [];
            foreach ($request->file('gallery') as $file) {
                $galleryImages[] = $file->store('projects', 'public');
            }
            $validated['gallery'] = $galleryImages;
        }

        Project::create($validated);

        return Redirect::route('admin-projects')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        return Inertia::render('Admin/Projects/Show', [
            'project' => $project,
        ]);
    }

    public function edit(Project $project)
    {
        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project,
            'statuses' => Project::getStatuses(),
            'categories' => Project::getCategories(),
            'technologies' => Project::getTechnologies(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        // Validasi data
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:' . implode(',', array_keys(Project::getStatuses())),
            'category' => 'nullable|in:' . implode(',', Project::getCategories()),
            'technologies' => 'nullable|array',
            'technologies.*' => 'nullable|in:' . implode(',', Project::getTechnologies()),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_existing_gallery' => 'nullable|array',
            'remove_existing_gallery.*' => 'string',
        ]);

        // Siapkan data untuk update
        $updateData = [
            'title' => $validated['title'] ?? $project->title,
            'description' => $validated['description'] ?? $project->description,
            'status' => $validated['status'] ?? $project->status,
            'category' => $validated['category'] ?? $project->category,
            'technologies' => isset($validated['technologies']) && is_array($validated['technologies']) ? array_values($validated['technologies']) : ($project->technologies ?? []),
            'image' => $project->image,
            'gallery' => $project->gallery ?? [],
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $updateData['image'] = $request->file('image')->store('projects', 'public');
        }

        // Handle gallery
        $currentGallery = $project->gallery ?? [];
        if ($request->has('remove_existing_gallery')) {
            $imagesToRemove = $request->input('remove_existing_gallery', []);
            foreach ($imagesToRemove as $imageToRemove) {
                if (in_array($imageToRemove, $currentGallery)) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentGallery = array_filter($currentGallery, fn($img) => $img !== $imageToRemove);
                }
            }
            $updateData['gallery'] = array_values($currentGallery);
        }

        if ($request->hasFile('gallery')) {
            $newGalleryImages = [];
            foreach ($request->file('gallery') as $file) {
                $newGalleryImages[] = $file->store('projects', 'public');
            }
            $updateData['gallery'] = array_merge($currentGallery, $newGalleryImages);
        }

        // Debug sementara untuk cek data
        Log::info('Update data prepared:', $updateData);

        // Update model
        $project->fill($updateData);
        $project->save(); // Force save untuk pastikan perubahan tersimpan

        return Redirect::route('admin-projects')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        if ($project->image) {
            Storage::disk('public')->delete($project->image);
        }

        if ($project->gallery) {
            foreach ($project->gallery as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $project->delete();

        return Redirect::route('admin-projects')
            ->with('success', 'Project deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,update_status',
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id',
            'status' => 'required_if:action,update_status|in:' . implode(',', array_keys(Project::getStatuses())),
        ]);

        $projects = Project::whereIn('id', $validated['project_ids']);

        switch ($validated['action']) {
            case 'delete':
                $projects->get()->each(function ($project) {
                    if ($project->image) {
                        Storage::disk('public')->delete($project->image);
                    }
                    if ($project->gallery) {
                        foreach ($project->gallery as $image) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                });
                $projects->delete();
                $message = 'Projects deleted successfully.';
                break;

            case 'update_status':
                $projects->update(['status' => $validated['status']]);
                $message = 'Projects status updated successfully.';
                break;
        }

        return Redirect::route('admin-projects')
            ->with('success', $message);
    }
}
