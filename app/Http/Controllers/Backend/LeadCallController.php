<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Lead;
use App\Models\Backend\LeadCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeadCallController extends Controller
{
    /**
     * Log a call against a Lead. Called from the "Log a Call" form on the Lead view page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'call_date' => ['required', 'date'],
            'call_status_id' => ['nullable', 'exists:call_status,id'],
            'call_purpose_id' => ['nullable', 'exists:call_purpose,id'],
            'call_outcome_id' => ['nullable', 'exists:call_outcome,id'],
            'remarks' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
            'move_to_stage' => ['nullable', Rule::in(array_keys(Lead::stageLabels()))],
            'is_completed' => ['nullable', 'boolean'],
        ], [
            'call_date.required' => 'Call date & time is required.',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);

        if (!$lead->isAccessibleByCurrentUser()) {
            abort(403, 'You are not authorized to log calls for this lead.');
        }

        $call = LeadCall::create([
            'lead_id' => $lead->id,
            'call_date' => $validated['call_date'],
            'call_status_id' => $validated['call_status_id'] ?? null,
            'call_purpose_id' => $validated['call_purpose_id'] ?? null,
            'call_outcome_id' => $validated['call_outcome_id'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'next_followup_date' => $validated['next_followup_date'] ?? null,
            'called_by' => Auth::id(),
            'is_completed' => $request->boolean('is_completed'),
        ]);

        // A fresh call means whatever call(s) came before it are done —
        // auto-complete every other pending call on this lead.
        LeadCall::where('lead_id', $lead->id)
            ->where('id', '!=', $call->id)
            ->pending()
            ->get()
            ->each->markComplete();

        // Optionally move the Lead's stage forward straight from the call log
        // (e.g. after a good call, mark the Lead as "Contacted" or "Interested").
        if (!empty($validated['move_to_stage'])) {
            $lead->lead_stage = $validated['move_to_stage'];
        }

        if (!empty($validated['next_followup_date'])) {
            $lead->next_followup_date = $validated['next_followup_date'];
        }

        $lead->save();

        return redirect()
            ->route('CreateLead.show', $lead->id)
            ->with('success', 'Call logged successfully!');
    }

    /**
     * Update an existing call log entry — used by the "Edit" action in the
     * Recent Conversations table on the Lead view page.
     */
    public function update(Request $request, string $id)
    {
        $call = LeadCall::findOrFail($id);

        if (!$call->lead->isAccessibleByCurrentUser()) {
            abort(403, 'You are not authorized to edit calls for this lead.');
        }

        $validated = $request->validate([
            'call_date' => ['required', 'date'],
            'call_status_id' => ['nullable', 'exists:call_status,id'],
            'call_purpose_id' => ['nullable', 'exists:call_purpose,id'],
            'call_outcome_id' => ['nullable', 'exists:call_outcome,id'],
            'remarks' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
        ], [
            'call_date.required' => 'Call date & time is required.',
        ]);

        $call->call_date = $validated['call_date'];
        $call->call_status_id = $validated['call_status_id'] ?? null;
        $call->call_purpose_id = $validated['call_purpose_id'] ?? null;
        $call->call_outcome_id = $validated['call_outcome_id'] ?? null;
        $call->remarks = $validated['remarks'] ?? null;
        $call->next_followup_date = $request->input('next_followup_date') ?: null;
        $call->save();

        return redirect()
            ->route('CreateLead.show', $call->lead_id)
            ->with('success', 'Call log entry updated.');
    }

    /**
     * Toggle a call log entry's completed state — the "Mark Complete" button
     * in the Recent Conversations table on the Lead view page.
     */
    public function toggleComplete(Request $request, string $id)
    {
        $call = LeadCall::findOrFail($id);

        if (!$call->lead->isAccessibleByCurrentUser()) {
            abort(403, 'You are not authorized to update calls for this lead.');
        }

        $isCompleted = $call->toggleComplete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $isCompleted,
            ]);
        }

        return back()->with('success', 'Call marked as ' . ($isCompleted ? 'Completed' : 'Pending') . '.');
    }

    public function destroy(string $id)
    {
        $call = LeadCall::findOrFail($id);

        if (!$call->lead->isAccessibleByCurrentUser()) {
            abort(403, 'You are not authorized to delete calls for this lead.');
        }

        $leadId = $call->lead_id;
        $call->delete();

        return redirect()
            ->route('CreateLead.show', $leadId)
            ->with('success', 'Call log entry deleted.');
    }
}