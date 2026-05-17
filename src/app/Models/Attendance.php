<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    public function getRequestStatusAttribute()
    {
        $request = $this->attendanceRequests->first();

        if (!$request) {
            return '';
        }

        return match ($request->status) {
            0 => '申請中',
            1 => '承認済み',
            2 => '却下',
            default => '',
        };
    }
}