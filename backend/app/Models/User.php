<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'password',
        'grade',
        'avatar',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'grade' => 'integer',
    ];

    // JWT Subject methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'name'     => $this->name,
            'username' => $this->username,
            'grade'    => $this->grade,
            'avatar'   => $this->avatar,
        ];
    }

    // Relationships
    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function proctoringLogs()
    {
        return $this->hasMany(ProctoringLog::class);
    }
}
