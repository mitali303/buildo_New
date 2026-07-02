<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvancePayment extends Model
{
    use HasFactory;

    protected $table = 'employee_advance_payments';

    protected $fillable = [
        'parent_id',
        'emp_id',
        'advance',
        'payment_method',
        'cheque_no',
        'remaining_amount',
        'narration',
        'date'
    ];
}
