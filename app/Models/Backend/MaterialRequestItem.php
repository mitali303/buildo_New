<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequestItem extends Model
{
    protected $table = 'material_request_items';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'ID',
        'request_id',
        'material_name',
        'type_id',
        'type_name',
        'quantity',
        'unit'
    ];
}