<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class ConstructionBoqItem extends Model
{
    protected $table = 'construction_boq_items';

    protected $fillable = [
        'boq_id',
        'category_name',
        'item_description',
        'material_id',
        'material_name',
        'agency_id',
        'agency_name',
        'unit',
        'quantity',
        'material_rate',
        'labour_rate',
        'material_amount',
        'labour_amount',
        'total_amount',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'material_rate' => 'decimal:2',
        'labour_rate' => 'decimal:2',
        'material_amount' => 'decimal:2',
        'labour_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function boq()
    {
        return $this->belongsTo( ConstructionBoq::class, 'boq_id' );
    }
    public function agency()
    {
        return $this->belongsTo( Agency::class, 'agency_id', 'ID'
        );
    }
}