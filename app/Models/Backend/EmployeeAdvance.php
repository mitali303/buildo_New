<?php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EmployeeAdvance extends Model
{
    use HasFactory;

    protected $table = 'employee_advance';

    protected $fillable = [
        'record_no', 'date', 'emp_id','advance','emi_amount','total_installments',
        'remaining_amount','narration','createdby',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'emp_id', 'ID');
    }
}
