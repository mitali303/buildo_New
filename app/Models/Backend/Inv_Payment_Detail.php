<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inv_Payment_Detail extends Model
{
    use HasFactory;

        protected $table = 'inv_payment_detail';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];
        // PurchaseOrder.php
        public function vendor()
        {
            return $this->belongsTo(Supplier_contractor::class, 'purchasefrom')->where('Type', 'VENDOR');
        }

        public function scheme()
        {
            return $this->belongsTo(SchemeDetail::class, 'destination');
        }

}
