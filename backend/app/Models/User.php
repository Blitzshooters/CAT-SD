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
        'plain_password',
        'grade',
        'avatar',
        'is_admin',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'grade'    => 'integer',
        'is_admin' => 'boolean',
        'role'     => 'string',
    ];

    protected static function booted(): void
    {
        static::saving(function ($user) {
            // Keep is_admin in sync with role for backward compatibility
            if (!empty($user->role)) {
                $user->is_admin = in_array($user->role, ['admin', 'teacher']);
            } elseif ($user->is_admin) {
                $user->role = $user->username === 'admin' ? 'admin' : 'teacher';
            } else {
                $user->role = 'student';
            }
        });
    }

    /**
     * Check if user is Super Admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is Guru / Pengawas
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user is Siswa SD
     */
    public function isStudent(): bool
    {
        return $this->role === 'student' || (!$this->hasAdminAccess());
    }

    /**
     * Check if user has access to Admin & Teacher Web Panel
     */
    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['admin', 'teacher']) || (bool)$this->is_admin;
    }

    /**
     * Human-readable role label
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin'   => 'Administrator',
            'teacher' => 'Guru / Pengawas',
            default   => 'Siswa SD',
        };
    }

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
            'is_admin'    => (bool)$this->is_admin,
            'role'        => $this->role ?: ($this->is_admin ? 'admin' : 'student'),
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
