<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCompetitiveQuestionLock extends Model
{
    use HasFactory;

    protected $table = 'student_competitive_question_locks';

    protected $fillable = [
        'student_id',
        'competitive_question_id',
        'locked',
        'competitive_match_id'
    ];

    protected $casts = [
        'locked' => 'boolean',
    ];

    /**
     * Get the student that owns the lock.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the competitive question that is locked.
     */
    public function competitiveQuestion(): BelongsTo
    {
        return $this->belongsTo(CompetitiveQuestion::class);
    }

    /**
     * Get the competitive match that owns the lock.
     */
    public function competitiveMatch(): BelongsTo
    {
        return $this->belongsTo(CompetitiveMatch::class);
    }
}