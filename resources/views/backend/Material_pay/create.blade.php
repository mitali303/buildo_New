@extends('backend.partials.master')

@section('title', !empty($invoice) ? 'Edit Material Payment' : 'Create Material Payment')
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
        <h1 class="h3 mb-3">{{ 'Create Material Payment' }}</h1>

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
                    <form action="{{ !empty($invoice) ? route('Material_pay.update', $invoice->ID) : route('Material_pay.store') }}"
                      method="POST">
                    @csrf
                    @if(!empty($invoice->ID))
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
                                    value="{{ \Carbon\Carbon::now()->format('d-m-Y') }}">
                            </div>

                            @error('Date') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>


                        <div class="col-md-4" id="po_div">
                            <label class="form-label">Supplier <span class="text-danger">*</span></label>

                            <select name="PurchaseFrom"
                                    id="PurchaseFromSelect"
                                    class="form-control choices-single-purchasefrom">
                                <option value="">Select</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->ID }}"
                                        {{ old('PurchaseFrom', $invoice->PurchaseFrom ?? '') == $vendor->ID ? 'selected' : '' }}>
                                        {{ $vendor->Name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('PurchaseFrom')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>                                            

                        <div class="col-md-4" id="scheme">
                            <label class="form-label">Scheme <span class="text-danger">*</span></label>

                            <select name="Destination" id="Destination"
                                    class="form-control choices-single-destination"
                                    data-placeholder="Select Destination"
                                    >
                                <option value="">Select</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->ID }}"
                                            {{ old('scheme', $transfer->scheme ?? $schemes[0]->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4" id="pay_type">
                            <label class="form-label">Payment Type <span class="text-danger">*</span></label>

                            <select name="pay_type"
                                    id="pay_type_select"
                                    class="form-control choices-single-pay-type" onchange="handleAdjustmentMode()">
                                <option value="">Select</option>
                                <option value="payment"
                                    {{ old('pay_type', $invoice->Type ?? '') == 'payment' ? 'selected' : '' }}>
                                    Payment
                                </option>
                                <option value="adjustment"
                                    {{ old('pay_type', $invoice->Type ?? '') == 'adjustment' ? 'selected' : '' }}>
                                    Adjustment Payment
                                </option>
                            </select>

                            @error('pay_type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        
                    </div>
@if ($errors->has('invoice_id'))
    <small class="text-danger">{{ $errors->first('invoice_id') }}</small>
@endif

                    <div class="row">
                       <div class="row mt-4">
                            <div class="col-md-12" id="invoiceSection" style="overflow-x: auto; width: 100%;">
                               {{-- 🔴 TABLE VALIDATION ERRORS (ROW-WISE, SAFE) --}}
                                @php
                                    $rowErrors = [];
                                    $rowIndexMap = []; // invoice_id => row number
                                    $rowCounter = 1;

                                    foreach ($errors->messages() as $key => $messages) {

                                        // match amt.{invoiceId} or extra.{invoiceId}
                                        if (preg_match('/^(amt|extra)\.(.+)$/', $key, $matches)) {

                                            $invoiceId = $matches[2];

                                            // assign row number if not already assigned
                                            if (!isset($rowIndexMap[$invoiceId])) {
                                                $rowIndexMap[$invoiceId] = $rowCounter++;
                                            }

                                            foreach ($messages as $msg) {
                                                $rowErrors[$invoiceId][] = $msg;
                                            }
                                        }

                                        // general table error (no row id)
                                        if ($key === 'checked_invoice') {
                                            $rowErrors['general'] = $messages;
                                        }
                                    }
                                @endphp

                                @if (!empty($rowErrors))
                                    <div class="alert alert-danger mb-3">
                                        <strong>Please fix errors in the invoice table below:</strong>

                                        <ul class="mb-0">
                                            {{-- General error --}}
                                            @if(isset($rowErrors['general']))
                                                @foreach($rowErrors['general'] as $msg)
                                                    <li>{{ $msg }}</li>
                                                @endforeach
                                            @endif

                                            {{-- Row-wise errors --}}
                                            @foreach($rowErrors as $invoiceId => $messages)
                                                @if($invoiceId !== 'general')
                                                    <li>
                                                        <strong>Row {{ $rowIndexMap[$invoiceId] ?? '?' }}:</strong>
                                                        <ul>
                                                            @foreach(array_unique($messages) as $msg)
                                                                <li>{{ $msg }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                               <table class="table table-bordered table-striped" id="materialTable" style="font-size:13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th style="min-width: 180px;">Inv Details</th>
                                        <th style="min-width: 90px;">Total Payable</th>
                                        <th style="min-width: 90px;">Amount</th>
                                        <th style="min-width: 90px;">Extra</th>
                                        <th style="min-width: 120px;">Balance</th>
                                    </tr>
                                </thead>

                                    <tbody id="invoiceBody">
                                    @isset($rows)
                                    @foreach($rows as $row)
                                    <tr class="invoice-row">
                                        <td>
                                            <input type="checkbox"
                                                class="select-row"
                                                name="checked_invoice[]"
                                                value="{{ $row['invoice_id'] }}"
                                                onchange="toggleRow(this)"
                                                {{ $row['checked'] ? 'checked' : '' }}>
                                        </td>

                                        <td>
                                            <input type="hidden" name="invoice_id[]" value="{{ $row['invoice_id'] }}">
                                            <strong>{{ $row['invno'] }}</strong>
                                        </td>

                                        <td>
                                            <input type="text" class="form-control payamount"
                                                value="{{ $row['payamount'] }}" readonly>
                                        </td>

                                        <td>
                                            <input type="text" name="amt[{{ $row['invoice_id'] }}]"
                                                class="form-control amt"
                                                value="{{ $row['amt'] }}"
                                                {{ !$row['checked'] ? 'disabled' : '' }}
                                                oninput="calculateBalance(this)">
                                        </td>

                                        <td>
                                            <input type="text" name="extra[{{ $row['invoice_id'] }}]"
                                                class="form-control extra"
                                                value="{{ $row['extra'] }}"
                                                {{ !$row['checked'] ? 'disabled' : '' }}
                                                oninput="updateTotal()">
                                        </td>

                                        <td>
                                            <input type="text" class="form-control balance"
                                                value="{{ $row['balance'] }}" readonly>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endisset
                                    </tbody>


                            </table>
                            </div>
                        </div>
                    </div> 
                    <input type="hidden" name="checkids" id="checkids">
                    <input type="hidden" name="count" id="count">
                    <div class="row">
                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Total<span class="text-danger">*</span></label>
                                <input name="Total" id="Total"
                                    type="text"
                                    class="form-control"
                                    readonly
                                    style="background-color:#f5f5f5;">
                            @error('Total') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="mb-2 d-block">Debit<span class="text-danger">*</span></label>

                            <input name="debit" id="debit"
                                type="text"
                                class="form-control"
                                value="{{ old('debit', 0) }}"
                                readonly
                                style="background-color:#f5f5f5;">
                        </div>

                        <div class="col-md-3">
                            <label class="mb-2 d-block">Amount Use<span class="text-danger">*</span></label>

                            <input name="amtuse" id="amtuse"
                                type="text"
                                class="form-control" 
                                value="{{ old('amtuse', $invoice->debit_amount ?? 0) }}">
                        </div>

                    </div><br>

                    <table id="pmttble" class="table table-bordered table-hover" style="width:95%;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Payment Method</th>
                            <th style="width: 15%;">Account No.</th>
                            <th style="width: 15%;">Balance</th>
                            <th style="width: 12%;">Amount Paid</th>
                            <th style="width: 15%;">Cheque No / Transaction ID</th>
                            <th style="width: 12%; display:none;" id="banktitle">Bank Charges</th>
                            <th style="width: 12%;">Payable Amount</th>
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
                                    {{ old('Pay_type', $invoice->payment_method ?? '') == 'cash' ? 'selected' : '' }}>
                                        Cash
                                    </option>

                                    <option value="cheque"
                                    {{ old('Pay_type', $invoice->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>
                                        Cheque
                                    </option>

                                    <option value="e-Payment"
                                    {{ old('Pay_type', $invoice->payment_method ?? '') == 'e-Payment' ? 'selected' : '' }}>
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
                                <input type="text" class="form-control" name="amount_pay" id="amount_pay" readonly>
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
                                <input type="text" class="form-control" name="bnk_charge" id="bnk_charge" value="{{ old('bnk_charge', $invoice->bankcharge ?? '') }}" oninput="calculateTotalPayable()">
                                @error('bnk_charge')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Payable Amount --}}
                            <td>
                                <input type="text" readonly class="form-control" name="totalpayable" id="totalpayable">
                                @error('totalpayable')
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
                            <a href="{{ route('Material_pay') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@endsection
<script>
let debitLoaded = false;
</script>
<script>
function calculateTotalPayable() {

    let amountPaid = parseFloat($("#amount_pay").val()) || 0;
    let bankCharge = parseFloat($("#bnk_charge").val()) || 0;

    let totalPayable = amountPaid + bankCharge;

    if (totalPayable < 0) totalPayable = 0;

    $("#totalpayable").val(totalPayable.toFixed(2));
}
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
document.addEventListener('DOMContentLoaded', function () {
    // Add Row
    document.getElementById('addRow').addEventListener('click', function () {
        fetch('{{ route('Material_pay.addRow') }}', {
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
}

function updateTotal() {
    let total = 0;

    document.querySelectorAll('.amt').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.querySelectorAll('.extra').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('Total').value = total.toFixed(2);
    document.getElementById('amount_pay').value = total.toFixed(2);
    calculateAmountPaid();
}
function check_type() {

    const payType = document.getElementById('pay_type_select').value;
    if (payType === 'adjustment') {
        return; // 🔥 THIS LINE FIXES YOUR ISSUE
    }

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
        }
    });
}



</script>
<script>
function toggleRow(checkbox) {
    let row = checkbox.closest('tr');

    let amt     = row.querySelector('.amt');
    let extra   = row.querySelector('.extra');
    let balance = row.querySelector('.balance');
    let payable = parseFloat(row.querySelector('.payamount').value) || 0;

    if (checkbox.checked) {
        amt.disabled   = false;
        extra.disabled = false;
        balance.value  = payable.toFixed(2);
    } else {
        amt.disabled   = true;
        extra.disabled = true;

        amt.value = '';
        extra.value = '';
        balance.value = '';
    }

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
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    let payType    = "{{ old('Pay_type', $invoice->payment_method ?? '') }}";
    let accountNo  = "{{ old('account_no', $invoice->account_no ?? '') }}";

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

                $("#Ac").html(response.html);

                if (accountNo) {
                    $("#account_no").val(accountNo);
                }

                getBalance(); // recalc balance
            }
        });
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const supplierSelect = document.getElementById('PurchaseFromSelect');

    if (!supplierSelect) {
        console.error("PurchaseFromSelect not found in DOM");
        return;
    }


    supplierSelect.addEventListener('change', function () {

        let supplierId     = this.value;
        let invoiceSection = document.getElementById('invoiceSection');
        let invoiceBody    = document.getElementById('invoiceBody');

        // Safety checks
        if (!invoiceSection || !invoiceBody) {
            console.error("invoiceSection or invoiceBody missing");
            return;
        }

        // reset everything
        invoiceBody.innerHTML = '';
        document.getElementById('Total').value = '';
        document.getElementById('amount_pay').value = '';

        const purchaseFromInput = document.getElementById('PurchaseFrom');
        if (purchaseFromInput) {
            purchaseFromInput.value = '';
        }

        if (!supplierId) {
            invoiceSection.style.display = 'none';
            return;
        }

        if (purchaseFromInput) {
            purchaseFromInput.value = supplierId;
        }

        fetch("{{ route('Material_pay.getInvoicesBySupplier') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ supplier_id: supplierId })
        })
        .then(res => res.json())
        .then(data => {
            invoiceBody.innerHTML = data.html || '';
            invoiceSection.style.display =
                data.html && data.html.trim() ? 'block' : 'none';
        })
        .catch(err => console.error(err));
    });

});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-Destination"));
        new Choices(document.querySelector(".choices-single-PurchaseFrom"));
        new Choices(document.querySelector(".choices-single-pay_type"));
    });
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const supplierSelect = document.getElementById('PurchaseFromSelect');
    const debitInput     = document.getElementById('debit');

    supplierSelect.addEventListener('change', function () {

        let supplierId = this.value;

        if (!supplierId) {
            debitInput.value = '0.00';
            return;
        }

        fetch("{{ route('Material_pay.getDebitBySupplier') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                supplier_id: supplierId,
                payment_id: "{{ $invoice->ID ?? '' }}"
            })
        })
        .then(res => res.json())
        .then(data => {
            debitInput.value = parseFloat(data.debit || 0).toFixed(2);
        })
        .catch(err => {
            console.error(err);
            debitInput.value = '0.00';
        });
    });

});
</script>
<script>
        const IS_EDIT = {{ !empty($invoice) ? 'true' : 'false' }};

