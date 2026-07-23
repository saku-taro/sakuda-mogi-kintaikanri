<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class AttendanceRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'before_data',
        'after_data',
        'reason',
        'status',
        'applicant_id',
        'approved_by',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data'  => 'array',
        'status'      => 'integer',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // public function applicant()
    // {
    //     return $this->belongsTo(User::class, 'applicant_id');
    // }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => '承認待ち',
            self::STATUS_APPROVED => '承認済み',
        };
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getFormattedClockInAttribute()
    {
        $clockIn = $this->after_data['clock_in'] ?? null;
        return $clockIn ? Carbon::parse($clockIn)->format('H:i') : null;
    }

    public function getFormattedClockOutAttribute()
    {
        $clockIn = $this->after_data['clock_out'] ?? null;
        return $clockIn ? Carbon::parse($clockIn)->format('H:i') : null;
    }

    public function getFormattedBreaksAttribute()
    {
        $breaks = $this->after_data['breaks'] ?? [];
        if (!is_array($breaks)) {
            return [];
        }

        $formatted = [];
        foreach ($breaks as $index => $breakData) {
            $formatted[$index] = [
                'break_in'  => !empty($breakData['break_in']) ? Carbon::parse($breakData['break_in'])->format('H:i') : null,
                'break_out' => !empty($breakData['break_out']) ? Carbon::parse($breakData['break_out'])->format('H:i') : null,
            ];
        }

        return $formatted;
    }
}
