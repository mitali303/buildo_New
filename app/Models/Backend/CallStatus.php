<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallStatus extends Model
{
    use HasFactory;
    protected $table = 'call_status';

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
