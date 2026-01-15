<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Register;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        // validate ข้อมูลก่อนบันทึก
        $request->validate([
            'username'   => 'required|max:50',
            'password'   => 'required|max:50',
            'first_name' => 'required|max:30',
            'last_name'  => 'required|max:50',
            'citizen_id' => 'required|digits:13',
            'phone'      => 'required|max:10',
            'email'      => 'required|email|max:50',
            'user_type'  => 'required',
        ]);

        try {

            // บันทึกลงฐานข้อมูล
            Register::create([
                'register_User'     => $request->username,
                'register_Password' => $request->password,
                'register_Name'     => $request->first_name,
                'register_Same'     => $request->last_name,
                'register_Number'   => $request->citizen_id,
                'register_Phone'    => $request->phone,
                'register_Gmail'    => $request->email,
                'register_Type'     => $request->user_type,
            ]);

            // ลงทะเบียนสำเร็จ → ไปหน้า /
            return redirect('/')->with('success', 'ลงทะเบียนสำเร็จแล้ว!');

        } catch (\Exception $e) {

            // บันทึกไม่ได้ → กลับไปหน้าเดิม
            return redirect()->back()->with('error', 'ลงทะเบียนไม่สำเร็จ กรุณาตรวจสอบข้อมูลอีกครั้งค่ะ');
        }
    }
}
