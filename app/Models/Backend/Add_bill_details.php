<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Add_bill_details extends Model
{
    use HasFactory;

        protected $table = 'add_bill_detail';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];

         public function Billid()
        {
            return $this->belongsTo(Add_bill::class, 'Bid');
        }

}
