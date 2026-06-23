<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer_detail extends Model
{
    use HasFactory;

        protected $table = 'transfer_detail';
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

        public function fromsite()
        {
            return $this->belongsTo(SchemeDetail::class, 'from_site');
        }

        public function Tosite()
        {
            return $this->belongsTo(SchemeDetail::class, 'To_site');
        }

        public function materials()
        {
            return $this->hasMany(Transfer_material::class, 'PID','ID');
        }

}
