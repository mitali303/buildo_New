@extends('backend.partials.master')

@section('title')
    Create Lead
@endsection

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

<div class="mb-3">
    <h1 class="h3 d-inline align-middle">
        {{ !empty($old) ? 'Edit Lead' : 'Create Lead' }}
    </h1>
</div>

<div class="row">
<div class="col-md-12">

<div class="card">
<div class="card-body">

<form action="{{ !empty($old) ? route('CreateLead.update') : route('CreateLead.store') }}" method="POST" id="lead-form">
    @csrf
    @if(!empty($old))
        @method('PUT')
        <input type="hidden" name="id" value="{{ $old->id }}">
    @endif

<div class="row">

    <div class="mb-3 col-md-4">
        <label class="form-label">Company Name <small class="text-danger">*</small></label>
        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
               value="{{ old('company_name',$old->company_name ?? '') }}">
        @error('company_name')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Lead Name</label>
        <input type="text" name="lead_name" class="form-control" value="{{ old('lead_name',$old->lead_name ?? '') }}">
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Address</label>
        <textarea name="address" rows="1" class="form-control">{{ old('address',$old->address ?? '') }}</textarea>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Mobile No 1 <small class="text-danger">*</small></label>
        <input type="text" name="mobile_1" class="form-control @error('mobile_1') is-invalid @enderror"
               value="{{ old('mobile_1',$old->mobile_1 ?? '') }}">
        @error('mobile_1')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Mobile No 2</label>
        <input type="text" name="mobile_2" class="form-control @error('mobile_2') is-invalid @enderror" value="{{ old('mobile_2',$old->mobile_2 ?? '') }}">
        @error('mobile_2')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Select Country</label>
        <select name="country" class="form-control choices-single">
            <option value="India" selected>India</option>
        </select>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">State</label>
        <select name="state" class="form-control choices-single">
            @foreach($states as $state)
                <option value="{{ $state->name }}"
                    {{ old('state', $old->state ?? 'Maharashtra') == $state->name ? 'selected' : '' }}>
                    {{ $state->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">District <small class="text-danger">*</small></label>
        
        <input type="text" name="district" class="form-control @error('district') is-invalid @enderror" value="{{ old('district',$old->district ?? '') }}" oninput="formatLocation(this)">
    
         @error('district')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Taluka/City <small class="text-danger">*</small></label>
        
         <input type="text" name="taluka" class="form-control @error('taluka') is-invalid @enderror" value="{{ old('taluka',$old->taluka ?? '') }}" oninput="formatLocation(this)">
    
         @error('taluka')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Pincode</label>
        <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode',$old->pincode ?? '') }}">
        @error('pincode')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Category name</label>
        <input type="text" name="category_name" class="form-control" value="{{ old('category_name',$old->category_name ?? '') }}">
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Subcategory</label>
        <input type="text" name="subcategory" class="form-control" value="{{ old('subcategory',$old->subcategory ?? '') }}">
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Lead Type <small class="text-danger">*</small></label>
        <select name="lead_type" class="form-control choices-single @error('lead_type') is-invalid @enderror">
            <option value="">Select Lead Type</option>
            <option value="1" {{ old('lead_type',$old->lead_type ?? '')=='1' ? 'selected' : '' }}>Hot</option>
            <option value="2" {{ old('lead_type',$old->lead_type ?? '')=='2' ? 'selected' : '' }}>Warm</option>
            <option value="3" {{ old('lead_type',$old->lead_type ?? '')=='3' ? 'selected' : '' }}>Cold</option>
        </select>
        @error('lead_type')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Email Address 1</label>
        <input type="email" name="email_1" class="form-control @error('email_1') is-invalid @enderror" value="{{ old('email_1',$old->email_1 ?? '') }}">
        @error('email_1')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Email Address 2</label>
        <input type="email" name="email_2" class="form-control @error('email_2') is-invalid @enderror" value="{{ old('email_2',$old->email_2 ?? '') }}">
        @error('email_2')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Lead Stage <small class="text-danger">*</small></label>
        <select name="lead_stage" class="form-control">
    @foreach($stageLabels as $value => $label)
        <option value="{{ $value }}"
            {{ old('lead_stage',$old->lead_stage ?? 1)==$value ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Description</label>
        <textarea name="description" rows="1" class="form-control">{{ old('description',$old->description ?? '') }}</textarea>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Lead Source <small class="text-danger">*</small></label>
        <select name="lead_source_id" class="form-control choices-single @error('lead_source_id') is-invalid @enderror">
            <option value="">Select Lead Source</option>
            @foreach($leadSources as $source)
                <option value="{{ $source->id }}" {{ old('lead_source_id',$old->lead_source_id ?? '')==$source->id ? 'selected' : '' }}>
                    {{ $source->name }}
                </option>
            @endforeach
        </select>
        @error('lead_source_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Product Requirements</label>
        <input type="text" name="product_requirements" class="form-control" value="{{ old('product_requirements',$old->product_requirements ?? '') }}">
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Assign To <small class="text-danger">*</small></label>
        <select name="assign_to" class="form-control choices-single @error('assign_to') is-invalid @enderror">
            <option value="">Select Assign To</option>
            @foreach($users as $user)
                <option value="{{ $user->ID }}" {{ old('assign_to',$old->assign_to ?? '')==$user->ID ? 'selected' : '' }}>
                    {{ $user->Name }}
                </option>
            @endforeach
        </select>
        @error('assign_to')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">Next Followup Date <small class="text-danger">*</small></label>
        <input type="date" name="next_followup_date" class="form-control @error('next_followup_date') is-invalid @enderror"
               value="{{ old('next_followup_date',$old->next_followup_date ?? date('Y-m-d')) }}">
        @error('next_followup_date')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

</div>

<hr>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Contact Details</h5>
    <button type="button" class="btn btn-sm btn-primary" id="add-contact-btn"><i class="fas fa-plus"></i> Add Contact</button>
</div>

@if($errors->has('contacts.*.name') || $errors->has('contacts.*.email') || $errors->has('contacts.*.mobile'))
    <div class="alert alert-danger py-2 small mb-3">
        <ul class="mb-0 ps-3">
            @foreach($errors->get('contacts.*') as $fieldErrors)
                @foreach($fieldErrors as $message)
                    <li>{{ $message }}</li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif

<div class="table-responsive mb-4">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th style="min-width:160px">Name</th>
                <th style="min-width:180px">Email Address</th>
                <th style="min-width:140px">Mobile Number</th>
                <th style="min-width:160px">Designation</th>
                <th style="min-width:160px">Department</th>
                <th style="width:40px">Action</th>
            </tr>
        </thead>
        <tbody id="contacts-tbody"></tbody>
    </table>
</div>

<button type="submit" class="btn btn-primary">
    {{ !empty($old) ? 'Update' : 'Submit' }}
</button>
<a href="{{ route('CreateLead') }}" class="btn btn-secondary">Cancel</a>

</form>

</div>
</div>

</div>
</div>

</div>
</main>
@endsection

<template id="contact-row-template">
    <tr class="contact-row">
        <td><input type="text" class="form-control form-control-sm" data-field="name" placeholder="Name"></td>
        <td><input type="email" class="form-control form-control-sm" data-field="email" placeholder="Email Address"></td>
        <td><input type="text" class="form-control form-control-sm" data-field="mobile" placeholder="Mobile Number"></td>
        <td><input type="text" class="form-control form-control-sm" data-field="designation" placeholder="Designation"></td>
        <td><input type="text" class="form-control form-control-sm" data-field="department" placeholder="Department"></td>
        <td class="text-center">
            <a href="#" class="text-danger remove-contact-btn"><i class="fas fa-trash"></i></a>
        </td>
    </tr>
</template>

<script>
function formatLocation(input) {

    // Remove everything except letters and spaces
    let value = input.value.replace(/[^a-zA-Z\s]/g, '');

    // Remove multiple spaces
    value = value.replace(/\s+/g, ' ').trimStart();

    // Capitalize first letter of every word
    value = value.toLowerCase().replace(/\b\w/g, function(char) {
        return char.toUpperCase();
    });

    input.value = value;
}

document.addEventListener("DOMContentLoaded", function () {

    new Choices(document.querySelector('[name="country"]'));
    new Choices(document.querySelector('[name="state"]'));
    new Choices(document.querySelector('[name="lead_type"]'));
    
    new Choices(document.querySelector('[name="lead_stage"]'), {
        shouldSort: false
    });
    new Choices(document.querySelector('[name="lead_source_id"]'));
    new Choices(document.querySelector('[name="assign_to"]'));
    
    const tbody = document.getElementById('contacts-tbody');
    const template = document.getElementById('contact-row-template');
    const form = document.getElementById('lead-form');

    const existingContacts = @json(old('contacts', optional($old ?? null)->contacts ?? []));
    
    // ---------- Contacts repeater ----------
    function addContactRow(data = {}) {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.contact-row');
        row.querySelectorAll('[data-field]').forEach(function (el) {
            const field = el.dataset.field;
            if (data[field] !== undefined && data[field] !== null) el.value = data[field];
        });
        tbody.appendChild(row);
    }

    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-contact-btn');
        if (btn) {
            e.preventDefault();
            if (tbody.querySelectorAll('.contact-row').length > 1) {
                btn.closest('.contact-row').remove();
            }
        }
    });

    document.getElementById('add-contact-btn').addEventListener('click', function () {
        addContactRow();
    });

    if (existingContacts && existingContacts.length > 0) {
        existingContacts.forEach(function (c) { addContactRow(c); });
    } else {
        addContactRow();
        // addContactRow();
    }

   
    // Re-index contact rows into contacts[i][field] right before submit
    form.addEventListener('submit', function () {
        tbody.querySelectorAll('.contact-row').forEach(function (row, index) {
            row.querySelectorAll('[data-field]').forEach(function (el) {
                el.name = 'contacts[' + index + '][' + el.dataset.field + ']';
            });
        });
    });

});
</script>