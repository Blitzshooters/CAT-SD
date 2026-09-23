<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['title', 'grade', 'duration_minutes'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
