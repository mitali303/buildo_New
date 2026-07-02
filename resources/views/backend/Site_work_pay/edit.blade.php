@extends('backend.partials.master')

@section('title', !empty($invoice) ? 'Edit Purchase Invoice' : 'Create Purchase Invoice')
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
        <h1 class="h3 mb-3">{{ 'Edit Site Work Payment ' }}</h1>

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
                <form action="{{ route('Site_work_pay.update', $payment->ID) }}" method="POST">
                    @csrf
                   @method('PUT')
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
                            <label class="form-label">Contractor <span class="text-danger">*</span></label>

                            <!-- Hidden field so value is submitted -->
                            <input type="hidden" name="purchasefrom"
                                value="{{ old('purchasefrom', $invoice->ContractorID ?? '') }}">

                            <select class="form-control" disabled>
                                <option value="">Select</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->ID }}"
                                        {{ old('purchasefrom', $invoice->ContractorID ?? '') == $vendor->ID ? 'selected' : '' }}>
                                        {{ $vendor->Name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('purchasefrom') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        
                        <div class="col-md-4" id="worktype-container">
                            <label class="form-label">Work Type <span class="text-danger">*</span></label>

                            <input type="hidden" name="worktype" value="{{ old('worktype', $invoice->worktype ?? '') }}">

                                <select class="form-control" disabled>
                                    <option value="">Select</option>
                                    @foreach($worktypes as $type)
                                        <option value="{{ $type->worktype }}"
                                            {{ old('worktype', $invoice->worktype ?? '') == $type->worktype ? 'selected' : '' }}>
                                            {{ $type->worktype }}
                                        </option>
                                    @endforeach
                                </select>

                            @error('worktype')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4" id="scheme">
                            <label class="form-label">Scheme <span class="text-danger">*</span></label>

                            <input type="hidden" name="Destination" value="{{ old('Destination', $invoice->SiteLocation ?? '') }}">

                                <select class="form-control" disabled>
                                    <option value="">Select</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->ID }}"
                                            {{ old('Destination', $invoice->SiteLocation ?? '') == $scheme->ID ? 'selected' : '' }}>
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                                </select>
                            @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        
                    </div>

                    <div class="row">
                       <div class="row mt-4">
                            <div class="col-md-12" style="overflow-x: auto; width: 100%;">
                               <table class="table table-bordered table-striped" id="materialTable" style="font-size:13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th></th>
                                        <th style="min-width: 180px;">Scope Details</th>
                                        <th style="min-width: 90px;">Total Payable</th>
                                        <th style="min-width: 90px;">Amount</th>
                                        <th style="min-width: 120px;">Balance</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $rowCount = old('scope', $items ?? []) ? count(old('scope', $items ?? [])) : 1;
                                    @endphp

                                    @foreach ($rows as $row)
                                        @include('backend.Site_work_pay.row', ['row' => $row])
                                    @endforeach

                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div> 
                    <input type="hidden" name="checkids" id="checkids">
                    <input type="hidden" name="count" id="count">
                    <div class="row">
                        <div class="col-md-3">
                                <label>Total:<span class="text-danger">*</span></label>
                                <input name="Total" id="Total"
                                    type="text"
                                    class="form-control"
                                    readonly
                                    style="background-color:#f5f5f5;">
                            @error('Total') <small class="text-danger">{{ $message }}</small> @enderror
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
                            <th style="width: 10%;">GST Amount</th>
                            <th style="width: 10%;">TDS Amount</th>
                            <th style="width: 12%; display:none;" id="banktitle">Bank Charges</th>
                            <th style="width: 10%;">Payable Amount</th>
                            <th style="width: 20%;">Narration</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>

                        @php
                            $selectedPayType = old('Pay_type', $payment->payment_method ?? '');
                        @endphp
                            {{-- Payment Method --}}
                            <td>
                                <select id="Pay_type" name="Pay_type" class="form-control" onchange="check_type()">
                                    <option value="">SELECT</option>

                                    <option value="cash" {{ $selectedPayType === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="cheque" {{ $selectedPayType === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="e-Payment" {{ $selectedPayType === 'e-Payment' ? 'selected' : '' }}>E-Payment</option>
                                </select>

                                @error('Pay_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>


                            {{-- Account No --}}
                            <td>
                                <input type="hidden" id="old_account_no" value="{{ old('account_no', $payment->account_no ?? '') }}">

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
                                <input type="text" readonly class="form-control" id="balanceamt" name="balanceamt" value="{{ old('balanceamt', $old->balanceamt ?? '') }}">
                                @error('balanceamt')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Amount Paid --}}
                            <td>
                                <input type="text" class="form-control" name="amount_pay" id="amount_pay" oninput="calculatePayable()" readonly>
                                <input type="hidden" name="amount_pay_old" id="amount_pay_old" oninput="calculatePayable()">
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

                            {{-- GST Amount --}}
                            <td>
                                <input type="text" class="form-control" name="tax" id="tax" value="{{ old('tax', $remainingTax ?? '') }}" oninput="calculatePayable()">
                                <input type="hidden" name="taxper" id="taxper" oninput="calculatePayable()">
                                @error('tax')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- TDS Amount --}}
                            <td>
                                <input type="text" class="form-control" name="tds" id="tds" value="{{ old('tds', $remainingTds ?? '') }}" oninput="calculatePayable()">
                                <input type="hidden" name="tdsper" id="tdsper" oninput="calculatePayable()">
                                @error('tds')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Bank Charges --}}
                            <td id="bankvalue" style="display:none;">
                                <input type="text" class="form-control" name="bnk_charge" id="bnk_charge" oninput="calculatePayable()"  value="{{ old('bnk_charge', $payment->bankcharge ?? '') }}">
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
                                {{ 'Update' }}
                            </button>
                            <a href="{{ route('Site_work_pay') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@endsection
