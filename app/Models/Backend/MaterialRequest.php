<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    use HasFactory;

    protected $table = 'material_request';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class, 'request_id', 'ID');
    }

    public function scheme()
    {
        return $this->belongsTo(SchemeDetail::class, 'ClientID', 'ID');
    }
}
