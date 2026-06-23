<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostDatedCheque extends Model
{
    use HasFactory;
    protected $table = 'post_dated_cheque';
    protected $primaryKey = 'ID';
    protected $keyType = 'string';

    public $timestamps = false;

      protected $fillable = [
        'ID',
        'ClientID',
        'schemeID',
        'Wing',
        'FlatID',
        'Booking_ID',
        'account_no',
        'narration',
        'Date',
        'amt_pay',
        'payment_method',
        'cheque_no',
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
    public function wings()
    {
        return $this->belongsTo(Flat_details::class, 'Wing', 'ID');
    }
    public function flats()
    {
        return $this->belongsTo(Flat_details::class, 'FlatID', 'ID');
    }
    public function customers()
    {
        return $this->belongsTo(Booking_Customer::class, 'Booking_ID', 'ID');
    }
}
