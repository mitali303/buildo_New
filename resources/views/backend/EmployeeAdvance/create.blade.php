@extends('backend.partials.master')
@section('title')
   Employee Advance
@endsection
@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Create Employee Advance</h1>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="@if(!empty($old)){{ route('EmployeeAdvance.update') }}@else{{ route('EmployeeAdvance.store') }}@endif" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!empty($old))
                                @method('PUT')
                                <input type="hidden" name="id" id="id" value="{{ $old->id }}"/>
                            @endif
                            <div class="row">                               

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="record_no">
                                        Record No <small class="text-danger">*</small>
                                    </label>

                                    <input type="text"
                                        class="form-control @error('record_no') is-invalid @enderror"
                                        value="{{ old('record_no', $old->record_no ?? $nextRecordNo ?? '') }}"
                                        name="record_no"
                                        id="record_no"
                                        readonly>

                                    @error('record_no')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                 <div class="mb-3 col-md-4">
                                    <label class="form-label" for="date">Date<small class="text-danger">*</small></label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror"
                                        value="{{ old('date', $old->date ?? '') }}" name="date" id="date"
                                        placeholder="Date" >
                                    @error('date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Employee -->
                               <!-- Employee -->
@php
    $selecteduser = old('emp_id', !empty($old->emp_id) ? $old->emp_id : '');
@endphp

<div class="mb-3 col-md-4">
    <label class="form-label">Employee <small class="text-danger">*</small></label>

    <select name="emp_id" class="form-control" required>
        <option value="" disabled selected>-- Select Employee --</option>

        @foreach($users as $user)
            <option value="{{ $user->id }}"
                {{ (string)$user->id === (string)$selecteduser ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>

    @error('emp_id')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="advance">Advance<small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('advance') is-invalid @enderror"
            value="{{ old('advance', $old->advance ?? '') }}" name="advance" id="advance"
            placeholder="Advance" >
        @error('advance')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="emi_amount">EMI Amount<small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('emi_amount') is-invalid @enderror"
            value="{{ old('emi_amount', $old->emi_amount ?? '') }}" name="emi_amount" id="emi_amount"
            placeholder="EMI Amount" >
        @error('emi_amount')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="total_installments">Total Installments<small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('total_installments') is-invalid @enderror"
            value="{{ old('total_installments', $old->total_installments ?? '') }}" name="total_installments" id="total_installments"
            placeholder="Total Installments" >
        @error('total_installments')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="remaining_amount">Remaining Amount<small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('remaining_amount') is-invalid @enderror"
            value="{{ old('remaining_amount', $old->remaining_amount ?? '') }}" name="remaining_amount" id="remaining_amount"
            placeholder="Remaining Amount" >
        @error('remaining_amount')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label" for="narration">Narration<small class="text-danger">*</small></label>
        <input type="text" class="form-control @error('narration') is-invalid @enderror"
            value="{{ old('narration', $old->narration ?? '') }}" name="narration" id="narration"
            placeholder="Narration" >
        @error('narration')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
    <!-- 👉 Payment Details येथे Add करा -->

<hr>
<h5>Payment Details</h5>

<div class="row">

    <div class="mb-3 col-md-3">
        <label class="form-label">Payment Method</label>
        <select id="Pay_type" name="Pay_type" class="form-control">
            <option value="">SELECT</option>
            <option value="cash">Cash</option>
            <option value="cheque">Cheque</option>
            <option value="e-Payment">E-Payment</option>
        </select>
    </div>

    <div class="mb-3 col-md-4" id="cheque_no_div" style="display:none;">
        <label class="form-label">Cheque No</label>
        <input type="number"
               class="form-control"
               name="cheque_no"
               id="cheque_no">
    </div>

    <div class="mb-3 col-md-3">
        <label class="form-label">Account No</label>
        <div id="Ac">
            <select id="account_no" name="account_no" class="form-control">
                <option value="">Select</option>
            </select>
        </div>
    </div>

    <div class="mb-3 col-md-2">
        <label class="form-label">Balance</label>
        <input type="text"
               class="form-control"
               id="balanceamt"
               name="balanceamt"
               readonly>
    </div>

</div>


                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($old) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('EmployeeAdvance') }}" class="btn btn-secondary">Cancel</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let oldPayType   = "{{ old('Pay_type', $old->payment_method ?? '') }}";
let oldAccountNo = "{{ old('account_no', $old->account_no ?? '') }}";

$(document).ready(function () {

    toggleChequeField();

    if (oldPayType) {
        loadAccounts(oldPayType, oldAccountNo);
    }

    $('#Pay_type').change(function () {
        toggleChequeField();
        loadAccounts($(this).val());
    });

    $(document).on('change', '#account_no', function () {
        getBalance();
    });

    $('#date').change(function () {
        getBalance();
    });

    $('#advance').on('input', function () {
        validateAdvance();
    });

});

function toggleChequeField() {

    if ($('#Pay_type').val() == 'cheque') {
        $('#cheque_no_div').show();
        $('#cheque_no').prop('required', true);
    } else {
        $('#cheque_no_div').hide();
        $('#cheque_no').prop('required', false).val('');
    }
}

function loadAccounts(pay_method, selectedAccount = null) {

    if (pay_method == '') {
        $("#Ac").html(
            '<select id="account_no" name="account_no" class="form-control"><option value="">Select</option></select>'
        );
        $("#balanceamt").val('');
        return;
    }

    $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: pay_method,
            selected_id: selectedAccount,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {

            $("#Ac").html(response.html);

            if (selectedAccount) {
                $("#account_no").val(selectedAccount).trigger('change');
            }
        }
    });
}

function getBalance() {

    let accId = $("#account_no").val();

    if (accId == "") {
        $("#balanceamt").val("");
        return;
    }

    $.ajax({
        url: "{{ route('ajax.getBalance') }}",
        type: "POST",
        data: {
            matid: accId,
            date: $("#date").val(),
            iid: $("#id").val() || 0,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {

            $("#balanceamt").val(response.balance);

            validateAdvance();
        }
    });
}

function validateAdvance() {

    let advance = parseFloat($("#advance").val()) || 0;
    let balance = parseFloat($("#balanceamt").val()) || 0;

    if (balance > 0 && advance > balance) {

        alert("Advance amount cannot be greater than available balance.");

        $("#advance").val(balance);
    }
}
</script>
