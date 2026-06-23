<?php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\backend\Staff;


class EmployeeAdvance extends Model
{
    use HasFactory;

    protected $table = 'employee_advance';

    protected $fillable = [
        'record_no', 'ClientID', 'Date', 'emp_id','advance','emi_amount','total_installments', 'payment_method',
        'account_no', 'remaining_amount','narration','createdby', 'cheque_no',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'emp_id', 'ID');
    }
}
