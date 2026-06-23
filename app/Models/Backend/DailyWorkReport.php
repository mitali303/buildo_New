<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWorkReport extends Model
{
    protected $table = 'daily_work_entr';

    protected $primaryKey = 'ID';

    public $timestamps = false;
}