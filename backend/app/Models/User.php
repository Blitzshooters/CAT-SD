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
        'nomor_induk',
        'password',
        'grade',
        'avatar',
        'is_admin',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'grade' => 'integer',
        'is_admin' => 'boolean',
    ];

    // JWT Subject methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'name'        => $this->name,
            'username'    => $this->username,
            'nomor_induk' => $this->nomor_induk,
            'grade'       => $this->grade,
            'avatar'      => $this->avatar,
            'is_admin'    => $this->is_admin,
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
