<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class LeadContact extends Model
{
    protected $table = "lead_contacts";

    protected $fillable = [
        'lead_id',
        'name',
        'email',
        'mobile',
        'designation',
        'department',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}
