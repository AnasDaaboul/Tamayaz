<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Unit extends Model implements HasMedia
{
    
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'course_id',
        'name',
        'description',
        'order',
        'is_active' // Added is_active
    ];

    protected $casts = [
        'is_active' => 'boolean' // Added cast for is_active
    ];

    /**
     * Get the course that owns the unit.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the competitive questions for the unit.
     */
    public function competitiveQuestions(): HasMany
    {
        return $this->hasMany(CompetitiveQuestion::class, 'unit_id'); // Ensured foreign key is specified
    }
}