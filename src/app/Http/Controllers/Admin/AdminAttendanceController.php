<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 指定があればその日、なければ今日を基準にする
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        // 日単位でデータを取得
        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date)
            ->get();

        return view('admin.attendance-list', compact('attendances', 'date'));
    }
}
