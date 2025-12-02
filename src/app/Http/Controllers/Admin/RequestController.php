<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Request as AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = AttendanceRequest::with('attendance.user')
            ->where('status', $status)
            ->orderBy('applied_date', 'desc')
            ->get()
            ->unique('attendance_id'); 
        return view('admin.application', compact('requests', 'status'));
    }

    public function show($id)
    {
        $attendanceRequest = AttendanceRequest::with('attendance.user', 'breaks')->findOrFail($id);

        $attendance = $attendanceRequest->attendance;


        return view('admin.approve', compact('attendanceRequest', 'attendance'));
    }

    public function update($id)
    {
        // 修正申請を取得（AttendanceRequest + RequestBreak）
        $request = AttendanceRequest::with('attendance', 'breaks.break')->findOrFail($id);

        $attendance = $request->attendance;

        $attendance->clock_in = $request->requested_clock_in;
        $attendance->clock_out = $request->requested_clock_out;
        $attendance->save();

        foreach ($request->breaks as $requestBreak) {
            $attendanceBreak = $attendance->breaks()->where('id', $requestBreak->break_id)->first();
            if ($attendanceBreak) {
                $attendanceBreak->break_start = $requestBreak->requested_break_start;
                $attendanceBreak->break_end   = $requestBreak->requested_break_end;
                $attendanceBreak->save();
            }
        }

        $request->status = 'approved';
        $request->approver_id = auth()->id();
        $request->save();

        return redirect()
            ->route('admin.requests', ['status' => 'approved'])
            ->with('success', '勤怠修正申請を承認しました。');
    }
}
