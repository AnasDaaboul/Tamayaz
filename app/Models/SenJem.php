<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SenJem extends Model
{
    protected $table = 'senjem';
    
    protected $fillable = [
        'competitive_match_id',
        'user_id',
        'points_earned',
        'question_difficulty',
        'is_correct',
        'player_number',
        'answered',
        'question_id'
    ];

    public function match()
    {
        return $this->belongsTo(CompetitiveMatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}