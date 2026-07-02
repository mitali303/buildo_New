<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\Staff;

class SalaryMaster extends Model
{
   use HasFactory;

     protected $table = 'salarymaster';

    protected $primaryKey = 'id';
    public $incrementing = true; // id is auto-increment
    protected $keyType = 'int';

    public $timestamps = true; // table has created_at and updated_at

    protected $fillable = [
         'id',
         'ClientID',
        'salary_no',
        'Date',
        'month',
        'year',
        'emp_id',
        'gross',
        'account_no',
        'basic_salary',
        'pf',
        'esi',
        'advance_emi',
        'net_salary',
        'payment_method',
        'cheque_no',
        'amount', // ✅ FIXED
        'narration',
        'created_at',
        'updated_at',
    ];

public function staff()
{
    return $this->belongsTo(Staff::class, 'emp_id', 'ID');
}
}
