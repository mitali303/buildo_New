<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $table = "quotations";

    protected $fillable = [
        'firm_id',
        'customer_id',
        'estimate_id',
        'quotation_no',
        'type',
        'date',
        'works_total',
        'material_total',
        'total_amount',
        'status',
        'createdby'
    ];

    const TYPE_SUPPLY = 1;
    const TYPE_INSTALLATION = 2;

    public function firm()
    {
        return $this->belongsTo(FirmMaster::class, 'firm_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }

    public function materials()
    {
        return $this->hasMany(QuotationMaterial::class, 'quotation_id');
    }

    public function proformaInvoice()
    {
        return $this->hasOne(ProformaInvoice::class, 'quotation_id');
    }

    public function saleOrder()
    {
        return $this->hasOne(SaleOrder::class, 'quotation_id');
    }
}
