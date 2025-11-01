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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_WORKING  => '勤務中',
            self::STATUS_BREAKING => '休憩中',
            self::STATUS_FINISHED => '退勤済み',
            default => '不明',
        };
    }

    public function scopeToday($query)
    {
        return $query->where('work_date', now()->toDateString());
    }

    public function isWorking(): bool
    {
        return $this->status === self::STATUS_WORKING;
    }

    public function isBreaking(): bool
    {
        return $this->status === self::STATUS_BREAKING;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function totalBreakMinutes(): int
    {
        return $this->breaks->reduce(function ($carry, $break) {
            $end = $break->break_end ?? now(); // 終了していない場合は現在時刻
            return $carry + $end->diffInMinutes($break->break_start);
        }, 0);
    }

    public function totalWorkMinutes(): int
    {
        $clockOut = $this->clock_out ?? now();
        $workMinutes = $clockOut->diffInMinutes($this->clock_in);
        return $workMinutes - $this->totalBreakMinutes();
    }
}
