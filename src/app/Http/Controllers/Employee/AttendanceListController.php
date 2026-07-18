<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $date = Carbon::parse($request->query('date', now()));

        $daysInMonth = $date->daysInMonth;
        $monthDays = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $monthDays[] = $date->copy()->day($i);
        }

        $attendances = Attendance::with('breakRecords')
            ->where('user_id', $user->id)
            ->monthly($date->year, $date->month)
            ->get()
            ->keyBy(fn($item) => $item->work_date->format('Y-m-d'));

        return view('employee.attendance-list', compact('monthDays', 'attendances', 'date'));
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $attendance = Attendance::where('user_id', $user->id)->findOrFail($id);

        $workDate = Carbon::parse($attendance->work_date);
        if ($workDate->startOfDay()->isFuture()) {
            return redirect()->route('attendance.list')->with('error', '未来の日付の詳細にはアクセスできません。');
        }

        $date = $workDate;
        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id ?? null)->where('status', AttendanceRequest::STATUS_PENDING)->first();
        $isEditable = $workDate->startOfDay()->lt(now()->startOfDay());
        return view('employee.attendance-detail', compact('attendance', 'user', 'date', 'attendanceRequest', 'isEditable'));
    }

    public function create(Request $request, $date)
    {
        $user = $request->user();
        $attendance = null;
        $attendanceRequest = null;
        try {
            $date = Carbon::parse($date);
        } catch (\Exception) {
            return redirect()->route('attendance.list')->with('error', '無効な日付です。');
        }

        if ($date->startOfDay()->isFuture()) {
            return redirect()->route('attendance.list')->with('error', '未来の日付は申請できません。');
        }

        $isEditable = $date->startOfDay()->lt(now()->startOfDay());

        return view('employee.attendance-detail', compact('date', 'user', 'attendance', 'attendanceRequest', 'isEditable'));
    }
}
