<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads';

    protected $primaryKey = 'ID';

    public $incrementing = true;

    protected $fillable = [
        'name'
    ];

    public $timestamps = true;
}