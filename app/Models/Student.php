<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['description'];

    public function userable()
    {
        return $this->morphOne(User::class, 'userable');
    }
    public function courseTeachers()
{
    return $this->belongsToMany(CourseTeacher::class, 'course_enrolls');
}




protected static function boot()
{
    parent::boot();

    static::deleting(function ($student) {
        $student->userable()->delete();
    });
}


}