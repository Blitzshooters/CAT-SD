<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'subject_id',
        'prompt',
        'options',
        'correct_answer_index',
    ];

    protected $casts = [
        'correct_answer_index' => 'integer',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function getOptionsAttribute($value)
    {
        return json_decode($value, true);
    }
}
