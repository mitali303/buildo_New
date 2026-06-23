<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partners_Investor_Loan extends Model
{
    use HasFactory;

        protected $table = 'partners';
    protected $primaryKey   = 'ID';     // varchar PK
    public    $timestamps   = false;
    protected $guarded      = [];

}
