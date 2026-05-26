<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\KuisResource;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Quizzes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KuisController extends Controller
{
    private const PER_PAGE_DEFAULT = 12;
    private const PER_PAGE_MAX     = 50;

    // ─────────────────────────────────────────────
    // GET
    // ─────────────────────────────────────────────

    /**
     * GET /api/v1/kuis
     *
     * Query params:
     *   search           - cari judul atau deskripsi
     *   type             - modul | umum
     *   is_duel_enabled  - true | false
     *   per_page         - maks 50  (default: 12)
     *   page
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search'          => ['sometimes', 'string', 'max:100'],
            'type'            => ['sometimes', Rule::in(['modul', 'umum'])],
            'is_duel_enabled' => ['sometimes', 'boolean'],
            'per_page'        => ['sometimes', 'integer', 'min:1', 'max:' . self::PER_PAGE_MAX],
        ]);

        $query = Quizzes::withCount(['questions', 'attempts'])
            ->where('status', 'active');

        if (! empty($validated['search'])) {
            $s = $validated['search'];
            $query->where(function ($q) use ($s) {
                $q->where('title', 'LIKE', "%{$s}%")
                  ->orWhere('description', 'LIKE', "%{$s}%");
            });
        }

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (isset($validated['is_duel_enabled'])) {
            $query->where('is_duel_enabled', $validated['is_duel_enabled']);
        }

        $query->orderByRaw("FIELD(type, 'umum', 'modul')")->orderBy('created_at', 'desc');

        $perPage  = $validated['per_page'] ?? self::PER_PAGE_DEFAULT;
        $paginate = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'message' => 'Data kuis berhasil diambil',
            'data'    => KuisResource::collection($paginate),
            'meta'    => [
                'current_page' => $paginate->currentPage(),
                'last_page'    => $paginate->lastPage(),
                'per_page'     => $paginate->perPage(),
                'total'        => $paginate->total(),
                'from'         => $paginate->firstItem(),
                'to'           => $paginate->lastItem(),
            ],
            'links'   => [
                'first' => $paginate->url(1),
                'last'  => $paginate->url($paginate->lastPage()),
                'prev'  => $paginate->previousPageUrl(),
                'next'  => $paginate->nextPageUrl(),
            ],
        ]);
    }

    /**
     * GET /api/v1/kuis/{slug}
     * Detail kuis beserta statistik (total attempt & rata-rata skor).
     */
    public function show(string $slug): JsonResponse
    {
        $quiz = Quizzes::with('modulPembelajaran')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (! $quiz) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        $quiz->stats = [
            'total_attempts' => $quiz->attempts()->whereNotNull('completed_at')->count(),
            'average_score'  => round(
                $quiz->attempts()->whereNotNull('completed_at')->avg('score') ?? 0, 2
            ),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data kuis berhasil diambil',
            'data'    => new KuisResource($quiz),
        ]);
    }

    /**
     * GET /api/v1/kuis/{slug}/soal
     * Daftar soal beserta opsi — TANPA correct_answer (aman untuk client).
     */
    public function soal(string $slug): JsonResponse
    {
        $quiz = Quizzes::where('slug', $slug)->where('status', 'active')->first();

        if (! $quiz) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        $soal = $quiz->questions()
            ->select(['id', 'quiz_id', 'question', 'question_type', 'order'])
            ->with(['options' => fn ($q) => $q->select(['id', 'quiz_question_id', 'option_text'])->reorder()->inRandomOrder()])
            ->reorder()->inRandomOrder()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Soal kuis berhasil diambil',
            'data'    => $soal,
            'meta'    => ['total_soal' => $soal->count()],
        ]);
    }

    /**
     * GET /api/v1/kuis/{slug}/hasil/{attempt_id}
     * Hasil kuis normal — termasuk review jawaban benar/salah.
     */
    public function hasil(string $slug, int $attemptId): JsonResponse
    {
        $quiz    = Quizzes::where('slug', $slug)->where('status', 'active')->first();
        $attempt = $quiz
            ? QuizAttempt::where('id', $attemptId)
                         ->where('quiz_id', $quiz->id)
                         ->whereNotNull('completed_at')
                         ->first()
            : null;

        if (! $quiz || ! $attempt) {
            return response()->json(['success' => false, 'message' => 'Hasil kuis tidak ditemukan'], 404);
        }

        $soal = $quiz->questions()
            ->select(['id', 'question', 'question_type', 'correct_answer', 'order'])
            ->with([
                'options' => fn ($q) => $q->select(['id', 'quiz_question_id', 'option_text', 'is_correct']),
            ])
            ->orderBy('order')
            ->get()
            ->map(function ($q) use ($attempt) {
                $jawaban = $attempt->answers->firstWhere('quiz_question_id', $q->id);
                return [
                    'question'       => $q->question,
                    'question_type'  => $q->question_type,
                    'correct_answer' => $q->correct_answer,
                    'options'        => $q->options,
                    'jawaban_user'   => $jawaban?->quiz_option_id ?? $jawaban?->answer_text,
                    'is_correct'     => $jawaban?->is_correct ?? false,
                ];
            });

        $attempt->load('answers');

        return response()->json([
            'success' => true,
            'message' => 'Hasil kuis berhasil diambil',
            'data'    => [
                'attempt' => [
                    'id'              => $attempt->id,
                    'participant'     => $attempt->participant_name,
                    'score'           => $attempt->score,
                    'correct_answers' => $attempt->correct_answers,
                    'total_questions' => $attempt->total_questions,
                    'duration_menit'  => $attempt->duration,
                    'completed_at'    => $attempt->completed_at?->toISOString(),
                ],
                'soal' => $soal,
            ],
        ]);
    }

    /**
     * GET /api/v1/kuis/{slug}/hasil-duel?attempt1_id=X&attempt2_id=Y
     * Hasil kuis duel (tarik tambang).
     */
    public function hasilDuel(Request $request, string $slug): JsonResponse
    {
        $request->validate([
            'attempt1_id' => ['required', 'integer'],
            'attempt2_id' => ['required', 'integer'],
        ]);

        $quiz     = Quizzes::where('slug', $slug)->where('status', 'active')->first();
        $attempt1 = $quiz ? QuizAttempt::where('id', $request->attempt1_id)->where('quiz_id', $quiz->id)->whereNotNull('completed_at')->first() : null;
        $attempt2 = $quiz ? QuizAttempt::where('id', $request->attempt2_id)->where('quiz_id', $quiz->id)->whereNotNull('completed_at')->first() : null;

        if (! $quiz || ! $attempt1 || ! $attempt2) {
            return response()->json(['success' => false, 'message' => 'Hasil duel tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hasil duel berhasil diambil',
            'data'    => [
                'quiz'     => ['id' => $quiz->id, 'title' => $quiz->title, 'total_questions' => $quiz->total_questions],
                'player1'  => [
                    'attempt_id'  => $attempt1->id,
                    'nama'        => $attempt1->player1_name,
                    'skor'        => $attempt1->player1_score,
                ],
                'player2'  => [
                    'attempt_id'  => $attempt2->id,
                    'nama'        => $attempt2->player2_name,
                    'skor'        => $attempt2->player2_score,
                ],
                'pemenang' => $attempt1->winner,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // POST
    // ─────────────────────────────────────────────

    /**
     * POST /api/v1/kuis/{slug}/mulai
     * Mulai attempt kuis normal. Mengembalikan attempt_id + soal.
     *
     * Body: { participant_name }
     */
    public function mulai(Request $request, string $slug): JsonResponse
    {
        $quiz = Quizzes::where('slug', $slug)->where('status', 'active')->first();

        if (! $quiz) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        if ($quiz->is_duel_enabled) {
            return response()->json(['success' => false, 'message' => 'Gunakan endpoint mulai-duel untuk kuis ini'], 422);
        }

        $request->validate([
            'participant_name' => ['required', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $totalSoal = $quiz->questions()->count();

            if ($totalSoal === 0) {
                return response()->json(['success' => false, 'message' => 'Kuis ini belum memiliki soal'], 422);
            }

            $attempt = QuizAttempt::create([
                'quiz_id'          => $quiz->id,
                'participant_name' => $request->participant_name,
                'score'            => 0,
                'correct_answers'  => 0,
                'total_questions'  => $totalSoal,
                'started_at'       => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memulai kuis'], 500);
        }

        // Kirim soal sekaligus agar mobile tidak perlu request tambahan
        $soal = $quiz->questions()
            ->select(['id', 'quiz_id', 'question', 'question_type', 'order'])
            ->with(['options' => fn ($q) => $q->select(['id', 'quiz_question_id', 'option_text'])->reorder()->inRandomOrder()])
            ->reorder()->inRandomOrder()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Kuis berhasil dimulai',
            'data'    => [
                'attempt_id' => $attempt->id,
                'quiz'       => ['id' => $quiz->id, 'title' => $quiz->title, 'duration' => $quiz->duration, 'music_url' => $quiz->music_url],
                'soal'       => $soal,
            ],
        ], 201);
    }

    /**
     * POST /api/v1/kuis/{slug}/mulai-duel
     * Mulai attempt kuis duel (2 pemain). Mengembalikan 2 attempt_id + soal.
     *
     * Body: { player1_name, player2_name }
     */
    public function mulaiDuel(Request $request, string $slug): JsonResponse
    {
        $quiz = Quizzes::where('slug', $slug)->where('status', 'active')->first();

        if (! $quiz) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        if (! $quiz->is_duel_enabled) {
            return response()->json(['success' => false, 'message' => 'Kuis ini bukan mode duel'], 422);
        }

        $request->validate([
            'player1_name' => ['required', 'string', 'max:255'],
            'player2_name' => ['required', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $totalSoal = $quiz->questions()->count();

            if ($totalSoal === 0) {
                return response()->json(['success' => false, 'message' => 'Kuis ini belum memiliki soal'], 422);
            }

            $shared = [
                'quiz_id'         => $quiz->id,
                'player1_name'    => $request->player1_name,
                'player2_name'    => $request->player2_name,
                'score'           => 0,
                'correct_answers' => 0,
                'total_questions' => $totalSoal,
                'started_at'      => now(),
            ];

            $attempt1 = QuizAttempt::create(array_merge($shared, ['participant_name' => $request->player1_name]));
            $attempt2 = QuizAttempt::create(array_merge($shared, ['participant_name' => $request->player2_name]));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memulai duel'], 500);
        }

        $soal = $quiz->questions()
            ->select(['id', 'quiz_id', 'question', 'question_type', 'order'])
            ->with(['options' => fn ($q) => $q->select(['id', 'quiz_question_id', 'option_text'])])
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Duel berhasil dimulai',
            'data'    => [
                'attempt1_id' => $attempt1->id,
                'attempt2_id' => $attempt2->id,
                'quiz'        => ['id' => $quiz->id, 'title' => $quiz->title, 'duration' => $quiz->duration],
                'soal'        => $soal,
            ],
        ], 201);
    }

    /**
     * POST /api/v1/kuis/{slug}/submit
     * Submit jawaban kuis normal. Mengembalikan skor langsung.
     *
     * Body: { attempt_id, answers: [{question_id, option_id?, answer_text?}] }
     */
    public function submit(Request $request, string $slug): JsonResponse
    {
        $quiz = Quizzes::where('slug', $slug)->where('status', 'active')->first();

        if (! $quiz) {
            return response()->json(['success' => false, 'message' => 'Kuis tidak ditemukan'], 404);
        }

        $request->validate([
            'attempt_id'               => ['nullable', 'integer'],
            'participant_name'         => ['required_without:attempt_id', 'string', 'max:255'],
            'started_at'               => ['required_without:attempt_id', 'date'],
            'completed_at'             => ['required_without:attempt_id', 'date'],
            'answers'                  => ['required', 'array'],
            'answers.*.question_id'    => ['required', 'exists:quiz_questions,id'],
            'answers.*.option_id'      => ['nullable', 'exists:quiz_options,id'],
            'answers.*.answer_text'    => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->attempt_id) {
            $attempt = QuizAttempt::where('id', $request->attempt_id)
                ->where('quiz_id', $quiz->id)
                ->whereNull('completed_at')
                ->first();
            if (! $attempt) {
                return response()->json(['success' => false, 'message' => 'Attempt tidak valid atau sudah selesai'], 422);
            }
        } else {
            $totalSoal = $quiz->questions()->count();
            $attempt = QuizAttempt::create([
                'quiz_id'          => $quiz->id,
                'participant_name' => $request->participant_name,
                'score'            => 0,
                'correct_answers'  => 0,
                'total_questions'  => $totalSoal,
                'started_at'       => $request->started_at,
                'completed_at'     => $request->completed_at,
            ]);
        }

        DB::beginTransaction();
        try {
            $allQuestions    = $quiz->questions()->get();
            $submittedAnswers = collect($request->answers)->keyBy('question_id');
            $correctCount    = 0;

            foreach ($allQuestions as $question) {
                $ans           = $submittedAnswers->get($question->id);
                $optionId      = null;
                $answerText    = null;
                $isCorrect     = false;

                if ($question->question_type === 'multiple_choice' && $ans && ! is_null($ans['option_id'])) {
                    $optionId  = $ans['option_id'];
                    $opt       = $question->options()->find($optionId);
                    $isCorrect = (bool) ($opt?->is_correct);
                } elseif ($question->question_type === 'fill_blank' && $ans && ! empty($ans['answer_text'])) {
                    $answerText = $ans['answer_text'];
                    $isCorrect  = strtolower(trim($answerText)) === strtolower(trim($question->correct_answer));
                }

                if ($isCorrect) {
                    $correctCount++;
                }

                QuizAnswer::create([
                    'quiz_attempt_id'  => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'quiz_option_id'   => $optionId,
                    'answer_text'      => $answerText,
                    'is_correct'       => $isCorrect,
                ]);
            }

            $total = $allQuestions->count();
            $score = $total > 0 ? round(($correctCount / $total) * 100, 2) : 0;

            $attempt->update([
                'score'           => $score,
                'correct_answers' => $correctCount,
                'completed_at'    => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan jawaban'], 500);
        }

        $review = $quiz->questions()
            ->select(['id', 'question', 'correct_answer', 'order'])
            ->with(['options' => fn ($q) => $q->select(['id', 'quiz_question_id', 'option_text', 'is_correct'])])
            ->orderBy('order')
            ->get()
            ->map(function ($q) use ($attempt) {
                $jawaban = QuizAnswer::where('quiz_attempt_id', $attempt->id)
                    ->where('quiz_question_id', $q->id)
                    ->first();
                return [
                    'question_id'        => $q->id,
                    'question'           => $q->question,
                    'options'            => $q->options,
                    'selected_option_id' => $jawaban?->quiz_option_id,
                    'is_correct'         => $jawaban?->is_correct ?? false,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Jawaban berhasil disimpan',
            'data'    => [
                'attempt_id'      => $attempt->id,
                'score'           => $score,
                'correct_answers' => $correctCount,
                'total_questions' => $total,
                'review'          => $review,
            ],
        ]);
    }

    /**
     * POST /api/v1/kuis/{slug}/submit-duel
     * Submit jawaban kuis duel 2 pemain. Mengembalikan pemenang + skor.
     *
     * Body: { attempt1_id, attempt2_id, player1_answers: [...], player2_answers: [...] }
     */
    public function submitDuel(Request $request, string $slug): JsonResponse
    {
        $quiz = Quizzes::where('slug', $slug)->where('status', 'active')->first();

        if (! $quiz || ! $quiz->is_duel_enabled) {
            return response()->json(['success' => false, 'message' => 'Kuis duel tidak ditemukan'], 404);
        }

        $request->validate([
            'attempt1_id'                   => ['required', 'integer'],
            'attempt2_id'                   => ['required', 'integer'],
            'player1_answers'               => ['required', 'array'],
            'player1_answers.*.question_id' => ['required', 'exists:quiz_questions,id'],
            'player1_answers.*.option_id'   => ['nullable', 'exists:quiz_options,id'],
            'player1_answers.*.answer_text' => ['nullable', 'string', 'max:500'],
            'player2_answers'               => ['required', 'array'],
            'player2_answers.*.question_id' => ['required', 'exists:quiz_questions,id'],
            'player2_answers.*.option_id'   => ['nullable', 'exists:quiz_options,id'],
            'player2_answers.*.answer_text' => ['nullable', 'string', 'max:500'],
        ]);

        $attempt1 = QuizAttempt::where('id', $request->attempt1_id)->where('quiz_id', $quiz->id)->whereNull('completed_at')->first();
        $attempt2 = QuizAttempt::where('id', $request->attempt2_id)->where('quiz_id', $quiz->id)->whereNull('completed_at')->first();

        if (! $attempt1 || ! $attempt2) {
            return response()->json(['success' => false, 'message' => 'Attempt tidak valid atau sudah selesai'], 422);
        }

        DB::beginTransaction();
        try {
            $allQuestions = $quiz->questions()->get();

            [$p1Correct] = $this->prosesJawabanDuel($attempt1, $request->player1_answers, $allQuestions);
            [$p2Correct] = $this->prosesJawabanDuel($attempt2, $request->player2_answers, $allQuestions);

            $winner = match(true) {
                $p1Correct > $p2Correct => 'player1',
                $p2Correct > $p1Correct => 'player2',
                default                 => 'draw',
            };

            $total = $allQuestions->count();

            foreach ([$attempt1, $attempt2] as $a) {
                $a->update([
                    'player1_score' => $p1Correct,
                    'player2_score' => $p2Correct,
                    'winner'        => $winner,
                    'completed_at'  => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan hasil duel'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Duel selesai',
            'data'    => [
                'player1'  => ['nama' => $attempt1->player1_name, 'skor' => $p1Correct, 'attempt_id' => $attempt1->id],
                'player2'  => ['nama' => $attempt2->player2_name, 'skor' => $p2Correct, 'attempt_id' => $attempt2->id],
                'pemenang' => $winner,
                'total_soal' => $total,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────

    /**
     * Proses jawaban satu pemain dalam mode duel, simpan ke DB, return [correctCount].
     */
    private function prosesJawabanDuel(QuizAttempt $attempt, array $answers, $allQuestions): array
    {
        $submitted    = collect($answers)->keyBy('question_id');
        $correctCount = 0;

        foreach ($allQuestions as $question) {
            $ans       = $submitted->get($question->id);
            $optionId  = null;
            $ansText   = null;
            $isCorrect = false;

            if ($question->question_type === 'fill_blank' && $ans && ! empty($ans['answer_text'])) {
                $ansText   = $ans['answer_text'];
                $isCorrect = strtolower(trim($ansText)) === strtolower(trim($question->correct_answer));
            } elseif ($ans && ! is_null($ans['option_id'] ?? null)) {
                $optionId  = $ans['option_id'];
                $opt       = $question->options()->where('is_correct', true)->first();
                $isCorrect = $opt && $opt->id == $optionId;
            }

            if ($isCorrect) {
                $correctCount++;
            }

            QuizAnswer::create([
                'quiz_attempt_id'  => $attempt->id,
                'quiz_question_id' => $question->id,
                'quiz_option_id'   => $optionId,
                'answer_text'      => $ansText,
                'is_correct'       => $isCorrect,
            ]);
        }

        return [$correctCount];
    }
}
