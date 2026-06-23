@extends('backend.partials.master')

@section('title', !empty($payment) ? 'Edit Payment' : 'Create Payment')
<style>
    .choices__list--dropdown .choices__item {
    min-width: 400px;           /* increase option width */
    white-space: nowrap;        /* prevent line breaks */
    overflow: hidden;
    text-overflow: ellipsis;    /* show "..." if too long */
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ 'Create Payment' }}</h1>

        @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Something went wrong!</strong>
        <ul style="margin-top:5px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


        <div class="card">
            <div class="card-body">
                     <form action="{{ !empty($payment)
                    ? route('Partner_pay.update', ['type' => $type, 'id' => $payment->ID])
                    : route('Partner_pay.store', ['type' => $type]) }}"
                    method="POST">
                    @csrf
                    @if(!empty($payment->ID))
                        @method('PUT')
                    @endif
                   
                    <input type="hidden" name="Pid" value="{{ $payment->ID ?? '' }}">

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>

                            <div class="input-group flatpickr-container">
                                <input type="text"
                                    name="Date"
                                    id="datepicker"
                                    class="form-control @error('Date') is-invalid @enderror"
                                    placeholder="Select date"
                                    value="{{ \Carbon\Carbon::now()->format('d-m-Y') }}">
                            </div>

                            @error('Date') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>


                        <div class="mb-3 col-md-4">
                            <label class="form-label">
                                Type <span class="text-danger">*</span>
                            </label>

                            <div class="d-flex align-items-center mt-2">

                                <div class="me-4">
                                    <input
                                        type="radio"
                                        id="payment_type_paid"
                                        name="paymenttype"
                                        value="Paid"
                                        onchange="handleAdjustmentMode()"
                                        {{ old('paymenttype', $payment->paytype ?? '') === 'Paid' ? 'checked' : '' }}
                                    >
                                    <label for="payment_type_paid" class="ms-1">
                                        Paid
                                    </label>
                                </div>

                                <div>
                                    <input
                                        type="radio"
                                        id="payment_type_recieve"
                                        name="paymenttype"
                                        value="Received"
                                        onchange="handleAdjustmentMode()"
                                        {{ old('paymenttype', $payment->paytype ?? '') === 'Received' ? 'checked' : '' }}
                                    >
                                    <label for="payment_type_recieve" class="ms-1">
                                        Receive
                                    </label>
                                </div>

                            </div>

                            @error('paymenttype')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>


                        <div class="col-md-4" id="payment_type">
                            <label class="form-label">Payment Type <span class="text-danger">*</span></label>

                            <select name="payment_type"
                                    id="pay_type_select"
                                    class="form-control choices-single-pay-type">
                                <option value="">Select</option>
                                <option value="0"
                                    {{ old('payment_type', $payment->type ?? '') == '0' ? 'selected' : '' }}>
                                    Payment
                                </option>
                                <option value="1"
                                    {{ old('payment_type', $payment->type ?? '') == '1' ? 'selected' : '' }}>
                                    Interest
                                </option>
                            </select>

                            @error('payment_type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                {{ $type === 'investor' ? 'Investor' : 'Partner' }} <span class="text-danger">*</span>
                            </label>

                            <select name="partner"
                                    id="partner_select"
                                    class="form-control choices-single-partner"
                                    data-placeholder="Select">
                                <option value="">Select</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->ID }}"
                                        {{ old('partner', $payment->partners ?? '') == $partner->ID ? 'selected' : '' }}>
                                        {{ $partner->Name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('partner')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>
                    <br>

                    <table id="pmttble" class="table table-bordered table-hover" style="width:95%;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Payment Method</th>
                            <th style="width: 15%;">Account No.</th>
                            <th style="width: 15%;" id="balance_th">Balance</th>
                            <th style="width: 12%;">Amount</th>
                            <th style="width: 15%;">Cheque No / Transaction ID</th>
                            <th style="width: 12%; display:none;" id="banktitle">Bank Charges</th>  
                            <th style="width: 12%; display:none;" id="bankdetails">Bank Details</th>  
                            <th style="width: 10%;">Payable Amount</th>
                            <th style="width: 20%;">Narration</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>

                            {{-- Payment Method --}}
                            <td>
                                <select id="Pay_type" name="Pay_type" class="form-control" onChange="check_type()">
                                    <option value="">SELECT</option>
                                    <option value="cash"
                                    {{ old('Pay_type', $payment->payment_method ?? '') == 'cash' ? 'selected' : '' }}>
                                        Cash
                                    </option>

                                    <option value="cheque"
                                    {{ old('Pay_type', $payment->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>
                                        Cheque
                                    </option>

                                    <option value="e-Payment"
                                    {{ old('Pay_type', $payment->payment_method ?? '') == 'e-Payment' ? 'selected' : '' }}>
                                        E-Payment
                                    </option>
                                </select>

                                @error('Pay_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>


                            {{-- Account No --}}
                            <td>
                                <div id="Ac">
                                    <select id="account_no" name="account_no" class="form-control" onchange="getBalance()">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                @error('account_no')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Balance --}}
                            <td id="balance_td">
                                <input  type="text" readonly class="form-control" id="balanceamt" name="balanceamt">
                                @error('balanceamt')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>
   
                            {{-- Amount Paid --}}
                            <td>
                                <input type="text" class="form-control" name="amount_pay" id="amount_pay" value="{{ old('amount_pay', $payment->amt_pay ?? '') }}">
                                <input type="hidden" name="amount_pay_old" id="amount_pay_old" >
                                @error('amount_pay')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Cheque / Transaction No --}}
                            <td>
                                <input type="text" class="form-control" name="cheque_no" id="cheque_no" value="{{ old('cheque_no', $payment->cheque_no ?? '') }}">
                                @error('cheque_no')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Bank Charges --}}
                            <td id="bankvalue" style="display:none;">
                                <input type="text" class="form-control" name="bnk_charge" id="bnk_charge" oninput="calculatePayable()" value="{{ old('bnk_charge', $payment->bankcharge ?? '') }}">
                                @error('bnk_charge')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Bank Details --}}
                            <td id="bankvaluedet" style="display:none;" value="{{ old('bankvaluedet', $payment->paydetail ?? '') }}">
                                <textarea class="form-control" name="paydetail" id="paydetail" style="height:34px;"></textarea>
                            </td>

                            {{-- Payable Amount --}}
                            <td>
                                <input type="text" readonly class="form-control" name="payable" id="payable">
                                @error('payable')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Narration --}}
                            <td>
                                <textarea class="form-control" name="narration" id="narration" style="height:34px;"></textarea>
                            </td>

                        </tr>
                    </tbody>
                </table>

                    <br>

                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-12">
                <button type="submit" id="submitBtn" class="btn btn-primary">
                        {{ !empty($payment->ID) ? 'Update' : 'Create' }}
                </button>
                            <a href="{{ route('Partner_pay',['type' => $type]) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<input type="hidden" name="request_token" value="{{ Str::uuid() }}">
