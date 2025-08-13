<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CourseMedia extends Model
{
    use HasFactory;
    protected $fillable = ['course_teacher_id', 'media_id', 'order', 'file_name', 'chapter_id'];

    // Define the relationships
    public function courses()
    {
        return $this->belongsToMany(CourseTeacher::class, 'course_media', 'media_id', 'course_teacher_id');
    }
    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function chapter()
    {
        return $this->belongsTo(\App\Models\chapter::class, 'chapter_id');
    }

    public function getLikesCountAttribute()
    {
        return StudentMediaLike::where('media_id', $this->media_id)->where('liked', true)->count();
    }

    public function getDislikesCountAttribute()
    {
        return StudentMediaLike::where('media_id', $this->media_id)->where('disliked', true)->count();
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id'); // Assuming teacher_id is the foreign key in course_media
    }

}