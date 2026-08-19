<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Backend\Shift;

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
         'date',
         'shift',
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
          'ot_hr',
          'createdby',
          'created_at',
          'updated_at',
    ];

   public function Shift()
{
    return $this->belongsTo(Shift::class, 'shift');
}


// public function user()
// {
//     return $this->belongsTo(User::class, 'emp_id', 'ID');
// }
public function user()
{
    return $this->belongsTo(User::class, 'emp_id', 'emp_id');
}


// public function user()
// {
//     return $this->belongsTo(User::class, 'emp_id');
// }

}
