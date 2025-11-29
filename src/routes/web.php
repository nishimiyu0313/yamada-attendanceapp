<?php

use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use App\Http\Controllers\User\BreakController as UserBreakController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ログアウト
Route::post('/logout', [CustomAuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/admin/login', function () {
    return view('auth.admin-login');
})->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->name('admin.login.post');


// 認証必須グループ
Route::middleware('auth', 'user')->group(function () {

    // ======================
    // 一般ユーザー側
    // ======================
    Route::get('/attendance', [UserAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [UserAttendanceController::class, 'store'])->name('attendance.store');
    Route::patch('/attendance/{id}', [UserAttendanceController::class, 'update'])->name('attendance.update');

    Route::post('/attendance/{attendance_id}/break', [UserBreakController::class, 'store'])->name('break.store');
    Route::patch('/attendance/{attendance_id}/break/{break_id}', [UserBreakController::class, 'update'])->name('break.update');

    Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('attendance.list');
    Route::get('/stamp_correction_request/user', [UserAttendanceController::class, 'request'])->name('attendance.request');

    Route::get('/attendance/detail/{id}', [UserAttendanceController::class, 'show'])->name('attendance.detail');
});

// ======================
// 管理者側
// ======================
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index']);
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'request'])->name('admin.attendance.request');
    Route::get('/admin/staff/list', [AdminUserController::class, 'index']);
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffindex'])->name('admin.attendance.staff');
    Route::get('/stamp_correction_request/list', [AdminRequestController::class, 'index'])->name('admin.requests');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'show'])->name('admin.stamp_request.approve');

});
