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

    public function scopeActive($query, $user_id)
    {
        return $query->where('user_id', $user_id)
            ->where('status', self::STATUS_WORKING)
            ->orderBy('work_date', 'desc');
    }

    public function scopeTodayStarted($query, $user_id)
    {
        return $query->where('user_id', $user_id)
            ->where('work_date', now()->format('Y-m-d'));
    }

    public function scopeFinishedToday($query, $user_id)
    {
        return $query->where('user_id', $user_id)
            ->where('work_date', now()->format('Y-m-d'))
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
        return $this->breakRecords->sum(function ($break) {
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
}
