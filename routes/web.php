<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HouseholdSurveyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// test
Route::get('/test', function () {
    return view('test');
});

// หน้า household_64 (ใช้ Controller ดึงข้อมูล)
Route::get('/household_64', [HouseholdSurveyController::class, 'index'])
     ->name('household_64');

// หน้าแรก
Route::get('/', function () {
    return view('welcome');
});

// ตัวอย่าง Hello
Route::get('/hello', function () {
    return "Hello laravel";
});

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
