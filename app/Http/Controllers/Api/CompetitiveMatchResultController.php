<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Matching;
use App\Models\PlayerRating; 
use App\Models\CourseStudentElo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

class CompetitiveMatchResultController extends Controller
{
    /**
     * Finalize a competitive match
     * This endpoint will be called by the Node.js server when a match is completed
     */
    public function finalizeMatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'match_id'       => 'required|exists:matchings,id',
            'player1_score'  => 'required|integer|min:0',
            'player2_score'  => 'required|integer|min:0',
            'winner_id'      => 'required|exists:students,id',
            'completed'      => 'required|boolean'
        ]);
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $match = Matching::findOrFail($request->match_id);
            
            // Update match details
            $match->update([
                'status' => 'completed',
                'player1_score' => $request->player1_score,
                'player2_score' => $request->player2_score,
                'winner_id' => $request->winner_id,
                'ended_at' => Carbon::now()
            ]);

            // Update player ratings
            $this->updatePlayerRatings($match, $request->completed);

            return response()->json([
                'message' => 'Match finalized successfully',
                'match' => $match->fresh()
            ]);
        });
    }

    /**
     * Update player ratings based on match results
     */
    private function updatePlayerRatings(Matching $match, bool $completed): void
{
    $player1Rating = PlayerRating::firstOrCreate(
        ['student_id' => $match->player1_id, 'course_id' => $match->course_id],
        ['rating' => 1200]
    );
    
    $player2Rating = PlayerRating::firstOrCreate(
        ['student_id' => $match->player2_id, 'course_id' => $match->course_id],
        ['rating' => 1200]
    );

    // Determine actual score for player1 (1 = win, 0 = loss, 0.5 = draw)
    $score = $match->winner_id === null ? 0.5 :
            ($match->winner_id === $match->player1_id ? 1 : 0);

    // Calculate Elo change using standard formula
    $player1EloChange = PlayerRating::calculateEloChange(
        $player1Rating->rating,
        $player2Rating->rating,
        $score
    );

    // If match was not completed (forfeit case), reduce Elo impact by 50%
    if (!$completed) {
        $player1EloChange = (int) round($player1EloChange * 0.5);
    }

    // Apply rating changes (zero-sum: one gains, one loses)
    $player1Rating->increment('rating', $player1EloChange);
    $player2Rating->increment('rating', -$player1EloChange);

    // Update match stats
    $player1Rating->increment('matches_played');
    $player2Rating->increment('matches_played');

    if ($match->winner_id === null) {
        $player1Rating->increment('draws');
        $player2Rating->increment('draws');
    } else {
        if ($match->winner_id === $match->player1_id) {
            $player1Rating->increment('wins');
            $player2Rating->increment('losses');
        } else {
            $player1Rating->increment('losses');
            $player2Rating->increment('wins');
        }
    }

    // Record Elo delta in the match
    $match->update([
        'player1_elo_change' => $player1EloChange,
        'player2_elo_change' => -$player1EloChange
    ]);
}


    public function completeMatch(Request $request)
    {
        // Validate required input
        $data = $request->validate([
            'match_id'       => 'required|integer|exists:matchings,id',
            'player1_score'  => 'required|integer',
            'player2_score'  => 'required|integer',
        ]);
        // dd("hh");

        $match = Matching::findOrFail($data['match_id']);
        $match->player1_score = $data['player1_score'];
        $match->player2_score = $data['player2_score'];
        $match->ended_at      = now();
        $match->status        = 'completed';

                // Determine winner (or draw)
        if ($match->player1_score > $match->player2_score) {
            $match->winner_id  = $match->player1_id;
            $score1 = 1.0;   // Player1 win
            $score2 = 0.0;   // Player2 loss
        } elseif ($match->player2_score > $match->player1_score) {
            $match->winner_id  = $match->player2_id;
            $score1 = 0.0;   // Player1 loss
            $score2 = 1.0;   // Player2 win
        } else {
            // In practice, ties trigger a sudden-death question (ensuring no tie), 
            // but handle tie as 0.5-0.5 for safety
            $match->winner_id = null;
            $score1 = 0.5;
            $score2 = 0.5;
        }
        $match->save();
        $courseId = $match->course_id;
        $player1Id = $match->player1_id;
        $player2Id = $match->player2_id;

        // Retrieve or initialize Elo records (assume default 1000 if new)
        $elo1 = CourseStudentElo::firstOrCreate(
            ['course_id' => $courseId, 'student_id' => $player1Id],
            ['elo_rating' => 1000]
        );
        $elo2 = CourseStudentElo::firstOrCreate(
            ['course_id' => $courseId, 'student_id' => $player2Id],
            ['elo_rating' => 1000]
        );

        $rating1 = $elo1->elo_rating;
        $rating2 = $elo2->elo_rating;

        // Calculate new Elo ratings (using K-factor = 32)
        list($newRating1, $newRating2) = $this->calculateEloRatings($rating1, $rating2, $score1, $score2, 32);

        // Compute rating changes
        $match->player1_elo_change = $newRating1 - $rating1;
        $match->player2_elo_change = $newRating2 - $rating2;

        // Update Elo records
        $elo1->elo_rating = $newRating1;
        $elo2->elo_rating = $newRating2;
        $elo1->save();
        $elo2->save();

        // Save Elo changes and winner
        $match->save();

        return response()->json([
            'message'       => 'Match completed and Elo ratings updated.',
            'winner_id'     => $match->winner_id,
            'player1_elo'   => $newRating1,
            'player2_elo'   => $newRating2,
        ]);


    }











/**
     * Calculate new Elo ratings for two players using the standard Elo formula.
     *
     * @param float $rating1 Current rating of player 1
     * @param float $rating2 Current rating of player 2
     * @param float $score1  Actual score for player 1 (1=win, 0=loss, 0.5=draw)
     * @param float $score2  Actual score for player 2
     * @param int   $K       K-factor (e.g., 32)
     * @return array        [newRating1, newRating2]
     */
    private function calculateEloRatings($rating1, $rating2, $score1, $score2, $K = 64)
    {
        // Calculate expected scores
        $expect1 = 1.0 / (1.0 + pow(10, ($rating2 - $rating1) / 400));
        $expect2 = 1.0 / (1.0 + pow(10, ($rating1 - $rating2) / 400));

        // Update ratings: R_new = R_old + K * (ActualScore - ExpectedScore):contentReference[oaicite:0]{index=0}
        $newRating1 = $rating1 + $K * ($score1 - $expect1);
        $newRating2 = $rating2 + $K * ($score2 - $expect2);

        // Return rounded integers (Elo ratings are usually integers)
        return [round($newRating1), round($newRating2)];
    }
    
}