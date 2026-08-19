@extends('backend.partials.master')

@section('title')
Attendance Master
@endsection

@section('maincontent')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<main class="content">

    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">
                {{ !empty($old) ? 'Edit Attendance' : 'Create Attendance' }}
            </h1>
        </div>

        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-body">

                        <form action="@if(!empty($old)){{ route('AttendanceMaster.update') }}@else{{ route('AttendanceMaster.store') }}@endif"
                              method="POST">

                            @csrf

                            @if(!empty($old))
                                @method('PUT')

                                <input type="hidden"
                                       name="id"
                                       value="{{ $old->id }}">
                            @endif

                            <div class="row">

                                {{-- DATE --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Date
                                        <small class="text-danger">*</small>
                                    </label>

                                    <input type="date"
                                           name="date"
                                           class="form-control @error('date') is-invalid @enderror"
                                           value="{{ old('date', $old->date ?? '') }}"
                                           required>

                                    @error('date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                </div>

                                {{-- EMPLOYEE --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Employee
                                        <small class="text-danger">*</small>
                                    </label>

                                    <select name="emp_id"
                                            id="emp_id"
                                            class="form-control select2 @error('emp_id') is-invalid @enderror"
                                            required>

                                        <option value="">
                                            -- Select Employee --
                                        </option>

                                        @foreach($users as $user)

                                            <option value="{{ $user->emp_id }}"
                                                {{ old('emp_id', $old->emp_id ?? '') == $user->emp_id ? 'selected' : '' }}>
                                                {{ $user->Name }} 
                                            </option>

                                        @endforeach

                                    </select>

                                    @error('emp_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                </div>

                                {{-- SHIFT --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Shift
                                        <small class="text-danger">*</small>
                                    </label>

                                    <select name="shift"
                                            class="form-control @error('shift') is-invalid @enderror"
                                            required>

                                        <option value="">
                                            -- Select Shift --
                                        </option>

                                        @foreach($empshift as $shift)

                                            <option value="{{ $shift->id }}"
                                                {{ old('shift', $old->shift ?? '') == $shift->id ? 'selected' : '' }}>

                                                {{ $shift->shift }}

                                            </option>

                                        @endforeach

                                    </select>

                                    @error('shift')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                </div>

                                {{-- DESIGNATION --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Designation
                                        <small class="text-danger">*</small>
                                    </label>

                                    <input type="text"
                                           name="designation"
                                           id="designation"
                                           class="form-control @error('designation') is-invalid @enderror"
                                           value="{{ old('designation', $old->designation ?? '') }}"
                                           placeholder="Designation"
                                           readonly>

                                    @error('designation')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                </div>

                                {{-- PRESENT --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Present
                                    </label>

                                    <input type="number"
                                           step="0.5"
                                           name="present"
                                           class="form-control"
                                           value="{{ old('present', $old->present ?? 0) }}">

                                </div>

                                {{-- ABSENT --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Absent
                                    </label>

                                    <input type="number"
                                           step="0.5"
                                           name="absent"
                                           class="form-control"
                                           value="{{ old('absent', $old->absent ?? 0) }}">

                                </div>

                                {{-- LEAVE --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Leave
                                    </label>

                                    <input type="number"
                                           step="0.5"
                                           name="emp_leave"
                                           class="form-control"
                                           value="{{ old('emp_leave', $old->emp_leave ?? 0) }}">

                                </div>

                                {{-- IN TIME --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        In Time
                                        <small class="text-danger">*</small>
                                    </label>

                                    <input type="time"
                                           name="intime"
                                           class="form-control @error('intime') is-invalid @enderror"
                                           value="{{ old('intime', $old->intime ?? '') }}"
                                           required>

                                    @error('intime')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                </div>

                                {{-- OUT TIME --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Out Time
                                        <small class="text-danger">*</small>
                                    </label>

                                    <input type="time"
                                           name="outtime"
                                           class="form-control @error('outtime') is-invalid @enderror"
                                           value="{{ old('outtime', $old->outtime ?? '') }}"
                                           required>

                                    @error('outtime')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                </div>

                                {{-- WORK HR --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Work Hr
                                    </label>

                                    <input type="text"
                                           name="work_hr"
                                           class="form-control"
                                           value="{{ old('work_hr', $old->work_hr ?? '') }}"
                                           placeholder="08:30">

                                </div>

                                {{-- OT HR --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        OT Hr
                                    </label>

                                    <input type="text"
                                           name="ot_hr"
                                           class="form-control"
                                           value="{{ old('ot_hr', $old->ot_hr ?? '00:00') }}"
                                           placeholder="00:00">

                                </div>

                                {{-- LATE MINS --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Late Minutes
                                    </label>

                                    <input type="number"
                                           name="late_mins"
                                           class="form-control"
                                           value="{{ old('late_mins', $old->late_mins ?? 0) }}">

                                </div>

                                {{-- EARLY DEP --}}
                                <div class="mb-3 col-md-4">

                                    <label class="form-label">
                                        Early Departure
                                    </label>

                                    <input type="number"
                                           name="early_dep"
                                           class="form-control"
                                           value="{{ old('early_dep', $old->early_dep ?? 0) }}">

                                </div>

                            </div>

                            <button type="submit"
                                    class="btn btn-primary">

                                {{ !empty($old) ? 'Update' : 'Create' }}

                            </button>

                            <a href="{{ route('AttendanceMaster') }}"
                               class="btn btn-secondary">

                                Cancel

                            </a>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>

$(document).ready(function () {

    $('#emp_id').select2({
        placeholder: "-- Select Employee --",
        allowClear: true,
        width: '100%'
    });


    const users = @json($users);

    $('#emp_id').on('change', function () {

        let empId = $(this).val();

        let selectedUser = users.find(x => x.emp_id == empId);

        if(selectedUser)
        {
            $('#designation').val(selectedUser.designation);
        }
        else
        {
            $('#designation').val('');
        }

    });

});

</script>

@endsection