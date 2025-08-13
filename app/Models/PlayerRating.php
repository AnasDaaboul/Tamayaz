<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerRating extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'rating',
        'matches_played',
        'wins',
        'losses',
        'draws'
    ];

    protected $casts = [
        'rating' => 'integer',
        'matches_played' => 'integer',
        'wins' => 'integer',
        'losses' => 'integer',
        'draws' => 'integer'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public static function calculateEloChange(int $playerRating, int $opponentRating, float $score): int
    {
        $expectedScore = 1 / (1 + pow(10, ($opponentRating - $playerRating) / 400));
        $kFactor = 32; // K-factor determines how much ratings can change
        return round($kFactor * ($score - $expectedScore));
    }
}