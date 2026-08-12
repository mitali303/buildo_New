<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class DailyWorkEntry extends Model
{
    protected $table = 'daily_work_entry';

    protected $primaryKey = 'ID';

    public $timestamps = false;


    protected $fillable = [

        'ClientID',
        'date',
        'sitename',
        'workdone',
        'UserID',
        'Created',
        'LastEdited'

    ];
}