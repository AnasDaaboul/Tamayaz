<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\SenJem;
use App\Models\Unit;
use App\Models\User;
use App\Models\CourseStudentElo;
use App\Models\CompetitiveQuestion;
use App\Models\CompetitiveMatch;
use App\Models\StudentCompetitiveQuestionLock;
use App\Models\MatchLifeline;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CompetitiveGameController extends Controller
{
    

    /**
     * Create a new competitive match.
     * Expects player1_name and player2_name.
     */
    public function createMatch(Request $request)
    {
        $user_id = auth()->id();
        // dd($user_id);
        $validator = Validator::make($request->all(), [
            'match_name' => 'required|string|max:255',
            'player1_name' => 'required|string|max:255',
            'player2_name' => 'required|string|max:255',
            'topics' => 'required|array|max:6',
            'topics.*' => 'integer|exists:units,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $match = CompetitiveMatch::create([
            'match_name' => $request->match_name,
            'player1_name' => $request->player1_name,
            'player2_name' => $request->player2_name,
            'topics' => $request->topics,
            'player1_score' => 0,
            'player2_score' => 0,
            'user_id' => auth()->id(), // Store the authenticated user's ID
        ]);
    
        return response()->json([
            'message' => 'Match created successfully',
            'match' => $match
        ], 201);
    }

    /**
     * Get all courses and their associated units.
     */
    public function getCoursesWithUnits()
    {
        $courses = Course::with(['units' => function ($query) {
            $query->where('is_active', true)->select('id', 'course_id', 'name')
                ->with(['media']);
        }])
        // ->where('is_active', true) // Assuming courses can also be inactive
          ->select('id', 'name') // Select only necessary fields from course
          ->get();

        return response()->json($courses->map(function ($course) {
    return [
        'id' => $course->id,
        'name' => $course->name,
        'units' => $course->units->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'media' => $unit->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'file_name' => $media->file_name,
                        'image_url'=> "https://backend1.tamayaz.tech/api/get-image/" . $media?->id . "/" . $media?->file_name,
                    ];
                })
            ];
        })
    ];
}));
    }

    
    public function getGameQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'match_id' => 'required|exists:competitive_matches,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $match_id = $request->match_id;
        $user_id = auth()->id();
        
        // Check if the user is the creator of this match
        $match = CompetitiveMatch::findOrFail($match_id);
        $isUserMatch = $match->user_id == $user_id;
            
        if (!$isUserMatch) {
            return response()->json(['error' => 'You are not authorized to access this match'], 403);
        }
        
        $match = CompetitiveMatch::findOrFail($match_id);
        // dd($match_id);
        $matchUnits = $match->topics;
        
        // $match = CompetitiveMatch::with('units')->findOrFail($request->match_id);
        $user = auth()->user();
        $student_id = $user->userable_id;
        
        
        $lockedQuestionsIds = StudentCompetitiveQuestionLock::where('student_id', $student_id)->pluck('competitive_question_id');
        // dd($student_id);
        $gameQuestions = [];
        
        // Get questions for each unit from topics array
        foreach ($matchUnits as $unitId) {
            $unitQuestions = [];
            
            // Get 2 questions for each difficulty level
            // Easy questions
            $easyQuestions = collect(
                CompetitiveQuestion::where('unit_id', $unitId)
                    ->where('difficulty', 'easy')
                    ->where('is_active', true)
                    ->whereNotIn('id', $lockedQuestionsIds)
                    ->inRandomOrder()
                    ->take(2)
                    ->with('media')
                    ->get(['id', 'question_text', 'correct_answer', 'difficulty'])
                    ->map(function ($question) {
                        $questionArray = $question->toArray();
                        $questionArray['media'] = $question->media->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'file_name' => $media->file_name,
                                'image_url'=> "https://backend1.tamayaz.tech/api/get-image/" . $media?->id . "/" . $media?->file_name,
                            ];
                        })->toArray();
                        return $questionArray;
                    })
            );
            

            
            // Medium questions
            $mediumQuestions = collect(
                CompetitiveQuestion::where('unit_id', $unitId)
                    ->where('difficulty', 'medium')
                    ->where('is_active', true)
                    ->whereNotIn('id', $lockedQuestionsIds)
                    ->inRandomOrder()
                    ->take(2)
                    ->with('media')
                    ->get(['id', 'question_text', 'correct_answer', 'difficulty'])
                    ->map(function ($question) {
                        $questionArray = $question->toArray();
                        $questionArray['media'] = $question->media->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'file_name' => $media->file_name,
                            ];
                        })->toArray();
                        return $questionArray;
                    })
            );
            
            
        
            
            // Hard questions
            $hardQuestions = collect(
                CompetitiveQuestion::where('unit_id', $unitId)
                    ->where('difficulty', 'hard')
                    ->where('is_active', true)
                    ->whereNotIn('id', $lockedQuestionsIds)
                    ->inRandomOrder()
                    ->take(2)
                    ->with('media')
                    ->get(['id', 'question_text', 'correct_answer', 'difficulty'])
                    ->map(function ($question) {
                        $questionArray = $question->toArray();
                        $questionArray['media'] = $question->media->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'file_name' => $media->file_name,
                            ];
                        })->toArray();
                        return $questionArray;
                    })
            );
            
    
            
            $unitQuestions = [
                'easy' => $easyQuestions,
                'medium' => $mediumQuestions,
                'hard' => $hardQuestions
            ];
            
            $gameQuestions[$unitId] = $unitQuestions;
            // dd($ques)

            // Lock all retrieved questions for this unit
            $allQuestions = $easyQuestions->merge($mediumQuestions)->merge($hardQuestions);
            foreach ($allQuestions as $question) {
                // dd($question['id']);
                 StudentCompetitiveQuestionLock::create([
                     'student_id' => $student_id,
                     'competitive_question_id' => $question['id'],
                     'locked'=>1,
                     'competitive_match_id'=>$match_id
                 ]);
            }
        }
        
        return response()->json([
            'message' => 'Game questions retrieved successfully',
            'questions' => $gameQuestions
        ]);
    }


