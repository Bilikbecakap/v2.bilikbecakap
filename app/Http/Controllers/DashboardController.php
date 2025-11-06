<?php

namespace App\Http\Controllers;

use App\Models\Kamus;
use App\Models\DatasetTranslate;
use App\Models\Artikel;
use App\Models\ModulPembelajaran;
use App\Models\Quizzes;
use App\Models\QuizAttempt;
use App\Models\GambarGaleri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. STATISTICS - Total counts dengan breakdown
        $stats = [
            'kamus' => [
                'total' => Kamus::count(),
                'aktif' => Kamus::where('status', 1)->count(),
                'pending' => Kamus::where('status', 3)->count(),
                'ditolak' => Kamus::where('status', 2)->count(),
            ],
            'dataset' => [
                'total' => DatasetTranslate::count(),
                'recent' => DatasetTranslate::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'artikel' => [
                'total' => Artikel::count(),
                'published' => Artikel::where('status', 'published')->count(),
                'pending' => Artikel::where('status', 'pending')->count(),
                'draft' => Artikel::where('status', 'draft')->count(),
            ],
            'modul' => [
                'total' => ModulPembelajaran::count(),
                'published' => ModulPembelajaran::where('status', 'published')->count(),
                'draft' => ModulPembelajaran::where('status', 'draft')->count(),
            ],
            'quiz' => [
                'total' => Quizzes::count(),
                'active' => Quizzes::where('status', 'active')->count(),
                'inactive' => Quizzes::where('status', 'inactive')->count(),
                'umum' => Quizzes::where('type', 'umum')->count(),
                'modul' => Quizzes::where('type', 'modul')->count(),
            ],
            'quiz_attempts' => [
                'total' => QuizAttempt::whereNotNull('completed_at')->count(),
                'today' => QuizAttempt::whereNotNull('completed_at')
                    ->whereDate('completed_at', today())->count(),
                'average_score' => round(QuizAttempt::whereNotNull('completed_at')->avg('score'), 2) ?? 0,
            ],
            'galeri' => [
                'total' => GambarGaleri::count(),
            ],
        ];

        // 2. PENDING ITEMS - Yang perlu approval
        $pendingItems = [
            'kamus' => Kamus::where('status', 3)
                ->with('creator')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->bahasa_melayu . ' → ' . $item->bahasa_indonesia,
                        'creator' => $item->creator?->name,
                        'created_at' => $item->created_at,
                        'type' => 'kamus',
                        'url' => route('kamus.index', ['status' => 3]),
                    ];
                }),
            'artikel' => Artikel::where('status', 'pending')
                ->with('creator')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->judul_indonesia ?: $item->judul_melayu ?: $item->judul_english,
                        'creator' => $item->creator?->name,
                        'created_at' => $item->created_at,
                        'type' => 'artikel',
                        'url' => route('artikel.show', $item->id),
                    ];
                }),
        ];

        // 3. RECENT ACTIVITY - 10 aktivitas terakhir
        $recentActivities = Activity::with(['causer', 'subject'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer' => $activity->causer?->name,
                    'subject_type' => class_basename($activity->subject_type),
                    'created_at' => $activity->created_at,
                ];
            });

        // 4. CHARTS DATA - Trend 7 hari terakhir
        $chartData = [
            'labels' => [],
            'kamus' => [],
            'artikel' => [],
            'quiz_attempts' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData['labels'][] = $date->format('d M');
            
            $chartData['kamus'][] = Kamus::whereDate('created_at', $date->toDateString())->count();
            $chartData['artikel'][] = Artikel::whereDate('created_at', $date->toDateString())->count();
            $chartData['quiz_attempts'][] = QuizAttempt::whereDate('completed_at', $date->toDateString())->count();
        }

        // 5. STATUS DISTRIBUTION - Untuk pie/bar charts
        $statusDistribution = [
            'kamus' => [
                'labels' => ['Aktif', 'Pending', 'Ditolak'],
                'data' => [
                    $stats['kamus']['aktif'],
                    $stats['kamus']['pending'],
                    $stats['kamus']['ditolak'],
                ],
            ],
            'artikel' => [
                'labels' => ['Published', 'Pending', 'Draft'],
                'data' => [
                    $stats['artikel']['published'],
                    $stats['artikel']['pending'],
                    $stats['artikel']['draft'],
                ],
            ],
            'quiz_type' => [
                'labels' => ['Quiz Umum', 'Quiz Modul'],
                'data' => [
                    $stats['quiz']['umum'],
                    $stats['quiz']['modul'],
                ],
            ],
        ];

        // 6. RECENT DATA - 5 data terbaru dari setiap modul
        $recentData = [
            'artikel' => Artikel::with('creator')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->judul_indonesia ?: $item->judul_melayu ?: $item->judul_english,
                        'status' => $item->status,
                        'creator' => $item->creator?->name,
                        'views' => $item->views_count ?? 0,
                        'created_at' => $item->created_at,
                        'url' => route('artikel.show', $item->id),
                    ];
                }),
            'modul' => ModulPembelajaran::with('creator')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'status' => $item->status,
                        'creator' => $item->creator?->name,
                        'views' => $item->view_count ?? 0,
                        'created_at' => $item->created_at,
                        'url' => route('modul-pembelajaran.show', $item->id),
                    ];
                }),
            'quiz' => Quizzes::with('modulPembelajaran')
                ->withCount('questions')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'type' => $item->type,
                        'status' => $item->status,
                        'total_questions' => $item->questions_count,
                        'created_at' => $item->created_at,
                        'url' => route('quiz.show', $item->id),
                    ];
                }),
            'kamus' => Kamus::with('creator')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'bahasa_melayu' => $item->bahasa_melayu,
                        'bahasa_indonesia' => $item->bahasa_indonesia,
                        'status' => $item->status,
                        'creator' => $item->creator?->name,
                        'created_at' => $item->created_at,
                    ];
                }),
        ];

        // 7. TOP PERFORMERS
        $topPerformers = [
            'most_viewed_artikel' => Artikel::where('status', 'published')
                ->orderBy('views_count', 'desc')
                ->take(3)
                ->get()
                ->map(function($item) {
                    return [
                        'title' => $item->judul_indonesia ?: $item->judul_melayu ?: $item->judul_english,
                        'views' => $item->views_count ?? 0,
                        'url' => route('artikel.show', $item->id),
                    ];
                }),
            'most_viewed_modul' => ModulPembelajaran::where('status', 'published')
                ->orderBy('view_count', 'desc')
                ->take(3)
                ->get()
                ->map(function($item) {
                    return [
                        'title' => $item->title,
                        'views' => $item->view_count ?? 0,
                        'url' => route('modul-pembelajaran.show', $item->id),
                    ];
                }),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'pendingItems' => $pendingItems,
            'recentActivities' => $recentActivities,
            'chartData' => $chartData,
            'statusDistribution' => $statusDistribution,
            'recentData' => $recentData,
            'topPerformers' => $topPerformers,
        ]);
    }
}