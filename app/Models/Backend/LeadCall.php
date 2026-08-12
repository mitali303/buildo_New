<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class LeadCall extends Model
{
    protected $table = 'lead_calls';

    protected $fillable = [
        'lead_id',
        'call_date',
        'call_status_id',
        'call_purpose_id',
        'call_outcome_id',
        'remarks',
        'next_followup_date',
        'called_by',
        'is_completed',
    ];

    protected $casts = [
        'call_date' => 'datetime',
        'next_followup_date' => 'date',
        'is_completed' => 'boolean',
    ];

    // ---------- Relations ----------

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function callStatus()
    {
        return $this->belongsTo(CallStatus::class, 'call_status_id');
    }

    public function callPurpose()
    {
        return $this->belongsTo(CallPurpose::class, 'call_purpose_id');
    }

    public function callOutcome()
    {
        return $this->belongsTo(CallOutcome::class, 'call_outcome_id');
    }

    public function calledByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'called_by');
    }

    // ---------- Mark-as-complete provision ----------

    /**
     * Toggle this call's completed state and persist it.
     * Returns the new state.
     */
    public function toggleComplete(): bool
    {
        $this->is_completed = !$this->is_completed;
        $this->save();

        return $this->is_completed;
    }

    /**
     * Explicitly mark this call as completed (used when a newer call
     * on the same lead is logged — see LeadCallController::store()).
     */
    public function markComplete(): void
    {
        if (!$this->is_completed) {
            $this->is_completed = true;
            $this->save();
        }
    }

    /**
     * Explicitly mark this call as pending/incomplete.
     */
    public function markPending(): void
    {
        if ($this->is_completed) {
            $this->is_completed = false;
            $this->save();
        }
    }

    // ---------- Query scopes ----------

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }
}