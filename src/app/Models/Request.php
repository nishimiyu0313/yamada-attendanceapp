<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests'; 

    protected $fillable = [
        'attendance_id',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'applied_date',
        'status',
        'approver_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    // 紐づく休憩申請
    /*public function breaks()
    {
        return $this->hasMany(RequestBreak::class, 'break_id', 'id'); // break_id が Request.id を参照
    }*/

    public function breaks()
    {
        return $this->hasMany(RequestBreak::class, 'request_id', 'id');
    }

    
}
