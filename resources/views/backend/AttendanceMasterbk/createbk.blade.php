@extends('backend.partials.master')
@section('title')
   Attendance Master
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Attendance Master</h1>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="@if(!empty($old)){{ route('AttendanceMaster.update') }}@else{{ route('AttendanceMaster.store') }}@endif" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!empty($old))
                                @method('PUT')
                                <input type="hidden" name="id" id="id" value="{{ $old->id }}"/>
                            @endif
                            <div class="row">


                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="date">Date<small class="text-danger">*</small></label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror"
                                        value="{{ old('date', $old->date ?? \Carbon\Carbon::today()->format('Y-m-d')) }}" name="date" id="date"
                                        placeholder="Date" >
                                    @error('date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Month <small class="text-danger">*</small></label>
                                    <select name="month" class="form-control" id="month" required>
                                        <option value="">-- Select Month --</option>
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" 
                                                {{ old('month', $old->month ?? '') == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Year <small class="text-danger">*</small></label>
                                    <input type="number" name="year" id="year" class="form-control"
                                        value="{{ old('year', $old->year ?? date('Y')) }}" required>
                                </div>

                                @php
                                    $selecteduser = old('emp_id', $old->emp_id ?? '');
                                @endphp
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Employee<small class="text-danger">*</small></label>
                                    <select name="emp_id" class="form-control" required>
                                        
                                        <option value="" disabled {{ empty($selecteduser) ? 'selected' : '' }}>
                                            -- Select Employee --
                                        </option>

                                        @foreach($staffs as $user)
                                            <option value="{{ $user->ID }}"
                                                {{ (string)$user->ID === (string)$selecteduser ? 'selected' : '' }}>
                                                {{ $user->Name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>


                                <!-- Task Description -->

                                <div class="mb-3 col-md-4">
                            <label class="form-label">Total Days</label>
                            <input type="number" id="total_days" class="form-control" readonly>
                        </div>

                            <div class="mb-3 col-md-4">
                                <label class="form-label">Present Days <small class="text-danger">*</small></label>
                                <input type="number" class="form-control @error('present') is-invalid @enderror"
                                    value="{{ old('present', $old->present ?? '') }}"
                                    name="present" placeholder="Present" required>
                                @error('present')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-4">
                                <label class="form-label">Absent Days <small class="text-danger">*</small></label>
                                <input type="number" class="form-control @error('absent') is-invalid @enderror"
                                    value="{{ old('absent', $old->absent ?? '') }}"
                                    name="absent" placeholder="Absent" required>
                                @error('absent')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-4">
                                <label class="form-label">Employee Leave  <small class="text-danger">*</small></label>
                                <input type="number" class="form-control @error('emp_leave') is-invalid @enderror"
                                    value="{{ old('emp_leave', $old->emp_leave ?? '') }}"
                                    name="emp_leave" placeholder="Employee Leave " required>
                                @error('emp_leave')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-4">
                                    <label class="form-label" for="intime">In Time<small class="text-danger">*</small></label>
                                    <input type="time" class="form-control @error('intime') is-invalid @enderror"
                                        value="{{ old('intime', $old->intime ?? '') }}" name="intime" id="intime"
                                        placeholder="In Time" >
                                    @error('intime')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="outtime">Out Time<small class="text-danger">*</small></label>
                                    <input type="time" class="form-control @error('outtime') is-invalid @enderror"
                                        value="{{ old('outtime', $old->outtime ?? '') }}" name="outtime" id="outtime"
                                        placeholder="Out Time" >
                                    @error('outtime')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                        <label class="form-label">Late mins<small class="text-danger">*</small></label>
                        <input type="number" class="form-control @error('late_mins') is-invalid @enderror"
                            value="{{ old('late_mins', $old->late_mins ?? '') }}"
                            name="late_mins" placeholder="Late mins " required>
                        @error('late_mins')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-4">
                        <label class="form-label">Early Dep <small class="text-danger">*</small></label>
                        <input type="number" class="form-control @error('early_dep') is-invalid @enderror"
                            value="{{ old('early_dep', $old->early_dep ?? '') }}"
                            name="early_dep" placeholder="Early Dep" required>
                        @error('early_dep')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-4">
                        <label class="form-label">Work Hr <small class="text-danger">*</small></label>
                        <input type="number" class="form-control @error('work_hr') is-invalid @enderror"
                            value="{{ old('work_hr', $old->work_hr ?? '') }}"
                            name="work_hr" placeholder="Work Hr" required>
                        @error('work_hr')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

    <div class="mb-3 col-md-4">
    <!-- <label class="form-label">Designation <small class="text-danger">*</small></label>
    <input type="text" name="designation"
        class="form-control @error('designation') is-invalid @enderror"
        value="{{ old('designation', $old->designation ?? '') }}"
        placeholder="Designation" required>

    @error('designation')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div> -->
<label class="form-label">
        Designation <small class="text-danger">*</small>
    </label>

    <select name="designation"
        id="designation"
        class="form-control select2-tags"
        required>

    <option value="">Select Designation</option>

    @foreach($designations as $designation)
        <option value="{{ $designation->designation }}"
            {{ old('designation', $old->designation ?? '') == $designation->designation ? 'selected' : '' }}>
            {{ $designation->designation }}
        </option>
    @endforeach

</select>

    @error('designation')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($old) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('AttendanceMaster') }}" class="btn btn-secondary">Cancel</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const monthInput = document.getElementById('month');
    const yearInput = document.getElementById('year');
    const totalDaysInput = document.getElementById('total_days');

    function calculateDays() {
        let month = monthInput.value;
        let year = yearInput.value;

        if (month && year) {
            let days = new Date(year, month, 0).getDate();
            totalDaysInput.value = days;
        } else {
            totalDaysInput.value = '';
        }
    }

    // ✅ Trigger on change
    monthInput.addEventListener('change', calculateDays);
    yearInput.addEventListener('change', calculateDays);

    // ✅ Run once (edit mode)
    calculateDays();
    $(document).ready(function () {
    $('#designation').select2({
        tags: true,
        placeholder: "Select or Add Designation",
        allowClear: true,
        width: '100%'
    });
    
});

});

</script>
<script>
    $('.select2-tags').select2({
    tags: true,
    width: '100%'
});
</script>