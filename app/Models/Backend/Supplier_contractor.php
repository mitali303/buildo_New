<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier_contractor extends Model
{
    use HasFactory;

        protected $table = 'vendor';
    protected $primaryKey = 'ID';       // real PK
    public    $incrementing = true;     // int auto‑increment
    public    $timestamps   = false;    // no created_at / updated_at
    protected $guarded      = [];       // allow mass‑assign if you ever need it

}
