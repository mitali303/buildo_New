@extends('backend.partials.master')

@section('title')
Late Mark Calculation
@endsection

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">
            {{ !empty($old) ? 'Update Late Mark Calculation' : 'Create Late Mark Calculation' }}
        </h1>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="@if(!empty($old)) {{ route('LateMarkCalculation.update') }}
                                  @else
                                    {{ route('LateMarkCalculation.store') }}
                                  @endif"
                          method="POST">
                        @csrf
                        @if(!empty($old))
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $old->id }}">
                        @endif
                        <div class="row">
                            {{-- EMPLOYEE --}}
                            <div class="mb-3 col-md-3">
                                <label class="form-label"> Employee
                                    <small class="text-danger">*</small>
                                </label>
                                <select name="employee_id" class="form-control choices-single-user" >
                                    <option value=""> Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->ID }}"
                                            {{ old('employee_id', $old->employee_id ?? '') == $employee->ID ? 'selected' : '' }}>
                                            {{ $employee->Name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <small class="text-danger"> {{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="form-label"> Month
                                    <small class="text-danger">*</small>
                                </label>
                                <select name="month"  class="form-control choices-single-month">
                                    <option value="">Select Month</option>
                                    @foreach([
                                        'January','February', 'March','April','May','June','July',
                                        'August','September','October','November','December'
                                    ] as $month)
                                        <option value="{{ $month }}"
                                            @if(old('month',$old->month ?? '') == $month)
                                                selected
                                            @endif>
                                            {{ $month }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('month')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="form-label"> Year <small class="text-danger">*</small></label>
                                <select name="year" class="form-control  choices-single-year">
                                       <option value="">Select Year</option>
                                    @for($y = date('Y') + 2; $y >= 2020; $y--)
                                        <option value="{{ $y }}"
                                            @if(old('year',$old->year ?? date('Y')) == $y)
                                                selected
                                            @endif>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                                @error('year')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </div>
                            {{-- LATE MARK COUNT --}}
                            <div class="mb-3 col-md-3">
                                <label class="form-label"> Late Mark Count <small class="text-danger">*</small>
                                </label>
                                <input type="number" name="late_mark_count" class="form-control @error('late_mark_count') is-invalid @enderror"
                                       value="{{ old('late_mark_count',$old->late_mark_count ?? '') }}" placeholder="Enter Late Mark Count">
                                @error('late_mark_count')
                                    <small class="text-danger"> {{ $message }} </small>
                                @enderror
                            </div>
                            {{--  Total Late Time --}}
                            <div class="mb-3 col-md-3">
                                <label class="form-label"> Total Late Time<small class="text-danger">*</small> </label>
                                <input type="time" name="total_late_time"  class="form-control @error('total_late_time') is-invalid @enderror"
                                       value="{{ old('total_late_time',$old->total_late_time ?? '') }}">
                                @error('total_late_time')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </div>
                            {{-- AMOUNT REDUCE --}}
                            <div class="mb-3 col-md-3">
                                <label class="form-label"> Amount Reduce <small class="text-danger">*</small> </label>
                                <input type="number" step="0.01" min="0" name="amount_reduce" class="form-control @error('amount_reduce') is-invalid @enderror"
                                       value="{{ old('amount_reduce',$old->amount_reduce ?? '') }}" placeholder="Enter Amount Reduce">
                                @error('amount_reduce')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        {{-- BUTTONS --}}
                        <button type="submit" class="btn btn-primary"> {{ !empty($old) ? 'Update' : 'Create' }}</button>
                        <a href="{{ route('LateMarkCalculation') }}" class="btn btn-secondary"> Cancel </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
@endsection
{{-- SEARCHABLE DROPDOWN SCRIPT START --}}
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {

    // Employee Dropdown
    new Choices('.choices-single-user', {

        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false

    });

    // Month Dropdown
    new Choices('.choices-single-month', {

        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false

    });

    // Year Dropdown
    new Choices('.choices-single-year', {

        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false

    });

});

</script>

@endpush

{{-- SEARCHABLE DROPDOWN SCRIPT END --}}