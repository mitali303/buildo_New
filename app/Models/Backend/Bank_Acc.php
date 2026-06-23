<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank_Acc extends Model
{
    use HasFactory;

        protected $table = 'accounts';
        protected $primaryKey = 'ID';
        public    $incrementing = false;
        protected $keyType      = 'string';
        public $timestamps = false;
        protected $guarded = [];
    // 🔥 ADD THIS
    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'ClientID', 'ID');
    }
}