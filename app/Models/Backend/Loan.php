<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
        protected $table = 'loan';
        protected $primaryKey = 'ID';
        protected $keyType = 'string';
        public $timestamps = false;

     protected $fillable = [
        'ID',
        'Date',
        'ClientID',
        'interest',
        'transType',
        'emi',
        'emi_no',
        'amt_with_interest',
        'payment_method',
        'account_no',
        'cheque_no',
        'amt_pay',
        'bankcharge',
        'narration',
        'paytype',
        'customer',
        'scheme',
        'loantype',
        'paydetail',
        'userID'
    ];

    public function partner()
    {
        return $this->belongsTo(Partners_Investor_Loan::class, 'customer');
    }
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme');
    }
    public function account()
    {
        return $this->belongsTo(Bank_Acc::class, 'account_no');
    }
}
