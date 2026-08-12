@extends('backend.partials.master')

@section('title')
User Shift Assignment
@endsection

@section('maincontent')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<main class="content">
    <div class="container-fluid p-0">

        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 d-inline align-middle">
                {{ !empty($data) ? 'Edit Shift Assignment' : 'Assign Shift' }}
            </h1>
        </div>

        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-body">

                        <form action="{{ !empty($data) ? route('UserShift.update') : route('UserShift.store') }}"
                              method="POST">

                            @csrf

                            @if(!empty($data))
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $data->id }}">
                            @endif

                            <div class="row">

                                <!-- EMPLOYEE -->
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">
                                        Employee <small class="text-danger">*</small>
                                    </label>

                                    <select name="user_id" class="form-control select2">
                                <option value="">Select Employee</option>

                                @foreach($users as $user)
                                    <option value="{{ $user->ID }}"
                                        {{ old('user_id', $data->user_id ?? '') == $user->ID ? 'selected' : '' }}>
                                        {{ $user->Name }}
                                    </option>
                                @endforeach
                            </select>
                                    @error('user_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- SHIFT -->
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">
                                        Shift <small class="text-danger">*</small>
                                    </label>

                                    <select name="shift_id" class="form-control">

                                        <option value="">Select Shift</option>

                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}"
                                                {{ !empty($data) && $data->shift_id == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->shift }} ({{ $shift->shift_intime }} - {{ $shift->shift_outtime }})
                                            </option>
                                        @endforeach

                                    </select>

                                    @error('shift_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- FROM DATE -->
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">
                                        From Date <small class="text-danger">*</small>
                                    </label>

                                    <input type="date"
                                           name="from_date"
                                           class="form-control"
                                           value="{{ $data->from_date ?? old('from_date') }}">

                                    @error('from_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- TO DATE -->
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">
                                        To Date
                                    </label>

                                    <input type="date"
                                           name="to_date"
                                           class="form-control"
                                           value="{{ $data->to_date ?? old('to_date') }}">

                                    @error('to_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- ACTIVE STATUS -->
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">
                                        Status
                                    </label>

                                    <select name="is_active" class="form-control">

                                        <option value="1"
                                            {{ !empty($data) && $data->is_active == 1 ? 'selected' : '' }}>
                                            Active
                                        </option>

                                        <option value="0"
                                            {{ !empty($data) && $data->is_active == 0 ? 'selected' : '' }}>
                                            Inactive
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <button type="submit" class="btn btn-primary">
                                {{ !empty($data) ? 'Update' : 'Assign' }}
                            </button>

                            <a href="{{ route('UserShift') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>
</main>

@endsection
@section('scripts')
<script>
$(document).ready(function () {
    $('.select2').select2({
        placeholder: "Select Employee",
        allowClear: true,
        width: '100%'
    });
});
</script>
@endsection