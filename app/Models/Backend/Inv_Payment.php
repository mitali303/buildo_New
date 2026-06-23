<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inv_Payment extends Model
{
    use HasFactory;

        protected $table = 'inv_payment';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];
        // PurchaseOrder.php
        public function vendor()
        {
            return $this->belongsTo(Supplier_contractor::class, 'PurchaseFrom')->where('Type', 'VENDOR');
        }

        public function scheme()
        {
            return $this->belongsTo(SchemeDetail::class, 'schemeID');
        }

}
