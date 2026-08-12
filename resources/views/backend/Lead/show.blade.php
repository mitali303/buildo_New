@extends('backend.partials.master')

@section('title')
    {{ $lead->company_name }}
@endsection

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

@php
    $typeColor = ['danger', 'warning', 'info'][($lead->lead_type ?? 1) - 1] ?? 'secondary';
    $convertStageKey = collect($stageLabels)->search(fn ($l) => str_contains(strtolower($l), 'convert'));
@endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h4 mb-0">{{ $lead->company_name }}</h1>
        <div class="text-muted small">
            <span id="header-stage-badge" class="badge rounded-pill" style="background:{{ $stageColors[$lead->lead_stage] ?? '#6c757d' }}">
                {{ $stageLabels[$lead->lead_stage] ?? '-' }}
            </span>
            {{ $lead->taluka ?? '-' }}@if($lead->district), {{ $lead->district }}@endif
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        @if(hasPermission('edit_CreateLead'))
            <a href="{{ route('CreateLead.edit', $lead->id) }}" class="icon-btn" style="background:#7b6fe0" title="Edit Lead">
                <i class="align-middle" data-feather="edit-2"></i>
            </a>
        @endif
        <!-- @if($lead->email_1)
            <a href="mailto:{{ $lead->email_1 }}" class="icon-btn" style="background:#e83e8c" title="Email">
                <i class="align-middle" data-feather="mail"></i>
            </a>
        @endif -->
        <!-- @if($lead->mobile_1)
            <a href="https://wa.me/91{{ $lead->mobile_1 }}" target="_blank" class="icon-btn" style="background:#25d366" title="WhatsApp">
                <i class="align-middle" data-feather="message-circle"></i>
            </a>
        @endif -->
        @if($convertStageKey !== false && hasPermission('edit_CreateLead'))
            <button type="button" class="btn btn-sm text-white" style="background:#20c997" data-stage-quick="{{ $convertStageKey }}" title="Convert">Convert</button>
        @endif
        <!-- <a href="{{ route('Quotation.create') }}?lead_id={{ $lead->id }}" class="btn btn-sm btn-danger">Add Quotation</a> -->
        <a href="{{ route('CreateLead') }}" class="btn btn-sm btn-secondary">Show List</a>
    </div>
</div>

<div id="stage-toast" class="alert alert-success py-2 px-3 small mb-3 d-none"></div>

{{-- Stage pipeline — click a pill to move the lead straight to that stage --}}
<div class="card border-0 shadow-sm mb-3">
<div class="card-body py-3">
    <span class="detail-label d-block mb-2">Lead Stage <span class="text-muted normal-case">(click to update)</span></span>
    <div class="d-flex flex-wrap gap-2" id="stage-pipeline">
        @foreach($stageLabels as $value => $label)
            <button type="button" class="stage-pill {{ $lead->lead_stage == $value ? 'active' : '' }}" data-stage="{{ $value }}"
                    style="--pill-color: {{ $stageColors[$value] ?? '#6c757d' }};">
                {{ strtoupper($label) }}
            </button>
        @endforeach
    </div>
</div>
</div>

