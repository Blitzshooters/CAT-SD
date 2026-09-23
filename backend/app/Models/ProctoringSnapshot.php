<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProctoringSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'exam_result_id',
        'file_path',
        'timestamp_ms',
    ];

    protected $casts = [
        'timestamp_ms' => 'integer',
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
