<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Models\RequestBreak;
use App\Models\Request as AttendanceRequest;

use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = Carbon::parse($request->query('work_date', Carbon::today()));
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->subDay()->format('Y-m-d');

        $attendances = Attendance::with('user', 'breaks')
            ->where('work_date', $currentDate)
            ->get();

        return view('admin.index', compact('currentDate', 'prevDate', 'nextDate', 'attendances'));
    }

    public function staffindex(Request $request ,$id)
    {

        $currentDate = Carbon::parse($request->query('work_date', Carbon::today()));
        $prevDate = $currentDate->copy()->subMonth()->format('Y-m-d');
        $nextDate = $currentDate->copy()->subMonth()->format('Y-m-d');

        $staff = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $id)
            ->whereMonth('work_date', $currentDate->month)
            ->whereYear('work_date', $currentDate->year)
            ->orderBy('work_date', 'asc')
            ->get();
        return view('admin.attendance', compact('staff', 'attendances', 'currentDate', 'prevDate', 'nextDate'));
    }




    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);
        return view('admin.detail', compact('attendance'));
    }

    public function request(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $workDate = \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');

        $requestedClockIn = \Carbon\Carbon::parse($attendance->work_date . ' ' . $request->clock_in);
        $requestedClockOut = $request->clock_out ? \Carbon\Carbon::parse($attendance->work_date . ' ' . $request->clock_out) : null;

        // 修正リクエストの新規登録
        AttendanceRequest::create([
            'attendance_id'        => $attendance->id,
            'requested_clock_in'   => $requestedClockIn,    // ← ここを Carbon 変数に変更
            'requested_clock_out'  => $requestedClockOut,
            'reason'               => $request->note ?? '勤怠修正申請',
            'applied_date'         => now()->toDateString(),
            'status'               => 'applied',  // 承認待ち
        ]);

        if ($request->break1_start || $request->break1_end) {
            $break1 = $attendance->breaks()->skip(0)->first();

            if ($break1) {
                RequestBreak::create([
                    'break_id'              => $break1->id,
                    'requested_break_start' => \Carbon\Carbon::parse($workDate . ' ' . $request->break1_start),
                    'requested_break_end'   => \Carbon\Carbon::parse($workDate . ' ' . $request->break1_end),
                ]);
            }
        }

        
        if ($request->break2_start || $request->break2_end) {
            $break2 = $attendance->breaks()->skip(1)->first();

            if ($break2) {
                RequestBreak::create([
                    'break_id'              => $break2->id,
                    'requested_break_start' => $request->break2_start,
                    'requested_break_end'   => $request->break2_end,
                ]);
            }
        }

        return redirect()
            ->route('admin.requests', ['status' => 'pending'])
            ->with('success', '修正申請を承認待ちに追加しました。');
    }


    /*public function update($request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // 出退勤更新
        $attendance->clock_in = $request->clock_in ? "{$attendance->work_date} {$request->clock_in}" : null;
        $attendance->clock_out = $request->clock_out ? "{$attendance->work_date} {$request->clock_out}" : null;
        $attendance->note = $request->note;
        $attendance->save();

        // 休憩更新
        $breakData = [
            ['start' => $request->break1_start, 'end' => $request->break1_end],
            ['start' => $request->break2_start, 'end' => $request->break2_end],
        ];

        foreach ($breakData as $index => $data) {
            if (isset($attendance->breaks[$index])) {
                $attendance->breaks[$index]->update([
                    'break_start' => $data['start'] ? "{$attendance->work_date} {$data['start']}" : null,
                    'break_end' => $data['end'] ? "{$attendance->work_date} {$data['end']}" : null,
                ]);
            } elseif ($data['start']) {
                $attendance->breaks()->create([
                    'break_start' => "{$attendance->work_date} {$data['start']}",
                    'break_end' => $data['end'] ? "{$attendance->work_date} {$data['end']}" : null,
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.detail', $attendance->id)
            ->with('success', '勤怠情報を更新しました。');*/
    }
