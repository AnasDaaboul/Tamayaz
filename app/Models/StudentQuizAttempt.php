<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'quiz_id',
        'score',
        'answers',
        'passed',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'answers' => 'array',
        'passed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
