<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    use HasFactory;
    protected $table = 'lead_source';

    protected $fillable = [
        'name',
        'status',
        'createdby',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
