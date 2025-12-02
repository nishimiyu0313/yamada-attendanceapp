<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Request as AttendanceRequest;

class AttendanceController extends Controller
{

public function create(Request $request)
{
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->first();

        $status = $attendance ? $attendance->status : null;
    
    return view('user.registration', compact('attendance', 'status'));
}
    public function store(Request $request)
    {
        $user = Auth::user();

        $exists = Attendance::where('user_id', $user->id)
            ->where('work_date', \Carbon\Carbon::today())
            ->exists();

        if ($exists) {
            return redirect()->back()->with('status', '本日はすでに出勤済みです。');
        }

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        return redirect()->back()->with('status', '出勤しました。');
    }

    public function update($id)
    {
        
        $attendance = Attendance::findOrFail($id);

       
        if ($attendance->clock_out) {
            return redirect()->back()->with('error', 'すでに退勤済みです。');
        }

        
        $attendance->update([
            'clock_out' => Carbon::now(),
            'status'    => Attendance::STATUS_FINISHED,
        ]);

        return redirect()->back()->with('success', '退勤が記録されました。');
    }


    public function index(Request $request)
    {
        $user = Auth::user();

        
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();
        return view('user.list', compact('attendances'));
    }

    public function show($id)
    {
        $attendanceRequest = AttendanceRequest::with('attendance.breaks')
            ->findOrFail($id);

        if ($attendanceRequest->attendance->user_id !== auth()->id()) {
            abort(403); // 他人の勤怠はアクセス禁止
        }

        $attendance = $attendanceRequest->attendance;

        $hasPendingRequest = AttendanceRequest::where('attendance_id', $id)
            ->where('status', 'applied')
            ->exists();

        return view('user.detail', compact('attendance', 'hasPendingRequest'));
    }

    public function request(Request $request)
    {
        $status = $request->query('status', 'applied');

        $requests = AttendanceRequest::with('attendance.user')
            ->where('status', $status)
            ->whereHas('attendance', function ($q) {
                $q->where('user_id', auth()->id()); // ログインユーザーの勤怠だけ
            })
            ->orderBy('applied_date', 'desc')
            ->get()
            ->unique('attendance_id');
        return view('user.request', compact('requests', 'status'));
    }

}