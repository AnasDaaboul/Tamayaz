<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class chapter extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $fillable = [
        'course_tacher_id',
        'title',
        'order',
        'content_id',
    ];
    public function course()
    {
        return $this->belongsTo(\App\Models\CourseTeacher::class, 'course_teacher_id');
    }

    public function videos()
    {
        return $this->hasMany(\App\Models\CourseMedia::class, 'chapter_id');
    }

    public function quizzes()
    {
        return $this->hasMany(\App\Models\Quiz::class);
    }

    // In Chapter model
public function contentOrder()
{
    return $this->hasMany(ChapterContentOrder::class)->orderBy('order');
}
}
