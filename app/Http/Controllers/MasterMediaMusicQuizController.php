<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterMediaMusicQuiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class MasterMediaMusicQuizController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view data master', only: ['index']),
            new Middleware('permission:create data master', only: ['create', 'store']),
            new Middleware('permission:edit data master', only: ['edit', 'update']),
            new Middleware('permission:delete data master', only: ['destroy']),
        ];
    }

    public function index()
    {
        $musicQuiz = MasterMediaMusicQuiz::orderBy('created_at', 'desc')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'audio' => $item->audio,
                'audio_url' => $item->audio_url,
                'keterangan' => $item->keterangan,
                'is_active' => $item->is_active,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return Inertia::render('DataMaster/QuizMusic/Index', [
            'musicQuiz' => $musicQuiz
        ]);
    }

    public function create()
    {
        return Inertia::render('DataMaster/QuizMusic/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,m4a|max:10240', // max 10MB
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $audioPath = null;
            
            if ($request->hasFile('audio')) {
                $file = $request->file('audio');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $audioPath = $file->storeAs('quizzes/music', $fileName, 'public');
            }

            $musicQuiz = MasterMediaMusicQuiz::create([
                'audio' => $audioPath,
                'keterangan' => $request->keterangan,
                'is_active' => $request->is_active ?? true,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($musicQuiz)
                ->log('Created new master media music quiz');

            DB::commit();

            return redirect()->route('data-master.quiz-music.index')->with('success', 'Media Music Quiz berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hapus file jika ada error
            if ($audioPath && Storage::disk('public')->exists($audioPath)) {
                Storage::disk('public')->delete($audioPath);
            }
            
            return back()->withInput()->withErrors(['error' => 'Gagal membuat media music quiz: ' . $e->getMessage()]);
        }
    }

    public function edit(MasterMediaMusicQuiz $quizMusic)
    {
        return Inertia::render('DataMaster/QuizMusic/Edit', [
            'musicQuiz' => [
                'id' => $quizMusic->id,
                'audio' => $quizMusic->audio,
                'audio_url' => $quizMusic->audio_url,
                'keterangan' => $quizMusic->keterangan,
                'is_active' => $quizMusic->is_active,
            ]
        ]);
    }

    public function update(Request $request, MasterMediaMusicQuiz $quizMusic)
    {
        $request->validate([
            'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240', // max 10MB
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'keterangan' => $request->keterangan,
                'is_active' => $request->is_active ?? true,
            ];

            // Handle file upload jika ada file baru
            if ($request->hasFile('audio')) {
                // Hapus file lama
                if ($quizMusic->audio && Storage::disk('public')->exists($quizMusic->audio)) {
                    Storage::disk('public')->delete($quizMusic->audio);
                }

                // Upload file baru
                $file = $request->file('audio');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $audioPath = $file->storeAs('quizzes/music', $fileName, 'public');
                $data['audio'] = $audioPath;
            }

            $quizMusic->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($quizMusic)
                ->log('Updated master media music quiz');

            DB::commit();

            return redirect()->route('data-master.quiz-music.index')->with('success', 'Media Music Quiz berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate media music quiz: ' . $e->getMessage()]);
        }
    }

    public function destroy(MasterMediaMusicQuiz $quizMusic)
    {
        DB::beginTransaction();
        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($quizMusic)
                ->withProperties(['deleted_music_quiz' => $quizMusic->toArray()])
                ->log('Deleted master media music quiz');

            // Hapus file audio
            if ($quizMusic->audio && Storage::disk('public')->exists($quizMusic->audio)) {
                Storage::disk('public')->delete($quizMusic->audio);
            }

            $quizMusic->delete();

            DB::commit();

            return redirect()->route('data-master.quiz-music.index')->with('success', 'Media Music Quiz berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus media music quiz: ' . $e->getMessage()]);
        }
    }
}