<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProctoringLog extends Model
{
    protected $fillable = [
        'user_id',
        'exam_result_id',
        'violation_type',
        'description',
        'question_index',
        'timestamp_ms',
    ];

    protected $casts = [
        'question_index' => 'integer',
        'timestamp_ms'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function examResult()
    {
        return $this->belongsTo(ExamResult::class, 'exam_result_id');
    }
}
