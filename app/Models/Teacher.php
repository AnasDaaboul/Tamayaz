<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Teacher extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;


    protected $fillable = [ 'percentage' , 'balance' , 'info'] ;

    public function userable()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function course()
    {
        return $this->belongsToMany(Course::class , 'course_teachers')->withPivot('id');
      

    }


protected static function boot()
{
    parent::boot();

    static::deleting(function ($teacher) {
        $teacher->userable()->delete();
    });
}





}