public function finalizeMatch(Request $request)
{
    $validated = $request->validate([
        'match_id' => 'required|integer|exists:competitive_matches,id',
        'player1_score' => 'required|integer|min:0',
        'player2_score' => 'required|integer|min:0'
    ]);

    $user_id = auth()->id();
    
    // Check if the user is the creator of this match
    $match = CompetitiveMatch::findOrFail($validated['match_id']);
    $isUserMatch = $match->user_id == $user_id;
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to finalize this match'], 403);
    }

    $match->update([
        'status' => 'finished',
        'player1_score' => $validated['player1_score'],
        'player2_score' => $validated['player2_score']
    ]);
    
    // Refresh the model to get the latest data
    $match = $match->fresh();

    return response()->json([
        'message' => 'Match finalized successfully',
        'data' => [
            'id' => $match->id,
            'match_name' => $match->match_name,
            'player1_name' => $match->player1_name,
            'player2_name' => $match->player2_name,
            'player1_score' => $match->player1_score,
            'player2_score' => $match->player2_score,
            'status' => $match->status
        ]
    ]);
}

    
public function myGames()
{
    $user = auth()->user();
    $student_id = $user->userable_id;
    // $matches = CompetitiveMatch::whereHas('studentCompetitiveQuestionLocks', function($query) use ($student_id) {
    //     $query->where('student_id', $student_id);
    // })->get();
    $matches = CompetitiveMatch::where('user_id', $user->id)->get();
    
    return response()->json([
        'message' => 'Your games retrieved successfully',
        'matches' => $matches
    ]);
}


