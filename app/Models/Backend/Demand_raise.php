<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demand_raise extends Model
{
    use HasFactory;

        protected $table = 'demand_rase';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];

         public function Scheme()
        {
            return $this->belongsTo(SchemeDetail::class, 'scheme');
        }

        public function flatno()
        {
            return $this->belongsTo(Flat_details::class, 'FlatNo', 'ID');
        }

}
