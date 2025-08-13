<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitiveMatchQuestion extends Model
{
    protected $fillable = [
        'match_id',
        'question_id',
        'question_order',
        'player1_answered',
        'player2_answered',
        'player1_correct',
        'player2_correct',
        'player1_response_time',
        'player2_response_time',
        'is_tiebreaker'
    ];

    protected $casts = [
        'player1_answered' => 'boolean',
        'player2_answered' => 'boolean',
        'player1_correct' => 'boolean',
        'player2_correct' => 'boolean',
        'player1_response_time' => 'float',
        'player2_response_time' => 'float',
        'is_tiebreaker' => 'boolean'
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(Matching::class, 'match_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CompetitiveQuestion::class, 'question_id');
    }
}