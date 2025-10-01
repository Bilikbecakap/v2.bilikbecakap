<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    public function index()
    {
        $latestProjects = Project::query()
            ->where('status', 'completed') // atau 'published' sesuai dengan status di model
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'slug' => $project->slug ?? str($project->title)->slug(),
                    'title' => $project->title,
                    'description' => $project->description,
                    'category' => $project->category,
                    'categoryColor' => $this->getCategoryColor($project->category),
                    'date' => $project->created_at->format('M Y'),
                    'techStack' => $project->technologies ?? [],
                    'image' => $project->image ? asset('storage/' . $project->image) : null,
                    'gallery' => $project->gallery ? collect($project->gallery)->map(fn($img) => asset('storage/' . $img)) : [],
                ];
            });

        return Inertia::render('Welcome', [
            'latestProjects' => $latestProjects
        ]);
    }

    private function getCategoryColor($category)
    {
        $colors = [
            'E-Commerce' => 'bg-blue-500/20 text-blue-400',
            'Web Development' => 'bg-green-500/20 text-green-400',
            'AI/ML' => 'bg-purple-500/20 text-purple-400',
            'Data Analytics' => 'bg-orange-500/20 text-orange-400',
            'Mobile App' => 'bg-pink-500/20 text-pink-400',
            'Web System' => 'bg-cyan-500/20 text-cyan-400',
            'Design' => 'bg-red-500/20 text-red-400',
            'Academic' => 'bg-yellow-500/20 text-yellow-400',
        ];

        return $colors[$category] ?? 'bg-gray-500/20 text-gray-400';
    }
}
