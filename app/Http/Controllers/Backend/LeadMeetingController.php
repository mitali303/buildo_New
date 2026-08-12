<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Lead;
use App\Models\Backend\LeadMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadMeetingController extends Controller
{
    /**
     * Schedule a meeting against a Lead. Called from the "Add Meeting" modal
     * on the Lead view page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'title' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'mode' => ['required', 'in:In-Person,Online,Phone'],
            'location' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
            'status' => ['nullable', 'in:Scheduled,Completed,Cancelled,Rescheduled'],
        ], [
            'title.required' => 'Meeting title is required.',
            'meeting_date.required' => 'Meeting date & time is required.',
        ]);

        LeadMeeting::create([
            'lead_id' => $validated['lead_id'],
            'title' => $validated['title'],
            'meeting_date' => $validated['meeting_date'],
            'mode' => $validated['mode'],
            'location' => $validated['location'] ?? null,
            'agenda' => $validated['agenda'] ?? null,
            'status' => $validated['status'] ?? 'Scheduled',
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('CreateLead.show', $validated['lead_id'])
            ->with('success', 'Meeting scheduled successfully!');
    }

    /**
     * Update an existing meeting — used by the "Edit" action on the Lead view page.
     */
    public function update(Request $request, string $id)
    {
        $meeting = LeadMeeting::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'mode' => ['required', 'in:In-Person,Online,Phone'],
            'location' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
            'status' => ['nullable', 'in:Scheduled,Completed,Cancelled,Rescheduled'],
            'notes' => ['nullable', 'string'],
        ], [
            'title.required' => 'Meeting title is required.',
            'meeting_date.required' => 'Meeting date & time is required.',
        ]);

        $meeting->title = $validated['title'];
        $meeting->meeting_date = $validated['meeting_date'];
        $meeting->mode = $validated['mode'];
        $meeting->location = $validated['location'] ?? null;
        $meeting->agenda = $validated['agenda'] ?? null;
        $meeting->status = $validated['status'] ?? $meeting->status;
        $meeting->notes = $validated['notes'] ?? null;
        $meeting->save();

        return redirect()
            ->route('CreateLead.show', $meeting->lead_id)
            ->with('success', 'Meeting updated successfully!');
    }

    /**
     * Delete a meeting entry.
     */
    public function destroy(string $id)
    {
        $meeting = LeadMeeting::findOrFail($id);
        $leadId = $meeting->lead_id;
        $meeting->delete();

        return redirect()
            ->route('CreateLead.show', $leadId)
            ->with('success', 'Meeting deleted.');
    }
}