public function submitAnswer(Request $request)
{
    $user_id = auth()->id();
    $user = User::find($user_id);
    $student_id = $user->userable_id;
    $validated = $request->validate([
        'match_id' => 'required|exists:competitive_matches,id',
        'question_id' => 'required|exists:competitive_questions,id',
        'answer' => 'required|string',
        'player_id' => 'integer|in:1,2',
        'player1_score' => 'required|integer|min:0',
        'player2_score' => 'required|integer|min:0'
    ]);

    // Check if the user is the creator of this match
    $match = CompetitiveMatch::findOrFail($validated['match_id']);
    $isUserMatch = $match->user_id == $user_id;
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to submit answers for this match'], 403);
    }

    $match = CompetitiveMatch::findOrFail($validated['match_id']);
    if($match->status == "finished")
    {
        return response()->json("this match is finished" ,409);
    }
    // Check if this player already answered the question
    $existingAnswer = SenJem::where('competitive_match_id', $match->id)
        ->where('question_id', $validated['question_id'])
        ->first();
        
    if ($existingAnswer) {
        return response()->json([
            'error' => 'Player already answered this question',
            'correct_answer' => CompetitiveQuestion::find($validated['question_id'])->correct_answer
        ], 409);
    }
    
    $question = CompetitiveQuestion::with('unit')->find($validated['question_id']);
    $isCorrect = $question->correct_answer === $validated['answer'];
    $points = $this->calculatePoints($question->difficulty);
    
    SenJem::create([
        'competitive_match_id' => $match->id,
        'question_id' => $question->id,
        'question_difficulty' => $question->difficulty,
        'is_correct' => $isCorrect,
        'user_id' => $user_id,
        'answered' => isset($validated['player_id']) ? 1 : 0,
        'player_number' => $validated['player_id'] ?? 0,
        'points_earned' => isset($validated['player_id']) ? $points : 0
    ]);

    // Update player scores with the values from the request
    $match->update([
        'player1_score' => $validated['player1_score'],
        'player2_score' => $validated['player2_score']
    ]);

    // Update match status if all questions answered
    
    // Calculate total questions based on number of topics

    return response()->json([
        'is_correct' => $isCorrect,
        'points' => $isCorrect ? $points : 0,
        'unit_name' => $question->unit->name,
        'difficulty' => $question->difficulty,
        'player1_score' => $validated['player1_score'],
        'player2_score' => $validated['player2_score']
    ]);
}




private function calculatePoints($difficulty)
{
    return match(strtolower($difficulty)) {
        'hard' => 300,
        'medium' => 200,
        'easy' => 100,
        default => 0
    };
}

