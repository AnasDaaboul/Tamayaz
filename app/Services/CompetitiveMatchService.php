<?php

namespace App\Services;

use App\Models\Matching;
use App\Models\PlayerRating;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompetitiveMatchService
{
    public function __construct()
    {

        // Constructor without debug code
    }
    
    public function findMatch(Student $player , $courseId): ?Matching
    {   
        
        // dd($course);
        $course = Course::find($courseId);
        
        return DB::transaction(function () use ($player, $course) {
            $waitingMatch = Matching::where('course_id', $course->id)
                ->where('status', 'waiting')
                ->where('player1_id', '!=', $player->id)
                ->lockForUpdate()
                ->first();

            if ($waitingMatch) {
                $waitingMatch->update([
                    'player2_id' => $player->id,
                    'status' => 'in_progress',
                    'started_at' => Carbon::now()
                ]);
                $this->notifyMatchStart($waitingMatch);
                return $waitingMatch;
            }

            return Matching::create([
                'player1_id' => $player->id,
                'course_id' => $course->id,
                'status' => 'waiting'
            ]);
        });
    }

    public function submitAnswer(Matching $match, Student $player, bool $isCorrect, float $responseTime): void
    {
        $isFirstPlayer = $player->id === $match->player1_id;
        $scoreField = $isFirstPlayer ? 'player1_score' : 'player2_score';
        $score = $isCorrect ? ($match->first_answer ? 2 : 1) : 0;

        $match->increment($scoreField, $score);
        $match->increment('current_question_index');

        if ($match->current_question_index >= 5) {
            $this->finalizeMatch($match);
        }

        event(new \App\Events\MatchEvent($match, 'answer-submitted', [
            'player_id' => $player->id,
            'is_correct' => $isCorrect,
            'score' => $score,
            'response_time' => $responseTime
        ]));
    }

    private function finalizeMatch(Matching $match): void
    {
        $match->status = 'completed';
        $match->ended_at = Carbon::now();

        if ($match->player1_score > $match->player2_score) {
            $match->winner_id = $match->player1_id;
        } elseif ($match->player2_score > $match->player1_score) {
            $match->winner_id = $match->player2_id;
        }

        $match->save();

        $this->updatePlayerRatings($match);
        $this->notifyMatchEnd($match);
    }

    private function updatePlayerRatings(Matching $match): void
    {
        $player1Rating = PlayerRating::firstOrCreate(
            ['student_id' => $match->player1_id, 'course_id' => $match->course_id],
            ['rating' => 1200]
        );
        $player2Rating = PlayerRating::firstOrCreate(
            ['student_id' => $match->player2_id, 'course_id' => $match->course_id],
            ['rating' => 1200]
        );

        $score = $match->winner_id === null ? 0.5 :
            ($match->winner_id === $match->player1_id ? 1 : 0);

        $player1EloChange = PlayerRating::calculateEloChange(
            $player1Rating->rating,
            $player2Rating->rating,
            $score
        );

        $player1Rating->increment('rating', $player1EloChange);
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

        $match->update([
            'player1_elo_change' => $player1EloChange,
            'player2_elo_change' => -$player1EloChange
        ]);
    }

    private function notifyMatchStart(Matching $match): void
    {
        event(new \App\Events\MatchEvent($match, 'match-started', [
            'match_id' => $match->id
        ]));
    }

    private function notifyMatchEnd(Matching $match): void
    {
        event(new \App\Events\MatchEvent($match, 'match-ended', [
            'winner_id' => $match->winner_id,
            'player1_score' => $match->player1_score,
            'player2_score' => $match->player2_score,
            'player1_elo_change' => $match->player1_elo_change,
            'player2_elo_change' => $match->player2_elo_change
        ]));
    }
}