function calculateAmountPaid() {

    const totalInput     = document.getElementById('Total');
    const debitInput     = document.getElementById('debit');
    const amtUseInput    = document.getElementById('amtuse');
    const amountPayInput = document.getElementById('amount_pay');

    let total   = parseFloat(totalInput.value)  || 0;
    let debit   = parseFloat(debitInput.value)  || 0;
    let amtUse  = parseFloat(amtUseInput.value) || 0;

    // 🔥 IMPORTANT: wait for debit on EDIT page
      if (IS_EDIT && !debitLoaded) {
        return;
    }

    // ❌ Amount Use cannot exceed Total
    if (amtUse > total) {
        alert("Amount Use cannot be greater than Total amount");
        amtUse = total;
        amtUseInput.value = total.toFixed(2);
    }

    // ❌ Amount Use cannot exceed Debit
    if (amtUse > debit) {
        alert("Amount Use cannot be greater than available Debit");
        amtUse = debit;
        amtUseInput.value = debit.toFixed(2);
    }

    // ✅ Amount Paid = Total - Amount Use
    let amountPaid = total - amtUse;
    if (amountPaid < 0) amountPaid = 0;

    amountPayInput.value = amountPaid.toFixed(2);

    calculateTotalPayable();
}

document.addEventListener("DOMContentLoaded", function () {

    const amtUseInput = document.getElementById('amtuse');

    amtUseInput.addEventListener('input', calculateAmountPaid);
});

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const supplierId = "{{ $invoice->PurchaseFrom ?? '' }}";
    const debitInput = document.getElementById('debit');

    // Only for EDIT page
    if (!supplierId) return;

    fetch("{{ route('Material_pay.getDebitBySupplier') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            supplier_id: supplierId,
            payment_id: "{{ $invoice->ID ?? '' }}"
        })
    })
    .then(res => res.json())
    .then(data => {
        debitInput.value = parseFloat(data.debit || 0).toFixed(2);
        debitLoaded = true;
        calculateAmountPaid(); // recalc safely
    })
    .catch(() => {
        debitInput.value = '0.00';
    });

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let supplierId = "{{ old('PurchaseFrom') }}";
    if (!supplierId) return;

    fetch("{{ route('Material_pay.getInvoicesBySupplier') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            supplier_id: supplierId,

            // ✅ ONLY checked rows
            checked_invoice: @json(old('checked_invoice', [])),

            // keep these for values
            old_amt: @json(old('amt', [])),
            old_extra: @json(old('extra', []))
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('invoiceBody').innerHTML = data.html || '';
        document.getElementById('invoiceSection').style.display = 'block';
    });

});
</script>

