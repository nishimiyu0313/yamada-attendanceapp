<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

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
        // 対象の勤怠データを取得
        $attendance = Attendance::findOrFail($id);

        // すでに退勤済みならエラー防止（任意）
        if ($attendance->clock_out) {
            return redirect()->back()->with('error', 'すでに退勤済みです。');
        }

        // 現在時刻を退勤時間として更新
        $attendance->update([
            'clock_out' => Carbon::now(),
            'status'    => Attendance::STATUS_FINISHED,
        ]);

        return redirect()->back()->with('success', '退勤が記録されました。');
    }


    public function index(Request $request)
    {
        $user = Auth::user();

        // ユーザーの勤怠データを日付順に取得
        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();
        return view('user.list', compact('attendances'));
    }


    public function request(Request $request)
    {
        return view('user.request');
    }


}