@endsection
<script>
let isInitialLoad = true;
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fp = flatpickr("#datepicker", {
            dateFormat: "d-m-Y",
            allowInput: true,
            clickOpens: false, // disable open on input click
        });

        // Trigger calendar when either input or icon is clicked
        document.getElementById('datepicker').addEventListener('click', () => fp.open());
        document.getElementById('calendar-icon').addEventListener('click', () => fp.open());
    });
    document.addEventListener('DOMContentLoaded', function () {
        const fp = flatpickr("#start_datepicker", {
            dateFormat: "d-m-Y",
            allowInput: true,
            clickOpens: false, // disable open on input click
        });

        // Trigger calendar when either input or icon is clicked
        document.getElementById('start_datepicker').addEventListener('click', () => fp.open());
        document.getElementById('calendar-icon').addEventListener('click', () => fp.open());
    });
    document.addEventListener('DOMContentLoaded', function () {
        const fp = flatpickr("#to_datepicker", {
            dateFormat: "d-m-Y",
            allowInput: true,
            clickOpens: false, // disable open on input click
        });

        // Trigger calendar when either input or icon is clicked
        document.getElementById('to_datepicker').addEventListener('click', () => fp.open());
        document.getElementById('calendar-icon').addEventListener('click', () => fp.open());
    });

</script>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-partner"));
    });
</script>



<script>
function calculateBalance(input) {
    let row = input.closest('tr');

    let totalPayable = parseFloat(row.querySelector('.payamount').value) || 0;
    let amount = parseFloat(input.value) || 0;

    // 🚨 Validation: entered amount > payable
    if (amount > totalPayable) {
        alert("Amount cannot be greater than Payable amount!");
        input.value = totalPayable.toFixed(2); // reset to max allowed
        amount = totalPayable;
    }

    let balance = totalPayable - amount;

    if (balance < 0) balance = 0; // prevent negative

    row.querySelector('.balance').value = balance.toFixed(2);

    // update total of all amounts
    updateTotal();
    calculatePayable();
}

function updateTotal() {
    let total = 0;

    document.querySelectorAll('.amt').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('amount_pay').value = total.toFixed(2);
}
function check_type() {

    var pay_method = $('#Pay_type').val();

    // ---------- BANK / CHEQUE UI LOGIC (ALWAYS RUN) ----------
    const paymentType = document.querySelector(
        'input[name="paymenttype"]:checked'
    )?.value;

    // Reset
    $('#banktitle').hide();
    $('#bankdetails').hide();
    $('#bankvalue').hide();
    $('#bankvaluedet').hide();

    if (pay_method === "e-Payment") {

        if (paymentType === "Paid") {
            // Paid + e-Payment → Bank Charge
            $('#banktitle').show();
            $('#bankvalue').show();

        } else if (paymentType === "Received") {
            // Receive + e-Payment → Bank Details
            $('#bankdetails').show();
            $('#bankvaluedet').show();
        }

        $('#cheque_no').prop("readonly", false);

    } else {

        if (pay_method === "cash") {
            $('#cheque_no').val('');
            $('#cheque_no').prop("readonly", true);
        } else {
            $('#cheque_no').prop("readonly", false);
        }
    }

    // ---------- SKIP AJAX ONLY ON FIRST LOAD ----------
    if (isInitialLoad) return;

    // ---------- LOAD ACCOUNT LIST ----------
    $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: pay_method,
            selected_id: $("#account_no").val(),
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            $("#Ac").html(response.html);
            $("#balanceamt").val('');
        }
    });
}


