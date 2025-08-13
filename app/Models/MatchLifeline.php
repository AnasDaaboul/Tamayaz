<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchLifeline extends Model
{
    protected $fillable = [
        'competitive_match_id',
        'student_id',
        'player_number',
        'change_question_used',
        'get_options_used',
        'third_lifeline_used',
        'fourth_lifeline_used',
        'updown_lifeline_used'
    ];

    protected $casts = [
        'change_question_used' => 'boolean',
        'get_options_used' => 'boolean',
        'third_lifeline_used' => 'boolean',
        'fourth_lifeline_used' => 'boolean',
        'updown_lifeline_used' => 'boolean',
        'player_number' => 'integer'
    ];

    public function competitiveMatch(): BelongsTo
    {
        return $this->belongsTo(CompetitiveMatch::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}