<div class="row g-3">
<div class="col-lg-8">

    {{-- Recent Conversations --}}
    <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h6 class="card-subtitle text-muted mb-0 d-flex align-items-center gap-2">
                <i class="align-middle" data-feather="phone-call" style="width:15px;height:15px;"></i> Recent Conversations
            </h6>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="call-search" class="form-control form-control-sm" style="width:180px" placeholder="Search...">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCallModal">
                    <i class="align-middle" data-feather="plus" style="width:14px;height:14px;"></i> Add Call
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" id="calls-table">
                <thead>
                    <tr>
                        <th>Sr.No</th>
                        <th>Date</th>
                        <th>Outcome</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Next Followup</th>
                        <th>Call By</th>
                        <th class="text-center">Completed</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lead->calls->sortByDesc('call_date')->values() as $i => $call)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($call->call_date)->format('d-m-Y h:i A') }}</td>
                            <td>{{ $call->callOutcome->name ?? '-' }}</td>
                            <td>{{ $call->remarks ?? '-' }}</td>
                            <td>{{ $call->callStatus->name ?? '-' }}</td>
                            <td>{{ $call->next_followup_date ?? '-' }}</td>
                            <td>{{ $call->calledByUser->name ?? '-' }}</td>
                            <td class="text-center">
                                <button type="button"
                                    class="btn btn-sm toggle-complete-btn {{ $call->is_completed ? 'btn-success' : 'btn-outline-secondary' }}"
                                    data-id="{{ $call->id }}"
                                    style="font-size:.68rem; padding:.15rem .5rem;">
                                    {{ $call->is_completed ? 'Completed' : 'Pending' }}
                                </button>
                            </td>
                            <td class="text-end">
                                <button type="button" class="icon-btn-sm bg-primary border-0 edit-call-btn"
                                        data-id="{{ $call->id }}"
                                        data-date="{{ \Carbon\Carbon::parse($call->call_date)->format('Y-m-d\TH:i') }}"
                                        data-status="{{ $call->call_status_id }}"
                                        data-purpose="{{ $call->call_purpose_id }}"
                                        data-outcome="{{ $call->call_outcome_id }}"
                                        data-remarks="{{ $call->remarks }}"
                                        data-followup="{{ $call->next_followup_date }}"
                                        title="Edit">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </button>
                                      <form action="{{ route('LeadCall.delete', $call->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this call log entry?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn-sm bg-secondary border-0" title="Delete"><i class="align-middle" data-feather="trash-2"></i></button>
                                    </form>
                                <!-- @if(hasPermission('edit_CreateLead'))
                                    <button type="button" class="icon-btn-sm bg-primary border-0 edit-call-btn"
                                        data-id="{{ $call->id }}"
                                        data-date="{{ \Carbon\Carbon::parse($call->call_date)->format('Y-m-d\TH:i') }}"
                                        data-status="{{ $call->call_status_id }}"
                                        data-purpose="{{ $call->call_purpose_id }}"
                                        data-outcome="{{ $call->call_outcome_id }}"
                                        data-remarks="{{ $call->remarks }}"
                                        data-followup="{{ $call->next_followup_date }}"
                                        title="Edit">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </button>
                                @endif -->
                                <!-- @if(hasPermission('delete_CreateLead'))
                                    <form action="{{ route('LeadCall.delete', $call->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this call log entry?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn-sm bg-secondary border-0" title="Delete"><i class="align-middle" data-feather="trash-2"></i></button>
                                    </form>
                                @endif -->
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No calls logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>

    {{-- Meetings --}}
    <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h6 class="card-subtitle text-muted mb-0 d-flex align-items-center gap-2">
                <i class="align-middle" data-feather="calendar" style="width:15px;height:15px;"></i> Meetings
            </h6>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMeetingModal">
                <i class="align-middle" data-feather="plus" style="width:14px;height:14px;"></i> Add Meeting
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date & Time</th>
                        <th>Mode</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meetings as $meeting)
                        @php
                            $meetingStatusColor = ['Scheduled'=>'primary','Completed'=>'success','Cancelled'=>'danger','Rescheduled'=>'warning'][$meeting->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $meeting->title }}</td>
                            <td class="text-nowrap">{{ optional($meeting->meeting_date)->format('d-m-Y h:i A') }}</td>
                            <td>{{ $meeting->mode }}</td>
                            <td>{{ $meeting->location ?? '-' }}</td>
                            <td><span class="badge bg-{{ $meetingStatusColor }}-subtle text-{{ $meetingStatusColor }}-emphasis">{{ $meeting->status }}</span></td>
                            <td class="text-end">
                                <button type="button" class="icon-btn-sm bg-primary border-0 edit-meeting-btn"
                                        data-id="{{ $meeting->id }}"
                                        data-title="{{ $meeting->title }}"
                                        data-date="{{ optional($meeting->meeting_date)->format('Y-m-d\TH:i') }}"
                                        data-mode="{{ $meeting->mode }}"
                                        data-location="{{ $meeting->location }}"
                                        data-agenda="{{ $meeting->agenda }}"
                                        data-status="{{ $meeting->status }}"
                                        data-notes="{{ $meeting->notes }}"
                                        title="Edit">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </button>
                                    <form action="{{ route('LeadMeeting.delete', $meeting->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this meeting?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn-sm bg-secondary border-0" title="Delete"><i class="align-middle" data-feather="trash-2"></i></button>
                                    </form>
                                <!-- @if(hasPermission('edit_CreateLead'))
                                    <button type="button" class="icon-btn-sm bg-primary border-0 edit-meeting-btn"
                                        data-id="{{ $meeting->id }}"
                                        data-title="{{ $meeting->title }}"
                                        data-date="{{ optional($meeting->meeting_date)->format('Y-m-d\TH:i') }}"
                                        data-mode="{{ $meeting->mode }}"
                                        data-location="{{ $meeting->location }}"
                                        data-agenda="{{ $meeting->agenda }}"
                                        data-status="{{ $meeting->status }}"
                                        data-notes="{{ $meeting->notes }}"
                                        title="Edit">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </button>
                                @endif -->
                                <!-- @if(hasPermission('delete_CreateLead'))
                                    <form action="{{ route('LeadMeeting.delete', $meeting->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this meeting?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn-sm bg-secondary border-0" title="Delete"><i class="align-middle" data-feather="trash-2"></i></button>
                                    </form>
                                @endif -->
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No meetings scheduled yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>

