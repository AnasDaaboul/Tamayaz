<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VideoWatch extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'media_id',
        'watched',
    ];
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id', 'id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}