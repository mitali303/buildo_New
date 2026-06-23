<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner_load extends Model
{
    use HasFactory;

        protected $table = 'partners_loan';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];

         // PurchaseOrder.php
        public function Partner()
        {
            return $this->belongsTo(Partners_Investor_Loan::class, 'partners');
        }
}
