<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequestRequest;
use App\Models\AttendanceRequest;
use App\Models\Attendance;

class AttendanceRequestController extends Controller
{
    public function store(AttendanceRequestRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $workDate = $validated['work_date'];
        // 1. まず空の勤怠データを作成（または特定の日のデフォルトデータを作成）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $workDate, // 申請対象の日付が必要
            'status'    => Attendance::STATUS_FINISHED, // 申請中を表すステータスなど
        ]);

        // 2. そのIDを使って申請を登録
        AttendanceRequest::create([
            'attendance_id' => $attendance->id, // ここでIDが確実に手に入る
            'user_id' => $user->id,
            'before_data' => [],
            'after_data'    => [
                'clock_in' => $validated['clock_in'],
                'clock_out' => $validated['clock_out'],
            ],
            'reason' => $validated['remarks'],
            'status' => AttendanceRequest::STATUS_PENDING,
        ]);

        return redirect()->route('attendance.list')->with('message', '修正申請を送信しました');
    }

    public function update(AttendanceRequestRequest $request, $id)
    {
        $validated = $request->validated();
        $attendance = Attendance::findOrFail($id);
        $user = $request->user();

        // 変更前後のデータを配列で作成
        $beforeData = [
            'clock_in' => $attendance->clock_in?->format('H:i'),
            'clock_out' => $attendance->clock_out?->format('H:i'),
            'breaks' => $attendance->breakRecords->map(fn($b) => [
                'break_in' => $b->break_in?->format('H:i'),
                'break_out' => $b->break_out?->format('H:i'),
            ])->toArray(),
        ];

        $afterData = [
            'clock_in' => $validated['clock_in'],
            'clock_out' => $validated['clock_out'],
            'breaks' => $validated['breaks'] ?? [],
        ];

        // テーブルへ保存
        AttendanceRequest::create([
            'attendance_id' => $id,
            'user_id' => $user->id,
            'before_data'   => $beforeData,
            'after_data'    => $afterData,
            'reason'        => $validated['remarks'] ?? null, // 備考を理由として保存
            'status'        => AttendanceRequest::STATUS_PENDING, // 承認待ち
        ]);

        return redirect()->route('attendance.list')->with('message', '修正申請を送信しました');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $status = $request->query('status', AttendanceRequest::STATUS_PENDING);

        $requests = AttendanceRequest::where('user_id', $user->id)
            ->with('user', 'attendance')
            ->where('status', $status)
            ->latest()
            ->get();

        return view('employee.request-list', compact('requests', 'status'));
    }
}
