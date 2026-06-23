<?php

namespace App\Models\Backend;
use Illuminate\Database\Eloquent\Model;

class OwnerPaymentDetail extends Model
{
    protected $table = 'owner_payment_details';
    public $timestamps = false;
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ID',
        'ClientID',
        'Date',
        'owner_id',
        'payment_amt',
        'title',
        'amount',
        'paymentID',
        'Created',      
        'LastEdited'    
    ];

    public function payment()
    {
        return $this->belongsTo(OwnerPayment::class, 'paymentID', 'ID');
    }
}
