<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class StampOtherExpenses extends Model
{
    use HasFactory;
     protected $table = 'stampotherexpenses';
    protected $primaryKey = 'ID';
    protected $keyType = 'string';

     public $timestamps = false;

    protected $fillable = [
        'ID',
        'ClientID',
        'schemeID',
        'Exp_type',
        'title',
        'account_no',
        'narration',
        'Date',
        'amt_pay',
        'payment_method',
        'cheque_no',
        'PaymentId',
        'bankcharge',
        'empID',
        'month',
        'year',
        'fromdate',
        'todate',
        'Type_Payment',
        'bFlag',
        'reconciliation',
        'userID'
    ];

    public function accountNo()
    {
        return $this->belongsTo(Bank_Acc::class, 'account_no', 'ID');
    }
    public function schemes()
    {
        return $this->belongsTo(SchemeDetail::class, 'schemeID', 'ID');
    }
    
}
