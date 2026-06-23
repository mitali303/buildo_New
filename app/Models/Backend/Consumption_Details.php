<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consumption_Details extends Model
{
    use HasFactory;

        protected $table = 'consumption_detail';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];
    
        public function Scheme()
        {
            return $this->belongsTo(SchemeDetail::class, 'scheme');
        }
        public function materials()
        {
            return $this->hasMany(Material_Consumption::class, 'Cid', 'ID');
        }

}