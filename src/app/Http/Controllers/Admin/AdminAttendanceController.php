<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequestRequest;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date)
            ->whereNotNull('clock_in')
            ->where('status', '!=', Attendance::STATUS_OFF)
            ->get();

        return view('admin.attendance-list', compact('attendances', 'date'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakRecords'])->findOrFail($id);
        $user = $attendance->user;

        if ($attendance->isFuture()) {
            return redirect()->route('admin.index')->with('error', '未来の日付の詳細にはアクセスできません。');
        }

        $date = Carbon::parse($attendance->work_date);


        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)->where('status', AttendanceRequest::STATUS_PENDING)->first();

        $isEditable = $attendance->work_date->startOfDay()->lt(now()->startOfDay());

        return view('admin.attendance-detail', compact('attendance', 'user', 'date', 'attendanceRequest', 'isEditable'));
    }

    // 【追加】管理者が直接勤怠を更新する処理
    public function update(AttendanceRequestRequest $request, $id)
    {
        $user = $request->user();
        $validated = $request->validated();
        $attendance = Attendance::findOrFail($id);

        $workDate = Carbon::parse($attendance->work_date);

        return DB::transaction(function () use ($user, $validated, $attendance, $workDate) {
            $beforeData = [
                'clock_in' => $attendance->clock_in?->format('Y-m-d H:i'),
                'clock_out' => $attendance->clock_out?->format('Y-m-d H:i'),
                'breaks' => $attendance->breakRecords->map(fn($b) => [
                    'break_in' => $b->break_in?->format('Y-m-d H:i'),
                    'break_out' => $b->break_out?->format('Y-m-d H:i'),
                ])->toArray(),
            ];

            // 変更後データの構築用に出勤・退勤日時をフル日時化
            $clockInDateTime = !empty($validated['clock_in'])
                ? $workDate->copy()->setTimeFromTimeString($validated['clock_in'])
                : null;

            $clockOutDateTime = !empty($validated['clock_out'])
                ? $workDate->copy()->setTimeFromTimeString($validated['clock_out'])
                : null;

            $afterData = [
                'clock_in' => $clockInDateTime?->format('Y-m-d H:i'),
                'clock_out' => $clockOutDateTime?->format('Y-m-d H:i'),
                'breaks' => collect($validated['breaks'] ?? [])->map(function ($break) use ($workDate) {
                    return [
                        'break_in' => isset($break['break_in']) ? $workDate->copy()->setTimeFromTimeString($break['break_in'])->format('Y-m-d H:i') : null,
                        'break_out' => isset($break['break_out']) ? $workDate->copy()->setTimeFromTimeString($break['break_out'])->format('Y-m-d H:i') : null,
                    ];
                })->toArray(),
            ];

            AttendanceRequest::create([
                'attendance_id' => $attendance->id,
                'before_data'   => $beforeData,
                'after_data'    => $afterData,
                'reason'        => $validated['remarks'],
                'status'        => AttendanceRequest::STATUS_APPROVED,
                'applicant_id'  => $user->id,
                'approved_by'   => $user->id,
            ]);

            $attendance->update([
                'clock_in'  => $clockInDateTime,
                'clock_out' => $clockOutDateTime,
            ]);

            $attendance->breakRecords()->delete();
            if (!empty($validated['breaks'])) {
                foreach ($validated['breaks'] as $break) {
                    if (!empty($break['break_in']) || !empty($break['break_out'])) {
                        $attendance->breakRecords()->create([
                            'break_in'  => !empty($break['break_in']) ? $workDate->copy()->setTimeFromTimeString($break['break_in']) : null,
                            'break_out' => !empty($break['break_out']) ? $workDate->copy()->setTimeFromTimeString($break['break_out']) : null,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.staff.attendance.list', ['id' => $attendance->user_id, 'date' => $workDate->format('Y-m-d'),])
                ->with('message', '勤怠情報を更新しました。');
        });
    }
}
