<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageProjectController extends Controller
{
    public function projects(Request $request)
    {
        $query = Project::query()->where('status', 'completed');

        // Filter by category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Get projects with pagination
        $projects = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString()
            ->through(function ($project) {
                return [
                    'id' => $project->id,
                    'slug' => str($project->title)->slug(),
                    'title' => $project->title,
                    'description' => $project->description,
                    'category' => $project->category,
                    'categoryColor' => $this->getCategoryColor($project->category),
                    'date' => $project->created_at->format('M Y'),
                    'techStack' => $project->technologies ?? [],
                    'image' => $project->image ? asset('storage/' . $project->image) : null,
                ];
            });

        // Get available categories for filter
        $categories = Project::where('status', 'completed')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function project($slug)
    {
        // Find project by generated slug from title
        $project = Project::where('status', 'completed')
            ->get()
            ->first(function ($project) use ($slug) {
                $generatedSlug = str($project->title)->slug();
                return $generatedSlug == $slug;
            });

        if (!$project) {
            abort(404);
        }

        // Transform project data for detail view
        $projectData = [
            'id' => $project->id,
            'slug' => str($project->title)->slug(),
            'title' => $project->title,
            'description' => $project->description,
            'category' => $project->category,
            'categoryColor' => $this->getCategoryColor($project->category),
            'status' => $project->status,
            'date' => $project->created_at->format('F Y'),
            'techStack' => $project->technologies ?? [],
            'image' => $project->image ? asset('storage/' . $project->image) : null,
            'gallery' => $project->gallery ? collect($project->gallery)->map(fn($img) => asset('storage/' . $img)) : [],
        ];

        // Get related projects (same category, exclude current)
        $relatedProjects = Project::where('status', 'completed')
            ->where('category', $project->category)
            ->where('id', '!=', $project->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($relatedProject) {
                return [
                    'id' => $relatedProject->id,
                    'slug' => str($relatedProject->title)->slug(),
                    'title' => $relatedProject->title,
                    'description' => $relatedProject->description,
                    'category' => $relatedProject->category,
                    'categoryColor' => $this->getCategoryColor($relatedProject->category),
                    'date' => $relatedProject->created_at->format('M Y'),
                    'techStack' => $relatedProject->technologies ?? [],
                    'image' => $relatedProject->image ? asset('storage/' . $relatedProject->image) : null,
                ];
            });

        // Get random projects to fill if not enough related projects
        $randomProjects = collect();
        if ($relatedProjects->count() < 3) {
            $randomProjects = Project::where('status', 'completed')
                ->where('id', '!=', $project->id)
                ->whereNotIn('id', $relatedProjects->pluck('id'))
                ->inRandomOrder()
                ->limit(3 - $relatedProjects->count())
                ->get()
                ->map(function ($randomProject) {
                    return [
                        'id' => $randomProject->id,
                        'slug' => str($randomProject->title)->slug(),
                        'title' => $randomProject->title,
                        'description' => $randomProject->description,
                        'category' => $randomProject->category,
                        'categoryColor' => $this->getCategoryColor($randomProject->category),
                        'date' => $randomProject->created_at->format('M Y'),
                        'techStack' => $randomProject->technologies ?? [],
                        'image' => $randomProject->image ? asset('storage/' . $randomProject->image) : null,
                    ];
                });
        }

        // Combine related and random projects
        $otherProjects = $relatedProjects->concat($randomProjects)->take(3);

        return Inertia::render('Projects/Show', [
            'project' => $projectData,
            'otherProjects' => $otherProjects,
        ]);
    }

    private function getCategoryColor($category)
    {
        $colors = [
            'E-Commerce' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'Web Development' => 'bg-green-500/20 text-green-400 border-green-500/30',
            'AI/ML' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            'Data Analytics' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
            'Mobile App' => 'bg-pink-500/20 text-pink-400 border-pink-500/30',
            'Web System' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
            'Design' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'Academic' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
        ];

        return $colors[$category] ?? 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }
}
