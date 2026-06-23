<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tds_payment extends Model
{
    use HasFactory;

        protected $table = 'tds_payment';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];

        public function Contractor()
        {
            return $this->belongsTo(Supplier_contractor::class, 'conID')->where('Type', 'CONTRACTOR');
        }
}
