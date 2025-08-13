<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitiveMatch extends Model
{
    use HasFactory;
    protected $fillable = [
        'match_name',
        'player1_name',
        'player2_name',
        'player1_score',
        'player2_score',
        'topics', // Make sure 'topics' is fillable
        'status',
        'user_id'
    ];

    protected $casts = [
        'topics' => 'array', // <-- Add this line
    ];
    public function units()
    {
        return $this->hasMany(Unit::class);
    }
    public function questions()
    {
        return $this->hasMany(CompetitiveQuestion::class);
    }

    public function studentCompetitiveQuestionLocks(): HasMany
    {
        return $this->hasMany(StudentCompetitiveQuestionLock::class);
    }

    public function matchLifelines(): HasMany
    {
        return $this->hasMany(MatchLifeline::class, 'competitive_match_id');
    }
}
