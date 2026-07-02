<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LateMarkCalculation extends Model
{
    use HasFactory;
    protected $table = 'late_mark_calculation_masters';

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'late_mark_count',
        'total_late_time',
        'amount_reduce',
        'status',
        'createdby',
        'updatedby'
    ];

    public function employee()
    {
         return $this->belongsTo(User::class, 'employee_id', 'ID');
    }
}
