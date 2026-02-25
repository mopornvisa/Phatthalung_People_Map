<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpRecord extends Model
{
    protected $table = 'help_records';

    protected $fillable = [
        'house_Id',
        'survey_year',
        'help_year',
        'action_date',
        'agency',
        'action_type',
        'budget',
        'detail',
        'status',
        'next_followup',
        'result',
    ];
}
