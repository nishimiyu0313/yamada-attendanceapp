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

        $requests = AttendanceRequest::with('user', 'attendance')
            ->where('status', $status)
            ->orderBy('applied_date', 'desc')
            ->get();
        return view('admin.application', compact('requests', 'status'));
    }

    public function show($id)
    {
        $request = Request::with('user', 'attendance')->findOrFail($id);
        return view('admin.approve', compact('request'));
    }

}