<script>
    document.addEventListener("DOMContentLoaded", function () {

    let payType = "{{ old('Pay_type', $payment->payment_method ?? '') }}";

    if (payType === "e-Payment") {
        $('#banktitle').show();
        $('#bankvalue').show();
    }
});


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
        fetch('{{ route('Site_work_pay.addRow') }}', {
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
// Helper: load account list and allow server to mark selected
function loadAccounts(pay_method, selectedId = "") {
    return $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: pay_method,
            selected_id: selectedId,
            _token: "{{ csrf_token() }}"
        }
    }).done(function(response) {
        $("#Ac").html(response.html);

        // optionally trigger change for any listeners
        $("#account_no").trigger("change");

        // update balance once account is set
        setTimeout(function(){ getBalance(); }, 50);

        // debug: show options in console (comment out later)
        console.log("Account options:", Array.from($("#account_no option")).map(o => ({val:o.value, txt:o.textContent})));
    });
}

// Modified check_type to use loadAccounts
function check_type() {
    var pay_method = $('#Pay_type').val();

    // pass empty selectedId because usually on user change we don't want preselect
    loadAccounts(pay_method, "");

    // rest of existing UI logic for cheque/bank charges
    if (pay_method === "e-Payment") {
        $('#banktitle').show();
        $('#bankvalue').show();
        $('#cheque_no').removeAttr("readonly");
    } else {
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
document.addEventListener("DOMContentLoaded", function () {
    // stored in hidden field by Blade
    var savedAccount = $("#old_account_no").val() || ""; // could be ID or ACNo
    var selectedPayType = "{{ old('Pay_type', $payment->payment_method ?? '') }}";

    if (selectedPayType) {
        $("#Pay_type").val(selectedPayType);

        // Pass savedAccount so server will include selected="selected" in option
        loadAccounts(selectedPayType, savedAccount);
    } else if (savedAccount) {
        // fallback: if pay type empty but account exists, attempt to load for a sensible default
        // (not usually needed)
        loadAccounts("", savedAccount);
    }
});


function getBalance() {

    let accId = $("#account_no").val();
    let payId = "{{ $invoice->ID ?? '' }}";  // or '' for create page
    let paymentId = "{{ $payment->ID ?? '' }}";  // or '' for create page
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
            paymentId: paymentId,
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

    let amount = parseFloat($("#amount_pay").val()) || 0;
    let gst    = parseFloat($("#tax").val()) || 0;
    let tds    = parseFloat($("#tds").val()) || 0;
    let bank   = parseFloat($("#bnk_charge").val()) || 0;
    // alert(tds);

    // Formula: Amount Paid + GST - TDS
    let payable = (amount + gst + bank - tds);

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

    let oldPayType = "{{ old('Pay_type') }}";
    let oldAccount = "{{ old('account_no') }}";

    // If validation error occurred and Pay_type exists
    if (oldPayType) {

        // Set old selected Pay_type
        $("#Pay_type").val(oldPayType);

        // Run AJAX to load account list for selected Pay_type
        $.ajax({
            url: "{{ route('ajax.getAccountList') }}",
            type: "POST",
            data: {
                pay_method: oldPayType,
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {

                $("#Ac").html(response.html);

                let oldAcc = $("#old_account_no").val();
                if (oldAcc) {
                    $("#account_no").val(oldAcc);
                }

                getBalance();
            }

        });
    }
});
document.addEventListener("DOMContentLoaded", function () {
    buildScopeSelection(); // <-- populate on load
});
</script>

