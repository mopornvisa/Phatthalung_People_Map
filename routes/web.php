<?php

use Illuminate\Support\Facades\Route;


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
Route::get('/hello', function () {
    return "Hello laravel";
});
Route::get('/login', function () {
    session()->flush();
    return view('login');
});
Route::get('/register', function () {
    return view('register');
});
use App\Http\Controllers\RegisterController;

Route::post('/register', [RegisterController::class, 'store']);

use App\Http\Controllers\LoginController;

Route::get('/login', [LoginController::class, 'showLoginForm']);
Route::post('/login', [LoginController::class, 'login']);
 
Route::get('/logout', function () {
    session()->flush(); // ลบ session ทั้งหมด
    return redirect('/'); // กลับไปหน้าแรก
});


