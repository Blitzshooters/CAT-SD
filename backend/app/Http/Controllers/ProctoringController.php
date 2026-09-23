<?php

namespace App\Http\Controllers;

use App\Models\ProctoringLog;
use App\Models\ProctoringSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProctoringController extends Controller
{
    /**
     * Log an AI proctoring violation event
     */
    public function logViolation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_result_id' => 'nullable|integer',
            'violation_type' => 'required|string|in:SWITCH_TAB,NO_FACE,MULTIPLE_FACES,LOOKING_AWAY,RAPID_ANSWERING,SNAPSHOT_CAPTURED',
            'description'    => 'required|string',
            'question_index' => 'nullable|integer',
            'timestamp_ms'   => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();

        ProctoringLog::create([
            'user_id'        => $user->id,
            'exam_result_id' => $request->exam_result_id,
            'violation_type' => $request->violation_type,
            'description'    => $request->description,
            'question_index' => $request->question_index,
            'timestamp_ms'   => $request->timestamp_ms ?? (time() * 1000),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catatan pelanggaran berhasil disimpan',
        ], 201);
    }

    /**
     * Upload a random snapshot from front camera
     */
    public function uploadSnapshot(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'snapshot'       => 'required|image|max:2048',
            'exam_result_id' => 'nullable|integer',
            'timestamp_ms'   => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $path = $request->file('snapshot')->store(
            "proctoring/snapshots/{$user->id}",
            'public'
        );

        ProctoringSnapshot::create([
            'user_id'        => $user->id,
            'exam_result_id' => $request->exam_result_id,
            'file_path'      => $path,
            'timestamp_ms'   => $request->timestamp_ms ?? (time() * 1000),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Snapshot berhasil diupload',
            'file_path' => Storage::url($path),
        ], 201);
    }

    /**
     * Get all proctoring logs for an exam session
     */
    public function sessionLogs(Request $request, int $examId): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $logs = ProctoringLog::where('user_id', $user->id)
            ->where('exam_result_id', $examId)
            ->orderBy('timestamp_ms', 'asc')
            ->get()
            ->map(function ($log) {
                return [
                    'id'             => $log->id,
                    'violation_type' => $log->violation_type,
                    'description'    => $log->description,
                    'question_index' => $log->question_index,
                    'timestamp_ms'   => $log->timestamp_ms,
                ];
            });

        return response()->json([
            'success' => true,
            'exam_id' => $examId,
            'logs'    => $logs,
        ]);
    }
}
