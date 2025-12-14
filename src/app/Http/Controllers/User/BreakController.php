<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BreakController extends Controller
{
    public function store($attendance_id)
    {
        DB::transaction(function () use ($attendance_id) {

            $attendance = Attendance::lockForUpdate()->findOrFail($attendance_id);
            //$attendance = Attendance::findOrFail($attendance_id);

       // DB::transaction(function () use ($attendance) {
            // 未終了の休憩があるか確認
            ///** @var Attendance $attendance */
            //$ongoingBreak = $attendance->breaks()->whereNull('break_end')->lockForUpdate()->first();

            if ($attendance->status === Attendance::STATUS_BREAKING) {
                return;
            }
                // 新しい休憩を作成
                $attendance->breaks()->create([
                    'break_start' => Carbon::now(),
                ]);

                // 勤怠ステータスを「休憩中」に変更
                $attendance->update([
                    'status' => Attendance::STATUS_BREAKING,
                ]);
    
        });

        return redirect()->route('attendance.create');
    }

    /**
     * 休憩終了
     */
    public function update($attendance_id, $break_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);

        DB::transaction(function () use ($attendance, $break_id) {
            $break = BreakTime::where('id', $break_id)
                ->where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->lockForUpdate()
                ->first();

            if ($break) {
                $break->update([
                    'break_end' => Carbon::now(),
                ]);

                // 勤怠ステータスを「勤務中」に戻す
                $attendance->update([
                    'status' => Attendance::STATUS_WORKING,
                ]);
            }
        });

        return redirect()->route('attendance.create');
    }
}



    /*public function store($attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);
     
        /*$ongoingBreak = $attendance->breaks()->whereNull('break_end')->first();
        if (!$ongoingBreak) {
            $attendance->breaks()->create([
                'break_start' => Carbon::now(),
            ]);
            $attendance->update(['status' => Attendance::STATUS_BREAKING]);
        }

        return redirect()->route('attendance.create');
    }

    
    public function update($attendance_id, $break_id)
    
    {

        $attendance = Attendance::findOrFail($attendance_id);
        $break = BreakTime::findOrFail($break_id);
        
        if (!$break->break_end) {
            $break->update([
                'break_end' => Carbon::now(),
            ]);
            $attendance->update(['status' => Attendance::STATUS_WORKING]);
        }

        return redirect()->route('attendance.create');
    }
}*/
