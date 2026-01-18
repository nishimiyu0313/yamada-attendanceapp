<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BreakTime;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
    ];

    protected $casts = [
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'work_date' => 'date',
    ];

    public const STATUS_WORKING  = 'working';
    public const STATUS_BREAKING = 'breaking';
    public const STATUS_FINISHED = 'finished';


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function scopeToday($query)
    {
        return $query->where('work_date', now()->toDateString());
    }


    public function getBreakMinutesTotalAttribute(): int
    {
        return $this->breaks->sum(function ($break) {
            $end = $break->break_end ?? now();
            return $end->diffInMinutes($break->break_start);
        });
    }

    public function getWorkMinutesTotalAttribute(): int
    {
        $clockOut = $this->clock_out ?? now();
        return $clockOut->diffInMinutes($this->clock_in) - $this->break_minutes_total;
    }

    public function getCanEditAttribute()
    {
       
        $hasPendingRequest = $this->requests()
            ->where('status', 'applied')
            ->exists();

        return !$hasPendingRequest;
    }
}
