<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reject_Mat extends Model
{
    use HasFactory;

        protected $table = 'rejected_material';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];
        // PurchaseOrder.php
        public function vendor()
{
    return $this->belongsTo(
        Supplier_contractor::class,
        'purchasefrom',
        'ID'
    );
}

        public function scheme()
        {
            return $this->belongsTo(SchemeDetail::class, 'destination');
        }

}