function getBalance() {

    let accId = $("#account_no").val();
    let payId = "{{ $payment->ID ?? '' }}";  // or '' for create page
    let date  = $("#datepicker").val();

    if (!accId) {
        $("#balanceamt").val("");
        return;
    }

    $.ajax({
        url: "{{ route('ajax.getBalance') }}",
        type: "POST",
        data: {
            matid: accId,
            payid: payId,
            date: date,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            $("#balanceamt").val(response.balance);
            validateAmountVsBalance();
            calculatePayable();
        }
    });
}
function calculatePayable() {

    let amount = parseFloat($("#amount_pay").val()) || 0;
    let gst    = parseFloat($("#tax").val()) || 0;
    let tds    = parseFloat($("#tds").val()) || 0;
    let bankch    = parseFloat($("#bnk_charge").val()) || 0;

    // Formula: Amount Paid + GST - TDS
    let payable = (amount + gst - tds + bankch);

    if (payable < 0) payable = 0;

    $("#payable").val(payable.toFixed(2));
}


</script>
<script>
function toggleRow(checkbox) {
    let row = checkbox.closest('tr');

    let amtInput = row.querySelector('.amt');
    let balanceInput = row.querySelector('.balance');

    if (checkbox.checked) {
        amtInput.disabled = false;
    } else {
        amtInput.disabled = true;
        amtInput.value = "";
        balanceInput.value = "";
    }

    buildScopeSelection();
    updateTotal();
}

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    let oldPayType  = "{{ old('Pay_type', $payment->payment_method ?? '') }}";
    let oldAccount  = "{{ old('account_no', $payment->account_no ?? '') }}";

    // if (!oldPayType) return;

    // set pay type
    $("#Pay_type").val(oldPayType);

    // load account list WITH selected_id
    $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: oldPayType,
            selected_id: oldAccount, // ✅ IMPORTANT
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {

            // account already selected in HTML
            $("#Ac").html(response.html);

            getBalance();
            isInitialLoad = false;

        }
    });

});

</script>

<script>
function handleAdjustmentMode() {

    const type = document.querySelector(
        'input[name="paymenttype"]:checked'
    )?.value;

    const balanceTh = document.getElementById('balance_th');
    const balanceTd = document.getElementById('balance_td');
    const amountInput = document.getElementById('amount_pay');

    if (type === 'Received') {

        // 🔴 HIDE BALANCE COLUMN
        if (balanceTh) balanceTh.style.display = 'none';
        if (balanceTd) balanceTd.style.display = 'none';

        // 🔴 DO NOT APPLY BALANCE LOGIC
        amountInput.removeAttribute('readonly');

    } else {

        // 🟢 SHOW BALANCE COLUMN
        if (balanceTh) balanceTh.style.display = '';
        if (balanceTd) balanceTd.style.display = '';

        // 🟢 APPLY BALANCE LOGIC
        getBalance();
    }
    check_type(); 
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    handleAdjustmentMode();
});
</script>
<script>
function validateAmountVsBalance() {

    const type = document.querySelector(
        'input[name="paymenttype"]:checked'
    )?.value;

    // ❌ Skip validation for RECEIVE
    if (type === 'Received') {
        return;
    }

    let balance = parseFloat($("#balanceamt").val()) || 0;
    let amount  = parseFloat($("#amount_pay").val()) || 0;

    if (balance > 0 && amount > balance) {
        alert("Amount Paid cannot be greater than Balance Amount");

        $("#amount_pay").val(balance.toFixed(2));
        amount = balance;
    }
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // when user types amount
    $("#amount_pay").on("input", function () {
        validateAmountVsBalance();
        calculatePayable(); // keep your existing logic
    });

});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form');

    form.addEventListener('submit', function () {

        const btn = form.querySelector('button[type="submit"]');

        if (btn.dataset.submitted === 'true') {
            return false;
        }

        btn.dataset.submitted = 'true';
        btn.disabled = true;
        btn.innerHTML = 'Saving...';

    });

});
</script>
<script>
$(document).ready(function () {

    $('form').on('submit', function () {

        $('#submitBtn').prop('disabled', true);
        $('#submitBtn').text('Saving...');

        return true;
    });

});
</script>
