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
            'reason'               => $request->note ?? '勤怠修正申請',
            'applied_date'         => now()->toDateString(),
            'status'               => 'applied',
        ]);

        // Break 1
        if ($request->break1_start && $request->break1_end) {
            $break1 = $attendance->breaks()->skip(0)->first();

            if ($break1) {
                RequestBreak::create([
                    'request_id' => $attendanceRequest->id,
                    'break_id'              => $break1->id,
                    'requested_break_start' => Carbon::parse("$workDate {$request->break1_start}"),
                    'requested_break_end'   => Carbon::parse("$workDate {$request->break1_end}"),
                ]);
            }
        }

        // Break 2
        if ($request->break2_start && $request->break2_end) {
            $break2 = $attendance->breaks()->skip(1)->first();

            if ($break2) {
                RequestBreak::create([
                    'request_id' => $attendanceRequest->id,
                    'break_id'              => $break2->id,
                    'requested_break_start' => Carbon::parse("$workDate {$request->break2_start}"),
                    'requested_break_end'   => Carbon::parse("$workDate {$request->break2_end}"),
                ]);
            }
        }

        return redirect()
            ->route('attendance.request', ['status' => 'pending'])
            ->with('success', '修正申請を承認待ちに追加しました。');
    }

}