</div>

<div class="col-lg-4">

    {{-- Lead details (compact) --}}
    <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="card-subtitle text-muted mb-3">Lead Details</h6>
        <div class="detail-grid-1col">
            <div><span>Mobile</span><a href="tel:{{ $lead->mobile_1 }}">{{ $lead->mobile_1 }}</a></div>
            <div><span>Email</span>{{ $lead->email_1 ?? '-' }}</div>
            <div><span>Lead Type</span><span class="badge bg-{{ $typeColor }}-subtle text-{{ $typeColor }}-emphasis">{{ $typeLabels[$lead->lead_type] ?? '-' }}</span></div>
            <div><span>Category</span>{{ $lead->category_name ?? '-' }}</div>
            <div><span>Lead Source</span>{{ $lead->leadSource->name ?? '-' }}</div>
            <div><span>Assigned To</span>{{ $lead->assignedUser->name ?? '-' }}</div>
            <div><span>Next Followup</span>{{ $lead->next_followup_date ?? '-' }}</div>
        </div>
        @if($lead->description)
            <hr class="my-2">
            <span class="detail-label">Description</span>
            <p class="mb-0 small">{{ $lead->description }}</p>
        @endif
    </div>
    </div>

    {{-- Related Contact --}}
    <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="card-subtitle text-muted mb-3">Related Contact</h6>
        @forelse($lead->contacts as $contact)
            <div class="contact-row">
                <div class="contact-avatar">{{ strtoupper(substr($contact->name, 0, 1)) }}</div>
                <div class="flex-grow-1">
                    <div class="fw-semibold small">{{ $contact->name }}</div>
                    <div class="text-muted" style="font-size:.72rem">
                        {{ $contact->designation }}{{ $contact->department ? ' · '.$contact->department : '' }}
                    </div>
                </div>
                <div class="text-end" style="font-size:.78rem">
                    @if($contact->mobile)<div><a href="tel:{{ $contact->mobile }}">{{ $contact->mobile }}</a></div>@endif
                </div>
            </div>
        @empty
            <p class="text-muted small mb-0">No Contact Found</p>
        @endforelse
    </div>
    </div>

    {{-- Related Quotation --}}
    <div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="card-subtitle text-muted mb-3">Related Quotation</h6>
        @forelse($quotations as $quotation)
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <div class="fw-semibold small">{{ $quotation->quotation_no ?? ('#'.$quotation->id) }}</div>
                    <div class="text-muted" style="font-size:.72rem">{{ optional($quotation->created_at)->format('d-m-Y') }}</div>
                </div>
                <a href="{{ route('Quotation.show', $quotation->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
            </div>
        @empty
            <p class="text-muted small mb-0">No Quotations Found</p>
        @endforelse
    </div>
    </div>

</div>
</div>

</div>
</main>

