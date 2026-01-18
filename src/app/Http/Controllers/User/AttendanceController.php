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
            return redirect()->back();
        }

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'clock_in'  => Carbon::now(),
            'status'    => Attendance::STATUS_WORKING,
        ]);

        return redirect()->back();
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
        $currentDate = $request->query('work_date') ? Carbon::parse($request->query('work_date')) : Carbon::today();
        $prevDate = $currentDate->copy()->subMonth()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addMonth()->format('Y-m-d');

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $dates = [];
        for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
            $dates[] = $date->copy();
        }

        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('work_date', $currentDate->month)
            ->whereYear('work_date', $currentDate->year)
            ->orderBy('work_date', 'asc')
            ->get()
            ->keyBy(fn($item) => $item->work_date->format('Y-m-d'));

        return view('user.list', compact('dates', 'attendances', 'currentDate', 'prevDate', 'nextDate'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('breaks')
            ->findOrFail($id);

        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $latestRequest = $attendance->requests()
            ->where('status', 'applied') 
            ->latest()
            ->first();

        if ($latestRequest) {
            $attendance->clock_in  = $latestRequest->requested_clock_in;
            $attendance->clock_out = $latestRequest->requested_clock_out;

            foreach ($attendance->breaks as $break) {
                $reqBreak = $latestRequest->breaks?->firstWhere('break_id', $break->id);
                if ($reqBreak) {
                    $break->break_start = $reqBreak->requested_break_start;
                    $break->break_end   = $reqBreak->requested_break_end;
                }
            }
   
        $isEditable = false;
        $message = '*修正待ちのため修正はできません。';
    } else {
        
        $isEditable = $attendance->can_edit;
        $message = $attendance->can_edit ? null : '*修正待ちのため修正はできません。';
    }



        return view('user.detail', [
            'attendance' => $attendance,
            'isEditable' => $isEditable,
            'message' => $message,
        ]);
    }

    public function showrequest($id)
    {

        $request = AttendanceRequest::with('attendance.breaks')->findOrFail($id);
        $attendance = $request->attendance;


        if (!$attendance) {
            abort(404, '勤怠情報が見つかりません');
        }

        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $attendance->clock_in  = $request->requested_clock_in;
        $attendance->clock_out = $request->requested_clock_out;

        foreach ($attendance->breaks as $break) {
            $reqBreak = $request->breaks?->where('break_id', $break->id)->first();
            if ($reqBreak) {
                $break->break_start = $reqBreak->requested_break_start;
                $break->break_end   = $reqBreak->requested_break_end;
            }
        }


        return view('user.detail', [
            'attendance' => $attendance,
            'isEditable' => false,
            'message' => $attendance->can_edit ? null : '*承認待ちのため修正はできません。',
        ]);
    }



    public function request(Request $request)
    {
        $status = $request->query('status', 'applied');

        $requests = AttendanceRequest::with('attendance.user')
            ->where('status', $status)
            ->whereHas('attendance', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('applied_date', 'desc')
            ->get()
            ->unique('attendance_id', true);


        return view('user.request', compact('requests', 'status'));
    }
}
