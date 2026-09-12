@extends('backend.partials.master')

@section('title', 'Create Labour Payment')
<style>
@media (max-width: 768px) {

    /* Labour Work Entry Table */
    .table-responsive table:first-child th:first-child,
    .table-responsive table:first-child td:first-child {
        white-space: nowrap !important;
        min-width: 100px !important;
        width: 100px !important;
    }

}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">Create Labour Payment</h1>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @php
                $firstWork = $works->first();
            @endphp
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('Labour_work_pay.store') }}" method="POST">
                        @csrf
                        {{-- DATE --}}
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <label>Date *</label>
                                    <input type="text" name="Date" id="datepicker" class="form-control" value="{{ \Carbon\Carbon::now()->format('d-m-Y') }}">
                            </div>
                            {{-- AGENCY --}}
                            <div class="col-md-4 mb-3">
                                <label>Agency</label>
                                <input type="text" class="form-control"value="{{ $firstWork->agency->Name ?? '' }}" readonly>
                                    <input type="hidden" name="AgencyID" value="{{ $firstWork->Agency_ID }}">
                            </div>
                            {{-- SCHEME --}}
                            <div class="col-md-4">
                                <label>Scheme</label>
                                <input type="text" class="form-control" value="{{ $firstWork->scheme->Name ?? '' }}" readonly>
                            </div>
                        </div>
                        {{-- TOTAL PENDING --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Total Pending</label>
                                <input type="text" id="Total" class="form-control" value="{{ number_format($totalPending,2) }}" readonly>
                            </div>
                        </div>
                        {{-- WORK ENTRY TABLE --}}
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Grand Total</th>
                                        <th>Pending</th>
                                        <th>Pay Now</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($works as $work)
                                        @if($work->pending > 0)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($work->Date)->format('d-m-Y') }}</td>
                                            <td>{{ number_format($work->gtotal,2) }}</td>
                                            <td>{{ number_format($work->pending,2) }}</td>
                                            <td>
                                                <input type="number" name="payments[{{ $work->ID }}]" class="form-control pay-input"
                                                    max="{{ $work->pending }}" step="0.01" value="0" oninput="calculateTotal()">
                                            </td>
                                        </tr>
                                        @endif

                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        {{-- PAYMENT SECTION --}}
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Payment Method</th>
                                        <th>Account</th>
                                        <th>Balance</th>
                                        <th>Total Amount</th>
                                        <th>Cheque / Txn</th>
                                        <th id="banktitle" style="display:none;">Bank Charges</th>
                                        <th>Payable</th>
                                        <th>Narration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select id="Pay_type" name="Pay_type"
                                                    class="form-control"
                                                    onchange="check_type()">
                                                <option value="">Select</option>
                                                <option value="cash">Cash</option>
                                                <option value="cheque">Cheque</option>
                                                <option value="e-Payment">E-Payment</option>
                                            </select>
                                        </td>

                                        <td>
                                            <div id="Ac">
                                                <select id="account_no" name="account_no"
                                                        class="form-control">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" id="balanceamt"  class="form-control" readonly>
                                        </td>
                                        <td>
                                            <input type="text" id="amount_pay" name="amount_pay" class="form-control" readonly>
                                        </td>
                                        <td>
                                            <input type="text"  name="cheque_no" id="cheque_no" class="form-control">
                                        </td>
                                        <td id="bankvalue" style="display:none;">
                                            <input type="number" name="bnk_charge" id="bnk_charge" class="form-control"
                                                value="0" oninput="calculatePayable()">
                                        </td>
                                        <td>
                                            <input type="text" id="payable" name="payable" class="form-control" readonly>
                                        </td>
                                        <td>
                                            <textarea name="narration" class="form-control"></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Create</button>
                            <a href="{{ route('Labour_work_pay') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

    </div>
</main>
@endsection
<script>
    $('#datatables-buttons').DataTable({
    responsive: false,
    scrollX: true,
    autoWidth: false,
    lengthChange: true,
    buttons: ['copy', 'print']
});

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

                // Inject the account select dropdown
                $("#Ac").html(response.html);

                // Set old selected account once dropdown is loaded
                if (oldAccount) {
                    $("#account_no").val(oldAccount);
                }

                // finally re-calc balance after selecting old account
                getBalance();
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
function calculateTotal() {
    let total = 0;

    document.querySelectorAll('.pay-input').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('amount_pay').value = total.toFixed(2);
    calculatePayable();
}
</script>
