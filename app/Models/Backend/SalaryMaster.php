<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SalaryMaster extends Model
{
    use HasFactory;

    protected $table = 'salarymaster';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [

        'salary_no',
        'date',
        'month',
        'year',
        'emp_id',
        'gross',
        'basic_salary',
        'pf',
        'esi',
        'advance_emi',
        'late_deduction',
        'overtime_amount',
        'total_present_days',
        'total_absent_days',
        'paid_leaves',
        'working_days',
        'per_day_salary',
        'absent_deduction',
        'overtime_hours',
        'per_hour_ot_rate',
        'net_salary',
        'payment_method',
        'cheque_no',
        'amount',
        'narration',
        'createdby',
    ];

    /* =====================================
       EMPLOYEE RELATION
    ===================================== */

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'emp_id');
    // }
    public function user()
{
    return $this->belongsTo(User::class, 'emp_id', 'ID');
}
}