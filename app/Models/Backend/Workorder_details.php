<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workorder_details extends Model
{
    use HasFactory;

        protected $table = 'workorder_detail';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];
        // PurchaseOrder.php
        public function Contractor()
        {
            return $this->belongsTo(Supplier_contractor::class, 'ContractorID')->where('Type', 'CONTRACTOR');
        }

        public function Scheme()
        {
            return $this->belongsTo(SchemeDetail::class, 'SiteLocation');
        }

}
