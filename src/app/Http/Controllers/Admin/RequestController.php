<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Request as AttendanceRequest;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'applied');

        $subQuery = AttendanceRequest::selectRaw('MAX(id) as id')
            ->where('status', $status)
            ->groupBy('attendance_id');

        $requests = AttendanceRequest::with('attendance.user')
            ->whereIn('id', $subQuery)
            ->orderBy('applied_date', 'desc')
            ->paginate(15);

        return view('admin.application', compact('requests', 'status'));
    }

    public function show($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceRequest::with('attendance.breaks', 'breaks')->findOrFail($attendance_correct_request_id);
        $attendance = $attendanceRequest->attendance;

        $attendance->clock_in  = $attendanceRequest->requested_clock_in;
        $attendance->clock_out = $attendanceRequest->requested_clock_out;

        foreach ($attendance->breaks as $break) {
            $reqBreak = $attendanceRequest->breaks?->where('break_id', $break->id)->first();
            if ($reqBreak) {
                $break->break_start = $reqBreak->requested_break_start;
                $break->break_end   = $reqBreak->requested_break_end;
            }
        }


        return view('admin.approve', compact('attendanceRequest', 'attendance'));
    }

    public function update($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceRequest::with('attendance', 'breaks.break')->findOrFail($attendance_correct_request_id);

        if ($attendanceRequest->status === 'approved') {
            return redirect()->back()->with('error', 'この申請は既に承認されています。');
        }

        DB::transaction(function () use ($attendanceRequest) {

            $attendance = $attendanceRequest->attendance;
            $attendance->clock_in  = $attendanceRequest->requested_clock_in;
            $attendance->clock_out = $attendanceRequest->requested_clock_out;

            $attendance->save();


            foreach ($attendanceRequest->breaks as $reqBreak) {
                $break = BreakTime::find($reqBreak->break_id);
                if ($break) {
                    $break->break_start = $reqBreak->requested_break_start;
                    $break->break_end   = $reqBreak->requested_break_end;
                    $break->save();
                }
            }


            $attendanceRequest->status = 'approved';
            $attendanceRequest->approver_id = Auth::id();
            $attendanceRequest->save();
        });

        return redirect()->route('admin.requests', ['status' => 'approved'])
            ->with('success', '勤怠修正申請を承認しました。');
    }
}
