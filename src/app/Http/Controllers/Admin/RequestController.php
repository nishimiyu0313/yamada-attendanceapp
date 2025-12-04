<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Request as AttendanceRequest;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $attendanceRequest = AttendanceRequest::with('attendance', 'breaks.break')->findOrFail($id);

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


        /*public function update($id)
    {
        // 修正申請を取得（AttendanceRequest + RequestBreak）
        $attendanceRequest = AttendanceRequest::with('attendance', 'breaks.break')->findOrFail($id);

        
        $attendance = $attendanceRequest->attendance;

        $attendance->clock_in = $attendanceRequest->requested_clock_in;
        $attendance->clock_out = $attendanceRequest->requested_clock_out;
        $attendance->save();



        foreach ($attendanceRequest->breaks as $reqBreak) {

            // break_times の元データを取得
            $break = BreakTime::find($reqBreak->break_id);

            if ($break) {
                $break->break_start = $reqBreak->requested_break_start;
                $break->break_end   = $reqBreak->requested_break_end;
                $break->save();
            }
        }

        $attendanceRequest->status = 'approved';
        $attendanceRequest->approver_id = auth()->id();
        $attendanceRequest->save();

        return redirect()
            ->route('admin.requests', ['status' => 'approved'])
            ->with('success', '勤怠修正申請を承認しました。');
    }*/
