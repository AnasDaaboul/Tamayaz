<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CourseTeacher extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $fillable = ['course_id' , 'teacher_id'];


    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class );
    }

    public function chapters()
    {
        return $this->hasMany(\App\Models\chapter::class);
    }
}