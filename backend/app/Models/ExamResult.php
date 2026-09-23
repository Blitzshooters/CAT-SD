<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'grade',
        'score',
        'correct_count',
        'wrong_count',
        'unanswered_count',
        'total_questions',
        'time_spent_seconds',
        'user_answers',
        'proctoring_summary',
    ];

    protected $casts = [
        'grade'              => 'integer',
        'score'              => 'integer',
        'correct_count'      => 'integer',
        'wrong_count'        => 'integer',
        'unanswered_count'   => 'integer',
        'total_questions'    => 'integer',
        'time_spent_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function proctoringLogs()
    {
        return $this->hasMany(ProctoringLog::class, 'exam_result_id');
    }

    public function proctoringSnapshots()
    {
        return $this->hasMany(ProctoringSnapshot::class, 'exam_result_id');
    }
}
