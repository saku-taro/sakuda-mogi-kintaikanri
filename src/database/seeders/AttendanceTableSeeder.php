<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 全対象ユーザーを取得
        $users = User::whereIn('email', ['user1@example.com', 'user2@example.com', 'user3@example.com'])->get();
        $today = now();

        foreach ($users as $user) {
            if ($user->email === 'user1@example.com') {
                $this->seedUser1($user, $today);
            } else {
                // ユーザー2と3は、過去5ヶ月+当月の平日すべてに通常勤務を記録
                $this->seedStandardUser($user, $today);
            }
        }
    }

    private function seedUser1($user, $today)
    {
        // 1. 過去5ヶ月分：各月平日からランダムに15日
        for ($m = 5; $m >= 1; $m--) {
            $month = $today->copy()->subMonths($m);
            $daysInMonth = $month->daysInMonth;

            $weekdays = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $day = $month->copy()->day($d);
                if ($day->isWeekday()) {
                    $weekdays[] = $day;
                }
            }

            shuffle($weekdays);
            $selectedDays = array_slice($weekdays, 0, 15);

            foreach ($selectedDays as $date) {
                // $user1 ではなく $user を使用
                $this->createAttendance($user, $date, '09:00', '18:00');
            }
        }

        // 2. 当月分：ランダムな17日を選んでパターンを割り当て
        $yesterday = $today->copy()->subDay();
        $daysInJuly = ($today->month === $yesterday->month) ? $yesterday->day : 0;
        $allDays = [];
        for ($d = 1; $d <= $daysInJuly; $d++) {
            $allDays[] = $today->copy()->day($d);
        }

        // 7月の日数が17日に満たない可能性があるため、min関数で調整するとより安全です
        $targetCount = min(count($allDays), 17);
        shuffle($allDays);
        $selectedDays = array_slice($allDays, 0, $targetCount);

        $patterns = [
            ['count' => 10, 'in' => '09:00', 'out' => '18:00'],
            ['count' => 3,  'in' => '09:00', 'out' => '20:00'],
            ['count' => 2,  'in' => '09:30', 'out' => '18:00'],
            ['count' => 1,  'in' => '09:00', 'out' => '17:00'],
            ['count' => 1,  'in' => '08:00', 'out' => '21:00'],
        ];

        $dayIndex = 0;
        foreach ($patterns as $p) {
            for ($i = 0; $i < $p['count']; $i++) {
                if (isset($selectedDays[$dayIndex])) {
                    // $user1 ではなく $user を使用
                    $this->createAttendance($user, $selectedDays[$dayIndex++], $p['in'], $p['out']);
                }
            }
        }
    }

    // ユーザー2, 3用：全平日埋めロジック
    private function seedStandardUser($user, $today)
    {
        // 過去5ヶ月分 + 当月(7月)
        for ($m = 5; $m >= 0; $m--) {
            $month = $today->copy()->subMonths($m);
            // 当月の場合は今日まで
            $limit = ($m === 0) ? $today->copy()->subDay()->day : $month->daysInMonth;

            for ($d = 1; $d <= $limit; $d++) {
                $day = $month->copy()->day($d);
                if ($day->isWeekday()) {
                    $this->createAttendance($user, $day, '09:00', '18:00');
                }
            }
        }
    }

    private function createAttendance($user, $date, $in, $out)
    {
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date->format('Y-m-d'),
            'clock_in' => $date->copy()->format('Y-m-d ') . $in,
            'clock_out' => $date->copy()->format('Y-m-d ') . $out,
            'status' => Attendance::STATUS_FINISHED
        ]);

        BreakRecord::create([
            'attendance_id' => $attendance->id,
            'break_in' => $date->copy()->format('Y-m-d ') . '12:00',
            'break_out' => $date->copy()->format('Y-m-d ') . '13:00',
        ]);
    }
}
