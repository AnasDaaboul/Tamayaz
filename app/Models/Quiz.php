<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'time_limit',
        'passing_score',
        'is_active',
        'number_of_attempts',
    ];

    public function chapter()
    {
        return $this->belongsTo(chapter::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(StudentQuizAttempt::class);
    }
}
