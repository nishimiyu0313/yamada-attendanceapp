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

    public function breaks()
    {
        return $this->hasMany(RequestBreak::class, 'request_id', 'id');
    }

}
