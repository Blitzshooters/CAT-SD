<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'subject_id',
        'prompt',
        'question_image',
        'options',
        'option_images',
        'correct_answer_index',
        'explanation',
    ];

    protected $casts = [
        'options' => 'array',
        'option_images' => 'array',
        'correct_answer_index' => 'integer',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
