<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\BreakController;
use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('/logout', [CustomAuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {


    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::patch('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::post('/attendance/{attendance}/break', [BreakController::class, 'store'])->name('break.store');
    Route::patch('/attendance/{attendance_id}/break/{break_id}', [BreakController::class, 'update'])
        ->name('break.update');

    Route::get('/attendance/list', [AttendanceController::class, 'index']);
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'request']);
});
