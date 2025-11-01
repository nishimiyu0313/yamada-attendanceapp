<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakController extends Controller
{
    // 休憩開始
    public function store(Attendance $attendance)
    {
        // 未終了の休憩がなければ作成
        $ongoingBreak = $attendance->breaks()->whereNull('break_end')->first();
        if (!$ongoingBreak) {
            $attendance->breaks()->create([
                'break_start' => Carbon::now(),
            ]);
            $attendance->update(['status' => Attendance::STATUS_BREAKING]);
        }

        return redirect()->route('attendance.create');
    }

    // 休憩終了
    public function update(Attendance $attendance, BreakTime $break)
    {
        if (!$break->break_end) {
            $break->update([
                'break_end' => Carbon::now(),
            ]);
            $attendance->update(['status' => Attendance::STATUS_WORKING]);
        }

        return redirect()->route('attendance.create');
    }
}