public function changeQuestion(Request $request)
{
    $validator = Validator::make($request->all(), [
        'match_id' => 'required|exists:competitive_matches,id',
        'question_id' => 'required|exists:competitive_questions,id',
        'player_id' => 'required|in:1,2'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user_id = auth()->id();
    $user = User::find($user_id);
    $student_id = $user->userable_id;

    // Check if the user is the creator of this match
    $match = CompetitiveMatch::findOrFail($request->match_id);
    $isUserMatch = $match->user_id == $user_id;
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to change questions for this match'], 403);
    }

    // Get match details
    $match = CompetitiveMatch::findOrFail($request->match_id);

    // Get or create lifeline for this player number
    $playerLifeline = MatchLifeline::firstOrCreate(
        [
            'competitive_match_id' => $request->match_id,
            'student_id' => $student_id,
            'player_number' => $request->player_id
        ],
        [
            'change_question_used' => false,
            'get_options_used' => false,
            'third_lifeline_used' => false,
            'fourth_lifeline_used' => false,
            'updown_lifeline_used' => false
        ]
    );

    if ($playerLifeline->change_question_used) {
        return response()->json(['error' => 'Change question lifeline already used for this player'], 400);
    }

    // Get current question details
    $currentQuestion = CompetitiveQuestion::findOrFail($request->question_id);

    // Get a new question with same difficulty and unit
    $newQuestion = CompetitiveQuestion::where('unit_id', $currentQuestion->unit_id)
        ->where('difficulty', $currentQuestion->difficulty)
        ->where('id', '!=', $currentQuestion->id)
        ->where('is_active', true)
        ->whereNotIn('id', function($query) use ($student_id) {
            $query->select('competitive_question_id')
                  ->from('student_competitive_question_locks')
                  ->where('student_id', $student_id);
        })
        ->inRandomOrder()
        ->first();

    if (!$newQuestion) {
        return response()->json(['error' => 'No alternative questions available'], 404);
    }

    // Find the lock record for the old question
    $questionLock = StudentCompetitiveQuestionLock::where([
        'student_id' => $student_id,
        'competitive_question_id' => $currentQuestion->id,
        'competitive_match_id' => $request->match_id,
    ])->first();

    if ($questionLock) {
        // Update the existing lock to point to the new question instead of creating a new one
        $questionLock->update([
            'competitive_question_id' => $newQuestion->id
        ]);
    } else {
        // If for some reason the lock doesn't exist, create a new one
        StudentCompetitiveQuestionLock::create([
            'student_id' => $student_id,
            'competitive_question_id' => $newQuestion->id,
            'competitive_match_id' => $request->match_id,
            'locked' => true
        ]);
    }

    // Mark lifeline as used
    $playerLifeline->update(['change_question_used' => true]);

    return response()->json([
        'message' => 'Question changed successfully',
        'new_question' => [
            'id' => $newQuestion->id,
            'question_text' => $newQuestion->question_text,
            'difficulty' => $newQuestion->difficulty,
            'media' => $newQuestion->media->map(fn($m) => ['id' => $m->id, 'file_name' => $m->file_name])
        ]
    ]);
}
public function getQuestionOptions(Request $request)
{
    $validator = Validator::make($request->all(), [
        'match_id' => 'required|exists:competitive_matches,id',
        'question_id' => 'required|exists:competitive_questions,id',
        'player_id' => 'required|in:1,2'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user_id = auth()->id();
    $user = User::find($user_id);
    $student_id = $user->userable_id;

    // Check if the user is the creator of this match
    $match = CompetitiveMatch::findOrFail($request->match_id);
    $isUserMatch = $match->user_id == $user_id;
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to get options for this match'], 403);
    }

    // Check if the user is associated with this match
    $isUserMatch = StudentCompetitiveQuestionLock::where('competitive_match_id', $request->match_id)
        ->where('student_id', $student_id)
        ->exists();
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to get options for this match'], 403);
    }

    // Get or create lifeline for this player number
    $playerLifeline = MatchLifeline::firstOrCreate(
        [
            'competitive_match_id' => $request->match_id,
            'student_id' => $student_id,
            'player_number' => $request->player_id
        ],
        [
            'change_question_used' => false,
            'get_options_used' => false,
            'third_lifeline_used' => false,
            'fourth_lifeline_used' => false,
            'updown_lifeline_used' => false
        ]
    );

    if ($playerLifeline->get_options_used) {
        return response()->json(['error' => 'Get options lifeline already used for this player'], 400);
    }

    // Get question options
    $question = CompetitiveQuestion::findOrFail($request->question_id);
    
    // Mark lifeline as used
    $playerLifeline->update(['get_options_used' => true]);

    return response()->json([
        'message' => 'Options retrieved successfully',
        'options' => $question->options
    ]);
}

public function getMatchQuestions(Request $request)
{
    $validator = Validator::make($request->all(), [
        'match_id' => 'required|exists:competitive_matches,id'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user_id = auth()->id();
     $user = User::find($user_id);
    $student_id = $user->userable_id;
    $match_id = $request->match_id;
    
    // Check if the user is the creator of this match
    $match = CompetitiveMatch::findOrFail($match_id);
    $isUserMatch = $match->user_id == $user_id;
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to access this match'], 403);
    }
    
    // Get match details
    $match = CompetitiveMatch::findOrFail($match_id);
    
    // Get lifeline status for both players
    $lifelines = MatchLifeline::where('competitive_match_id', $match_id)
        ->get();
    
    // Determine player number for current user
    $player_number = null;
    $senjem = SenJem::where('competitive_match_id', $match_id)
        ->where('user_id', $student_id)
        ->first();
    if ($senjem) {
        $player_number = $senjem->player_number;
    }
    
    // Get or create lifeline for current player if player number is known
    if ($player_number) {
        $playerLifeline = MatchLifeline::firstOrCreate(
            [
                'competitive_match_id' => $match_id,
                'student_id' => $student_id,
                'player_number' => $player_number
            ],
            [
                'change_question_used' => false,
                'get_options_used' => false,
                'third_lifeline_used' => false,
                'fourth_lifeline_used' => false,
                'updown_lifeline_used' => false
            ]
        );
    }
    
    // Get all locked questions for this match
    $lockedQuestions = StudentCompetitiveQuestionLock::where('competitive_match_id', $match_id)
        ->with(['competitiveQuestion' => function($query) {
            $query->with(['unit', 'media'])
                  ->select('id', 'question_text', 'correct_answer', 'difficulty', 'unit_id');
        }])
        ->get();
    
    // Organize questions by unit_id and difficulty
    $questionsByUnit = [];
    foreach ($lockedQuestions as $lock) {
        $question = $lock->competitiveQuestion;
        $unitId = $question->unit_id;
        $difficulty = $question->difficulty;
        
        // Initialize unit array if not exists
        if (!isset($questionsByUnit[$unitId])) {
            $questionsByUnit[$unitId] = [
                'easy' => [],
                'medium' => [],
                'hard' => []
            ];
        }
        
        // Get answer details if question is answered
        $answered = SenJem::where('competitive_match_id', $lock->competitive_match_id)
            ->where('question_id', $lock->competitive_question_id)
            ->first();
        
        // Format question data
        $questionData = [
            'id' => $question->id,
            'question_text' => $question->question_text,
            'correct_answer' => $question->correct_answer,
            'difficulty' => $question->difficulty,
            'media' => $question->media->map(fn($m) => [
                'id' => $m->id,
                'file_name' => $m->file_name,
                'image_url'=> "https://backend1.tamayaz.tech/api/get-image/" . $m?->id . "/" . $m?->file_name,
            ]),
            'unit' => $question->unit->name,
            'is_answered' => !is_null($answered),
            'answer_details' => $answered ? [
                'is_correct' => $answered->is_correct,
                'points_earned' => $answered->points_earned,
                'player_number' => $answered->player_number
            ] : null
        ];
        
        // Add question to appropriate difficulty array
        $questionsByUnit[$unitId][strtolower($difficulty)][] = $questionData;
    }
    
    // Get selected topics
    $selectedTopics = [];
    if (!empty($match->topics)) {
        $topicIds = $match->topics;
        $units = Unit::whereIn('id', $topicIds)
            ->with('media')
            ->get()
            ->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'media' => $unit->media->map(fn($m) => [
                        // dd($m),
                        'id' => $m->id,
                        'file_name' => $m->file_name,
                        'image_url'=> "https://backend1.tamayaz.tech/api/get-image/" . $m?->id . "/" . $m?->file_name,
                    ])
                ];
            });
        $selectedTopics = $units;
    }
    
    // Get lifeline status for both players
    $player1Lifeline = $lifelines->where('player_number', 1)->first() ?? new MatchLifeline([
        'change_question_used' => false,
        'get_options_used' => false,
        'third_lifeline_used' => false,
        'fourth_lifeline_used' => false,
        'updown_lifeline_used' => false
    ]);
    
    $player2Lifeline = $lifelines->where('player_number', 2)->first() ?? new MatchLifeline([
        'change_question_used' => false,
        'get_options_used' => false,
        'third_lifeline_used' => false,
        'fourth_lifeline_used' => false,
        'updown_lifeline_used' => false
    ]);
    
    // Return response with all data
    return response()->json([
        'questions' => $questionsByUnit,
        'lifelines' => [
            'player1' => [
                'change_question_used' => $player1Lifeline->change_question_used,
                'get_options_used' => $player1Lifeline->get_options_used,
                'third_lifeline_used' => $player1Lifeline->third_lifeline_used,
                'fourth_lifeline_used' => $player1Lifeline->fourth_lifeline_used,
                'updown_lifeline_used' => $player1Lifeline->updown_lifeline_used
            ],
            'player2' => [
                'change_question_used' => $player2Lifeline->change_question_used,
                'get_options_used' => $player2Lifeline->get_options_used,
                'third_lifeline_used' => $player2Lifeline->third_lifeline_used,
                'fourth_lifeline_used' => $player2Lifeline->fourth_lifeline_used,
                'updown_lifeline_used' => $player2Lifeline->updown_lifeline_used
            ]
        ],
        'selected_topics' => $selectedTopics
    ]);
}



