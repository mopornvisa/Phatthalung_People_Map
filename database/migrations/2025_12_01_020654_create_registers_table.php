<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('register', function (Blueprint $table) {
        // Primary Key
        $table->increments('register_Id');  // int + PK

        // ฟิลด์ตาม Data Store
        $table->string('register_User', 50);      // varchar(50)
        $table->string('register_Password', 50);  // varchar(50)
        $table->string('register_Name', 30);      // varchar(30)
        $table->string('register_Same', 50);      // varchar(50) นามสกุล

       $table->string('register_Number', 13)->change();
 
        $table->string('register_Phone', 10);     // เดิม int(10)

        $table->string('register_Gmail', 50);     // varchar(50)
        $table->char('register_Type', 1);         // varchar(1) -> ใช้ char(1)

        $table->timestamps(); // ถ้าไม่ใช้ก็ลบได้
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registers');
    }
};
