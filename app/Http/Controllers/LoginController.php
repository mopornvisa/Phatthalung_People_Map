<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Register;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // ตรวจสอบข้อมูลที่กรอก
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // ดึงข้อมูลจาก register table
        $user = Register::where('register_User', $request->username)
                        ->where('register_Password', $request->password)
                        ->first();

        if ($user) {

            // 🔹 เก็บข้อมูลลง session (เก็บครบทุกอย่างที่ต้องใช้)
            session([
                'login_user'      => $user->register_User,
                'login_type'      => $user->register_Type,
                'user_firstname'  => $user->register_Name,
                'user_lastname'   => $user->register_Same,
            ]);

            // 🔹 ล็อกอินสำเร็จ → ไปหน้า Dashboard
            return redirect('/');
        }

        // ❌ ถ้าผิด
        return back()->withErrors([
            'login_error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
        ]);
    }
}
