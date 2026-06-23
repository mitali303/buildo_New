{{-- resources/views/backend/staff/form.blade.php --}}
@extends('backend.partials.master')

@section('title', !empty($staff) ? 'Edit Staff' : 'Create Staff')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">{{ !empty($staff) ? 'Edit Staff' : 'Create Staff' }}</h1>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ !empty($staff) ? route('Staff.update') : route('Staff.store') }}"
                              method="POST">
                            @csrf
                            @if(!empty($staff))
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $staff->ID }}">
                            @endif

                            <div class="row gy-3">

                                {{-- Name --}}
                                <div class="col-md-4">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                           value="{{ old('name', $staff->Name ?? '') }}"
                                           class="form-control @error('name') is-invalid @enderror">
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Contact No --}}
                                <div class="col-md-4">
                                    <label class="form-label">Contact No <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_no"
                                           value="{{ old('contact_no', $staff->ContactNo ?? '') }}"
                                           class="form-control @error('contact_no') is-invalid @enderror" >
                                    @error('contact_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Email --}}
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email"
                                           value="{{ old('email', $staff->Email ?? '') }}"
                                           class="form-control @error('email') is-invalid @enderror">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Department --}}
                                    <div class="col-md-4" id="dept_div">   {{-- ← wrapper so we can swap HTML --}}
                                        <label class="form-label">Department <span class="text-danger">*</span></label>

                                        <select name="department" id="department_select"     
                                                class="form-control choices-single"
                                                data-placeholder="Select Department"
                                                onchange="GetInputFieldDept()"                  
                                                >
                                            <option value="">Select</option>

                                <option value="OTHER"
                                {{ old('department') && !in_array(old('department'), $departments->toArray()) ? 'selected' : '' }}>
                                    ➕ ADD NEW
                                </option>

                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}"
                                        {{ old('department', $staff->Department ?? '') == $dept ? 'selected' : '' }}>
                                        {{ $dept }}
                                    </option>
                                @endforeach
                                        </select>
                                    @error('department') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                               
                                {{-- Designation – new text input --}}
                                    <div class="col-md-4" id="desig_div">
                                        <label class="form-label">Designation <span class="text-danger">*</span></label>

                                        <select name="designation" id="designation_select"
                                                class="form-control choices-single-designation"
                                                data-placeholder="Select Designation"
                                                onchange="GetInputFieldDesig()"
                                                >
                                            <option value="">Select</option>

                                    <option value="OTHER"
                                    {{ old('designation') && !in_array(old('designation'), $designations->toArray()) ? 'selected' : '' }}>
                                        ➕ ADD NEW
                                    </option>

                                    @foreach($designations as $desig)
                                        <option value="{{ $desig }}"
                                            {{ old('designation', $staff->Designation ?? '') == $desig ? 'selected' : '' }}>
                                            {{ $desig }}
                                        </option>
                                    @endforeach
                                        </select>
                                        @error('designation') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                {{-- Salary Type --}}
                                <!-- <div class="col-md-4">
                                    <label class="form-label">Salary Type <span class="text-danger">*</span></label>
                                    <select name="salary_type" class="form-control @error('salary_type') is-invalid @enderror" >
                                        <option value="">Select</option>
                                        <option value="Monthly" {{ old('salary_type', $staff->SalaryType ?? '') == 'Monthly' ? 'selected' : '' }}>Monthly</option> -->
                                        <!-- <option value="Weekly"  {{ old('salary_type', $staff->SalaryType ?? '') == 'Weekly'  ? 'selected' : '' }}>Weekly</option>
                                        <option value="Daily"  {{ old('salary_type', $staff->SalaryType ?? '') == 'Daily'  ? 'selected' : '' }}>Daily</option> -->
                                    <!-- </select>
                                    @error('salary_type') <small class="text-danger">{{ $message }}</small> @enderror
                                </div> -->
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Salary Type <span class="text-danger">*</span>
                                    </label>

                                    <select name="salary_type"
                                            class="form-control @error('salary_type') is-invalid @enderror">
                                        <option value="Monthly" selected>Monthly</option>
                                    </select>

                                    @error('salary_type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                
                                {{-- Salary --}}
                                <div class="col-md-4">
                                    <label class="form-label">Salary <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="salary"
                                           value="{{ old('salary', $staff->DailyWage ?? '') }}"
                                           class="form-control @error('salary') is-invalid @enderror" >
                                    @error('salary') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Username --}}
                                <div class="col-md-4">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username"
                                           value="{{ old('username', $staff->username ?? '') }}"
                                           class="form-control @error('username') is-invalid @enderror" >
                                    @error('username') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Password (create only or to change) --}}
                                <div class="col-md-4">
                                    <label class="form-label">
                                        Password
                                        @if(!empty($staff))
                                            <small class="text-danger">(leave blank to keep current)</small>
                                        @else
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           {{ empty($staff) ? 'required' : '' }}>
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Scheme --}}
                                <div class="col-md-4">
                                    <label class="form-label">Scheme <span class="text-danger">*</span></label>
                                    <select class="form-control choices-single-scheme choices-single_status @error('scheme') is-invalid @enderror"
                                            name="scheme" >
                                        <option value="">Select Scheme</option>
                                        @foreach($schemes as $scheme)
                                            <option value="{{ $scheme->ID }}"
                                                {{ old('scheme', $staff->scheme ?? '') == $scheme->ID ? 'selected' : '' }}>
                                                {{ $scheme->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('scheme') <small class="text-danger">{{ $message }}</small> @enderror
          {{-- status  --}}                    </div> 
<div class="col-md-4">
    <label class="form-label">Status <span class="text-danger">*</span></label>

    <select name="status"
            id="status_select"
            class="form-control choices-single-status"
            data-placeholder="Select Status">

        <option value="">Select</option>

        <option value="ACTIVE"
            {{ old('status', $staff->Status ?? '') == 'ACTIVE' ? 'selected' : '' }}>
            Active
        </option>

        <option value="INACTIVE"
            {{ old('status', $staff->Status ?? '') == 'INACTIVE' ? 'selected' : '' }}>
            Inactive
        </option>

    </select>

    @error('status')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
                                  {{-- Address --}}
                                <div class="col-md-4">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" rows="2"
                                              class="form-control @error('address') is-invalid @enderror">{{ old('address', $staff->Address ?? '') }}</textarea>
                                    @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                            </div>

                            <br>
                            <button type="submit" class="btn btn-primary">{{ !empty($staff) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('Staff') }}" class="btn btn-secondary">Cancel</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
function GetInputFieldDept() { 
    var value = $('#department_select').val();
    var oldVal = "{{ old('department') }}"; // 🔥 add this

    if (value === 'OTHER') {
        $('#dept_div').html(
            "<label class='form-label'>Department <span class='text-danger'>*</span></label>" +
            "<input type='text' name='department' id='department_input' " +
            "class='form-control required' value='"+oldVal+"' " +  "placeholder='Enter new department' required />"
        );
        $('#department_input').focus();
    }
}

function GetInputFieldDesig() {           // your existing logic
    var value = $('#designation_select').val();
     var oldVal = "{{ old('designation') }}"; // ✅ ADD THIS

    if (value === 'OTHER') {
        $('#desig_div').html(
            "<label class='form-label'>Designation <span class='text-danger'>*</span></label>" +
            "<input type='text' name='designation' id='designation_input' " +
            "class='form-control required' value='"+oldVal+"' " + "placeholder='Enter new designation' required />"
        );
        $('#designation_input').focus();
    }
}
</script>

@endpush
<!-- searchable dropdown script start-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-designation"));
        new Choices(document.querySelector(".choices-single-scheme"));
    });
</script>
<!-- searchable dropdown script end-->

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Department
    if (document.getElementById('department_select').value === 'OTHER') {
        GetInputFieldDept();
    }

    // Designation
    if (document.getElementById('designation_select').value === 'OTHER') {
        GetInputFieldDesig();
    }

});
</script>

