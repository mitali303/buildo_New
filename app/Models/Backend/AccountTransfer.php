<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTransfer extends Model
{
    use HasFactory;
    protected $table = 'account_transfer';
    protected $primaryKey = 'ID';
    protected $keyType = 'string';

    public $timestamps = false; 
    protected $fillable = [
        'ID',
        'ClientID',
        'account_from',
        'account_to',
        'balance',
        'Date',
        'amt_pay',
        'payment_method',
        'cheque_no',
        'Type_Payment',
        'reconciliation',
        'userID',
    ];

    public function accountFrom()
    {
        return $this->belongsTo(Bank_Acc::class, 'account_from', 'ID');
    }

    public function accountTo()
    {
        return $this->belongsTo(Bank_Acc::class, 'account_to', 'ID');
    }
}
