<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseStudentElo extends Model
{
    protected $table = 'course_student_elos';

    protected $fillable = [
        'course_id',
        'student_id',
        'elo_rating'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}