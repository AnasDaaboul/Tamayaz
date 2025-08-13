<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitiveQuestion extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'unit_id',
        'question_text',
        'options',
        'correct_answer',
        'difficulty',
        'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean'
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function matchQuestions(): HasMany
    {
        return $this->hasMany(CompetitiveMatchQuestion::class, 'question_id');
    }

    /**
     * Get the student locks for this question.
     */
    public function studentLocks(): HasMany
    {
        return $this->hasMany(StudentCompetitiveQuestionLock::class, 'competitive_question_id');
    }

    /**
     * Get random questions for a competitive match
     *
     * @param int $courseId
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRandomQuestionsForMatch(int $unitId, int $count = 5)
    {
        return self::where('unit_id', $unitId)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take($count)
            ->get();
    }
}