<?php

namespace App\Http\Controllers;

use App\Models\Quizzes;
use App\Models\ModulPembelajaran;
use App\Models\MasterMediaMusicQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class QuizController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view quiz', only: ['index', 'show']),
            new Middleware('permission:create quiz', only: ['create', 'store']),
            new Middleware('permission:edit quiz', only: ['edit', 'update']),
            new Middleware('permission:delete quiz', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Quizzes::with(['modulPembelajaran', 'masterMediaMusicQuiz']); // TAMBAH relasi

        // Filter berdasarkan search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Filter berdasarkan type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        if ($request->has('sort') && $request->sort === 'created_at') {
            $direction = $request->direction === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $direction);
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }

        $quizzes = $query->paginate(15)->appends($request->query());

        return Inertia::render('Quiz/Index', [
            'quizzes' => $quizzes,
            'search' => $request->search,
            'type' => $request->type,
            'status' => $request->status,
            'sort' => $request->sort,
            'direction' => $request->direction,
        ]);
    }

    public function create()
    {
        $moduls = ModulPembelajaran::where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title']);

        $musicQuizzes = MasterMediaMusicQuiz::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($music) {
                return [
                    'id' => $music->id,
                    'audio' => $music->audio,
                    'audio_url' => $music->audio_url, 
                    'keterangan' => $music->keterangan,
                    'is_active' => $music->is_active,
                ];
            });

        return Inertia::render('Quiz/Create', [
            'moduls' => $moduls,
            'musicQuizzes' => $musicQuizzes
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048', 
            'master_media_music_quiz_id' => 'nullable|exists:master_media_music_quiz,id', // UBAH
            'duration' => 'required|integer|min:1',
            'type' => 'required|in:umum,modul',
            'modul_pembelajaran_id' => 'required_if:type,modul|nullable|exists:modul_pembelajaran,id',
            'status' => 'required|in:active,inactive',
        ], [
            'title.required' => 'Judul quiz wajib diisi.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Thumbnail harus berformat jpeg, jpg, png, atau webp.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB.',
            'master_media_music_quiz_id.exists' => 'Music quiz yang dipilih tidak valid.', // UBAH
            'duration.required' => 'Durasi quiz wajib diisi.',
            'duration.min' => 'Durasi minimal 1 menit.',
            'type.required' => 'Tipe quiz wajib dipilih.',
            'modul_pembelajaran_id.required_if' => 'Modul pembelajaran wajib dipilih untuk quiz modul.',
            'status.required' => 'Status quiz wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['thumbnail']); 

            // Jika type umum, set modul_pembelajaran_id ke null
            if ($data['type'] === 'umum') {
                $data['modul_pembelajaran_id'] = null;
            }

            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('quizzes/thumbnails', 'public');
                $data['thumbnail'] = $thumbnailPath;
            }


            $quiz = Quizzes::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($quiz)
                ->withProperties([
                    'type' => $data['type'],
                    'status' => $data['status'],
                    'has_thumbnail' => isset($data['thumbnail']),
                    'has_music' => isset($data['master_media_music_quiz_id']), // UBAH
                ])
                ->log('Created new quiz');

            DB::commit();

            return redirect()->route('quiz.index')
                ->with('success', 'Quiz berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus file yang sudah diupload jika terjadi error
            if (isset($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
            
            return back()->withInput()->withErrors(['error' => 'Gagal membuat quiz: ' . $e->getMessage()]);
        }
    }

    public function show(Quizzes $quiz)
    {
        $quiz->load(['modulPembelajaran', 'masterMediaMusicQuiz', 'questions.options']); 

        // Hitung total attempts dan statistics
        $totalAttempts = $quiz->attempts()->count();
        $averageScore = $quiz->attempts()->avg('score');

        return Inertia::render('Quiz/Show', [
            'quiz' => $quiz,
            'totalAttempts' => $totalAttempts,
            'averageScore' => round($averageScore, 2) ?? 0,
        ]);
    }

    public function edit(Quizzes $quiz)
    {
        $quiz->load('masterMediaMusicQuiz'); 

        $moduls = ModulPembelajaran::where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title']);

        $musicQuizzes = MasterMediaMusicQuiz::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Quiz/Edit', [
            'quiz' => $quiz,
            'moduls' => $moduls,
            'musicQuizzes' => $musicQuizzes 
        ]);
    }

    public function update(Request $request, Quizzes $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'master_media_music_quiz_id' => 'nullable|exists:master_media_music_quiz,id',
            'remove_thumbnail' => 'nullable|boolean', 
            'duration' => 'required|integer|min:1',
            'type' => 'required|in:umum,modul',
            'modul_pembelajaran_id' => 'required_if:type,modul|nullable|exists:modul_pembelajaran,id',
            'status' => 'required|in:active,inactive',
        ], [
            'title.required' => 'Judul quiz wajib diisi.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Thumbnail harus berformat jpeg, jpg, png, atau webp.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB.',
            'master_media_music_quiz_id.exists' => 'Music quiz yang dipilih tidak valid.', 
            'duration.required' => 'Durasi quiz wajib diisi.',
            'duration.min' => 'Durasi minimal 1 menit.',
            'type.required' => 'Tipe quiz wajib dipilih.',
            'modul_pembelajaran_id.required_if' => 'Modul pembelajaran wajib dipilih untuk quiz modul.',
            'status.required' => 'Status quiz wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['thumbnail', 'remove_thumbnail']); 

            // Jika type umum, set modul_pembelajaran_id ke null
            if ($data['type'] === 'umum') {
                $data['modul_pembelajaran_id'] = null;
            }

            if ($request->remove_thumbnail) {
                $quiz->deleteThumbnail();
                $data['thumbnail'] = null;
            }

            if ($request->hasFile('thumbnail')) {
                // Hapus thumbnail lama
                $quiz->deleteThumbnail();
                
                // Upload thumbnail baru
                $thumbnailPath = $request->file('thumbnail')->store('quizzes/thumbnails', 'public');
                $data['thumbnail'] = $thumbnailPath;
            }


            $quiz->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($quiz)
                ->withProperties([
                    'old_type' => $quiz->getOriginal('type'),
                    'new_type' => $data['type'],
                    'old_status' => $quiz->getOriginal('status'),
                    'new_status' => $data['status'],
                    'thumbnail_updated' => $request->hasFile('thumbnail'),
                    'music_updated' => isset($data['master_media_music_quiz_id']), // UBAH
                ])
                ->log('Updated quiz');

            DB::commit();

            return redirect()->route('quiz.index')
                ->with('success', 'Quiz berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus file yang sudah diupload jika terjadi error
            if (isset($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
            
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate quiz: ' . $e->getMessage()]);
        }
    }

    public function destroy(Quizzes $quiz)
    {
        DB::beginTransaction();
        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($quiz)
                ->withProperties([
                    'deleted_quiz' => $quiz->toArray()
                ])
                ->log('Deleted quiz');

            $quiz->deleteThumbnail();

            $quiz->delete();

            DB::commit();

            return redirect()->route('quiz.index')
                ->with('success', 'Quiz berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus quiz: ' . $e->getMessage()]);
        }
    }
}