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

    /**
     * Display a listing of questions for a specific quiz.
     */
    public function index(Quizzes $quiz)
    {
        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get();

        return Inertia::render('Quiz/Questions/Index', [
            'quiz' => $quiz,
            'questions' => $questions
        ]);
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Quizzes $quiz)
    {
        // Get next order number
        $nextOrder = $quiz->questions()->max('order') + 1;

        return Inertia::render('Quiz/Questions/Create', [
            'quiz' => $quiz,
            'nextOrder' => $nextOrder
        ]);
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request, Quizzes $quiz)
    {
        $request->validate([
            'question' => 'required|string',
            'order' => 'required|integer|min:0',
            'options' => 'required|array|min:2|max:6',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
            'options.*.order' => 'required|integer|min:0',
        ], [
            'question.required' => 'Pertanyaan wajib diisi.',
            'options.required' => 'Minimal harus ada 2 pilihan jawaban.',
            'options.min' => 'Minimal harus ada 2 pilihan jawaban.',
            'options.max' => 'Maksimal 6 pilihan jawaban.',
            'options.*.option_text.required' => 'Teks pilihan jawaban wajib diisi.',
            'options.*.is_correct.required' => 'Status jawaban benar/salah wajib ditentukan.',
        ]);

        // Validasi: minimal 1 jawaban benar
        $hasCorrectAnswer = collect($request->options)->contains('is_correct', true);
        if (!$hasCorrectAnswer) {
            return back()->withInput()->withErrors(['options' => 'Minimal harus ada 1 jawaban yang benar.']);
        }

        DB::beginTransaction();
        try {
            // Create question
            $question = $quiz->questions()->create([
                'question' => $request->question,
                'order' => $request->order,
            ]);

            // Create options
            foreach ($request->options as $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $optionData['is_correct'],
                    'order' => $optionData['order'],
                ]);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($question)
                ->withProperties([
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'options_count' => count($request->options)
                ])
                ->log('Created new quiz question');

            DB::commit();

            return redirect()->route('quiz.questions.index', $quiz)
                ->with('success', 'Soal berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menambahkan soal: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Quizzes $quiz, QuizQuestion $question)
    {
        $question->load('options');

        return Inertia::render('Quiz/Questions/Edit', [
            'quiz' => $quiz,
            'question' => $question
        ]);
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, Quizzes $quiz, QuizQuestion $question)
    {
        $request->validate([
            'question' => 'required|string',
            'order' => 'required|integer|min:0',
            'options' => 'required|array|min:2|max:6',
            'options.*.id' => 'nullable|integer|exists:quiz_options,id',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
            'options.*.order' => 'required|integer|min:0',
        ], [
            'question.required' => 'Pertanyaan wajib diisi.',
            'options.required' => 'Minimal harus ada 2 pilihan jawaban.',
            'options.min' => 'Minimal harus ada 2 pilihan jawaban.',
            'options.max' => 'Maksimal 6 pilihan jawaban.',
            'options.*.option_text.required' => 'Teks pilihan jawaban wajib diisi.',
            'options.*.is_correct.required' => 'Status jawaban benar/salah wajib ditentukan.',
        ]);

        // Validasi: minimal 1 jawaban benar
        $hasCorrectAnswer = collect($request->options)->contains('is_correct', true);
        if (!$hasCorrectAnswer) {
            return back()->withInput()->withErrors(['options' => 'Minimal harus ada 1 jawaban yang benar.']);
        }

        DB::beginTransaction();
        try {
            // Update question
            $question->update([
                'question' => $request->question,
                'order' => $request->order,
            ]);

            // Get existing option IDs
            $existingOptionIds = $question->options->pluck('id')->toArray();
            $submittedOptionIds = collect($request->options)->pluck('id')->filter()->toArray();

            // Delete options that are not in the submitted data
            $optionsToDelete = array_diff($existingOptionIds, $submittedOptionIds);
            if (!empty($optionsToDelete)) {
                QuizOption::whereIn('id', $optionsToDelete)->delete();
            }

            // Update or create options
            foreach ($request->options as $optionData) {
                if (isset($optionData['id'])) {
                    // Update existing option
                    QuizOption::where('id', $optionData['id'])->update([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'],
                        'order' => $optionData['order'],
                    ]);
                } else {
                    // Create new option
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
                    'options_count' => count($request->options)
                ])
                ->log('Updated quiz question');

            DB::commit();

            return redirect()->route('quiz.questions.index', $quiz)
                ->with('success', 'Soal berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate soal: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified question from storage.
     */
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
                    'deleted_question' => $question->toArray()
                ])
                ->log('Deleted quiz question');

            $question->delete();

            DB::commit();

            return redirect()->route('quiz.questions.index', $quiz)
                ->with('success', 'Soal berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus soal: ' . $e->getMessage()]);
        }
    }
}