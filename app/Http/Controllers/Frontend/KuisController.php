<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Quizzes;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KuisController extends Controller
{
    public function index(Request $request)
    {
        $query = Quizzes::with(['modulPembelajaran', 'masterMediaMusicQuiz'])
            ->withCount('questions')
            ->withCount('attempts')
            ->where('status', 'active');

        // Filter berdasarkan type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
        }

        $quizzes = $query->orderBy('created_at', 'desc')->paginate(12)->appends($request->query());

        // Transform untuk menambah total_questions
        $quizzes->getCollection()->transform(function ($quiz) {
            $quiz->total_questions = $quiz->questions_count;
            return $quiz;
        });

        return Inertia::render('Frontend/Kuis', [
            'quizzes' => $quizzes,
            'search' => $request->search,
            'type' => $request->type,
        ]);
    }

    /**
     * Show quiz detail & start quiz form
     */
    public function show(Quizzes $quiz)
    {
        // Pastikan quiz active
        if ($quiz->status !== 'active') {
            return back()->withErrors(['error' => 'Kuis ini tidak tersedia.']);
        }

        $quiz->load(['modulPembelajaran', 'masterMediaMusicQuiz']);
        $quiz->total_questions = $quiz->questions()->count();

        // Get total attempts & average score
        $totalAttempts = $quiz->attempts()->whereNotNull('completed_at')->count();
        $averageScore = $quiz->attempts()->whereNotNull('completed_at')->avg('score');

        return Inertia::render('Frontend/KuisDetail', [
            'quiz' => $quiz,
            'totalAttempts' => $totalAttempts,
            'averageScore' => round($averageScore, 2) ?? 0,
        ]);
    }

    /**
     * Begin quiz attempt - create attempt record dan tampilkan soal
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

            return redirect()->route('quiz-attempt.quiz', [$quiz->id, $attempt->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memulai kuis: ' . $e->getMessage()]);
        }
    }

    /**
     * Display quiz questions untuk dikerjakan
     */
    public function quiz(Quizzes $quiz, QuizAttempt $attempt)
    {
        // Validasi bahwa attempt milik quiz ini
        if ($attempt->quiz_id !== $quiz->id) {
            return back()->withErrors(['error' => 'Invalid quiz attempt.']);
        }

        $quiz->load('masterMediaMusicQuiz');
        
        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get();

        return Inertia::render('Frontend/KuisQuiz', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $questions,
        ]);
    }

    /**
     * Submit quiz answers dan hitung score
     */
    public function submit(Request $request, Quizzes $quiz, QuizAttempt $attempt)
    {
        // Validasi bahwa attempt milik quiz ini
        if ($attempt->quiz_id !== $quiz->id) {
            return back()->withErrors(['error' => 'Invalid quiz attempt.']);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.option_id' => 'nullable|exists:quiz_options,id',
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

            return redirect()->route('quiz-attempt.result', [$quiz->id, $attempt->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quiz submit error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan jawaban: ' . $e->getMessage()]);
        }
    }

    /**
     * Display quiz result
     */
    public function result(Quizzes $quiz, QuizAttempt $attempt)
    {
        // Validasi bahwa attempt milik quiz ini
        if ($attempt->quiz_id !== $quiz->id) {
            return back()->withErrors(['error' => 'Invalid quiz attempt.']);
        }

        // Load attempt dengan jawaban
        $attempt->load([
            'answers.question',
            'answers.selectedOption',
            'quiz'
        ]);

        // Get correct answer untuk setiap question
        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get()
            ->map(function ($question) {
                $question->correctOption = $question->options()->where('is_correct', true)->first();
                return $question;
            });

        return Inertia::render('Frontend/KuisResult', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $questions,
        ]);
    }
}