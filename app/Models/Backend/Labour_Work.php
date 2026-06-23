<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labour_Work extends Model
{
    use HasFactory;

        protected $table = 'labour_work';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];

      public function agency()
    {
        return $this->belongsTo(
            \App\Models\Backend\Agency::class,
            'Agency_ID',   // Foreign key in labour_work table
            'ID'           // Primary key in agency table
        );
    }
  
    public function scheme()
    {
        return $this->belongsTo(
            \App\Models\Backend\SchemeDetail::class,
            'schemeID',   // foreign key in labour_work
            'ID'          // primary key in scheme_details
        );
    }

}
