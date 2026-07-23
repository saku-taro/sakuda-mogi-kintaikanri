<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequestRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class AttendanceRequestController extends Controller
{
    public function store(AttendanceRequestRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $workDate = Carbon::parse($validated['work_date']);

        // DBトランザクションで囲む
        DB::transaction(function () use ($validated, $user, $workDate) {

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $workDate->format('Y-m-d'),
                'status'    => Attendance::STATUS_FINISHED,
            ]);

            $clockInDateTime = !empty($validated['clock_in'])
                ? $workDate->copy()->setTimeFromTimeString($validated['clock_in'])
                : null;

            $clockOutDateTime = !empty($validated['clock_out'])
                ? $workDate->copy()->setTimeFromTimeString($validated['clock_out'])
                : null;

            // 2. そのIDを使って申請を登録
            AttendanceRequest::create([
                'attendance_id' => $attendance->id,
                'before_data' => [],
                'after_data'    => [
                    'clock_in' => $clockInDateTime?->format('Y-m-d H:i'),
                    'clock_out' => $clockOutDateTime?->format('Y-m-d H:i'),
                    'breaks' => collect($validated['breaks'] ?? [])->map(function ($break) use ($workDate) {
                        return [
                            'break_in' => isset($break['break_in']) ? $workDate->copy()->setTimeFromTimeString($break['break_in'])->format('Y-m-d H:i') : null,
                            'break_out' => isset($break['break_out']) ? $workDate->copy()->setTimeFromTimeString($break['break_out'])->format('Y-m-d H:i') : null,
                        ];
                    })->toArray(),
                ],
                'reason' => $validated['remarks'],
                'applicant_id'  => $user->id,
                'status' => AttendanceRequest::STATUS_PENDING,
            ]);
        });

        return redirect()->route('attendance.list')->with('message', '修正申請を送信しました');
    }

    public function update(AttendanceRequestRequest $request, $id)
    {
        $user = $request->user();
        $validated = $request->validated();
        $attendance = $user->attendances()->findOrFail($id);

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

            // 出勤時間をベース日時として作成
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
                'status'        => AttendanceRequest::STATUS_PENDING,
                'applicant_id'  => $user->id,
            ]);

            return redirect()->route('attendance.list')->with('message', '修正申請を送信しました');
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $status = $request->query('status', AttendanceRequest::STATUS_PENDING);

        $query = $user->attendanceRequests()->with('attendance')->where('status', $status);

        if ($status === AttendanceRequest::STATUS_PENDING) {
            $requests = $query->orderBy('created_at', 'asc')->get();
        } else {
            $requests = $query->orderBy('created_at', 'desc')->get();
        }

        return view('employee.request-list', compact('requests', 'status'));
    }
}
