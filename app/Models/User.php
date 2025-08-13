<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;

use Filament\Models\Contracts\HasName;

use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject , FilamentUser ,HasName
{
    use HasApiTokens , HasFactory, Notifiable , HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guard_name = 'web';
    protected $fillable = [
        'full_name',
        'email',
        'school_name',
        'Gender',
        'mobile_number',
        'password',
        'userable_type',
        'userable_id',

    ];
    public function userable()
    {
        return $this->morph();
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getFilamentName(): string
    {
        return $this->first_name  . " " .$this->last_name;
    }


    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->userable_type == "App\Models\Employee" || $this->userable_type == "App\Models\Teacher" ;
        }
    }
    public function senjemAnswers()
    {
        return $this->hasManyThrough(
            SenJem::class,
            CompetitiveMatch::class,
            'user_id', // Match user ID
            'competitive_match_id'
        );
    }
}