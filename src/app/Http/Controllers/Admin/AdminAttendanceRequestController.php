<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Models\Attendance;


class AdminAttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', AttendanceRequest::STATUS_PENDING);

        $query = AttendanceRequest::with(['attendance.user', 'attendance'])
            ->where('status', $status);

        if ($status == AttendanceRequest::STATUS_PENDING) {
            $requests = $query->orderBy('created_at', 'asc')->get();
        } else {
            $requests = $query->orderBy('created_at', 'desc')->get();
        }

        return view('admin.request-list', compact('requests', 'status'));
    }

    public function show($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceRequest::with(['attendance.user', 'attendance'])
            ->findOrFail($attendance_correct_request_id);

        $rawBreaks = $attendanceRequest->formatted_breaks ?? [];

        $validBreaks = collect($rawBreaks)->filter(function ($break) {
            $breakIn = $break['break_in'] ?? null;
            $breakOut = $break['break_out'] ?? null;
            return !empty($breakIn) || !empty($breakOut);
        })->values();

        return view('admin.request-detail', compact('attendanceRequest', 'validBreaks'));
    }

    public function update(Request $request, $attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceRequest::findOrFail($attendance_correct_request_id);

        if (!$attendanceRequest->isPending()) {
                    return redirect()->route('stamp_correction_request.list', [
                        'status' => AttendanceRequest::STATUS_PENDING,
                        'from' => 'admin'
                    ])->with('error', 'この申請はすでに承認されています。');
                }

        $attendanceRequest->status = AttendanceRequest::STATUS_APPROVED;
        $attendanceRequest->approved_by = auth()->id();
        $attendanceRequest->save();

        $attendance = Attendance::findOrFail($attendanceRequest->attendance_id);
        $afterData = $attendanceRequest->after_data;

        $attendance->update([
            'clock_in'  => $afterData['clock_in'] ?? $attendance->clock_in,
            'clock_out' => $afterData['clock_out'] ?? $attendance->clock_out,
        ]);

        if (isset($afterData['breaks']) && is_array($afterData['breaks'])) {
            
            $attendance->breakRecords()->delete();

            foreach ($afterData['breaks'] as $breakData) {
                if (!empty($breakData['break_in']) || !empty($breakData['break_out'])) {
                    $attendance->breakRecords()->create([
                        'break_in'  => $breakData['break_in'],
                        'break_out' => $breakData['break_out'],
                    ]);
                }
            }
        }

        return redirect()->route('stamp_correction_request.list', [
            'status' => AttendanceRequest::STATUS_PENDING,
            'from' => 'admin'
        ])->with('message', '申請を承認しました。');
    }
}
