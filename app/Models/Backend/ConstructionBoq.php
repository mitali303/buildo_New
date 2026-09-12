<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class ConstructionBoq extends Model
{
    protected $table = 'construction_boq';

    protected $fillable = [
        'boq_no',
        'scheme_id',
        'scheme_name',
        'estimate_date',
        'customer_name',
        'site_address',
        'built_up_area',

        'material_total',
        'labour_total',
        'subtotal',

        'overhead_percent',
        'overhead_amount',

        'contingency_percent',
        'contingency_amount',

        'taxable_amount',

        'gst_percent',
        'gst_amount',
        'grand_total',

        'notes',
        'status',
        'createdby',
    ];

    protected $casts = [
        'estimate_date' => 'date',
        'built_up_area' => 'decimal:2',
        'material_total' => 'decimal:2',
        'labour_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'overhead_percent' => 'decimal:2',
        'overhead_amount' => 'decimal:2',
        'contingency_percent' => 'decimal:2',
        'contingency_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(
            ConstructionBoqItem::class,
            'boq_id'
        );
    }
    public function agency()
    {
        return $this->belongsTo(
            Agency::class,
            'agency_id',
            'ID'
        );
    }
}