<?php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\backend\Staff;


class Empadv_Pay extends Model
{
    use HasFactory;

    protected $table = 'employee_advance_payments';

    protected $fillable = [
        'Id', 'ClientID', 'pid', 'Date', 'emp_id','advance','payment_method','amt_pay','cheque_no','created_by', 'account_no',
        'created_at','narration','createdby','updated_at'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'emp_id', 'ID');
    }
}
