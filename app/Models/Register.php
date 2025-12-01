<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    protected $table = 'register';
    protected $primaryKey = 'register_Id';
    public $timestamps = true; // หรือ false ถ้าตารางไม่มี timestamps

    protected $fillable = [
        'register_User',
        'register_Password',
        'register_Name',
        'register_Same',
        'register_Number',
        'register_Phone',
        'register_Gmail',
        'register_Type',
    ];
}
