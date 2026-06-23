<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

        protected $table = 'staff';
    protected $primaryKey   = 'ID';     // varchar PK
    public    $incrementing = false;
    public    $timestamps   = false;
    protected $guarded      = [];

}
