<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;

class AttendanceController extends Controller
{
public function show($id)
{
$attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);
return view('admin.attendance.detail', compact('attendance'));
}

public function update($request, $id)
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
->with('success', '勤怠情報を更新しました。');
}
}