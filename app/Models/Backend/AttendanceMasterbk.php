<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AttendanceMaster extends Model
{
   use HasFactory;

     protected $table = 'attendancemaster';

    protected $primaryKey = 'id';
    public $incrementing = true; // id is auto-increment
    protected $keyType = 'int';

    public $timestamps = true; // table has created_at and updated_at

    protected $fillable = [
         'id',
         'month',
         'year',
         'date',
         'emp_id',
         'designation',
          'present',
          'absent',
          'emp_leave',
          'intime',
          'outtime',
          'late_mins',
          'early_dep',
          'work_hr',
          'created_at',
          'updated_at',
    ];



public function staff()
{
    return $this->belongsTo(Staff::class, 'emp_id', 'ID');
}



}
