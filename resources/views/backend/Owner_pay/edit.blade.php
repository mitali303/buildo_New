@extends('backend.partials.master')

@section('title', !empty($invoice) ? 'Edit Owner Payment' : 'Create Owner Payment')
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
        <h1 class="h3 mb-3">{{ 'Create Owner Payment' }}</h1>

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
                <form action="{{ route('Owner_Pay.update', $payment->ID) }}" method="POST">
                    @csrf
                   
                    @method('PUT')
                <input type="hidden" name="Pid" value="{{ $invoice->ID ?? '' }}">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Date</label>
                            <input type="text" name="Date"
                                class="form-control"
                                value="{{ \Carbon\Carbon::parse($payment->Date)->format('d-m-Y') }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Scheme</label>
                            <input type="text"
                                class="form-control"
                                value="{{ $scheme->Name }}"
                                readonly>
                            <input type="hidden" name="scheme" value="{{ $scheme->ID }}">
                        </div>

                        <div class="col-md-4">
                            <label>Total Amount</label>
                            <input type="text"
                                class="form-control"
                                value="{{ number_format($totalAmount,2) }}"
                                readonly>
                        </div>
                    </div>

                    {{-- Summary Section --}}
                    <div class="row mb-3">
                        
                        <div class="col-md-4">
                            <label>Paid Amount</label>
                            <input type="text"
                                class="form-control"
                                value="{{ number_format($paidAmount,2) }}"
                                readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Pending Amount</label>
                            <input type="text"
                                id="pendingAmt"
                                class="form-control"
                                value="{{ number_format($pendingAmount,2) }}"
                                readonly>
                        </div>
                    </div>
                    <hr>

                    <h5 class="mb-3">Owner Detail</h5>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="5%">Select</th>
                                <th>Title</th>
                                <th>Total Amount</th>
                                <th>Pending Amount</th>
                                <th>Payable Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($ownerDetails as $index => $row)
                            <tr>

                                {{-- Checkbox --}}
                                <td>
                                    @if($row->pending > 0)
                                    <input type="checkbox"
                                        class="owner-check"
                                        data-index="{{ $index }}"
                                        {{ $row->selected ? 'checked' : '' }}>

                                    @endif
                                </td>

                                {{-- Title --}}
                                <td>
                                    {{ $row->title }}
                                    <input type="hidden"
                                        name="details[{{ $index }}][owner_id]"
                                        value="{{ $row->id ?? $row->ID }}">
                                        <input type="hidden"
                                        name="details[{{ $index }}][title]"
                                        value="{{ $row->title }}">
                                </td>

                                {{-- Total --}}
                                <td>
                                    {{ number_format($row->amt,2) }}
                                </td>

                                {{-- Pending --}}
                                <td>
                                    {{ number_format($row->pending,2) }}
                                    <input type="hidden"
                                        id="pending_{{ $index }}"
                                        value="{{ $row->pending }}">
                                </td>

                                {{-- Payable --}}
                                <td>
                                    @if($row->pending > 0)
                                    <input type="number"
                                    name="details[{{ $index }}][amount]"
                                    class="form-control pay-input"
                                    id="pay_{{ $index }}"
                                    data-index="{{ $index }}"
                                    value="{{ $row->edit_amount }}"
                                    max="{{ $row->pending }}"
                                    step="0.01"
                                    {{ $row->selected ? '' : 'disabled' }}>

                                    @else
                                    <span class="text-success">Paid</span>
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <br>

                    <table id="pmttble" class="table table-bordered table-hover" style="width:95%;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Payment Method</th>
                            <th style="width: 15%;">Account No.</th>
                            <!-- <th style="width: 15%;">Balance</th> -->
                            <th style="width: 12%;">Total Amount</th>
                            <th style="width: 15%;">Cheque No / Transaction ID</th>
                            <!-- <th style="width: 12%; display:none;" id="banktitle">Bank Charges</th> -->
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
                                        {{ old('Pay_type', $payment->payment_method) == 'cash' ? 'selected' : '' }}>
                                        Cash
                                    </option>

                                    <option value="cheque"
                                        {{ old('Pay_type', $payment->payment_method) == 'cheque' ? 'selected' : '' }}>
                                        Cheque
                                    </option>

                                    <option value="e-Payment"
                                        {{ old('Pay_type', $payment->payment_method) == 'e-Payment' ? 'selected' : '' }}>
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
                            <!-- <td>
                                <input type="text" readonly class="form-control" id="balanceamt" name="balanceamt">
                                @error('balanceamt')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td> -->

                            {{-- Amount Paid --}}
                            <td>
                                <input type="text" class="form-control" name="amount_pay" id="amount_pay"  value="{{ old('amount_pay', $payment->amt_pay) }}" readonly>
                                <input type="hidden" name="amount_pay_old" id="amount_pay_old" >
                                @error('amount_pay')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Cheque / Transaction No --}}
                            <td>
                                <input type="text" class="form-control" name="cheque_no" id="cheque_no" value="{{ old('cheque_no', $payment->cheque_no) }}">
                                @error('cheque_no')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Bank Charges --}}
                            <!-- <td id="bankvalue" style="display:none;">
                                <input type="text" class="form-control" name="bnk_charge" id="bnk_charge" value="{{ old('bnk_charge') }}" oninput="calculatePayable()">
                                @error('bnk_charge')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td> -->

                            {{-- Payable Amount --}}
                            <td>
                                <input type="text" readonly class="form-control" name="payable" id="payable" value="{{ old('payable', $payment->total_pay) }}">
                                @error('payable')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </td>

                            {{-- Narration --}}
                            <td>
                                <textarea class="form-control" name="narration" id="narration">{{ old('narration', $payment->narration) }}</textarea>
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
                            <a href="{{ route('Owner_Pay') }}" class="btn btn-secondary">Cancel</a>
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

    // ❌ if no .amt fields exist, do nothing
    if (document.querySelectorAll('.amt').length === 0) {
        return;
    }

    let total = 0;

    document.querySelectorAll('.amt').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

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
            // getBalance();
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

