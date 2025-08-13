<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matching extends Model
{
    protected $fillable = [
        'player1_id',
        'player2_id',
        'course_id',
        'status',
        'player1_score',
        'player2_score',
        'player1_elo_change',
        'player2_elo_change',
        'winner_id',
        'current_question_index',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'player1_score' => 'integer',
        'player2_score' => 'integer',
        'player1_elo_change' => 'integer',
        'player2_elo_change' => 'integer',
        'current_question_index' => 'integer'
    ];

    public function player1(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'player2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'winner_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
    
    /**
     * Get the competitive match questions for this match
     */
    public function competitiveMatchQuestions(): HasMany
    {
        return $this->hasMany(CompetitiveMatchQuestion::class, 'match_id');
    }
}