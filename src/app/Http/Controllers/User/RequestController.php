<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\RequestBreak;
use App\Models\Request as AttendanceRequest;
use Carbon\Carbon;

class RequestController extends Controller
{
    public function store(Request $request, $id)
    {
       
        $attendance = Attendance::findOrFail($id);
  

        $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

      

        $requestedClockIn = Carbon::parse("$workDate {$request->clock_in}");
        $requestedClockOut = $request->clock_out
            ? Carbon::parse("$workDate {$request->clock_out}")
            : null;

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id'        => $attendance->id,
            'requested_clock_in'   => $requestedClockIn,
            'requested_clock_out'  => $requestedClockOut,
            'reason'               => $request->reason ?? '勤怠修正申請',
            'applied_date'         => now()->toDateString(),
            'status'               => 'applied',
        ]);

        // Break 1
        foreach ($request->breaks ?? [] as $breakInput) {
            if (empty($breakInput['id'])) continue;
            if (empty($breakInput['start']) && empty($breakInput['end'])) continue; // ←追加

            RequestBreak::create([
                'request_id' => $attendanceRequest->id,
                'break_id'   => $breakInput['id'],
                'requested_break_start' => !empty($breakInput['start']) ? Carbon::parse("$workDate {$breakInput['start']}") : null,
                'requested_break_end'   => !empty($breakInput['end']) ? Carbon::parse("$workDate {$breakInput['end']}") : null,
            ]);
        }




        return redirect()
            ->route('attendance.request', ['status' => 'applied'])
            ->with('success', '修正申請を承認待ちに追加しました。');
    }

}