<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminAttendanceRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminStaffAttendanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\StampCorrectionRequestController;

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

Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/{id}/request', [AttendanceRequestController::class, 'store'])->name('attendance.request.store');
    Route::get('/attendance/request/detail/{id}', [AttendanceRequestController::class, 'show'])->name('attendance.request.show');
});

Route::middleware(['stamp.request.role'])->group(function () {
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp.request.list');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('admin.attendance.detail');
    Route::post('/admin/attendance/{id}/update', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');
    Route::get('/admin/attendance/create/{user_id}/{work_date}', [AdminAttendanceController::class, 'create'])
        ->name('admin.attendance.create');
    Route::post('/admin/attendance/create/{user_id}/{work_date}', [AdminAttendanceController::class, 'store'])
        ->name('admin.attendance.store');

    Route::get('/admin/staff/list', [AdminStaffController::class, 'list'])->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'index'])->name('admin.staff.attendance.list');
    Route::get('/admin/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])
        ->name('admin.attendance.staff.csv');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceRequestController::class, 'show'])
        ->name('stamp.request.approve');

    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceRequestController::class, 'approve'])
        ->name('stamp.request.approve.update');
});