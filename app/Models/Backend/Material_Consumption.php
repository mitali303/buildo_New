<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material_Consumption extends Model
{
    use HasFactory;

        protected $table = 'material_consumption';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];

        public function materialDetail()
    {
        return $this->belongsTo(
            Material::class,
            'material', // FK in material_consumption
            'ID'        // PK in material table
        );
    }
 
}
