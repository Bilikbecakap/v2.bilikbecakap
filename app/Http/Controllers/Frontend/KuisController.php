<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Quizzes;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KuisController extends Controller
{
    /**
     * Tampilkan daftar semua quiz (normal + duel digabung)
     */
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

        // Filter berdasarkan duel mode
        if ($request->filled('is_duel_enabled')) {
            $query->where('is_duel_enabled', $request->is_duel_enabled);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
        }

        $quizzes = $query
            ->orderByRaw("FIELD(type, 'umum', 'modul')")
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->query());

        // Transform untuk menambah total_questions
        $quizzes->getCollection()->transform(function ($quiz) {
            $quiz->total_questions = $quiz->questions_count;
            return $quiz;
        });

        return Inertia::render('Frontend/Kuis', [
            'quizzes' => $quizzes,
            'search' => $request->search,
            'type' => $request->type,
            'is_duel_enabled' => $request->is_duel_enabled,
        ]);
    }

    /**
     * Show quiz detail
     * REDIRECT ke detail berbeda untuk normal vs duel
     */
    public function show($slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        // Pastikan quiz active
        if ($quiz->status !== 'active') {
            return back()->withErrors(['error' => 'Kuis ini tidak tersedia.']);
        }

        $quiz->load(['modulPembelajaran', 'masterMediaMusicQuiz']);
        $quiz->total_questions = $quiz->questions()->count();

        // Get total attempts & average score
        $totalAttempts = $quiz->attempts()->whereNotNull('completed_at')->count();
        $averageScore = $quiz->attempts()->whereNotNull('completed_at')->avg('score');

        // REDIRECT: Jika duel, ke halaman detail duel
        if ($quiz->is_duel_enabled) {
            return Inertia::render('Frontend/KuisDuelDetail', [
                'quiz' => $quiz,
                'totalAttempts' => $totalAttempts,
                'averageScore' => round($averageScore, 2) ?? 0,
            ]);
        }

        // Normal quiz detail
        return Inertia::render('Frontend/KuisDetail', [
            'quiz' => $quiz,
            'totalAttempts' => $totalAttempts,
            'averageScore' => round($averageScore, 2) ?? 0,
        ]);
    }

    /**
     * Begin NORMAL quiz attempt
     */
    public function begin(Request $request, $slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        // Pastikan ini BUKAN duel quiz
        if ($quiz->is_duel_enabled) {
            return back()->withErrors(['error' => 'Gunakan endpoint duel untuk quiz ini.']);
        }

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

            return redirect()->route('quiz-attempt.quiz', $quiz->slug);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memulai kuis: ' . $e->getMessage()]);
        }
    }

    /**
     * Begin DUEL quiz attempt (2 players)
     */
    public function beginDuel(Request $request, $slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        // Pastikan ini ADALAH duel quiz
        if (!$quiz->is_duel_enabled) {
            return back()->withErrors(['error' => 'Quiz ini bukan mode tantangan.']);
        }

        $request->validate([
            'player1_name' => 'required|string|max:255',
            'player2_name' => 'required|string|max:255',
        ], [
            'player1_name.required' => 'Nama Player 1 wajib diisi.',
            'player2_name.required' => 'Nama Player 2 wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            // Create 2 attempts untuk 2 players
            $attempt1 = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'participant_name' => $request->player1_name,
                'player1_name' => $request->player1_name,    
                'player2_name' => $request->player2_name,     
                'score' => 0,
                'correct_answers' => 0,
                'total_questions' => $quiz->questions()->count(),
                'started_at' => now(),
                'completed_at' => null,
            ]);

            $attempt2 = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'participant_name' => $request->player2_name,
                'player1_name' => $request->player1_name,    
                'player2_name' => $request->player2_name,
                'score' => 0,
                'correct_answers' => 0,
                'total_questions' => $quiz->questions()->count(),
                'started_at' => now(),
                'completed_at' => null,
            ]);

            DB::commit();

            // Redirect ke arena duel dengan 2 attempt IDs
            return redirect()->route('quiz-attempt.duel', [
                'slug' => $quiz->slug,
                'attempt1' => $attempt1->id,
                'attempt2' => $attempt2->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal memulai tantangan: ' . $e->getMessage()]);
        }
    }

    /**
     * Display NORMAL quiz questions
     */
    public function quiz($slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        // Jika quiz adalah duel mode, redirect
        if ($quiz->is_duel_enabled) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Quiz ini adalah mode tantangan.']);
        }

        // Cek attempt terbaru yang belum completed
        $attempt = $quiz->attempts()
            ->whereNull('completed_at')
            ->latest('created_at')
            ->first();

        if (!$attempt) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Kuis belum dimulai atau sudah selesai.']);
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
     * Display quiz dalam Duel Mode (Tarik Tambang)
     */
    public function duel(Request $request, $slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        // Pastikan quiz ini duel enabled
        if (!$quiz->is_duel_enabled) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Quiz ini bukan mode tantangan.']);
        }

        // Ambil 2 attempts dari query string
        $attempt1Id = $request->get('attempt1');
        $attempt2Id = $request->get('attempt2');

        if (!$attempt1Id || !$attempt2Id) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Data pemain tidak lengkap.']);
        }

        $attempt1 = QuizAttempt::find($attempt1Id);
        $attempt2 = QuizAttempt::find($attempt2Id);

        if (!$attempt1 || !$attempt2) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Data pemain tidak ditemukan.']);
        }

        $quiz->load('masterMediaMusicQuiz');
        
        // FIX: EKSPLISIT SELECT semua kolom termasuk correct_answer
        $questions = $quiz->questions()
            ->select([
                'id',
                'quiz_id',
                'question',
                'question_type',
                'correct_answer',  // ← INI YANG PENTING!
                'order',
                'created_at',
                'updated_at'
            ])
            ->with('options')
            ->orderBy('order')
            ->get();

        // Jika tidak ada soal
        if ($questions->isEmpty()) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Quiz ini belum memiliki soal.']);
        }

        return Inertia::render('Frontend/KuisDuel', [
            'quiz' => $quiz,
            'attempt1' => $attempt1,
            'attempt2' => $attempt2,
            'questions' => $questions,
        ]);
    }

    /**
     * Submit NORMAL quiz answers
     */
    public function submit(Request $request, $slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        // Cek attempt terbaru yang belum completed
        $attempt = $quiz->attempts()
            ->whereNull('completed_at')
            ->latest('created_at')
            ->first();

        if (!$attempt) {
            return back()->withErrors(['error' => 'Kuis belum dimulai atau sudah selesai.']);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.option_id' => 'nullable|exists:quiz_options,id',
            'answers.*.answer_text' => 'nullable|string',
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
                
                $selectedOptionId = null;
                $answerText = null;
                $isCorrect = false;

                // HANDLE MULTIPLE CHOICE
                if ($question->question_type === 'multiple_choice') {
                    if ($submittedAnswer && !is_null($submittedAnswer['option_id'])) {
                        $selectedOptionId = $submittedAnswer['option_id'];
                        $selectedOption = $question->options()->find($selectedOptionId);
                        
                        if ($selectedOption) {
                            $isCorrect = $selectedOption->is_correct;
                            if ($isCorrect) {
                                $correctCount++;
                            }
                        }
                    }
                }
                
                // HANDLE FILL BLANK
                elseif ($question->question_type === 'fill_blank') {
                    if ($submittedAnswer && !empty($submittedAnswer['answer_text'])) {
                        $answerText = $submittedAnswer['answer_text'];
                        $correctAnswer = strtolower(trim($question->correct_answer));
                        $userAnswer = strtolower(trim($answerText));
                        
                        $isCorrect = ($correctAnswer === $userAnswer);
                        
                        if ($isCorrect) {
                            $correctCount++;
                        }
                    }
                }

                QuizAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'quiz_option_id' => $selectedOptionId,
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect,
                ]);
            }

            $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;

            $attempt->update([
                'score' => $score,
                'correct_answers' => $correctCount,
                'completed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('quiz-attempt.result', $quiz->slug);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quiz submit error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan jawaban: ' . $e->getMessage()]);
        }
    }

    /**
     * Submit DUEL quiz answers (2 players)
     */
    public function submitDuel(Request $request, $slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        if (!$quiz->is_duel_enabled) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Quiz ini bukan mode tantangan.']);
        }

        // Validate request
        $request->validate([
            'attempt1_id' => 'required|exists:quiz_attempts,id',
            'attempt2_id' => 'required|exists:quiz_attempts,id',
            'player1_answers' => 'required|array',
            'player2_answers' => 'required|array',
        ]);

        $attempt1 = QuizAttempt::findOrFail($request->attempt1_id);
        $attempt2 = QuizAttempt::findOrFail($request->attempt2_id);

        DB::beginTransaction();
        try {
            // Process Player 1 Answers
            $player1Correct = 0;
            foreach ($request->player1_answers as $answer) {
                $question = QuizQuestion::find($answer['question_id']);
                
                $isCorrect = false;
                if ($question->question_type === 'fill_blank') {
                    $correctAnswer = $question->correct_answer;
                    $userAnswer = $answer['answer_text'] ?? '';
                    $isCorrect = strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
                } else {
                    $correctOption = $question->options()->where('is_correct', true)->first();
                    $isCorrect = $answer['option_id'] == $correctOption->id;
                }
                
                if ($isCorrect) $player1Correct++;
                
                // Save answer
                QuizAnswer::create([
                    'quiz_attempt_id' => $attempt1->id,
                    'quiz_question_id' => $question->id,
                    'quiz_option_id' => $answer['option_id'] ?? null,
                    'text_answer' => $answer['answer_text'] ?? null,
                    'answer_type' => $question->question_type === 'fill_blank' ? 'text' : 'option',
                    'is_correct' => $isCorrect,
                ]);
            }

            // Process Player 2 Answers
            $player2Correct = 0;
            foreach ($request->player2_answers as $answer) {
                $question = QuizQuestion::find($answer['question_id']);
                
                $isCorrect = false;
                if ($question->question_type === 'fill_blank') {
                    $correctAnswer = $question->correct_answer;
                    $userAnswer = $answer['answer_text'] ?? '';
                    $isCorrect = strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
                } else {
                    $correctOption = $question->options()->where('is_correct', true)->first();
                    $isCorrect = $answer['option_id'] == $correctOption->id;
                }
                
                if ($isCorrect) $player2Correct++;
                
                // Save answer
                QuizAnswer::create([
                    'quiz_attempt_id' => $attempt2->id,
                    'quiz_question_id' => $question->id,
                    'quiz_option_id' => $answer['option_id'] ?? null,
                    'text_answer' => $answer['answer_text'] ?? null,
                    'answer_type' => $question->question_type === 'fill_blank' ? 'text' : 'option',
                    'is_correct' => $isCorrect,
                ]);
            }

            // Determine winner
            $winner = null;
            if ($player1Correct > $player2Correct) {
                $winner = 'player1';
            } elseif ($player2Correct > $player1Correct) {
                $winner = 'player2';
            } else {
                $winner = 'draw';
            }

            // Update attempts
            $attempt1->update([
                'player1_score' => $player1Correct,
                'player2_score' => $player2Correct,
                'winner' => $winner,
                'correct_answers' => $player1Correct,
                'score' => round(($player1Correct / $quiz->questions()->count()) * 100),
                'completed_at' => now(),
            ]);

            $attempt2->update([
                'player1_score' => $player1Correct,
                'player2_score' => $player2Correct,
                'winner' => $winner,
                'correct_answers' => $player2Correct,
                'score' => round(($player2Correct / $quiz->questions()->count()) * 100),
                'completed_at' => now(),
            ]);

            DB::commit();

            // Redirect to result page
            return redirect()->route('quiz-attempt.duel-result', [
                'slug' => $quiz->slug,
                'attempt1' => $attempt1->id,
                'attempt2' => $attempt2->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Duel submit error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan hasil: ' . $e->getMessage()]);
        }
    }

    /**
     * Helper: Process duel answers for one player
     */
    private function processDuelAnswers($attempt, $answers, $quiz)
    {
        $correctCount = 0;
        $totalQuestions = $quiz->questions()->count();
        $allQuestions = $quiz->questions()->get();
        $submittedAnswers = collect($answers)->keyBy('question_id');

        foreach ($allQuestions as $question) {
            $submittedAnswer = $submittedAnswers->get($question->id);
            $selectedOptionId = null;
            $answerText = null;
            $isCorrect = false;

            if ($question->question_type === 'multiple_choice') {
                if ($submittedAnswer && !is_null($submittedAnswer['option_id'])) {
                    $selectedOptionId = $submittedAnswer['option_id'];
                    $selectedOption = $question->options()->find($selectedOptionId);
                    
                    if ($selectedOption) {
                        $isCorrect = $selectedOption->is_correct;
                        if ($isCorrect) {
                            $correctCount++;
                        }
                    }
                }
            } elseif ($question->question_type === 'fill_blank') {
                if ($submittedAnswer && !empty($submittedAnswer['answer_text'])) {
                    $answerText = $submittedAnswer['answer_text'];
                    $correctAnswer = strtolower(trim($question->correct_answer));
                    $userAnswer = strtolower(trim($answerText));
                    
                    $isCorrect = ($correctAnswer === $userAnswer);
                    
                    if ($isCorrect) {
                        $correctCount++;
                    }
                }
            }

            QuizAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'quiz_question_id' => $question->id,
                'quiz_option_id' => $selectedOptionId,
                'answer_text' => $answerText,
                'is_correct' => $isCorrect,
            ]);
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;

        $attempt->update([
            'score' => $score,
            'correct_answers' => $correctCount,
            'completed_at' => now(),
        ]);
    }

    /**
     * Display NORMAL quiz result
     */
    public function result($slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        $attempt = $quiz->attempts()
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->first();

        if (!$attempt) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Hasil kuis tidak ditemukan.']);
        }

        $attempt->load([
            'answers.question',
            'answers.selectedOption',
            'quiz'
        ]);

        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get()
            ->map(function ($question) {
                if ($question->question_type === 'multiple_choice') {
                    $question->correctOption = $question->options()->where('is_correct', true)->first();
                }
                return $question;
            });

        return Inertia::render('Frontend/KuisResult', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $questions,
        ]);
    }

    /**
     * Display DUEL quiz result
     */
    public function duelResult(Request $request, $slug)
    {
        $quiz = Quizzes::where('slug', $slug)->firstOrFail();

        $attempt1Id = $request->get('attempt1');
        $attempt2Id = $request->get('attempt2');

        if (!$attempt1Id || !$attempt2Id) {
            return redirect()->route('kuis.show', $slug)
                ->withErrors(['error' => 'Data hasil tidak ditemukan.']);
        }

        $attempt1 = QuizAttempt::with('answers.question')->findOrFail($attempt1Id);
        $attempt2 = QuizAttempt::with('answers.question')->findOrFail($attempt2Id);

        // ✅ TAMBAH TOTAL QUESTIONS
        $quiz->total_questions = $quiz->questions()->count();

        return Inertia::render('Frontend/KuisDuelResult', [
            'quiz' => $quiz,
            'attempt1' => $attempt1,
            'attempt2' => $attempt2,
        ]);
    }
}