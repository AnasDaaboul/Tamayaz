<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnroll extends Model
{
    use HasFactory;
    protected $fillable = [

         'student_id' ,
          'course_teacher_id'
        ];
    // public function student()
    // {
    //     return $this->belongsTo(Student::class);
    // }
    // public function cobon()
    // {
    //     return $this->belongsTo(Cobon::class);
    // }
    // public function course()
    // {
    //     return $this->belongsTo(Course::class);
    // }
    // public function teacher()
    // {
    //     return $this->belongsTo(Teacher::class);
    // }


    // In App/Models/CourseEnroll.php


    
public function teacher()
{
    // Specify the custom foreign key 'course_teacher_id'
    return $this->belongsTo(Teacher::class, 'course_teacher_id');
}

public function course()
{
    // If your course relationship uses a non-standard foreign key, specify it here
    return $this->belongsTo(Course::class, 'course_teacher_id');
}

// Add this if you want to show student name later
public function student()
{
    return $this->belongsTo(Student::class);
}
public function courseTeacher()
{
    return $this->belongsTo(CourseTeacher::class, 'course_teacher_id');
}
}