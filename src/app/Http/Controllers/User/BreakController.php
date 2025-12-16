<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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


            if ($attendance->status === Attendance::STATUS_BREAKING) {
                return;
            }

            $attendance->breaks()->create([
                'break_start' => Carbon::now(),
            ]);

            $attendance->update([
                'status' => Attendance::STATUS_BREAKING,
            ]);
        });

        return redirect()->route('attendance.create');
    }

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

                $attendance->update([
                    'status' => Attendance::STATUS_WORKING,
                ]);
            }
        });

        return redirect()->route('attendance.create');
    }
}
