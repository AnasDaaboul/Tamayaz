<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Matching;
use App\Models\Student;
use App\Services\CompetitiveMatchService;    
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetitiveMatchController extends Controller
{
    private CompetitiveMatchService $matchService;

    public function __construct(CompetitiveMatchService $matchService)
    {
        // dd(auth()->user());
        $this->matchService = $matchService;
    }

    public function findMatch(Course $course )
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated123.'], 401);
        }
        
        $studentId = $user->userable_id;
        $student = Student::find($studentId);
        
        if (!$student) {
            return response()->json(['message' => 'Student not found.'], 404);
        }
        
        $match = $this->matchService->findMatch($student, $course->id);
        // dd($match->player2_id);
        // $user = User::find("userable_id" , $match->player2_id)->where("userable_type" , 'App\Models\Student');\
            if($match->status !== 'waiting')
        {$user = User::where('userable_id', $match->player2_id)
            ->where('userable_type', 'App\Models\Student')
            ->first();}
        return response()->json([
            'match_id' => $match->id,
            'status' => $match->status,
            'opponent' => $match->status === 'waiting' ? null : [
                'id' => $match->player2_id,
                'name' => $user->full_name
            ]
        ]);
    }

    public function submitAnswer(Request $request, Matching $match): JsonResponse
    {
        $request->validate([
            'is_correct' => 'required|boolean',
            'response_time' => 'required|numeric|min:0'
        ]);

        $student = Auth::user()->student;

        if (!in_array($student->id, [$match->player1_id, $match->player2_id])) {
            return response()->json(['message' => 'غير مصرح لك بالمشاركة في هذه المباراة'], 403);
        }

        $this->matchService->submitAnswer(
            $match,
            $student,
            $request->boolean('is_correct'),
            $request->float('response_time')
        );

        return response()->json(['message' => 'تم تسجيل إجابتك بنجاح']);
    }

    public function getLeaderboard(Course $course): JsonResponse
    {
        $topPlayers = $course->playerRatings()
            ->with('student.user')
            ->orderByDesc('rating')
            ->take(10)
            ->get()
            ->map(function ($rating) {
                return [
                    'student_id' => $rating->student_id,
                    'name' => $rating->student->user->name,
                    'rating' => $rating->rating,
                    'wins' => $rating->wins,
                    'losses' => $rating->losses,
                    'draws' => $rating->draws
                ];
            });

        return response()->json($topPlayers);
    }
}