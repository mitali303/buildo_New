@extends('backend.partials.master')

@section('title')
Salary Master
@endsection

@section('maincontent')

<main class="content">

<div class="container-fluid p-0">

    <div class="mb-3">

        <h1 class="h3 d-inline align-middle">

            {{ !empty($old) ? 'Update Salary Master' : 'Create Salary Master' }}

        </h1>

    </div>

    <div class="row">

        <div class="col-md-12">

            <div class="card">

                <div class="card-body">

                    <form action="@if(!empty($old))
                                    {{ route('SalaryMaster.update') }}
                                  @else
                                    {{ route('SalaryMaster.store') }}
                                  @endif"
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
                                       value="{{ old('date', $old->date ?? date('Y-m-d')) }}">

                                @error('date')

                                    <small class="text-danger">

                                        {{ $message }}

                                    </small>

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
                                        class="form-control choices-single-user">

                                    <option value="">
                                        Select Employee
                                    </option>

                                    @foreach($users as $user)

                                        <option value="{{ $user->emp_id }}" {{ old('emp_id', $old->emp_id ?? '') == $user->emp_id? 'selected' : '' }}>

                                            {{ $user->Name }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('emp_id')

                                    <small class="text-danger">

                                        {{ $message }}

                                    </small>

                                @enderror

                            </div>


                            {{-- MONTH --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Month
                                    <small class="text-danger">*</small>

                                </label>

                                <select name="month"
                                        id="month"
                                        class="form-control choices-single-month">

                                    <option value="">
                                        Select Month
                                    </option>

                                    @foreach([
                                        'January',
                                        'February',
                                        'March',
                                        'April',
                                        'May',
                                        'June',
                                        'July',
                                        'August',
                                        'September',
                                        'October',
                                        'November',
                                        'December'
                                    ] as $month)

                                        <option value="{{ $month }}" {{ old('month', $old->month ?? '') == $month ? 'selected' : '' }}>

                                            {{ $month }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('month')

                                    <small class="text-danger">

                                        {{ $message }}

                                    </small>

                                @enderror

                            </div>


                            {{-- YEAR --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Year
                                    <small class="text-danger">*</small>

                                </label>

                                <select name="year"
                                        id="year"
                                        class="form-control choices-single-year">

                                    @for($y = date('Y') + 1; $y >= 2020; $y--)

                                        <option value="{{ $y }}"
                                            {{ old('year', $old->year ?? date('Y')) == $y ? 'selected' : '' }}>

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


                            {{-- SALARY NO --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Salary No
                                    <small class="text-danger">*</small>

                                </label>

                                <input type="text"
                                       name="salary_no"
                                       class="form-control @error('salary_no') is-invalid @enderror"
                                       value="{{ old('salary_no', $salaryNo ?? '') }}" readonly>

                                @error('salary_no')

                                    <small class="text-danger">

                                        {{ $message }}

                                    </small>

                                @enderror

                            </div>

                            {{-- =========================================================
PRESENT DAYS
========================================================= --}}

<div class="mb-3 col-md-3">

    <label class="form-label">

        Present Days

    </label>

    <input type="number"
           id="total_present_days"
           name="total_present_days"
           class="form-control @error('total_present_days') is-invalid @enderror"
           value="{{ old('total_present_days', $old->total_present_days ?? 0) }}"
           readonly>

    @error('total_present_days')

        <small class="text-danger">

            {{ $message }}

        </small>

    @enderror

</div>


{{-- =========================================================
ABSENT DAYS
========================================================= --}}

<div class="mb-3 col-md-3">

    <label class="form-label">

        Absent Days

    </label>

    <input type="number"
           id="total_absent_days"
           name="total_absent_days"
           class="form-control @error('total_absent_days') is-invalid @enderror"
           value="{{ old('total_absent_days', $old->total_absent_days ?? 0) }}"
           readonly>

    @error('total_absent_days')

        <small class="text-danger">

            {{ $message }}

        </small>

    @enderror

</div>


{{-- =========================================================
PAID LEAVES
========================================================= --}}

<div class="mb-3 col-md-3">

    <label class="form-label">

        Paid Leaves

    </label>

    <input type="number"
           id="paid_leaves"
           name="paid_leaves"
           class="form-control @error('paid_leaves') is-invalid @enderror"
           value="{{ old('paid_leaves', $old->paid_leaves ?? 0) }}"
           readonly>

    @error('paid_leaves')

        <small class="text-danger">

            {{ $message }}

        </small>

    @enderror

</div>


{{-- =========================================================
WORKING DAYS
========================================================= --}}

<div class="mb-3 col-md-3">

    <label class="form-label">

        Working Days

    </label>

    <input type="number"
           id="working_days"
           name="working_days"
           class="form-control @error('working_days') is-invalid @enderror"
           value="{{ old('working_days', $old->working_days ?? 0) }}"
           readonly>

    @error('working_days')

        <small class="text-danger">

            {{ $message }}

        </small>

    @enderror

</div>


{{-- =========================================================
PER DAY SALARY
========================================================= --}}

<div class="mb-3 col-md-3">

    <label class="form-label">

        Per Day Salary

    </label>

    <input type="number"
           step="0.01"
           id="per_day_salary"
           name="per_day_salary"
           class="form-control @error('per_day_salary') is-invalid @enderror"
           value="{{ old('per_day_salary', $old->per_day_salary ?? 0) }}"
           readonly>

    @error('per_day_salary')

        <small class="text-danger">

            {{ $message }}

        </small>

    @enderror

</div>


{{-- =========================================================
ABSENT DEDUCTION
========================================================= --}}

<div class="mb-3 col-md-3">

    <label class="form-label">

        Absent Deduction

    </label>

    <input type="number"
           step="0.01"
           id="absent_deduction"
           name="absent_deduction"
           class="form-control @error('absent_deduction') is-invalid @enderror"
           value="{{ old('absent_deduction', $old->absent_deduction ?? 0) }}"
           readonly>

    @error('absent_deduction')

        <small class="text-danger">

            {{ $message }}

        </small>

    @enderror

</div>


{{-- =========================================================
OVERTIME HOURS
========================================================= --}}

<div class="mb-3 col-md-3">

    <label class="form-label">

        OT Hours

    </label>

    <input type="number"
           step="0.01"
           id="overtime_hours"
           name="overtime_hours"
           class="form-control @error('overtime_hours') is-invalid @enderror"
           value="{{ old('overtime_hours', $old->overtime_hours ?? 0) }}"
           readonly>

    @error('overtime_hours')

        <small class="text-danger">

            {{ $message }}

        </small>

    @enderror

</div>


{{-- =========================================================
OT RATE PER HOUR
========================================================= --}}

<div class="mb-3 col-md-3">

    <label class="form-label">

        OT Rate / Hour

    </label>

    <input type="number"
           step="0.01"
           id="per_hour_ot_rate"
           name="per_hour_ot_rate"
           class="form-control @error('per_hour_ot_rate') is-invalid @enderror"
           value="{{ old('per_hour_ot_rate', $old->per_hour_ot_rate ?? 0) }}"
           readonly>

    @error('per_hour_ot_rate')

        <small class="text-danger">

            {{ $message }}

        </small>

    @enderror

</div>


                            {{-- BASIC SALARY --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Basic Salary

                                </label>

                                <input type="number"
                                       id="basic_salary"
                                       name="basic_salary"
                                       class="form-control"
                                       value="{{ old('basic_salary', $old->basic_salary ?? '') }}"
                                       readonly>

                            </div>


                            {{-- GROSS --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Gross Salary

                                </label>

                                <input type="number"
                                       id="gross"
                                       name="gross"
                                       class="form-control"
                                        value="{{ old('gross', $old->gross ?? '') }}"
                                       readonly>

                            </div>


                            {{-- PF --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    PF

                                </label>

                                <input type="number"
                                       id="pf"
                                       name="pf"
                                       class="form-control"
                                        value="{{ old('pf', $old->pf ?? '') }}"
                                       readonly>

                            </div>


                            {{-- ESI --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    ESI

                                </label>

                                <input type="number"
                                       id="esi"
                                       name="esi"
                                       class="form-control"
                                       value="{{ old('esi', $old->esi ?? '') }}"
                                       readonly>

                            </div>


                            {{-- ADVANCE EMI --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Advance EMI

                                </label>

                                <input type="number"
                                       id="advance_emi"
                                       name="advance_emi"
                                       class="form-control"
                                       value="{{ old('advance_emi', $old->advance_emi ?? 0) }}">

                            </div>


                            {{-- LATE DEDUCTION --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Late Deduction

                                </label>

                                <input type="number"
                                       id="late_deduction"
                                       class="form-control"
                                       value="{{ old('late_deduction', $old->late_deduction ?? 0) }}"
                                       readonly>

                            </div>


                            {{-- OVERTIME --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Overtime Amount

                                </label>

                                <input type="number"
                                       id="overtime_amount"
                                       class="form-control"
                                       value="{{ old('overtime_amount', $old->overtime_amount ?? 0) }}"
                                       readonly>

                            </div>


                            {{-- NET SALARY --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Net Salary

                                </label>

                                <input type="number"
                                       id="net_salary"
                                       name="net_salary"
                                       class="form-control"
                                       value="{{ old('net_salary', $old->net_salary ?? '') }}" readonly >

                            </div>


                            {{-- PAYMENT METHOD --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Payment Method
                                    <small class="text-danger">*</small>

                                </label>

                                <select name="payment_method"
                                        id="payment_method"
                                        class="form-control choices-single-payment">                                   

                                    <option value="">
                                        Select Payment Method
                                    </option>

                                    <option value="cash"
                                        {{ old('payment_method', $old->payment_method ?? '') == 'cash' ? 'selected' : '' }}>
                                        Cash
                                    </option>

                                    <option value="cheque"
                                        {{ old('payment_method', $old->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>
                                        Cheque
                                    </option>

                                    <option value="DD"
                                        {{ old('payment_method', $old->payment_method ?? '') == 'DD' ? 'selected' : '' }}>
                                        DD
                                    </option>

                                </select>

                                @error('payment_method')

                                    <small class="text-danger">

                                        {{ $message }}

                                    </small>

                                @enderror

                            </div>


                            {{-- CHEQUE NO --}}

                            <div class="mb-3 col-md-4"
                                 id="cheque_div"
                                 style="display:none;">

                                <label class="form-label">

                                    Cheque No

                                </label>

                                <input type="text"
                                       name="cheque_no"
                                       class="form-control">

                            </div>


                            {{-- NARRATION --}}

                            <div class="mb-3 col-md-4">

                                <label class="form-label">

                                    Narration

                                </label>

                                <input type="text"
                                       name="narration"
                                       class="form-control"
                                       value="{{ old('narration', $old->narration ?? '') }}">

                            </div>

                        </div>


                        <button type="submit"
                                class="btn btn-primary">

                            {{ !empty($old) ? 'Update' : 'Create' }}

                        </button>

                        <a href="{{ route('SalaryMaster') }}"
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

@endsection



@push('scripts')

<script>

document.addEventListener("DOMContentLoaded", function () {

    new Choices('.choices-single-user', {

        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false

    });

    new Choices('.choices-single-month', {

        searchEnabled: false,
        itemSelectText: '',
        shouldSort: false

    });

    new Choices('.choices-single-year', {

        searchEnabled: false,
        itemSelectText: '',
        shouldSort: false

    });

    new Choices('.choices-single-payment', {

        searchEnabled: false,
        itemSelectText: '',
        shouldSort: false

    });

});


/* =========================================================
PAYMENT METHOD
========================================================= */

$('#payment_method').on('change', function(){

    if($(this).val() == 'cheque'){

        $('#cheque_div').show();

    }else{

        $('#cheque_div').hide();
    }
});


/* =========================================================
AUTO SALARY CALCULATION
========================================================= */

$('#emp_id, #month, #year').on('change', function () {

    console.log('CHANGE EVENT FIRED');

    let empId = $('#emp_id').val();
    let month = $('#month').val();
    let year  = $('#year').val();

    let editId = "{{ $old->id ?? '' }}";

    console.log('EMP ID:', empId);
    console.log('MONTH:', month);
    console.log('YEAR:', year);
    console.log('EDIT ID:', editId);

    if (!empId || !month || !year) {

        console.log('❌ IF FALSE - value missing');

        return;
    }

    console.log('✅ IF TRUE');
    console.log('🚀 AJAX CALL STARTING');

    let ajaxUrl = "{{ url('salary/get-details') }}/"
                + empId + "/"
                + month + "/"
                + year;

    if (editId) {
        ajaxUrl += "/" + editId;
    }

    console.log('AJAX URL:', ajaxUrl);

    $.ajax({

        url: ajaxUrl,

        type: "GET",

        beforeSend: function () {

            console.log('AJAX beforeSend');

            $('button[type="submit"]').prop('disabled', true);
        },

        success: function (res) {

            console.log('✅ AJAX SUCCESS');
            console.log(res);

            if (res.status == false) {

                alert(res.message);

                $('button[type="submit"]').prop('disabled', true);

                return;
            }

            $('button[type="submit"]').prop('disabled', false);

            $('#gross').val(res.gross_salary);
            $('#pf').val(res.pf);
            $('#esi').val(res.esi);
            $('#advance_emi').val(res.advance_emi);

            $('#basic_salary').val(res.basic_salary);
            $('#late_deduction').val(res.late_deduction);
            $('#overtime_amount').val(res.overtime_amount);
            $('#net_salary').val(res.net_salary);

            $('#total_present_days').val(res.total_present_days);
            $('#total_absent_days').val(res.total_absent_days);
            $('#paid_leaves').val(res.paid_leaves);
            $('#working_days').val(res.working_days);
            $('#per_day_salary').val(res.per_day_salary);
            $('#absent_deduction').val(res.absent_deduction);
            $('#overtime_hours').val(res.overtime_hours);
            $('#per_hour_ot_rate').val(res.per_hour_ot_rate);
        },

        error: function (xhr) {

            console.log('❌ AJAX ERROR');
            console.log('HTTP STATUS:', xhr.status);
            console.log('RESPONSE:', xhr.responseText);

            alert('Something went wrong');
        }
    });

});

/* =========================================================
NET SALARY RECALCULATE
========================================================= */

$('#advance_emi').on('keyup change', function(){

    let gross       = parseFloat($('#gross').val()) || 0;

    let pf          = parseFloat($('#pf').val()) || 0;

    let esi         = parseFloat($('#esi').val()) || 0;

    let advance     = parseFloat($('#advance_emi').val()) || 0;

    let late        = parseFloat($('#late_deduction').val()) || 0;

    let net = gross - pf - esi - advance - late;

    $('#net_salary').val(net.toFixed(2));

});

</script>

@endpush