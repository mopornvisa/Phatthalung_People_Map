<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HouseholdSurveyController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelfareController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


Route::get('/health', [HealthController::class, 'index'])->name('health.index');
Route::get('/welfare', [WelfareController::class, 'index'])->name('welfare.index');


// ถ้าคุณยังใช้ /test อยู่ ให้เด้งไปหน้าเดียวกัน
Route::get('/test', function () {
    return redirect()->route('health.index');
});

// ✅ หน้า Dashboard (หน้าแรก)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ✅ Health page (หน้าแสดงข้อมูลสุขภาพจริงจาก DB)
Route::get('/health', [HealthController::class, 'index'])->name('health.index');

// ✅ /test (ถ้ายังอยากใช้ลิงก์เดิมจากการ์ด) -> ให้เด้งไป /health
Route::get('/test', function () {
    return redirect()->route('health.index');
});

// หน้า household_64
Route::get('/household_64', [HouseholdSurveyController::class, 'index'])->name('household_64');

// Register
Route::get('/register', function () {
    return view('register');
});
Route::post('/register', [RegisterController::class, 'store']);

// Login
Route::get('/login', [LoginController::class, 'showLoginForm']);
Route::post('/login', [LoginController::class, 'login']);

// Logout
Route::get('/logout', function () {
    session()->flush();
    return redirect('/');
});

// ตัวอย่าง Hello
Route::get('/hello', function () {
    return "Hello laravel";
});
