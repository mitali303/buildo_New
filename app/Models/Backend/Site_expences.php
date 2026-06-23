<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site_expences extends Model
{
    use HasFactory;

        protected $table = 'site_expences';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];

         public function scheme()
        {
            return $this->belongsTo(SchemeDetail::class, 'schemeID');
        }
}
