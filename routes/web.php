<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WelfareController;
use App\Http\Controllers\HousingPhysicalController;
use App\Http\Controllers\Household64Controller;
use App\Http\Controllers\HelpRecordController;
use App\Http\Controllers\CapitalsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ✅ เบา ๆ ไว้เช็คความเร็วระบบ (ลบได้ทีหลัง)
Route::get('/ping', function () {
    return response()->json(['ok' => true, 'time' => now()->toDateTimeString()]);
})->name('ping');

// ======================
// Dashboard (หน้าแรก)
// ======================
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ======================
// Health
// ======================
Route::get('/health', [HealthController::class, 'index'])->name('health.index');
Route::get('/health/export', [HealthController::class, 'export'])->name('health.export');

// ถ้ายังมีลิงก์ /test เดิม ให้เด้งไป /health
Route::get('/test', function () {
    return redirect()->route('health.index');
})->name('test.redirect');

// ======================
// Welfare
// ======================
Route::get('/welfare', [WelfareController::class, 'index'])->name('welfare.index');

// ======================
// Capitals
// ======================
Route::get('/capitals', [CapitalsController::class, 'index'])->name('capitals.index');

// ======================
// Housing
// ======================
Route::get('/housing', [HousingPhysicalController::class, 'dashboard'])->name('housing.dashboard');
Route::get('/housing/map', [HousingPhysicalController::class, 'map'])->name('housing.map');
Route::get('/housing/house/{houseId}', [HousingPhysicalController::class, 'show'])->name('housing.show');

// Help records (housing)
Route::get('/housing/house/{houseId}/help/create', [HelpRecordController::class, 'create'])->name('help.create');
Route::post('/housing/house/{houseId}/help', [HelpRecordController::class, 'store'])->name('help.store');

// ======================
// Household (ปี 2564)
// ======================
Route::get('/household_64', [Household64Controller::class, 'index'])->name('household_64');

// ======================
// Auth (Register / Login / Logout)
// ======================
Route::get('/register', function () {
    return view('register');
})->name('register.form');

Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');

Route::get('/logout', function () {
    session()->flush();
    return redirect()->route('dashboard');
})->name('logout');

// ======================
// Demo
// ======================
Route::get('/hello', function () {
    return "Hello laravel";
})->name('hello');