<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequestRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class AdminStaffAttendanceController extends Controller
{
    public function index(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $date = Carbon::parse($request->query('date', now()));

        $monthDays = Attendance::getMonthDays($date);

        $attendances = Attendance::getMonthlyForUser($staff->id, $date->year, $date->month);

        return view('admin.staff-attendance-list', compact('staff', 'monthDays', 'attendances', 'date'));
    }

    public function create(Request $request, $date)
    {
        // クエリパラメータからスタッフIDを取得
        $staffId = $request->query('id');
        $staff = User::findOrFail($staffId);

        try {
            $date = Carbon::parse($date);
        } catch (\Exception) {
            return redirect()->route('admin.staff.attendance.list', $staff->id)->with('error', '無効な日付です。');
        }

        if ($date->startOfDay()->isFuture()) {
            return redirect()->route('admin.staff.attendance.list', $staff->id)->with('error', '未来の日付は登録できません。');
        }

        // すでにデータが存在する場合は、詳細画面へリダイレクト
        $attendance = Attendance::where('user_id', $staff->id)
            ->whereDate('work_date', $date)
            ->first();

        if ($attendance) {
            return redirect()->route('admin.attendance.detail', $attendance->id);
        }

        // データが存在しない場合は新規作成用の空データを準備してビューを返す
        // （※一般画面の create メソッドの管理者版を作成してください）
        $attendance = new Attendance([
            'user_id' => $staff->id,
            'work_date' => $date,
            'status' => Attendance::STATUS_OFF,
        ]);

        $isEditable = $date->startOfDay()->lt(now()->startOfDay());
        $attendanceRequest = null;
        $user = $staff;
        return view('admin.attendance-detail', compact('attendance', 'staff', 'date', 'isEditable', 'attendanceRequest', 'user'));
    }

    public function store(AttendanceRequestRequest $request)
    {
        $validated = $request->validated();
        $staffId = $request->input('user_id');
        $staff = User::findOrFail($staffId);
        $workDate = Carbon::parse($validated['work_date']);
        $adminUser = $request->user(); // 操作している管理者

        DB::transaction(function () use ($validated, $staff, $adminUser, $workDate) {

            $attendance = Attendance::create([
                'user_id' => $staff->id,
                'work_date' => $workDate->format('Y-m-d'),
                'status'    => Attendance::STATUS_FINISHED,
            ]);

            $clockInDateTime = !empty($validated['clock_in'])
                ? $workDate->copy()->setTimeFromTimeString($validated['clock_in'])
                : null;

            $clockOutDateTime = !empty($validated['clock_out'])
                ? $workDate->copy()->setTimeFromTimeString($validated['clock_out'])
                : null;

            // 申請情報の登録（管理者が直接登録・承認する場合）
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
                'reason' => $validated['remarks'] ?? null,
                'status' => AttendanceRequest::STATUS_APPROVED,
                'applicant_id' => $staff->id,
                'approved_by'   => $adminUser->id,
            ]);

            $attendance->update([
                'clock_in'  => $clockInDateTime,
                'clock_out' => $clockOutDateTime,
            ]);

            // 休憩データの保存（複数ある場合はループして登録する）
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
        });

        return redirect()->route('admin.staff.attendance.list', ['id' => $staffId, 'date' => $validated['work_date']])
            ->with('message', '勤怠情報を更新しました。');
    }

    public function exportCsv(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $date = Carbon::parse($request->query('date', now()));

        // その月の全日数と、登録済みの勤怠データを取得
        $monthDays = Attendance::getMonthDays($date);
        $attendances = Attendance::getMonthlyForUser($staff->id, $date->year, $date->month);

        $csvFileName = 'attendance_' . $staff->id . '_' . $date->format('Y_m') . '.csv';

        $callback = function() use ($monthDays, $attendances, $staff) {
            $file = fopen('php://output', 'w');

            // 1. 文字化け対策としてBOMを付与（Excelで開く場合必須）
            fwrite($file, "\xEF\xBB\xBF");

            // 2. ヘッダー行の書き込み
            fputcsv($file, ['氏名', '日付', '出勤', '退勤', '休憩', '合計']);

            // 3. 各日ごとのデータの書き込み
            foreach ($monthDays as $day) {
                $dayStr = $day->format('Y-m-d');
                $data = $attendances->get($dayStr);

                fputcsv($file, [
                    $staff->name,
                    $day->format('Y/m/d') . '(' . $day->isoFormat('ddd') . ')',
                    $data?->clock_in?->format('H:i') ?? '',
                    $data?->clock_out?->format('H:i') ?? '',
                    $data ? $data->breakTotalTime : '',
                    $data ? $data->workTime : '',
                ]);
            }

            fclose($file);
        };

        // レスポンスヘッダーを設定してダウンロードを実行
        return response()->stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"{$csvFileName}\"",
        ]);
    }
}
