<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRefund extends Model
{
    use HasFactory;
    protected $table = 'customer_refund';
    protected $primaryKey = 'ID';
    protected $keyType = 'string';

     public $timestamps = false;

    protected $fillable = [
        'ID',
        'ClientID',
        'schemeID',
        'bookingcustomer',
        'account_no',
        'narration',
        'Date',
        'amt_pay',
        'payment_method',
        'cheque_no',
        'paydetail',
        'paytype',
        'bankcharge',
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
    public function customers()
    {
        return $this->belongsTo(Booking_Customer::class, 'bookingcustomer', 'ID');
    }
}
