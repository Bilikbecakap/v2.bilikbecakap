<?php

namespace App\Http\Controllers;

use App\Models\Quizzes;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class QuizQuestionController extends Controller implements HasMiddleware
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

    public function index(Quizzes $quiz)
    {
        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get();

        $questions->transform(function ($question) {
            $question->type_label = $question->isMultipleChoice() ? 'Pilihan Ganda' : 'Isian';
            return $question;
        });

        return Inertia::render('Quiz/Questions/Index', [
            'quiz' => $quiz,
            'questions' => $questions
        ]);
    }

    public function create(Quizzes $quiz)
    {
        $nextOrder = $quiz->questions()->max('order') + 1;

        return Inertia::render('Quiz/Questions/Create', [
            'quiz' => $quiz,
            'nextOrder' => $nextOrder
        ]);
    }

    public function store(Request $request, Quizzes $quiz)
    {
        $request->validate([
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,fill_blank',
            'order' => 'required|integer|min:0',
        ], [
            'question.required' => 'Pertanyaan wajib diisi.',
            'question_type.required' => 'Tipe soal wajib dipilih.',
            'question_type.in' => 'Tipe soal tidak valid.',
            'order.required' => 'Urutan soal wajib diisi.',
        ]);

        if ($request->question_type === 'multiple_choice') {
            $request->validate([
                'options' => 'required|array|min:2|max:6',
                'options.*.option_text' => 'required|string',
                'options.*.is_correct' => 'required|boolean',
                'options.*.order' => 'required|integer|min:0',
            ], [
                'options.required' => 'Pilihan jawaban wajib diisi untuk soal pilihan ganda.',
                'options.min' => 'Minimal harus ada 2 pilihan jawaban.',
                'options.max' => 'Maksimal 6 pilihan jawaban.',
                'options.*.option_text.required' => 'Teks pilihan jawaban wajib diisi.',
                'options.*.is_correct.required' => 'Status jawaban benar/salah wajib ditentukan.',
            ]);

            $hasCorrectAnswer = collect($request->options)->contains('is_correct', true);
            if (!$hasCorrectAnswer) {
                return back()->withInput()->withErrors(['options' => 'Minimal harus ada 1 jawaban yang benar.']);
            }

            $correctCount = collect($request->options)->where('is_correct', true)->count();
            if ($correctCount > 1) {
                return back()->withInput()->withErrors(['options' => 'Hanya boleh ada 1 jawaban yang benar.']);
            }

        } elseif ($request->question_type === 'fill_blank') {
            $request->validate([
                'correct_answer' => 'required|string|max:255',
            ], [
                'correct_answer.required' => 'Jawaban yang benar wajib diisi untuk soal isian.',
                'correct_answer.max' => 'Jawaban maksimal 255 karakter.',
            ]);
        }

        DB::beginTransaction();
        try {
            // ✅ CREATE QUESTION dengan correct_answer
            $questionData = [
                'question' => $request->question,
                'question_type' => $request->question_type,
                'order' => $request->order,
            ];

            // ✅ TAMBAH correct_answer untuk fill_blank
            if ($request->question_type === 'fill_blank') {
                $questionData['correct_answer'] = trim($request->correct_answer);
            }

            $question = $quiz->questions()->create($questionData);

            // Create options
            if ($request->question_type === 'multiple_choice') {
                foreach ($request->options as $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'],
                        'order' => $optionData['order'],
                    ]);
                }
            }
            // ✅ Untuk fill_blank, TIDAK perlu buat option lagi
            // Karena correct_answer sudah disimpan di quiz_questions.correct_answer

            activity()
                ->causedBy(auth()->user())
                ->performedOn($question)
                ->withProperties([
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'question_type' => $request->question_type,
                    'options_count' => $request->question_type === 'multiple_choice' 
                        ? count($request->options) 
                        : 0
                ])
                ->log('Created new quiz question');

            DB::commit();

            return redirect()->route('quiz.questions.index', $quiz)
                ->with('success', 'Soal berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating quiz question: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Gagal menambahkan soal: ' . $e->getMessage()]);
        }
    }

    public function edit(Quizzes $quiz, QuizQuestion $question)
    {
        $question->load('options');

        // ✅ Untuk fill_blank, ambil dari kolom correct_answer
        if ($question->isFillBlank()) {
            // Tidak perlu ambil dari options, langsung dari kolom
            // $question->correct_answer sudah ada di model
        }

        return Inertia::render('Quiz/Questions/Edit', [
            'quiz' => $quiz,
            'question' => $question
        ]);
    }

    public function update(Request $request, Quizzes $quiz, QuizQuestion $question)
    {
        $request->validate([
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,fill_blank',
            'order' => 'required|integer|min:0',
        ], [
            'question.required' => 'Pertanyaan wajib diisi.',
            'question_type.required' => 'Tipe soal wajib dipilih.',
            'order.required' => 'Urutan soal wajib diisi.',
        ]);

        if ($request->question_type === 'multiple_choice') {
            $request->validate([
                'options' => 'required|array|min:2|max:6',
                'options.*.id' => 'nullable|integer|exists:quiz_options,id',
                'options.*.option_text' => 'required|string',
                'options.*.is_correct' => 'required|boolean',
                'options.*.order' => 'required|integer|min:0',
            ], [
                'options.required' => 'Pilihan jawaban wajib diisi untuk soal pilihan ganda.',
                'options.min' => 'Minimal harus ada 2 pilihan jawaban.',
                'options.max' => 'Maksimal 6 pilihan jawaban.',
                'options.*.option_text.required' => 'Teks pilihan jawaban wajib diisi.',
            ]);

            $hasCorrectAnswer = collect($request->options)->contains('is_correct', true);
            if (!$hasCorrectAnswer) {
                return back()->withInput()->withErrors(['options' => 'Minimal harus ada 1 jawaban yang benar.']);
            }

            $correctCount = collect($request->options)->where('is_correct', true)->count();
            if ($correctCount > 1) {
                return back()->withInput()->withErrors(['options' => 'Hanya boleh ada 1 jawaban yang benar.']);
            }

        } elseif ($request->question_type === 'fill_blank') {
            $request->validate([
                'correct_answer' => 'required|string|max:255',
            ], [
                'correct_answer.required' => 'Jawaban yang benar wajib diisi untuk soal isian.',
                'correct_answer.max' => 'Jawaban maksimal 255 karakter.',
            ]);
        }

        DB::beginTransaction();
        try {
            // ✅ UPDATE QUESTION dengan correct_answer
            $questionData = [
                'question' => $request->question,
                'question_type' => $request->question_type,
                'order' => $request->order,
            ];

            // ✅ UPDATE correct_answer untuk fill_blank
            if ($request->question_type === 'fill_blank') {
                $questionData['correct_answer'] = trim($request->correct_answer);
            } else {
                // Reset correct_answer jika diubah ke multiple_choice
                $questionData['correct_answer'] = null;
            }

            $question->update($questionData);

            // Hapus semua options lama
            $question->options()->delete();

            // Buat options baru HANYA untuk multiple_choice
            if ($request->question_type === 'multiple_choice') {
                foreach ($request->options as $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'],
                        'order' => $optionData['order'],
                    ]);
                }
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($question)
                ->withProperties([
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'question_type' => $request->question_type,
                    'options_count' => $request->question_type === 'multiple_choice' 
                        ? count($request->options) 
                        : 0
                ])
                ->log('Updated quiz question');

            DB::commit();

            return redirect()->route('quiz.questions.index', $quiz)
                ->with('success', 'Soal berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating quiz question: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate soal: ' . $e->getMessage()]);
        }
    }

    public function destroy(Quizzes $quiz, QuizQuestion $question)
    {
        DB::beginTransaction();
        try {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($question)
                ->withProperties([
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'question_type' => $question->question_type,
                    'deleted_question' => $question->toArray()
                ])
                ->log('Deleted quiz question');

            $question->delete();

            DB::commit();

            return redirect()->route('quiz.questions.index', $quiz)
                ->with('success', 'Soal berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting quiz question: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menghapus soal: ' . $e->getMessage()]);
        }
    }
}