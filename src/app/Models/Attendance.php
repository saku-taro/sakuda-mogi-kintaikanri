<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_OFF = 0;
    const STATUS_WORKING = 1;
    const STATUS_FINISHED = 2;

    const STATUS_MAP = [
        self::STATUS_OFF => '勤務外',
        self::STATUS_WORKING => '出勤中',
        self::STATUS_FINISHED => '退勤済み',
    ];

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'status' => 'integer',
    ];

    // リレーション関係
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakRecords()
    {
        return $this->hasMany(BreakRecord::class);
    }


    public function getStatusTextAttribute()
    {
        return self::STATUS_MAP[$this->status] ?? '不明';
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_WORKING)
            ->latest('work_date');
    }

    public function scopeTodayStarted($query)
    {
        return $query->where('work_date', now()->format('Y-m-d'));
    }

    public function scopeFinishedToday($query)
    {
        return $query->where('work_date', now()->format('Y-m-d'))
            ->where('status', self::STATUS_FINISHED);
    }

    public function isResting()
    {
        return $this->status === self::STATUS_WORKING &&
            $this->breakRecords()->whereNull('break_out')->exists();
    }

    public function activeBreak()
    {
        return $this->breakRecords()->whereNull('break_out')->first();
    }

    //AttendanceListControllerで使用
    public function scopeMonthly($query, $year, $month)
    {
        return $query->whereYear('work_date', $year)
            ->whereMonth('work_date', $month);
    }

    public function getBreakTotalAttribute()
    {
        return $this->breakRecords
            ->filter(function ($break) {
                return $break->break_in && $break->break_out;
            })
            ->sum(function ($break) {
                return $break->break_in->diffInMinutes($break->break_out);
            });
    }

    public function getBreakTotalTimeAttribute()
    {
        $minutes = $this->breakTotal;
        if ($minutes <= 0) {
            return '';
        }
        return sprintf('%d:%02d', floor($minutes / 60), $minutes % 60);
    }

    public function calculateWorkMinutes()
    {
        if (!$this->clock_in || !$this->clock_out) return 0;

        $total = $this->clock_in->diffInMinutes($this->clock_out);
        return max(0, $total - $this->breakTotal);
    }

    public function getWorkTimeAttribute()
    {
        $minutes = $this->calculateWorkMinutes();
        return $minutes > 0 ? sprintf('%d:%02d', floor($minutes / 60), $minutes % 60) : '';
    }

    public function canBeRequested(): bool
    {
        return $this->work_date->startOfDay()->isPast();
    }

    public function isFuture(): bool
    {
        return $this->work_date->startOfDay()->isFuture();
    }

    public function scopeGetMonthlyForUser($query, $userId, $year, $month)
    {
        return $query->with('breakRecords')
            ->where('user_id', $userId)
            ->monthly($year, $month)
            ->get()
            ->keyBy(fn($item) => $item->work_date->format('Y-m-d'));
    }

    public static function getMonthDays(Carbon $date)
    {
        $daysInMonth = $date->daysInMonth;
        $monthDays = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $monthDays[] = $date->copy()->day($i);
        }
        return $monthDays;
    }

    // public function getDisplayBreaks(bool $isEditable)
    // {
    //     $breaks = $this->breakRecords ?? collect();

    //     // 編集不可（当日など）の場合は、空のデータをフィルタリングして除外する
    //     if (!$isEditable) {
    //         return $breaks->filter(function ($break) {
    //             return !empty($break->break_in) || !empty($break->break_out);
    //         })->values();
    //     }

    //     return $breaks;
    // }

    /**
     * ループで使用する休憩枠の最大数を取得する
     */
    // public function getDisplayMaxBreaks(bool $isEditable): int
    // {
    //     $breaks = $this->getDisplayBreaks($isEditable);

    //     // 編集可能な場合は、新規入力用の空き枠として +1 する
    //     return $isEditable ? $breaks->count() + 1 : $breaks->count();
    // }

    /**
     * 修正申請（承認待ち）がある場合に、申請後のデータ（after_data）を考慮した出退勤・備考・休憩を取得する
     */
    public function getDisplayData(?AttendanceRequest $attendanceRequest, bool $isEditable)
    {
        $hasPendingRequest = $attendanceRequest && $attendanceRequest->isPending();

        $clockIn = null;
        $clockOut = null;
        $remarks = '';

        if ($hasPendingRequest) {
            $afterData = $attendanceRequest->after_data ?? [];
            $clockIn = !empty($afterData['clock_in']) ? Carbon::parse($afterData['clock_in']) : null;
            $clockOut = !empty($afterData['clock_out']) ? Carbon::parse($afterData['clock_out']) : null;

            // 💡 変更理由（備考）は AttendanceRequest の reason カラムから取得する
            $remarks = $attendanceRequest->reason ?? '';
        } else {
            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;
            $remarks = $this->remarks ?? '';
        }

        return [
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'remarks' => $remarks,
            'has_pending_request' => $hasPendingRequest,
        ];
    }

    public function getDisplayBreaksModel(?AttendanceRequest $attendanceRequest, bool $isEditable)
    {
        $hasPendingRequest = $attendanceRequest && $attendanceRequest->isPending();

        if ($hasPendingRequest) {
            $breaks = $attendanceRequest->after_data['breaks'] ?? [];
            $formatted = [];
            foreach ($breaks as $breakData) {
                // 💡 追加：両方とも空のデータはスキップする
                if (empty($breakData['break_in']) && empty($breakData['break_out'])) {
                    continue;
                }

                $formatted[] = (object)[
                    'break_in' => !empty($breakData['break_in']) ? Carbon::parse($breakData['break_in']) : null,
                    'break_out' => !empty($breakData['break_out']) ? Carbon::parse($breakData['break_out']) : null,
                ];
            }
            return collect($formatted);
        }

        // 通常の休憩データ
        $breaks = $this->breakRecords ?? collect();

        // 編集不可（当日など）の場合は空のデータを除外
        if (!$isEditable) {
            return $breaks->filter(function ($break) {
                return !empty($break->break_in) || !empty($break->break_out);
            })->values();
        }

        return $breaks;
    }

    public function getDisplayMaxBreaksModel(?AttendanceRequest $attendanceRequest, bool $isEditable): int
    {
        $breaks = $this->getDisplayBreaksModel($attendanceRequest, $isEditable);
        $hasPendingRequest = $attendanceRequest && $attendanceRequest->isPending();

        if ($hasPendingRequest) {
            return max(1, $breaks->count()); // 最低1行は表示したい場合は max(1, ...) に調整してください
        }

        // 申請中、または編集不可の場合はコレクションの数そのまま。編集可能な場合は入力枠用に +1
        return $isEditable ? $breaks->count() + 1 : $breaks->count();
    }
}
