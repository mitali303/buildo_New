<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LeadMeeting extends Model
{
    protected $table = 'lead_meetings';

    protected $fillable = [
        'lead_id',
        'title',
        'meeting_date',
        'mode',
        'location',
        'agenda',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
