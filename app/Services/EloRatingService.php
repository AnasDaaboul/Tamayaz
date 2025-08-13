<?php

namespace App\Services;

use App\Models\CourseStudentElo;

class EloRatingService
{
    const K_FACTOR = 32;

    public function calculateRatings(float $player1Rating, float $player2Rating, float $score) : array
    {
        $expected1 = 1 / (1 + pow(10, ($player2Rating - $player1Rating) / 400));
        $expected2 = 1 / (1 + pow(10, ($player1Rating - $player2Rating) / 400));

        $newRating1 = $player1Rating + self::K_FACTOR * ($score - $expected1);
        $newRating2 = $player2Rating + self::K_FACTOR * ((1 - $score) - $expected2);

        return [
            round($newRating1, 2),
            round($newRating2, 2)
        ];
    }

    public function updateRatings(int $courseId, int $winnerId, int $loserId, float $score)
    {
        $winnerElo = CourseStudentElo::firstOrCreate(
            ['course_id' => $courseId, 'student_id' => $winnerId],
            ['elo_rating' => 1200]
        );

        $loserElo = CourseStudentElo::firstOrCreate(
            ['course_id' => $courseId, 'student_id' => $loserId],
            ['elo_rating' => 1200]
        );

        [$newWinnerRating, $newLoserRating] = $this->calculateRatings(
            $winnerElo->elo_rating,
            $loserElo->elo_rating,
            $score
        );

        $winnerElo->update(['elo_rating' => $newWinnerRating]);
        $loserElo->update(['elo_rating' => $newLoserRating]);
    }
}