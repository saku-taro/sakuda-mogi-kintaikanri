<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
