<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\BreakRecordController;
use App\Http\Controllers\Employee\AttendanceListController;
use App\Http\Controllers\Employee\AttendanceRequestController;
use App\Http\Controllers\Employee\AttendanceReportController;

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AdminStaffAttendanceController;
use App\Http\Controllers\Admin\AdminAttendanceRequestController;

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

// ログイン関係
Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/login', [LoginController::class, 'showEmployeeLogin'])->name('employee.login');
Route::post('/login', [LoginController::class, 'store'])->name('login');
Route::get('/admin/login', [LoginController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'store'])->name('admin.login.post');

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// メール認証
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->middleware(['throttle:6,1'])->name('verification.send');
});

// 一般関係
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠登録画面からの出退勤の打刻
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/update', [AttendanceController::class, 'update'])->name('attendance.update');

    // 勤怠登録画面からの休憩の打刻
    Route::post('/break/store', [BreakRecordController::class, 'store'])->name('break.store');
    Route::post('/break/update', [BreakRecordController::class, 'update'])->name('break.update');

    // 勤怠一覧画面と勤怠詳細画面の表示
    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceListController::class, 'show'])->name('attendance.detail');
    Route::get('/attendance/create/{date}', [AttendanceListController::class, 'create'])->name('attendance.detail.create');

    // 修正申請
    Route::post('/attendance/request', [AttendanceRequestController::class, 'store'])->name('attendance.request.store');
    Route::patch('/attendance/request/{id}', [AttendanceRequestController::class, 'update'])->name('attendance.request.update');

    // レポートの表示
    Route::get('/attendance/report', [AttendanceReportController::class, 'index'])->name('attendance.report');
});

// 管理者関係
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.index');

    // スタッフ一覧の表示
    Route::get('/admin/staff/list', [StaffController::class, 'index'])->name('admin.staff.index');

    // スタッフの月次勤怠一覧
    Route::get('/admin/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'index'])->name('admin.staff.attendance.list');
    // CSV出力用ルート
    Route::get('/admin/staff/attendance/{id}/csv', [AdminStaffAttendanceController::class, 'exportCsv'])->name('admin.staff.attendance.csv');

    // 勤怠詳細画面の表示
    Route::get('/admin/attendance/create/{date}', [AdminStaffAttendanceController::class, 'create'])->name('admin.staff.attendance.create');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');

    // 修正申請
    Route::post('/admin/attendance/store', [AdminStaffAttendanceController::class, 'store'])->name('admin.attendance.store');
    Route::patch('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    // 修正申請承認画面
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceRequestController::class, 'show'])->name('admin.attendance.request.show');
    Route::patch('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceRequestController::class, 'update'])->name('admin.attendance.request.update');
});

// 申請一覧（一般と管理者をmiddlewareで振り分け）
Route::middleware(['auth', 'verified', 'redirect.by.role'])->group(function () {
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('stamp_correction_request.list');
});
