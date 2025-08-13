<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    public function userable()
    {
        return $this->morphOne(User::class, 'userable');
    }
    protected static function boot()
{
    parent::boot();

    static::deleting(function ($employee) {
        $employee->userable()->delete();
    });
}
}