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

        $attendances = Attendance::getMonthlyAttendances($user->id, $date->year, $date->month);

        return view('employee.attendance-list', compact('monthDays', 'attendances', 'date'));
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $attendance = Attendance::where('user_id', $user->id)->findOrFail($id);
        $date = Carbon::parse($attendance->work_date);
        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id ?? null)->where('status', AttendanceRequest::STATUS_PENDING)->first();
        return view('employee.attendance-detail', compact('attendance', 'user', 'date', 'attendanceRequest'));
    }

    public function create(Request $request, $date)
    {
        $user = $request->user();
        $attendance = null;
        $attendanceRequest = null;
        try {
            Carbon::parse($date);
        } catch (\Exception) {
            return redirect()->route('attendance.list')->with('error', '無効な日付です。');
        }
        $date = Carbon::parse($date);
        // 未来日を変更申請できないようにするには下記に変更すること。
        // try {
        //     $targetDate = Carbon::parse($date);
        // } catch (\Exception) {
        //     return redirect()->route('attendance.list')->with('error', '無効な日付です。');
        // }

        // if ($targetDate->isFuture()) {
        //     return redirect()->route('attendance.list')->with('error', '未来の日付は申請できません。');
        // }
        return view('employee.attendance-detail', compact('date', 'user', 'attendance', 'attendanceRequest'));
    }
}
