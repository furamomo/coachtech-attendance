<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'clock_in_at',
        'clock_out_at',
        'note',
        'status',
        'approved_by',
        'approved_at',
    ];

    public const STATUS_PENDING = 0;
    public const STATUS_APPROVED = 1;

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestBreaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
