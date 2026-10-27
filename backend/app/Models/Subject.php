<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'title',
        'grade',
        'duration_minutes',
        'max_questions',
        'max_violations',
        'remedy_code',
        'icon_name',
        'description',
    ];

    protected $casts = [
        'grade'            => 'integer',
        'duration_minutes' => 'integer',
        'max_questions'    => 'integer',
        'max_violations'   => 'integer',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
