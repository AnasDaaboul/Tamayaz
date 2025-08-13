<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Course extends Model implements HasMedia
{
    use HasFactory , InteractsWithMedia;
    protected $fillable = [
        'name',
        'price',
        'total_subs',
        'description',
    ];
    public function cobons()
    {
        return $this->morphMany(Cobon::class, 'cobonable');
    }
    public function students()
    {
        return $this->belongsToMany(CourseEnroll::class , 'course_enrolls');
    }


    public function teachers()
    {

        return $this->belongsToMany(Teacher::class , 'course_teachers')->withPivot('id');
    }

    // Removed chapters relationship as per new structure
    // public function chapters()
    // {
    //     return $this->hasMany(\App\Models\chapter::class);
    // }

    public function videos()
    {
        return $this->belongsToMany(Media::class, 'course_media', 'course_id', 'media_id');
    }
    public function course_media()
    {
        return $this->hasMany(CourseMedia::class);
    }

    /**
     * Get the units for the course.
     */
    public function units(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Unit::class);
    }


    // This will override the media model_type for videos
    public function attachCourseMedia($media): void
    {
        $this->videos()->attach($media->id);
    }


}