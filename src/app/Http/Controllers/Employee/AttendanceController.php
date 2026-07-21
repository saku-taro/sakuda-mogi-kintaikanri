<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $attendance = $user->attendances()->active()->first();

        $isFinished = $user->attendances()->finishedToday()->exists();

        $isResting = $attendance ? $attendance->isResting() : false;

        $today = now();
        $dateString = $today->format('Y年n月j日');
        $dayOfWeek = $today->isoFormat('ddd');
        $currentTime = $today->format('H:i');

        return view('employee.index', compact('attendance', 'isResting', 'isFinished', 'dateString', 'dayOfWeek', 'currentTime'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->attendances()->todayStarted()->exists()) {
            return back()->with('error', '本日はすでに出勤済みです。');
        }

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        return back()->with('message', '出勤しました');
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $attendance = $user->attendances()->active()->first();

        if (!$attendance) {
            return back()->with('error', '出勤中の記録が見つかりません。');
        }

        $attendance->update([
            'clock_out' => now(),
            'status' => Attendance::STATUS_FINISHED,
        ]);

        return back()->with('message', '退勤しました');
    }
}