public function getUserElo(Request $request)
{
    $validator = Validator::make($request->all(), [
        'subject_id' => 'required|exists:courses,id'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = auth()->user();
    $student_id = $user->userable_id;

    $elo = CourseStudentElo::where([
        'course_id' => $request->subject_id,
        'student_id' => $student_id
    ])->first();

    if (!$elo) {
        $elo = CourseStudentElo::create([
            'course_id' => $request->subject_id,
            'student_id' => $student_id,
            'elo_rating' => 1200 // Default ELO rating
        ]);
    }

    return response()->json([
        'user_name' => $user->full_name,
        'elo_rating' => $elo->elo_rating
    ]);
}




public function useUpDownLifeline(Request $request)
{
    $validator = Validator::make($request->all(), [
        'match_id' => 'required|exists:competitive_matches,id',
        'player_id' => 'required|in:1,2',
        'question_id' => 'required|exists:competitive_questions,id',
        'answer' => 'required|string',
        'player1_score' => 'required|integer|min:0',
        'player2_score' => 'required|integer|min:0'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user_id = auth()->id();
    $user = User::find($user_id);
    $student_id = $user->userable_id;

    // Check if the user is the creator of this match
    $match = CompetitiveMatch::findOrFail($request->match_id);
    $isUserMatch = $match->user_id == $user_id;
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to use lifelines for this match'], 403);
    }
    
    // Check if the question is retrieved for this match
    $questionLock = StudentCompetitiveQuestionLock::where('competitive_match_id', $request->match_id)
        ->where('competitive_question_id', $request->question_id)
        ->first();
        
    if (!$questionLock) {
        return response()->json(['error' => 'This question is not retrieved for this match'], 400);
    }
    
    // Check if the question is already answered
    $existingAnswer = SenJem::where('competitive_match_id', $request->match_id)
        ->where('question_id', $request->question_id)
        ->first();
        
    if ($existingAnswer) {
        return response()->json(['error' => 'This question is already answered'], 400);
    }

    // Get or create lifeline for this player number
    $playerLifeline = MatchLifeline::firstOrCreate(
        [
            'competitive_match_id' => $request->match_id,
            'student_id' => $student_id,
            'player_number' => $request->player_id
        ],
        [
            'change_question_used' => false,
            'get_options_used' => false,
            'third_lifeline_used' => false,
            'fourth_lifeline_used' => false,
            'updown_lifeline_used' => false
        ]
    );

    if ($playerLifeline->updown_lifeline_used) {
        return response()->json(['error' => 'UpDown lifeline already used for this player'], 400);
    }

    // Get question to determine points
    $question = CompetitiveQuestion::findOrFail($request->question_id);
    $isCorrect = $question->correct_answer === $request->answer;
    $pointsToTransfer = 0;
    $player1Score = $request->player1_score;
    $player2Score = $request->player2_score;
    
    // Only calculate and transfer points if the answer is correct
    if ($isCorrect) {
        // Determine points based on difficulty
        switch (strtolower($question->difficulty)) {
            case 'easy':
                $pointsToTransfer = 200;
                break;
            case 'medium':
                $pointsToTransfer = 300;
                break;
            case 'hard':
                $pointsToTransfer = 400;
                break;
            default:
                $pointsToTransfer = 200;
        }

        // Update scores
        $currentPlayerNumber = $request->player_id;
        $otherPlayerNumber = $currentPlayerNumber == 1 ? 2 : 1;
        
        // Transfer points
        if ($currentPlayerNumber == 1) {
            $player1Score += $pointsToTransfer;
            $player2Score = max(0, $player2Score - $pointsToTransfer); // Ensure score doesn't go below 0
        } else {
            $player2Score += $pointsToTransfer;
            $player1Score = max(0, $player1Score - $pointsToTransfer); // Ensure score doesn't go below 0
        }
        
        // Update match scores
        $match->update([
            'player1_score' => $player1Score,
            'player2_score' => $player2Score
        ]);
    }

    // Mark lifeline as used
    $playerLifeline->update(['updown_lifeline_used' => true]);
    
    // Mark the question as answered
    SenJem::create([
        'competitive_match_id' => $request->match_id,
        'question_id' => $request->question_id,
        'question_difficulty' => $question->difficulty,
        'is_correct' => $isCorrect,
        'user_id' => $user_id,
        'answered' => 1,
        'player_number' => $request->player_id,
        'points_earned' => $isCorrect ? $pointsToTransfer : 0 // Points only if correct
    ]);

    return response()->json([
        'message' => 'UpDown lifeline used successfully',
        'is_correct' => $isCorrect,
        'lifeline_status' => [
            'change_question_used' => $playerLifeline->change_question_used,
            'get_options_used' => $playerLifeline->get_options_used,
            'third_lifeline_used' => $playerLifeline->third_lifeline_used,
            'fourth_lifeline_used' => $playerLifeline->fourth_lifeline_used,
            'updown_lifeline_used' => true
        ],
        'updated_scores' => [
            'player1_score' => $player1Score,
            'player2_score' => $player2Score
        ],
        'points_earned' => $isCorrect ? $pointsToTransfer : 0
    ]);
}


public function useFourthLifeline(Request $request)
{
    $validator = Validator::make($request->all(), [
        'match_id' => 'required|exists:competitive_matches,id',
        'player_id' => 'required|in:1,2'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user_id = auth()->id();
    $user = User::find($user_id);
    $student_id = $user->userable_id;

    // Check if the user is the creator of this match
    $match = CompetitiveMatch::findOrFail($request->match_id);
    $isUserMatch = $match->user_id == $user_id;
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to use lifelines for this match'], 403);
    }

    // Get or create lifeline for this player number
    $playerLifeline = MatchLifeline::firstOrCreate(
        [
            'competitive_match_id' => $request->match_id,
            'student_id' => $student_id,
            'player_number' => $request->player_id
        ],
        [
            'change_question_used' => false,
            'get_options_used' => false,
            'third_lifeline_used' => false,
            'fourth_lifeline_used' => false,
            'updown_lifeline_used' => false
        ]
    );

    if ($playerLifeline->fourth_lifeline_used) {
        return response()->json(['error' => 'Fourth lifeline already used for this player'], 400);
    }

    // Mark lifeline as used
    $playerLifeline->update(['fourth_lifeline_used' => true]);

    return response()->json([
        'message' => 'Fourth lifeline used successfully',
        'lifeline_status' => [
            'change_question_used' => $playerLifeline->change_question_used,
            'get_options_used' => $playerLifeline->get_options_used,
            'third_lifeline_used' => $playerLifeline->third_lifeline_used,
            'fourth_lifeline_used' => true,
            'updown_lifeline_used' => $playerLifeline->updown_lifeline_used
        ]
    ]);
}

public function useThirdLifeline(Request $request)
{
    $validator = Validator::make($request->all(), [
        'match_id' => 'required|exists:competitive_matches,id',
        'player_id' => 'required|in:1,2'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user_id = auth()->id();
     $user = User::find($user_id);
    $student_id = $user->userable_id;

    // Check if the user is the creator of this match
    $match = CompetitiveMatch::findOrFail($request->match_id);
    $isUserMatch = $match->user_id == $user_id;
        
    if (!$isUserMatch) {
        return response()->json(['error' => 'You are not authorized to use lifelines for this match'], 403);
    }

    // Get match details
    $match = CompetitiveMatch::findOrFail($request->match_id);

    // Get or create lifeline for this player number
    $playerLifeline = MatchLifeline::firstOrCreate(
        [
            'competitive_match_id' => $request->match_id,
            'student_id' => $student_id,
            'player_number' => $request->player_id
        ],
        [
            'change_question_used' => false,
            'get_options_used' => false,
            'third_lifeline_used' => false,
            'fourth_lifeline_used' => false,
            'updown_lifeline_used' => false
        ]
    );

    if ($playerLifeline->third_lifeline_used) {
        return response()->json(['error' => 'Third lifeline already used for this player'], 400);
    }

    // Mark lifeline as used
    $playerLifeline->update(['third_lifeline_used' => true]);

    return response()->json([
        'message' => 'Third lifeline used successfully',
        'lifeline_status' => [
            'change_question_used' => $playerLifeline->change_question_used,
            'get_options_used' => $playerLifeline->get_options_used,
            'third_lifeline_used' => true,
            'fourth_lifeline_used' => $playerLifeline->fourth_lifeline_used,
            'updown_lifeline_used' => $playerLifeline->updown_lifeline_used
        ]
    ]);
}



}