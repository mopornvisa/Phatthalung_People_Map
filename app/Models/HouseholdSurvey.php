<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseholdSurvey extends Model
{
    protected $table = 'household_surveys_2564'; // ชื่อตารางจริง
    protected $primaryKey = 'id';                 // PK ชื่อตามจริง
    public $timestamps = false;                  // ไม่มี created_at / updated_at

    // ถ้า id เป็น auto-increment ให้เปิดบรรทัดนี้
    public $incrementing = true;                 
    protected $keyType = 'int';                  // เปลี่ยนเป็น string ถ้าไม่ใช่เลข

    protected $fillable = [
        'id',
        'house_Id',
        'survey_Year',
        'survey_No',
        'survey_Has_agri_book',
        'survey_Agri_household_no',
        'house_Number',
        'village_No',
        'village_Name',
        'survey_Soi',
        'survey_Road',
        'id_Subdistrict',
        'survey_Subdistrict',
        'id_District',
        'survey_District',
        'id_Province',
        'survey_Province',
        'survey_Postcode',
        'survey_Householder_title',
        'survey_Householder_fname',
        'survey_Householder_lname',
        'survey_Householder_cid',
    ];
}