<script>
function handleAdjustmentMode() {

    const payType = document.getElementById('pay_type_select').value;
    const payMethod = document.getElementById('Pay_type');

    const otherFields = [
        'account_no',
        'cheque_no',
        'bnk_charge',
        'totalpayable',
        'narration'
    ];

    if (payType === 'adjustment') {

        // 🔒 HARD LOCK payment method
        if (payMethod) {
            payMethod.value = '';
            payMethod.setAttribute('disabled', true);
            payMethod.onchange = null;              // 🔥 REMOVE EVENT
            payMethod.style.pointerEvents = 'none'; // 🔥 BLOCK UI
        }

        // 🔒 Disable & clear others
        otherFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.value = '';
                el.setAttribute('disabled', true);
            }
        });

        $('#banktitle').hide();
        $('#bankvalue').hide();

        calculateAmountPaid();

    } else {

        // 🔓 UNLOCK payment method
        if (payMethod) {
            payMethod.removeAttribute('disabled');
            payMethod.style.pointerEvents = '';
            payMethod.onchange = check_type; // restore handler
        }

        otherFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.removeAttribute('disabled');
        });
    }
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    handleAdjustmentMode();
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    calculateTotalPayable();
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    check_type();          // show bank charge if e-Payment
    calculateTotalPayable();
});
</script>




