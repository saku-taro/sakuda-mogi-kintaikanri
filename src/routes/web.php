<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\BreakRecordController;
use App\Http\Controllers\Employee\AttendanceListController;
use App\Http\Controllers\Employee\AttendanceRequestController;

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
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::get('/admin/login', [LoginController::class, 'showAdminLogin'])->name('admin.login');

// メール認証
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->middleware(['throttle:6,1'])->name('verification.send');
});

// employee関係
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

    // 申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('stamp_correction_request.list');
});