{{-- ===================== ADD CALL MODAL ===================== --}}
<div class="modal fade" id="addCallModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
    <form action="{{ route('LeadCall.store') }}" method="POST">
        @csrf
        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
        <div class="modal-header">
            <h6 class="modal-title">Log a Call</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-2">
                <label class="form-label small mb-1">Call Date & Time <small class="text-danger">*</small></label>
                <input type="datetime-local" name="call_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label small mb-1">Status</label>
                    <select name="call_status_id" class="form-select form-select-sm select2">
                        <option value="">Select</option>
                        @foreach($callStatuses as $status)<option value="{{ $status->id }}">{{ $status->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label small mb-1">Purpose</label>
                    <select name="call_purpose_id" class="form-select form-select-sm select2">
                        <option value="">Select</option>
                        @foreach($callPurposes as $purpose)<option value="{{ $purpose->id }}">{{ $purpose->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label small mb-1">Outcome</label>
                    <select name="call_outcome_id" class="form-select form-select-sm select2">
                        <option value="">Select</option>
                        @foreach($callOutcomes as $outcome)<option value="{{ $outcome->id }}">{{ $outcome->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="form-check mt-2">
                <input type="checkbox" name="is_completed" value="1" id="add-call-completed" class="form-check-input">
                <label for="add-call-completed" class="form-check-label small">Mark this call as already completed</label>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Remarks</label>
                <textarea name="remarks" rows="2" class="form-control form-control-sm" placeholder="What was discussed..."></textarea>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small mb-1">Next Followup Date</label>
                    <input type="date" name="next_followup_date" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Move Lead to Stage</label>
                    <select name="move_to_stage" class="form-select form-select-sm">
                        <option value="">Keep current</option>
                        @foreach($stageLabels as $value => $label)
                            <option value="{{ $value }}" {{ $lead->lead_stage == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">Save Call</button>
        </div>
    </form>
</div>
</div>
</div>

{{-- ===================== EDIT CALL MODAL ===================== --}}
<div class="modal fade" id="editCallModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
    <form id="edit-call-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="modal-header">
            <h6 class="modal-title">Edit Call</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-2">
                <label class="form-label small mb-1">Call Date & Time <small class="text-danger">*</small></label>
                <input type="datetime-local" name="call_date" id="edit-call-date" class="form-control form-control-sm" required>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label small mb-1">Status</label>
                    <select name="call_status_id" id="edit-call-status" class="form-select form-select-sm select2">
                        <option value="">Select</option>
                        @foreach($callStatuses as $status)<option value="{{ $status->id }}">{{ $status->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label small mb-1">Purpose</label>
                    <select name="call_purpose_id" id="edit-call-purpose" class="form-select form-select-sm select2">
                        <option value="">Select</option>
                        @foreach($callPurposes as $purpose)<option value="{{ $purpose->id }}">{{ $purpose->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label small mb-1">Outcome</label>
                    <select name="call_outcome_id" id="edit-call-outcome" class="form-select form-select-sm select2">
                        <option value="">Select</option>
                        @foreach($callOutcomes as $outcome)<option value="{{ $outcome->id }}">{{ $outcome->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Remarks</label>
                <textarea name="remarks" id="edit-call-remarks" rows="2" class="form-control form-control-sm"></textarea>
            </div>
            <div class="mb-1">
                <label class="form-label small mb-1">Next Followup Date</label>
                <input type="date" name="next_followup_date" id="edit-call-followup" class="form-control form-control-sm">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">Update Call</button>
        </div>
    </form>
</div>
</div>
</div>

{{-- ===================== ADD MEETING MODAL ===================== --}}
<div class="modal fade" id="addMeetingModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
    <form action="{{ route('LeadMeeting.store') }}" method="POST">
        @csrf
        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
        <div class="modal-header">
            <h6 class="modal-title">Add Meeting</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-2">
                <label class="form-label small mb-1">Title <small class="text-danger">*</small></label>
                <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Site visit discussion" required>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-7">
                    <label class="form-label small mb-1">Date & Time <small class="text-danger">*</small></label>
                    <input type="datetime-local" name="meeting_date" class="form-control form-control-sm" value="{{ now()->addDay()->format('Y-m-d\TH:i') }}" required>
                </div>
                <div class="col-5">
                    <label class="form-label small mb-1">Mode</label>
                    <select name="mode" class="form-select form-select-sm">
                        <option value="In-Person">In-Person</option>
                        <option value="Online">Online</option>
                        <option value="Phone">Phone</option>
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Location / Link</label>
                <input type="text" name="location" class="form-control form-control-sm" placeholder="Address or meeting link">
            </div>
            <div class="mb-1">
                <label class="form-label small mb-1">Agenda</label>
                <textarea name="agenda" rows="2" class="form-control form-control-sm" placeholder="What's this meeting about..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">Save Meeting</button>
        </div>
    </form>
</div>
</div>
</div>

{{-- ===================== EDIT MEETING MODAL ===================== --}}
<div class="modal fade" id="editMeetingModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
    <form id="edit-meeting-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="modal-header">
            <h6 class="modal-title">Edit Meeting</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-2">
                <label class="form-label small mb-1">Title <small class="text-danger">*</small></label>
                <input type="text" name="title" id="edit-meeting-title" class="form-control form-control-sm" required>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-7">
                    <label class="form-label small mb-1">Date & Time <small class="text-danger">*</small></label>
                    <input type="datetime-local" name="meeting_date" id="edit-meeting-date" class="form-control form-control-sm" required>
                </div>
                <div class="col-5">
                    <label class="form-label small mb-1">Mode</label>
                    <select name="mode" id="edit-meeting-mode" class="form-select form-select-sm">
                        <option value="In-Person">In-Person</option>
                        <option value="Online">Online</option>
                        <option value="Phone">Phone</option>
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Location / Link</label>
                <input type="text" name="location" id="edit-meeting-location" class="form-control form-control-sm">
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Agenda</label>
                <textarea name="agenda" id="edit-meeting-agenda" rows="2" class="form-control form-control-sm"></textarea>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" id="edit-meeting-status" class="form-select form-select-sm">
                        <option value="Scheduled">Scheduled</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Rescheduled">Rescheduled</option>
                    </select>
                </div>
            </div>
            <div class="mb-1 mt-2">
                <label class="form-label small mb-1">Notes</label>
                <textarea name="notes" id="edit-meeting-notes" rows="2" class="form-control form-control-sm" placeholder="Outcome / notes after the meeting..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">Update Meeting</button>
        </div>
    </form>
</div>
</div>
</div>

<style>
.icon-btn {
    width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
    color: #fff !important;
}
.icon-btn svg { width: 15px; height: 15px; }
.icon-btn-sm {
    width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
    color: #fff !important; margin-left: .3rem;
}
.icon-btn-sm svg { width: 12px; height: 12px; }

.detail-label { display: block; font-size: .68rem; text-transform: uppercase; letter-spacing: .03em; color: #adb5bd; margin-bottom: .15rem; }
.normal-case { text-transform: none; letter-spacing: 0; font-size: .72rem; }

.detail-grid-1col { display: grid; grid-template-columns: 1fr; gap: .55rem; }
.detail-grid-1col > div { display: flex; justify-content: space-between; align-items: center; font-size: .82rem; border-bottom: 1px solid #f8f9fa; padding-bottom: .35rem; }
.detail-grid-1col > div span { color: #adb5bd; font-size: .72rem; text-transform: uppercase; letter-spacing: .02em; }

.stage-pill {
    border: 1px solid #dee2e6; background: #f8f9fa; color: #868e96;
    border-radius: 6px; padding: .35rem .75rem; font-size: .7rem; font-weight: 700; letter-spacing: .02em;
    cursor: pointer; transition: all .15s ease;
}
.stage-pill:hover { border-color: var(--pill-color, #198754); color: var(--pill-color, #198754); }
.stage-pill.active { background: var(--pill-color, #198754); color: #fff; border-color: var(--pill-color, #198754); }

.contact-row { display: flex; align-items: center; gap: .6rem; padding: .55rem 0; border-bottom: 1px solid #f1f3f5; }
.contact-row:last-child { border-bottom: none; padding-bottom: 0; }
.contact-avatar {
    width: 34px; height: 34px; border-radius: 50%; background: #e7f6f6; color: #0da5aa;
    display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0;
}

#calls-table th { font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: #868e96; border-top: none; }
#calls-table td { font-size: .8rem; }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    feather.replace();

    // Simple client-side search over the Recent Conversations table
    document.getElementById('call-search').addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#calls-table tbody tr').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });

    // Populate + open the Edit Call modal
    document.querySelectorAll('.edit-call-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit-call-form').action = `{{ url('/LeadCall/update') }}/${btn.dataset.id}`;
            document.getElementById('edit-call-date').value = btn.dataset.date || '';
            document.getElementById('edit-call-status').value = btn.dataset.status || '';
            document.getElementById('edit-call-purpose').value = btn.dataset.purpose || '';
            document.getElementById('edit-call-outcome').value = btn.dataset.outcome || '';
            document.getElementById('edit-call-remarks').value = btn.dataset.remarks || '';
            document.getElementById('edit-call-followup').value = btn.dataset.followup || '';
            new bootstrap.Modal(document.getElementById('editCallModal')).show();
        });
    });

    // Mark Complete / Pending toggle on the Recent Conversations table
    const toggleCompleteBaseUrl = "{{ url('/LeadCall/toggle-complete') }}";
    document.querySelectorAll('.toggle-complete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            fetch(`${toggleCompleteBaseUrl}/${btn.dataset.id}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(async res => {
                    if (!res.ok) {
                        const body = await res.text();
                        console.error('Toggle complete failed:', res.status, body);
                        throw new Error('HTTP ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.is_completed) {
                        btn.textContent = 'Completed';
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('btn-success');
                    } else {
                        btn.textContent = 'Pending';
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-secondary');
                    }
                })
                .catch(err => alert('Could not update call status (' + err.message + '). Check the browser console / Network tab.'));
        });
    });

    // Populate + open the Edit Meeting modal
    document.querySelectorAll('.edit-meeting-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit-meeting-form').action = `{{ url('/LeadMeeting/update') }}/${btn.dataset.id}`;
            document.getElementById('edit-meeting-title').value = btn.dataset.title || '';
            document.getElementById('edit-meeting-date').value = btn.dataset.date || '';
            document.getElementById('edit-meeting-mode').value = btn.dataset.mode || 'In-Person';
            document.getElementById('edit-meeting-location').value = btn.dataset.location || '';
            document.getElementById('edit-meeting-agenda').value = btn.dataset.agenda || '';
            document.getElementById('edit-meeting-status').value = btn.dataset.status || 'Scheduled';
            document.getElementById('edit-meeting-notes').value = btn.dataset.notes || '';
            new bootstrap.Modal(document.getElementById('editMeetingModal')).show();
        });
    });

    // Stage pills — update instantly via AJAX, no page reload
    const stageUpdateUrl = "{{ url('/CreateLead/stage') }}/{{ $lead->id }}";
    const toast = document.getElementById('stage-toast');

    function updateStage(stageValue) {
        fetch(stageUpdateUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ lead_stage: stageValue }),
        })
            .then(async res => {
                if (!res.ok) {
                    const body = await res.text();
                    console.error('Stage update failed:', res.status, body);
                    throw new Error('HTTP ' + res.status);
                }
                return res.json();
            })
            .then(() => {
                document.querySelectorAll('#stage-pipeline .stage-pill').forEach(p => p.classList.remove('active'));
                const activePill = document.querySelector(`#stage-pipeline .stage-pill[data-stage="${stageValue}"]`);
                activePill.classList.add('active');

                const badge = document.getElementById('header-stage-badge');
                badge.textContent = activePill.textContent.trim();
                badge.style.background = getComputedStyle(activePill).getPropertyValue('--pill-color');

                toast.textContent = 'Lead stage updated to "' + activePill.textContent.trim() + '".';
                toast.classList.remove('d-none');
                setTimeout(() => toast.classList.add('d-none'), 2500);
            })
            .catch(err => alert('Could not update lead stage (' + err.message + '). Check the browser console / Network tab for details.'));
    }

    document.querySelectorAll('#stage-pipeline .stage-pill').forEach(function (pill) {
        pill.addEventListener('click', function () { updateStage(pill.dataset.stage); });
    });

    document.querySelectorAll('[data-stage-quick]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (confirm('Mark this lead as Converted?')) updateStage(btn.dataset.stageQuick);
        });
    });
    $('.select2').select2({
    width: '100%',
    dropdownParent: $('#addCallModal')
    });
    $('#editCallModal').on('shown.bs.modal', function () {
    $('#edit-call-status').select2({
        dropdownParent: $('#editCallModal'),
        width: '100%'
    });

    $('#edit-call-purpose').select2({
        dropdownParent: $('#editCallModal'),
        width: '100%'
    });

    $('#edit-call-outcome').select2({
        dropdownParent: $('#editCallModal'),
        width: '100%'
    });
    });
});
</script>

@endsection