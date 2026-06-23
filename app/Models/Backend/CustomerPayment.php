<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;
    protected $table = 'booking_payment';
    protected $primaryKey = 'ID';
    protected $keyType = 'string';

     public $timestamps = false;

    protected $fillable = [
        'ID',
        'ClientID',
        'Date',
        'receipt_no',
        'payment_method',
        'account_no',
        'cheque_no',
        'amt_pay',
        'narration',
        'schemeID',
        'FlatID',
        'Wing',
        'Booking_ID',
        'type',
        'bill_payment',
        'reconciliation',
        'userID',
        'payement_by',
    ];



    public function accountNo()
    {
        return $this->belongsTo(Bank_Acc::class, 'account_no', 'ID');
    }
    public function schemes()
    {
        return $this->belongsTo(SchemeDetail::class, 'schemeID', 'ID');
    }
    public function wings()
    {
        return $this->belongsTo(Flat_details::class, 'wing', 'ID');
    }
    public function booking_cust()
    {
        return $this->belongsTo(Booking_Customer::class, 'Booking_ID', 'ID');
    }



}
