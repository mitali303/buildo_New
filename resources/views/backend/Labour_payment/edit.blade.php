@extends('backend.partials.master')

@section('title','Edit Labour Payment')

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

    <h1 class="h3 mb-3">Edit Labour Payment</h1>

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

            <form action="{{ route('Labour_work_pay.update',$payment->ID) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- DATE / AGENCY / SCHEME --}}
                <div class="row mb-3">

                    <div class="col-md-4">
                        <label>Date *</label>
                        <input type="text"
                               name="Date"
                               id="datepicker"
                               class="form-control"
                               value="{{ \Carbon\Carbon::parse($payment->Date)->format('d-m-Y') }}">
                    </div>

                    <div class="col-md-4">
                        <label>Agency</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $firstWork->agency->Name ?? '' }}"
                               readonly>
                    </div>

                    <div class="col-md-4">
                        <label>Scheme</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $firstWork->scheme->Name ?? '' }}"
                               readonly>
                    </div>

                </div>

                {{-- ENTRY WISE WORK TABLE --}}
                <h5 class="mb-3">Work Allocation</h5>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Grand Total</th>
                            <th>Pending</th>
                            <th>Pay Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($works as $work)
                            @php
                                $detail = $details->firstWhere('labourworkID',$work->ID);
                                $allocated = $detail->amt_pay ?? 0;
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($work->Date)->format('d-m-Y') }}</td>
                                <td>{{ number_format($work->gtotal,2) }}</td>
                                <td>{{ number_format($work->pending,2) }}</td>
                                <td>
                                    <input type="number"
                                           name="payments[{{ $work->ID }}]"
                                           value="{{ $allocated }}"
                                           class="form-control pay-input"
                                           step="0.01"
                                           oninput="calculateTotal()">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr>

                {{-- PAYMENT SECTION --}}
                <h5 class="mb-3">Payment Details</h5>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th>Account</th>
                            <th>Total Amount</th>
                            <th>Cheque / Txn</th>
                            <th>Bank Charge</th>
                            <th>Payable</th>
                            <th>Narration</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>

                            <td>
                                <select name="Pay_type" id="Pay_type" class="form-control">
                                    <option value="cash" {{ $payment->payment_method=='cash'?'selected':'' }}>Cash</option>
                                    <option value="cheque" {{ $payment->payment_method=='cheque'?'selected':'' }}>Cheque</option>
                                    <option value="e-Payment" {{ $payment->payment_method=='e-Payment'?'selected':'' }}>E-Payment</option>
                                </select>
                            </td>

                            <td>
                                <input type="text"
                                       name="account_no"
                                       value="{{ $payment->account_no }}"
                                       class="form-control">
                            </td>

                            <td>
                                <input type="text"
                                       name="amount_pay"
                                       id="amount_pay"
                                       value="{{ $payment->amt_pay }}"
                                       class="form-control"
                                       readonly>
                            </td>

                            <td>
                                <input type="text"
                                       name="cheque_no"
                                       value="{{ $payment->cheque_no }}"
                                       class="form-control">
                            </td>

                            <td>
                                <input type="number"
                                       name="bnk_charge"
                                       id="bnk_charge"
                                       value="{{ $payment->bankcharge }}"
                                       class="form-control"
                                       oninput="calculatePayable()">
                            </td>

                            <td>
                                <input type="text"
                                       name="payable"
                                       id="payable"
                                       value="{{ $payment->Payable }}"
                                       class="form-control"
                                       readonly>
                            </td>

                            <td>
                                <textarea name="narration"
                                          class="form-control">{{ $payment->narration }}</textarea>
                            </td>

                        </tr>
                    </tbody>
                </table>

                <div class="mt-3">
                    <button class="btn btn-primary">Update</button>
                    <a href="{{ route('Labour_work_pay') }}" class="btn btn-secondary">Cancel</a>
                </div>

            </form>

        </div>
    </div>

</div>
</main>

<script>
function calculateTotal() {

    let total = 0;

    document.querySelectorAll('.pay-input').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });

    document.getElementById('amount_pay').value = total.toFixed(2);
    calculatePayable();
}

function calculatePayable() {

    let amount = parseFloat(document.getElementById('amount_pay').value) || 0;
    let bank   = parseFloat(document.getElementById('bnk_charge').value) || 0;

    document.getElementById('payable').value = (amount + bank).toFixed(2);
}

document.addEventListener("DOMContentLoaded", function(){
    calculateTotal();
});
</script>


{{-- JS --}}
<script>

      document.addEventListener("DOMContentLoaded", function () {

    let payType = "{{ old('Pay_type', $payment->payment_method ?? '') }}";

    if (payType === "e-Payment") {
        $('#banktitle').show();
        $('#bankvalue').show();
    }
});

function calcPayable() {

    let amt     = parseFloat($("#amount_pay").val()) || 0;
    let bank    = parseFloat($("#bnk_charge").val()) || 0;
    let balance = parseFloat($("#balanceamt").val()) || 0;

    // 🚨 Prevent paying more than balance
    if (balance > 0 && amt > balance) {
        alert("Amount cannot be greater than available balance!");
        amt = balance;
        $("#amount_pay").val(balance.toFixed(2));
    }

    let payable = amt + bank;

    $("#payable").val(payable.toFixed(2));
}


function toggleBank(){
    let type = document.getElementById('Pay_type').value;
    let bank = document.getElementById('bnk_charge');

    if(type==='e-Payment'){
        bank.removeAttribute('readonly');
    }else{
        bank.value='';
        bank.setAttribute('readonly',true);
        calcPayable();
    }
}

document.addEventListener("DOMContentLoaded",function(){
    calcPayable();
    toggleBank();
});


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

            let fetchedBalance = parseFloat(response.balance) || 0;
            let oldPaid        = parseFloat($("#old_amount_pay").val()) || 0;

            // ✅ ADD BACK old payment for edit mode
            let effectiveBalance = fetchedBalance + oldPaid;

            $("#balanceamt").val(effectiveBalance.toFixed(2));

            calcPayable();
        }
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
</script>

@endsection
