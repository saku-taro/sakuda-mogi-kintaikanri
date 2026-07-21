<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BreakRecordController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $attendance = $user->attendances()->active()->first();

        if (!$attendance) {
            return back()->with('error', '出勤中の記録がないため休憩を開始できません。');
        }

        if ($attendance->isResting()) {
            return back()->with('error', 'すでに休憩中です。');
        }

        $attendance->breakRecords()->create([
            'break_in' => now(),
        ]);

        return back()->with('message', '休憩を開始しました');
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $attendance = $user->attendances()->active()->first();
        $breakRecord = $attendance ? $attendance->activeBreak() : null;

        if ($breakRecord) {
            $breakRecord->update(['break_out' => now()]);
            return back()->with('message', '休憩を終了しました');
        }

        return back()->with('error', '休憩中の記録が見つかりませんでした。');
    }
}
