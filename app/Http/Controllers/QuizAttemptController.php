<?php

namespace App\Http\Controllers;

use App\Models\Quizzes;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class QuizAttemptController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view quiz', only: ['index', 'show']),
        ];
    }

    /**
     * Display quiz list untuk dikerjakan (testing).
     */
    public function index(Request $request)
    {
        $query = Quizzes::with(['modulPembelajaran'])
            ->withCount('questions') // 👈 Tambahkan ini
            ->where('status', 'active');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $quizzes = $query->orderBy('created_at', 'desc')->paginate(15);

        $quizzes->getCollection()->transform(function ($quiz) {
            $quiz->total_questions = $quiz->questions_count;
            return $quiz;
        });

        return Inertia::render('Quiz/Test/Index', [
            'quizzes' => $quizzes,
            'type' => $request->type,
        ]);
    }

    /**
     * Show form untuk input nama sebelum mulai quiz.
     */
    public function start(Quizzes $quiz)
    {
        $quiz->load(['modulPembelajaran']);
        $totalQuestions = $quiz->questions()->count();

        if ($totalQuestions === 0) {
            return back()->withErrors(['error' => 'Quiz ini belum memiliki soal.']);
        }

        return Inertia::render('Quiz/Test/Start', [
            'quiz' => $quiz,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    /**
     * Begin quiz attempt - create attempt record dan tampilkan soal.
     */
    public function begin(Request $request, Quizzes $quiz)
    {
        $request->validate([
            'participant_name' => 'required|string|max:255',
        ], [
            'participant_name.required' => 'Nama peserta wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'participant_name' => $request->participant_name,
                'score' => 0,
                'correct_answers' => 0,
                'total_questions' => $quiz->questions()->count(),
                'started_at' => now(),
                'completed_at' => null,
            ]);

            DB::commit();

            return redirect()->route('quiz.attempt.quiz', [$quiz, $attempt]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memulai quiz: ' . $e->getMessage()]);
        }
    }

    /**
     * Display quiz questions untuk dikerjakan.
     */
    public function quiz(Quizzes $quiz, QuizAttempt $attempt)
    {
        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get();

        return Inertia::render('Quiz/Test/Quiz', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $questions,
        ]);
    }

    /**
     * Submit quiz answers dan hitung score.
     */
    public function submit(Request $request, Quizzes $quiz, QuizAttempt $attempt)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.option_id' => 'nullable|exists:quiz_options,id', // NULLABLE
        ], [
            'answers.required' => 'Jawaban wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $correctCount = 0;
            $totalQuestions = $quiz->questions()->count();

            $allQuestions = $quiz->questions()->get();
            
            $submittedAnswers = collect($request->answers)->keyBy('question_id');

            foreach ($allQuestions as $question) {
                $submittedAnswer = $submittedAnswers->get($question->id);
                
                // Default values untuk soal tidak dijawab
                $selectedOptionId = null;
                $isCorrect = false;

                // Jika soal dijawab
                if ($submittedAnswer && !is_null($submittedAnswer['option_id'])) {
                    $selectedOptionId = $submittedAnswer['option_id'];
                    
                    // Validasi option milik question ini
                    $selectedOption = $question->options()->find($selectedOptionId);
                    
                    if ($selectedOption) {
                        $isCorrect = $selectedOption->is_correct;
                        if ($isCorrect) {
                            $correctCount++;
                        }
                    }
                }

                QuizAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'quiz_option_id' => $selectedOptionId, 
                    'is_correct' => $isCorrect,
                ]);
            }

            // Calculate score (percentage)
            $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;

            // Update attempt
            $attempt->update([
                'score' => $score,
                'correct_answers' => $correctCount,
                'completed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('quiz.attempt.result', [$quiz, $attempt]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quiz submit error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan jawaban: ' . $e->getMessage()]);
        }
    }

    /**
     * Display quiz result.
     */
    public function result(Quizzes $quiz, QuizAttempt $attempt)
    {
        $attempt->load(['answers.question.options', 'answers.selectedOption']);

        return Inertia::render('Quiz/Test/Result', [
            'quiz' => $quiz,
            'attempt' => $attempt,
        ]);
    }

    /**
     * Display all attempts history untuk sebuah quiz (untuk admin).
     */
    public function history(Quizzes $quiz)
    {
        $attempts = $quiz->attempts()
            ->orderBy('completed_at', 'desc')
            ->paginate(20);

        return Inertia::render('Quiz/Test/History', [
            'quiz' => $quiz,
            'attempts' => $attempts,
        ]);
    }

    /**
     * Show detailed attempt result (untuk admin review).
     */
    public function show(Quizzes $quiz, QuizAttempt $attempt)
    {
        $attempt->load(['answers.question.options', 'answers.selectedOption']);

        return Inertia::render('Quiz/Test/Show', [
            'quiz' => $quiz,
            'attempt' => $attempt,
        ]);
    }
}