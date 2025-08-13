<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobon extends Model
{
    use HasFactory;
    protected $fillable = ['name' , 'active' , 'cobonable_type', 'cobonable_id' , 'library_id'];

    public function cobonable()
    {
        return $this->morphTo();
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function courseEnrolls()
    {
        return $this->hasMany(CourseEnroll::class);
    }



}