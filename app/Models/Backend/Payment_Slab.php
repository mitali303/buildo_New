<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment_Slab extends Model
{
    use HasFactory;

        protected $table = 'slabs';
    protected $primaryKey   = 'ID';     // varchar PK
    public    $incrementing = false;
    protected $keyType      = 'string';
    public    $timestamps   = false;
    protected $guarded      = [];

}
