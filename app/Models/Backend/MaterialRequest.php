<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $table = 'material_request';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'ID',
        'ClientID',
        'Date',
        'request_no',
        'description',
        'remark',
        'userID',
        'Created',
        'LastEdited'
    ];

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class,'request_id','ID');
    }
}