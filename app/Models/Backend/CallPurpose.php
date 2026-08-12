<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallPurpose extends Model
{
    use HasFactory;
    protected $table = 'call_purpose';

    protected $fillable = [
        'name',
        'type',
        'status',
        'createdby',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
