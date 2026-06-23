<?php

namespace App\Models\backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

     protected $table = 'Shift';

    protected $primaryKey = 'id';
    public $incrementing = true; // id is auto-increment
    protected $keyType = 'int';

    public $timestamps = true; // table has created_at and updated_at

    protected $fillable = [
        'shift', 'shift_intime', 'shift_outtime'
    ];
}
