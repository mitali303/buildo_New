<?php

namespace App\Models\Backend;
use Illuminate\Database\Eloquent\Model;

class OwnerPayment extends Model
{
    protected $table = 'owner_payment';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ID',
        'ClientID',
        'Date',
        'type',
        'Project_ID',
        'payment_method',
        'amt_pay',
        'cheque_no',
        'bankcharge',
        'total_pay',
        'narration',
        'account_no',
        'Type_Payment',
        'created',
        'last_edited'
    ];

    public function details()
    {
        return $this->hasMany(OwnerPaymentDetail::class, 'paymentID', 'ID');
    }

    public function scheme()
    {
        return $this->belongsTo(SchemeDetail::class, 'Project_ID', 'ID');
    }
}
