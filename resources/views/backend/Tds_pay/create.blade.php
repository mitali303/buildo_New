@extends('backend.partials.master')

@section('title', !empty($invoice) ? 'Edit TDS Payment' : 'Create TDS Payment ')
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
        <h1 class="h3 mb-3">{{ 'Create TDS Payment' }}</h1>

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
                 <form  action="{{ !empty($isEdit) ? route('Tds_pay.update', $payment->ID) : route('Tds_pay.store') }}"
                        method="POST">
                    @csrf
                        @if(!empty($isEdit))
                             @method('PUT')
                        @endif
                   
<input type="hidden" name="Pid" value="{{ $invoice->ID ?? '' }}">

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>

                            <div class="input-group flatpickr-container">
                                <input type="text"
                                name="Date"
                                id="datepicker"
                                class="form-control @error('Date') is-invalid @enderror"
                                placeholder="Select date"
                                value="{{ old('Date', isset($payment) 
                                    ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') 
                                    : \Carbon\Carbon::now()->format('d-m-Y')) }}">
                            </div>
                            @error('Date') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>


                        <div class="col-md-4" id="po_div">
                            <label class="form-label">Contractor <span class="text-danger">*</span></label>
                            <input type="hidden" name="conID" value="{{ $contractor->ID }}">
                            <input type="text"
                            class="form-control"
                            value="{{ $contractor->Name }}"
                            readonly>
                            @error('purchasefrom') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                      
                        <div class="col-md-3">
                                <label>Payable TDS:<span class="text-danger">*</span></label>
                               <input name="pay_tds"
                                    id="pay_tds"
                                    type="text"
                                    class="form-control"
                                    value="{{ number_format($pendingTds,2) }}"
                                    readonly
                                    style="background-color:#f5f5f5;">
                            @error('pay_tds') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <table id="pmttble" class="table table-bordered table-hover" style="width:95%;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Payment Method</th>
                            <th style="width: 15%;">Account No.</th>
                            <th style="width: 15%;">Balance</th>
                            <th style="width: 12%;">Amount Paid</th>
                            <th style="width: 15%;">Cheque No / Transaction ID</th>
                            <th style="width: 12%; display:none;" id="banktitle">Bank Charges</th>
                            <th style="width: 10%;">Payable Amount</th>
                            <th style="width: 20%;">Narration</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>

                            {{-- Payment Method --}}
                            <td>
                                <select id="Pay_type"
                                        name="Pay_type"
                                        class="form-control"
                                        onchange="check_type()">

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
                                    <select id="account_no" name="account_no" class="form-control">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                @error('account_no')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Balance --}}
                            <td>
                                <input type="text" readonly class="form-control" id="balanceamt" name="balanceamt">
                                @error('balanceamt')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Amount Paid --}}
                            <td>
                                <input type="text"
                                    class="form-control @error('amount_pay') is-invalid @enderror"
                                    name="amount_pay"
                                    id="amount_pay"
                                    value="{{ old('amount_pay', $payment->amt_pay ?? '') }}">
                                <input type="hidden" name="amount_pay_old" id="amount_pay_old" >
                                @error('amount_pay')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Cheque / Transaction No --}}
                            <td>
                                <input type="text" class="form-control" name="cheque_no" id="cheque_no">
                                @error('cheque_no')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Bank Charges --}}
                            <td id="bankvalue" style="display:none;">
                                <input type="text" class="form-control" name="bnk_charge" id="bnk_charge"  oninput="calculatePayable()" >
                                @error('bnk_charge')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
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
                            <button type="submit" class="btn btn-primary">
                                {{ 'Create' }}
                            </button>
                            <a href="{{ route('Tds_pay') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@endsection
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
document.addEventListener('DOMContentLoaded', function () {
    // Add Row
    document.getElementById('addRow').addEventListener('click', function () {
        fetch('{{ route('Tds_pay.addRow') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        })
        .then(res => res.text())
        .then(html => {
            const tbody = document.querySelector('#materialTable tbody');
            tbody.insertAdjacentHTML('beforeend', html);

            // Initialize Choices.js for the newly added row
            const newSelect = tbody.lastElementChild.querySelector('.choices-single-material');
            if (newSelect) new Choices(newSelect);

            if (typeof feather !== 'undefined') feather.replace();
        });
    });

    // Remove Row
    document.querySelector('#materialTable tbody').addEventListener('click', function (e) {
        if (e.target.closest('.removeRow')) {
            const row = e.target.closest('tr');
            if (document.querySelectorAll('#materialTable tbody tr').length > 1) {
                row.remove();
            }
        }
    });
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-purchasefrom"));
        new Choices(document.querySelector(".choices-single-destination"));
        new Choices(document.querySelector(".choices-single-worktype"));
        document.querySelectorAll(".choices-single-material").forEach(el => new Choices(el));
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

    document.getElementById('Total').value = total.toFixed(2);
    document.getElementById('amount_pay').value = total.toFixed(2);
}
function check_type() {

    var pay_method = $('#Pay_type').val();

    $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: pay_method,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {

            // Replace the account select box
            $("#Ac").html(response.html);

            // Reinitialize chosen or choices if needed
            if ($('.chosen-select').length) {
                $('.chosen-select').chosen();
            }

            // Call other functions if required
            getBalance();
        }
    });

    // Logic for cheque field + bank charges
    if (pay_method === "e-Payment") {
        $('#banktitle').show();
        $('#bankvalue').show();
        $('#cheque_no').removeAttr("readonly");
    }
    else {
        $('#banktitle').hide();
        $('#bankvalue').hide();

        if (pay_method === "cash") {
            $('#cheque_no').val('');
            $('#cheque_no').prop('readonly', true);
        } else {
            $('#cheque_no').removeAttr("readonly");
        }
    }
}