function calculatePayable() {

    let amount = parseFloat($("#amount_pay").val()) || 0;
  
    let bank   = parseFloat($("#bnk_charge").val()) || 0;

    let payable = (amount + bank);
    
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

    let oldPayType = "{{ old('Pay_type', $payment->payment_method) }}";
    let oldAccount = "{{ old('account_no', $payment->account_no) }}";

    if (oldPayType) {

        $("#Pay_type").val(oldPayType);

        $.ajax({
            url: "{{ route('ajax.getAccountList') }}",
            type: "POST",
            data: {
                pay_method: oldPayType,
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {

                $("#Ac").html(response.html);

                if (oldAccount) {
                    $("#account_no").val(oldAccount);
                }
            }
        });
    }

});

</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector(
        'form[action="{{ route('Site_work_pay.store') }}"]'
    );

    if (!form) return;

    form.addEventListener('submit', function (e) {

        // force rebuild before submit
        buildScopeSelection();

        const count = document.getElementById('count').value;

        if (!count || parseInt(count, 10) === 0) {
            e.preventDefault();
            e.stopImmediatePropagation(); // 🔴 important

            alert('Please select at least one scope from the table.');

            document.getElementById('materialTable')
                ?.scrollIntoView({ behavior: 'smooth', block: 'center' });

            return false;
        }
    }, true); // 🔴 CAPTURE PHASE
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const oldCheckIds = "{{ old('checkids') }}";

    if (!oldCheckIds) return;

    const selected = oldCheckIds.split(',');

    document.querySelectorAll('.invoice-row').forEach(row => {

        const checkbox = row.querySelector('.select-row');
        if (!checkbox) return;

        if (selected.includes(checkbox.value)) {
            checkbox.checked = true;

            // enable amount field
            const amt = row.querySelector('.amt');
            if (amt) amt.disabled = false;
        }
    });

    // rebuild totals after restoring checks
    buildScopeSelection();
    updateTotal();
    calculatePayable();
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    let oldType = "{{ old('Pay_type') }}";

    if (oldType) {
        $("#Pay_type").val(oldType);

        // show / hide bank column properly
        check_type();

        // restore payable
        calculatePayable();
    }
});
</script>
<script>
function validateAmountPay(el) {

    let pending = parseFloat(document.getElementById("Total").value) || 0;
    let amount  = parseFloat(el.value) || 0;

    if (amount > pending) {

        alert("Amount Paid cannot be greater than Pending Amount!");

        // force reset to pending
        el.value = pending;

        calculatePayable();
        return;
    }

    calculatePayable();
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const amountPayField = document.getElementById("amount_pay");

    // Enable input when checkbox checked
    document.querySelectorAll('.owner-check').forEach(checkbox => {

        checkbox.addEventListener('change', function () {

            let index = this.dataset.index;
            let input = document.getElementById('pay_' + index);

            if (this.checked) {
                input.disabled = false;
                input.focus();
            } else {
                input.disabled = true;
                input.value = '';
            }

            calculateTotal();
        });
    });

    // Validate + Calculate
    document.querySelectorAll('.pay-input').forEach(input => {

        input.addEventListener('input', function () {

            let index = this.dataset.index;
            let max = parseFloat(document.getElementById('pending_' + index).value);
            let val = parseFloat(this.value) || 0;

            if (val > max) {
                alert("Cannot pay more than pending amount!");
                this.value = max;
            }

            calculateTotal();
        });
    });

    function calculateTotal() {

        let total = 0;

        document.querySelectorAll('.pay-input').forEach(input => {
            if (!input.disabled) {
                total += parseFloat(input.value) || 0;
            }
        });

        // ✅ Bind directly into Total Amount field
        amountPayField.value = total.toFixed(2);

        // ✅ Also recalculate payable (amount + bank charge)
        calculatePayable();
    }

});
</script>


