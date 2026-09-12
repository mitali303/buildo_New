<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
	protected $table = 'estimate1';

	protected $fillable = [
		'estimate_no', 'scheme_id', 'scheme_name', 'built_up_area', 'rate_per_sqft', 'customer_name', 'site_address', 'date', 'notes', 'items',
		'material_total', 'tax_percent',
		'construction_total', 'tax_amount', 'grand_total', 'status', 'createdby',
	];

	protected $casts = [
		'date' => 'date:Y-m-d',
		'items' => 'array',
		'built_up_area' => 'decimal:2',
		'rate_per_sqft' => 'decimal:2',
		'material_total' => 'decimal:2',
		'construction_total' => 'decimal:2',
		'tax_percent' => 'decimal:2',
		'tax_amount' => 'decimal:2',
		'grand_total' => 'decimal:2',
	];

	public const STATUS_CANCELLED = 0;
	public const STATUS_DRAFT = 1;
	public const STATUS_SENT = 2;
	public const STATUS_APPROVED = 3;

	public static function statusLabels(): array
	{
		return [0 => 'Cancelled', 1 => 'Draft', 2 => 'Sent', 3 => 'Approved'];
	}

	public function getStatusLabelAttribute(): string
	{
		return self::statusLabels()[$this->status] ?? 'Unknown';
	}

	public function getTotalAmountAttribute(): float
	{
		return (float) $this->grand_total;
	}

	public function getBuiltUpAreaAttribute($value): float
	{
		return (float) ($value ?: ($this->attributes['total_area'] ?? 0));
	}

	public function getRatePerSqftAttribute($value): float
	{
		return (float) ($value ?: ($this->attributes['rate'] ?? 0));
	}
}