function getBalance() {

    let accId = $("#account_no").val();
    let payId = "{{ $invoice->ID ?? '' }}";  // or '' for create page
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
            calculatePayable();
        }
    });
}
function calculatePayable() {

    let amount  = parseFloat($("#amount_pay").val()) || 0;
    let bankch  = parseFloat($("#bnk_charge").val()) || 0;

    // For TDS payment:
    // Payable = Amount Paid + Bank Charges
    let payable = amount + bankch;

    if (payable < 0) payable = 0;

    $("#payable").val(payable.toFixed(2));
}

function calculatePayable() {
    let amount  = parseFloat($("#amount_pay").val()) || 0;
    let bankch  = parseFloat($("#bnk_charge").val()) || 0;

    let payable = amount + bankch;

    if (payable < 0) payable = 0;

    $("#payable").val(payable.toFixed(2));
}

// 👇 THIS ADD HERE (same script block)
function validateAmount() {
    let tds = parseFloat($("#pay_tds").val()) || 0;
    let amt = parseFloat($("#amount_pay").val()) || 0;

    if (amt > tds) {
        $("#amount_pay").val(tds.toFixed(2));
    }
}

// 👇 EVENT BINDING (same script block)
$("#amount_pay").on("input", function () {
    validateAmount();
    calculatePayable();
});
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

function buildScopeSelection() {
    // collect scope[] values for checked rows (these values may be IDs or scope text)
    let checkids = [];
    let count = 0;

    document.querySelectorAll('.invoice-row').forEach((row) => {
        let checkbox = row.querySelector('.select-row');
        if (!checkbox) return;

        let scopeInput = row.querySelector('input[name="scope[]"]');
        if (!scopeInput) return;

        let scopeVal = scopeInput.value?.toString().trim();
        if (!scopeVal) return;

        if (checkbox.checked) {
            checkids.push(scopeVal);
            count++;
        }
    });

    document.getElementById('checkids').value = checkids.join(',');
    document.getElementById('count').value = count;
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // Re-enable amt[] for checked rows
    document.querySelectorAll('.invoice-row').forEach(row => {

        let checkbox = row.querySelector('.select-row');
        let amtInput = row.querySelector('.amt');
        let balanceInput = row.querySelector('.balance');

        if (checkbox && checkbox.checked) {
            amtInput.disabled = false;
        }
    });

    // Recalculate totals on page load
    updateTotal();

    // Recalculate payable on page load
    calculatePayable();
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    let payType   = "{{ old('Pay_type', $payment->payment_method ?? '') }}";
    let accountNo = "{{ old('account_no', $payment->account_no ?? '') }}";

    if (payType) {

        $("#Pay_type").val(payType);

        $.ajax({
            url: "{{ route('ajax.getAccountList') }}",
            type: "POST",
            data: {
                pay_method: payType,
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {

                // inject dropdown
                $("#Ac").html(response.html);

                // ✅ NOW select saved account
                if (accountNo) {
                    $("#account_no").val(accountNo);
                }

                // load balance
                getBalance();
            }
        });
    }
});
</script>

{{-- <script>
document.addEventListener('DOMContentLoaded', function () {

    const amountInput  = document.getElementById('amount_pay');
    const payableTdsEl = document.getElementById('pay_tds');   // pending TDS
    const payableEl    = document.getElementById('payable');

    function validateAndRecalculate() {

        let payableTds = parseFloat(payableTdsEl.value) || 0;
        let amountPaid = parseFloat(amountInput.value) || 0;

        // 🚫 Rule 1: amount paid cannot exceed payable TDS
        if (amountPaid > payableTds) {
            alert('Amount Paid cannot be greater than Payable TDS');

            amountPaid = payableTds;
            amountInput.value = payableTds.toFixed(2);
        }

        // 🔄 Rule 2: payable amount changes with amount paid
        // For TDS payment, payable = amount paid (no GST logic here)
        payableEl.value = amountPaid.toFixed(2);
    }

    // 🔹 Trigger on typing & change
    amountInput.addEventListener('input', validateAndRecalculate);
    amountInput.addEventListener('change', validateAndRecalculate);

});
</script> --}}
{{-- <script>
document.querySelector('form').addEventListener('submit', function (e) {

    let payableTds = parseFloat(document.getElementById('pay_tds').value) || 0;
    let amountPaid = parseFloat(document.getElementById('amount_pay').value) || 0;

    if (amountPaid <= 0) {
        alert('Please enter Amount Paid');
        e.preventDefault();
        return;
    }

    if (amountPaid > payableTds) {
        alert('Amount Paid cannot exceed Payable TDS');
        e.preventDefault();
        return;
    }
});
</script> --}}

<script>function validateAmount() {

    let tds = parseFloat($("#pay_tds").val()) || 0;
    let amt = parseFloat($("#amount_pay").val()) || 0;

    if (amt > tds) {
        amt = tds;
        $("#amount_pay").val(amt.toFixed(2));
    }
}
</script>