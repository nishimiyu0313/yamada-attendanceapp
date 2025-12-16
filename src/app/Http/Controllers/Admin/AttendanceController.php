<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = Carbon::parse($request->query('work_date', Carbon::today()));
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        $attendances = Attendance::with('user', 'breaks')
            ->where('work_date', $currentDate)
            ->whereHas('user', function ($query) {
                $query->where('role', 'user');
            })
            ->get();

        return view('admin.index', compact('currentDate', 'prevDate', 'nextDate', 'attendances'));
    }


    public function staffindex(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $currentDate = $request->query('work_date') ? Carbon::parse($request->query('work_date')) : Carbon::today();
        $prevDate = $currentDate->copy()->subMonth()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addMonth()->format('Y-m-d');

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $dates = [];
        for ($date = $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
            $dates[] = $date->copy();
        }

        $attendances = Attendance::with('breaks')
            ->where('user_id', $staff->id)
            ->whereMonth('work_date', $currentDate->month)
            ->whereYear('work_date', $currentDate->year)
            ->orderBy('work_date', 'asc')
            ->get()
            ->keyBy(fn($item) => $item->work_date->format('Y-m-d'));

        return view('admin.attendance', compact('staff', 'dates', 'attendances', 'currentDate', 'prevDate', 'nextDate'));
    }


    public function csv(Request $request)
    {

        $month = $request->month;

        $query = Attendance::with('breaks')
            ->where('user_id', auth()->id());

        if ($month) {
            $year = substr($month, 0, 4);
            $monthNum = substr($month, 5, 2);

            $query->whereYear('work_date', $year)
                ->whereMonth('work_date', $monthNum);
        }

        $attendances = $query->get();


        $csvHeader = [
            'id',
            'work_date',
            'clock_in',
            'clock_out',
            'breaktime',
            'worktime',
            'created_at',
            'updated_at'
        ];

        $response = new StreamedResponse(function () use ($csvHeader, $attendances) {
            $file = fopen('php://output', 'w');

            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);

            fputcsv($file, $csvHeader);

            foreach ($attendances as $attendance) {
                $breakMinutes = $attendance->break_minutes_total;
                $workMinutes  = $attendance->work_minutes_total;

                $breakH = intdiv($breakMinutes, 60);
                $breakM = $breakMinutes % 60;
                $breakTime = "{$breakH}:" . str_pad($breakM, 2, '0', STR_PAD_LEFT);

                $workH = intdiv($workMinutes, 60);
                $workM = $workMinutes % 60;
                $workTime = "{$workH}:" . str_pad($workM, 2, '0', STR_PAD_LEFT);

                $row = [
                    $attendance->id,
                    $attendance->work_date,
                    $attendance->clock_in?->format('H:i') ?? '',
                    $attendance->clock_out?->format('H:i') ?? '',
                    $breakTime,
                    $workTime,
                    Carbon::parse($attendance->created_at)->setTimezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
                    Carbon::parse($attendance->updated_at)->setTimezone('Asia/Tokyo')->format('Y/m/d H:i:s'),
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance.csv"',
        ]);

        return $response;
    }


    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks', 'requests'])->findOrFail($id);


        $hasPendingRequest = $attendance->requests()
            ->where('status', 'applied')
            ->exists();

        if (auth()->user()->is_admin) {
            $isEditable = true;
        } else {

            $isEditable = !$hasPendingRequest;
        }

        $message = $hasPendingRequest
            ? '*承認待ちのため修正できません。'
            : null;

        return view('admin.detail', [
            'attendance' => $attendance,
            'isEditable' => $isEditable,
            'message'    => $message,
        ]);
    }

    public function request(AdminAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breaks', 'requests')->findOrFail($id);

        $hasPendingRequest = $attendance->requests()->where('status', 'applied')->exists();
        if ($hasPendingRequest) {
            return back()->with('error', '*承認待ちのため修正できません。');
        }

        $workDate = $attendance->work_date->format('Y-m-d');


        $attendance->clock_in  = $request->clock_in ? Carbon::parse("$workDate {$request->clock_in}") : null;
        $attendance->clock_out = $request->clock_out ? Carbon::parse("$workDate {$request->clock_out}") : null;
        $attendance->save();


        if ($request->has('breaks')) {
            foreach ($request->breaks as $index => $breakData) {
                $break = $attendance->breaks[$index] ?? $attendance->breaks()->create([]);
                if (!empty($breakData['start'])) {
                    $break->break_start = Carbon::parse("$workDate {$breakData['start']}");
                }
                if (!empty($breakData['end'])) {
                    $break->break_end = Carbon::parse("$workDate {$breakData['end']}");
                }
                $break->save();
            }
        }

        return redirect()->back()->with('message', '勤怠を修正しました。');
    }
}
