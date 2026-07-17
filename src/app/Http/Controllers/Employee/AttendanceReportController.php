<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceReportController extends Controller
{

    private function calculateSummary($attendances)
    {
        $totalMinutes = 0;
        $totalOvertimeMinutes = 0;

        foreach ($attendances as $a) {
            $workMinutes = $a->calculateWorkMinutes(); // 労働時間を算出するモデルメソッド
            $totalMinutes += $workMinutes;

            // 8時間(480分)を超えた分を算出
            if ($workMinutes > 480) {
                $totalOvertimeMinutes += ($workMinutes - 480);
            }
        }

        return [
            'total_hours' => round($totalMinutes / 60, 1),
            'total_overtime' => round($totalOvertimeMinutes / 60, 1),
            'avg_daily' => $attendances->count() > 0 ? floor((($totalMinutes / $attendances->count()) / 60) * 100) / 100 : 0,
        ];
    }

    private function calculateMonthlyTrends($attendances)
    {
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthlyData = $attendances->filter(fn($a) => $a->work_date->format('Y-m') === $month);

            $trends[$month] = [
                'hours' => round($monthlyData->sum(fn($a) => $a->calculateWorkMinutes()) / 60, 1),
                'overtime' => round($monthlyData->sum(fn($a) => max(0, $a->calculateWorkMinutes() - 480)) / 60, 1)
            ];
        }
        return $trends;
    }

    private function calculateAnomalies($attendances)
    {
        // 今月のみを対象とする場合
        $thisMonth = $attendances->filter(fn($a) => $a->work_date->isCurrentMonth());

        foreach ($thisMonth as $a) {
            $minutes = $a->calculateWorkMinutes();
            // ログに出力して確認
            \Illuminate\Support\Facades\Log::info("日付: {$a->work_date}, 労働時間: {$minutes}分, 10時間超判定: " . ($minutes > 600 ? 'YES' : 'NO'));
        }

        return [
            'late' => $thisMonth->filter(function ($a) {
                if (!$a->clock_in) return false;
                return $a->clock_in->format('H:i:s') > '09:00:00';
            })->count(),

            'early_leave' => $thisMonth->filter(function ($a) {
                if (!$a->clock_out) return false;
                return $a->clock_out->format('H:i:s') < '18:00:00';
            })->count(),

            'long_work' => $thisMonth->filter(fn($a) => $a->calculateWorkMinutes() > 600)->count(),
        ];
    }

    private function formatTime($hours)
    {
        $totalMinutes = round($hours * 60);
        $h = floor($totalMinutes / 60);
        $m = $totalMinutes % 60;
        return $h . 'h ' . $m . 'm';
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();

        // 過去6ヶ月の勤怠データを取得
        $attendances = Attendance::where('user_id', $user->id)
            ->where('work_date', '>=', $sixMonthsAgo)
            ->with('breakRecords')
            ->get();

        // 1. サマリー計算（後述のサービスメソッドを使用）
        $rawSummary = $this->calculateSummary($attendances);
        $summary = [
            'total_hours' => $this->formatTime($rawSummary['total_hours']),
            'total_overtime' => $this->formatTime($rawSummary['total_overtime']),
            'avg_daily' => $this->formatTime($rawSummary['avg_daily']),
        ];

        // 2. 月次推移計算
        $rawMonthlyTrends = $this->calculateMonthlyTrends($attendances);
        $monthlyTrends = [];
        foreach ($rawMonthlyTrends as $month => $data) {
            $monthlyTrends[$month] = [
                'hours' => $this->formatTime($data['hours']),
                'overtime' => $this->formatTime($data['overtime']),
            ];
        }
        // 3. 異常検知計算
        $anomalies = $this->calculateAnomalies($attendances);

        return view('employee.report', compact('summary', 'monthlyTrends', 'anomalies'));
    }
}
