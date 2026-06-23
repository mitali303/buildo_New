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
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif



        <div class="card">
            <div class="card-body">
                     <form action="{{ !empty($payment)
                    ? route('Return_pay.update', ['id' => $payment->ID])
                    : route('Return_pay.store') }}"
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
                                    name="Date" readonly
                                    id="datepicker"
                                    class="form-control @error('Date') is-invalid @enderror"
                                    placeholder="Select date"
                                    value="{{ \Carbon\Carbon::now()->format('d-m-Y') }}">
                            </div>

                            @error('Date') 
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
                                            {{ old('Destination',  $payment->schemeID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4" id="exp-type-container">
                            <label class="form-label">
                                Expenses Type <span class="text-danger">*</span>
                            </label>

                            <select name="Exp_type" id="Exp_type"
                                    class="form-control choices-single-Exp_type"
                                    data-placeholder="Select"
                                    onchange="GetExpenseInputField()"
                                    >

                                <option value="">Select</option>
                                <option value="OTHER">ADD NEW</option>

                                @php
                                    $fixedTypes = [
                                        'Contractor',
                                        'Matrial Transfer',
                                        'Return/Rejected Matrial',
                                        'Salary',
                                        'Advanced Salary'
                                    ];
                                    $selectedValue = old('Exp_type', $payment->IncomeType  ?? '');
                                @endphp

                                @foreach($fixedTypes as $type)
                                    <option value="{{ $type }}"
                                        {{ $selectedValue == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach

                                @foreach($expences as $expence)
                                    @if(!in_array($expence->IncomeType, $fixedTypes))
                                        <option value="{{ $expence->IncomeType }}"
                                            {{ $selectedValue == $expence->IncomeType ? 'selected' : '' }}>
                                            {{ $expence->IncomeType }}
                                        </option>
                                    @endif
                                @endforeach

                            </select>

                            @error('Exp_type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 d-none" id="contractor-div">
                            <label class="form-label">Contractor <span class="text-danger">*</span></label>

                            <select name="contractor" id="contractor"
                                    class="form-control choices-single-contractor"
                                    data-placeholder="Select contractor"
                                    >
                                <option value="">Select</option>
                                    @foreach($contractors as $contractor)
                                        <option value="{{ $contractor->ID }}"
                                            {{ old('contractor',  $payment->vendor ?? '') == $contractor->ID ? 'selected' : '' }}>
                                            {{ $contractor->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('contractor') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        

                        <div class="col-md-4 d-none" id="invoice-container">
                            <label class="form-label">Invoice No <span class="text-danger">*</span></label>

                            <select name="invoice_id" id="invoice_id"
                                    class="form-control choices-single-invoice"
                                    data-placeholder="Select Invoice">
                                <option value="">Select</option>
                            </select>

                            @error('invoice_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4" id="supplier-div">
                            <label class="form-label">Supplier <span class="text-danger">*</span></label>

                            <select name="supplier" id="supplier"
                                    class="form-control choices-single-supplier"
                                    data-placeholder="Select supplier"
                                    >
                                <option value="">Select</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->ID }}"
                                            {{ old('supplier',  $payment->vendor ?? '') == $supplier->ID ? 'selected' : '' }}>
                                            {{ $supplier->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('supplier') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4 d-none" id="order-container">
                            <label class="form-label">Order No <span class="text-danger">*</span></label>

                            <select name="order_id" id="order_id"
                                    class="form-control choices-single-order"
                                    data-placeholder="Select Order">
                                <option value="">Select</option>
                            </select>

                            @error('order_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 d-none" id="pending_amount">
                            <label class="form-label">Pending Amount</label>
                            <input type="text" id="pending"
                                class="form-control"
                                name="pending"
                                readonly>
                        </div>

                        <div class="col-md-4" id="employee-container">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>

                            <select name="employee" id="employee"
                                    class="form-control choices-single-employee"
                                    data-placeholder="Select employee">
                                <option value="">Select</option>
                                @foreach($employes as $employee)
                                    <option value="{{ $employee->ID }}"
                                        {{ old('employee', $payment->empID ?? '') == $employee->ID ? 'selected' : '' }}>
                                        {{ $employee->Name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('employee')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                        <div class="col-md-4 d-none" id="salary_type_div">
                            <label class="form-label">Salary Type</label>
                            <input type="text" id="salary_type"
                                class="form-control"
                                name="salary_type"
                                readonly>
                        </div>

                        <div class="col-md-4 d-none" id="salary_amount_div">
                            <label class="form-label">Salary Amount</label>
                            <input type="text" id="salary_amount"
                                class="form-control"
                                name="salary_amount"
                                readonly>
                        </div>

                        <div class="mb-3 col-md-4 d-none" id="fdate_div">
                            <label class="form-label">From Date <span class="text-danger">*</span></label>

                            <div class="input-group flatpickr-container">
                                <input type="text"
                                    name="FDate" readonly
                                    id="start_datepicker"
                                    class="form-control @error('FDate') is-invalid @enderror"
                                    placeholder="Select date"
                                    value="{{ old('FDate', isset($payment->fromdate)
                                        ? \Carbon\Carbon::parse($payment->fromdate)->format('d-m-Y')
                                        : '') }}"
                                    >
                            </div>

                            @error('FDate') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4 d-none" id="tdate_div">
                            <label class="form-label">To Date <span class="text-danger">*</span></label>

                            <div class="input-group flatpickr-container">
                                <input type="text"
                                    name="TDate" readonly
                                    id="to_datepicker"
                                    class="form-control @error('TDate') is-invalid @enderror"
                                    placeholder="Select date"
                                    value="{{ old('TDate', isset($payment->todate)
                                        ? \Carbon\Carbon::parse($payment->todate)->format('d-m-Y')
                                        : '') }}"
                                    >
                            </div>

                            @error('TDate') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>

                        <div class="col-md-4 d-none" id="month_div">
                            <label class="form-label">Month <span class="text-danger">*</span></label>
                            <select name="month" class="form-control choices-single-month">
                                <option value="">Select Month</option>
                                @foreach([
                                    '01'=>'January','02'=>'February','03'=>'March','04'=>'April',
                                    '05'=>'May','06'=>'June','07'=>'July','08'=>'August',
                                    '09'=>'September','10'=>'October','11'=>'November','12'=>'December'
                                ] as $key => $month)
                                    <option value="{{ $key }}"
                                         {{ old('month', $payment->month ?? '') == $key ? 'selected' : '' }}>
                                        {{ $month }}
                                    </option>
                                @endforeach
                            </select>
                            @error('month')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                        </div>

                        <div class="col-md-4 d-none" id="year_div">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <select name="year" class="form-control choices-single-year">
                                <option value="">Select Year</option>

                                @for($y = 2015; $y <= 2070; $y++)
                                    <option value="{{ $y }}"
                                        {{ old('year', $payment->year ?? '') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor

                            </select>
                            @error('year')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                        </div>

                        {{-- Description --}}
                        <div class="col-md-4">
                            <label class="form-label">Description</label>
                            <textarea name="Description" rows="2"
                                    class="form-control @error('Description') is-invalid @enderror">{{ old('Description', $staff->Description ?? '') }}</textarea>
                                    @error('Description') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                    </div>
                    <br>

                    <div class="col-md-12 d-none" id="order-table-container">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Material</th>
                                    <th>Type</th>
                                    <th>Unit</th>
                                    <th>Qty</th>
                                    <th>Return Quantity</th>
                                    <th>Rate</th>
                                    <th>Disc %</th>
                                    <th>CGST</th>
                                    <th>SGST</th>
                                    <th>IGST</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="orderTableBody"></tbody>
                        </table>
                    </div>
                    <br>

                    <table id="pmttble" class="table table-bordered table-hover" style="width:95%;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Payment Method</th>
                            <th style="width: 15%;">Account No.</th>
                            <th style="width: 15%;" id="balance_th">Balance</th>
                            <th style="width: 12%;">Amount Paid</th>
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
                            <button type="submit" class="btn btn-primary">
                                {{ 'Create' }}
                            </button>
                            <a href="{{ route('Return_pay') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<!-- @if(empty($isEdit) && !old('Exp_type'))
    resetEmployeeSection();
@endif -->


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
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-destination"));
        new Choices(document.querySelector(".choices-single-contractor"));
        new Choices(document.querySelector(".choices-single-employee"));
        new Choices(document.querySelector(".choices-single-year"));
        new Choices(document.querySelector(".choices-single-Exp_type"));
        new Choices(document.querySelector(".choices-single-supplier"));
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

    // 🔹 get current payment type (radio)
const paymentType = document.querySelector(
    'input[name="paymenttype"]:checked'
)?.value;

// Reset everything first

$('#bankdetails').hide();
$('#bankvalue').hide();
$('#bankvaluedet').hide();

if (pay_method === "e-Payment") {
    $('#banktitle').show();
    $('#bankvalue').show();
    $('#cheque_no').removeAttr("readonly");

} else {

    // Cash / Cheque
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

    if (!oldPayType) return;

    // 1️⃣ Set Pay Type
    $("#Pay_type").val(oldPayType);

    // 2️⃣ Load account list via AJAX
    $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: oldPayType,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {

            // 3️⃣ Inject account dropdown
            $("#Ac").html(response.html);

            // 4️⃣ NOW account_no exists → set old value
            if (oldAccount) {
                $("#account_no").val(oldAccount);
            }

            // 5️⃣ Trigger balance after account is selected
            getBalance();
        }
    });

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
function GetExpenseInputField() {
    let value = $("#Exp_type").val();

    if (value === 'OTHER') {

        // 🔴 remove required from hidden select
        $("#Exp_type").prop('required', false);

        $("#exp-type-container").html(`
            <label class="form-label">
                Expenses Type <span class="text-danger">*</span>
            </label>
            <input type="text"
                   name="Exp_type"
                   class="form-control"
                   placeholder="Enter Expense Type"
                   >
        `);
    }
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // hide all dependent fields on load
    $('#salary_type_div, #salary_amount_div, #fdate_div, #tdate_div, #month_div, #year_div')
        .addClass('d-none');

    $('#employee').on('change', function () {

        let employeeId = $(this).val();

        // Reset if empty
        if (!employeeId) {
            $('#salary_type_div, #salary_amount_div, #fdate_div, #tdate_div, #month_div, #year_div')
                .addClass('d-none');

            $('#salary_type, #salary_amount').val('');
            return;
        }

        $.ajax({
            url: "{{ route('employee.salary.details') }}",
            type: "POST",
            data: {
                employee_id: employeeId,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {

    // 1️⃣ Fill salary data
    $('#salary_type').val(res.salary_type);
    $('#salary_amount').val(res.salary_amount);

    $('#salary_type_div, #salary_amount_div').removeClass('d-none');

    // 2️⃣ Toggle Monthly / Daily UI
    if (res.salary_type === 'Monthly') {

        $('#month_div, #year_div').removeClass('d-none');
        $('#fdate_div, #tdate_div').addClass('d-none');

        // 🔥 restore old month/year
        @if(old('month'))
            $('select[name="month"]').val("{{ old('month') }}");
        @endif

        @if(old('year'))
            $('select[name="year"]').val("{{ old('year') }}");
        @endif

    } else {

        $('#fdate_div, #tdate_div').removeClass('d-none');
        $('#month_div, #year_div').addClass('d-none');

        // 🔥 restore old dates
        @if(old('FDate'))
            $('input[name="FDate"]').val("{{ old('FDate') }}");
        @endif

        @if(old('TDate'))
            $('input[name="TDate"]').val("{{ old('TDate') }}");
        @endif
    }
}
,
            error: function () {
                alert('Failed to load employee salary details');
            }
        });
    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function resetEmployeeSection() {
        // hide employee selector
        $('#employee-container').addClass('d-none');

        // reset employee
        $('#employee').val('').trigger('change');

        // hide all dependent fields
        $('#salary_type_div, #salary_amount_div, #fdate_div, #tdate_div, #month_div, #year_div')
            .addClass('d-none');

        // clear salary values
        $('#salary_type, #salary_amount').val('');
    }

    function showEmployeeSection() {
        $('#employee-container').removeClass('d-none');
    }

    @if(empty($isEdit) && !old('Exp_type'))
        resetEmployeeSection();
    @endif

    // 🔹 EXPENSE TYPE CHANGE
    $('#Exp_type').on('change', function () {

        let expType = $(this).val();

        if (expType === 'Salary' || expType === 'Advanced Salary') {
            showEmployeeSection();
        } else {
            resetEmployeeSection();
        }
    });

    // 🔹 EDIT / OLD VALUE SUPPORT
    @if(old('Exp_type', $payment->Exp_type ?? '') === 'Salary'
        || old('Exp_type', $payment->Exp_type ?? '') === 'Advanced Salary')
        showEmployeeSection();
    @endif

});
</script>
<script>
function GetTitleInputField() {
    let value = $("#title").val();

    if (value === 'OTHER') {

        // 🔴 remove required from hidden select
        $("#title").prop('required', false);

        $("#title-container").html(`
            <label class="form-label">
                Title <span class="text-danger">*</span>
            </label>
            <input type="text"
                   name="title"
                   class="form-control"
                   placeholder="Enter Title"
                   required>
        `);
    }
}
</script>
@if(old('Exp_type') === 'Salary' || old('Exp_type') === 'Advanced Salary')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const oldEmployee = "{{ old('employee') }}";

    if (oldEmployee) {

        // 1️⃣ Show employee section
        $('#employee-container').removeClass('d-none');

        // 2️⃣ Set old employee value
        $('#employee').val(oldEmployee);

        // 3️⃣ TRIGGER CHANGE (THIS IS THE KEY 🔑)
        $('#employee').trigger('change');

        // 4️⃣ Restore Month / Year (after AJAX runs)
        setTimeout(function () {

            @if(old('month'))
                $('select[name="month"]').val("{{ old('month') }}");
            @endif

            @if(old('year'))
                $('select[name="year"]').val("{{ old('year') }}");
            @endif

            @if(old('FDate'))
                $('input[name="FDate"]').val("{{ old('FDate') }}");
            @endif

            @if(old('TDate'))
                $('input[name="TDate"]').val("{{ old('TDate') }}");
            @endif

        }, 300); // wait for salary AJAX
    }

});
</script>
@endif
@if(isset($payment) && in_array($payment->Exp_type, ['Salary', 'Advanced Salary']))
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Ensure employee section is visible
    $('#employee-container').removeClass('d-none');

    const empId = "{{ $payment->empID }}";

    if (empId) {
        // Set employee value (again, safely)
        $('#employee').val(empId);

        // 🔥 FORCE salary AJAX to run
        $('#employee').trigger('change');
    }

});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {

    let invoiceChoices;

    $('#contractor').on('change', function () {

        let contractorId = $(this).val();

        // reset invoice dropdown
        $('#invoice_id').html('<option value="">Select</option>');
        $('#invoice-container').addClass('d-none');

        if (!contractorId) return;

        $.ajax({
            url: "{{ route('ajax.getContractorInvoices') }}",
            type: "POST",
            data: {
                contractor_id: contractorId,
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {

                if (response.length > 0) {

                    response.forEach(inv => {
                        $('#invoice_id').append(
                            `<option value="${inv.ID}">${inv.WorkorderNo}</option>`
                        );
                    });

                    $('#invoice-container').removeClass('d-none');

                    // Re-init Choices
                    if (invoiceChoices) {
                        invoiceChoices.destroy();
                    }

                    invoiceChoices = new Choices(
                        document.querySelector('.choices-single-invoice'),
                        { searchEnabled: true }
                    );
                }
            }
        });
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    let orderChoices = null;

    $('#supplier').on('change', function () {

        let supplierId = $(this).val();

        $('#order-container').addClass('d-none');

        if (!supplierId) return;

        $.ajax({
            url: "{{ route('ajax.getSopplierOrder') }}",
            type: "POST",
            data: {
                supplier_id: supplierId,
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {

                console.log('ORDER RESPONSE:', response);

                if (!Array.isArray(response) || response.length === 0) {
                    return;
                }

                $('#order-container').removeClass('d-none');

                // Destroy previous instance safely
                if (orderChoices) {
                    orderChoices.destroy();
                }

                // Recreate Choices with placeholder
                orderChoices = new Choices('#order_id', {
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: 'Select Order'
                });

                // Set options
                orderChoices.setChoices(
                    response.map(ord => ({
                        value: ord.Invno,
                        label: `${ord.srno}`, // 👈 srno clearly visible
                        selected: false
                    })),
                    'value',
                    'label',
                    true
                );
            }
        });
    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    $('#invoice_id').on('change', function () {

        let invoiceId = $(this).val();

        $('#pending').val('');
        $('#pending_amount').addClass('d-none');

        if (!invoiceId) return;

        $.ajax({
            url: "{{ route('ajax.getInvoicePending') }}",
            type: "POST",
            data: {
                invoice_id: invoiceId,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {

                $('#pending').val(res.pending);
                $('#pending_amount').removeClass('d-none');
            }
        });
    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    $('#order_id').on('change', function () {

        let orderId = $(this).val();

        $('#pending').val('');
        $('#pending_amount').addClass('d-none');
        $('#order-table-container').addClass('d-none');
        $('#orderTableBody').html('');

        if (!orderId) return;

        $.ajax({
            url: "{{ route('ajax.getOrderDetails') }}",
            type: "POST",
            data: {
                order_id: orderId,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {

                // 1️⃣ Pending amount
                $('#pending').val(res.pending);
                $('#pending_amount').removeClass('d-none');

                // 2️⃣ Table rows
                if (res.items && res.items.length > 0) {

                    res.items.forEach(item => {
                        $('#orderTableBody').append(`
                            <tr>
                                <td>${item.Material}</td>
                                <td>${item.Type}</td>
                                <td>${item.Unit}</td>
                                <td>${item.Qty}</td>
                                <td>${item.rejected_qty}</td>
                                <td>${item.Rate}</td>
                                <td>${item.Disc}</td>
                                <td>${item.CGST}</td>
                                <td>${item.SGST}</td>
                                <td>${item.IGST}</td>
                                <td>${item.Total}</td>
                            </tr>
                        `);
                    });

                    $('#order-table-container').removeClass('d-none');
                }
            }
        });
    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    function toggleContractor(expType) {

        // normalize value
        expType = (expType || '').toLowerCase();

        if (expType === 'contractor') {
            $('#contractor-div').removeClass('d-none');
            $('#contractor').prop('required', true);
        } else {
            $('#contractor-div').addClass('d-none');
            $('#contractor').prop('required', false).val('');
            $('#invoice-container').addClass('d-none');
            $('#pending_amount').addClass('d-none');
        }
    }

    // 🔹 On change
    $('#Exp_type').on('change', function () {
        toggleContractor($(this).val());
    });

    // 🔹 On page load (edit / validation error)
    toggleContractor("{{ old('Exp_type', $payment->Exp_type ?? '') }}");

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    function toggleSupplier(expType) {

        // normalize
        expType = (expType || '').toLowerCase();

        if (expType === 'return/rejected matrial') {
            $('#supplier-div').removeClass('d-none');
            $('#supplier').prop('required', true);
        } else {
            $('#supplier-div').addClass('d-none');
            $('#supplier').prop('required', false).val('');

            // also reset dependent fields
            $('#order-container').addClass('d-none');
            $('#order_id').val('');

            $('#pending_amount').addClass('d-none');
            $('#pending').val('');

            $('#order-table-container').addClass('d-none');
            $('#orderTableBody').html('');
        }
    }

    // 🔹 On expense type change
    $('#Exp_type').on('change', function () {
        toggleSupplier($(this).val());
    });

    // 🔹 On page load (edit / validation error)
    toggleSupplier("{{ old('Exp_type', $payment->Exp_type ?? '') }}");

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    function handleMaterialTransfer(expType) {

        expType = (expType || '').toLowerCase();

        // Reset first
        $('#pending').val('');
        $('#pending_amount').addClass('d-none');

        if (expType !== 'matrial transfer') return;

        let schemeId = $('#Destination').val();
        if (!schemeId) return;

        $.ajax({
            url: "{{ route('ajax.getMaterialTransferBalance') }}",
            type: "POST",
            data: {
                scheme_id: schemeId,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {
                $('#pending').val(res.pending);
                $('#pending_amount').removeClass('d-none');
            }
        });
    }

    // 🔹 On Expense Type change
    $('#Exp_type').on('change', function () {
        handleMaterialTransfer($(this).val());
    });

    // 🔹 On Scheme change (important)
    $('#Destination').on('change', function () {
        handleMaterialTransfer($('#Exp_type').val());
    });

    // 🔹 Page load (edit / old value)
    handleMaterialTransfer("{{ old('Exp_type', $payment->Exp_type ?? '') }}");

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    function validateAmountVsPending() {

        let pending = parseFloat($('#pending').val()) || 0;
        let amount  = parseFloat($('#amount_pay').val()) || 0;

        // if pending field is hidden, skip validation
        if ($('#pending_amount').hasClass('d-none')) {
            return;
        }

        if (amount > pending) {
            alert('Amount Paid cannot be greater than Pending Amount');

            // reset amount to max allowed
            $('#amount_pay').val(pending.toFixed(2));

            amount = pending;
        }

        calculatePayable(); // keep payable in sync
    }

    // 🔹 When user types amount
    $('#amount_pay').on('input', function () {
        validateAmountVsPending();
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    function resetAllBlocks() {
        $('#employee-div, #supplier-div, #contractor-div, #order-container').addClass('d-none');
    }

    function handleExpenseType(expType) {

        resetAllBlocks();

        expType = (expType || '').toLowerCase();

        // ✅ Salary & Advance Salary
        if (expType === 'salary' || expType === 'advance salary') {
            $('#employee-div').removeClass('d-none');
            triggerEmployeeSalaryLoad(); // 🔥 important
        }

        // ✅ Contractor
        else if (expType === 'contractor') {
            $('#contractor-div').removeClass('d-none');
        }

        // ✅ Return / Rejected Material
        else if (expType === 'return/rejected matrial') {
            $('#supplier-div').removeClass('d-none');
        }

        // ✅ Material Transfer
        else if (expType === 'matrial transfer') {
            // no employee / contractor
        }
    }

    // 🔹 Trigger salary AJAX manually
    function triggerEmployeeSalaryLoad() {
        let empId = $('#employee').val();
        if (empId) {
            $('#employee').trigger('change');
        }
    }

    // 🔹 On Expense Type change
    $('#Exp_type').on('change', function () {
        handleExpenseType($(this).val());
    });

    // 🔹 On page load (EDIT MODE FIX)
    handleExpenseType("{{ old('Exp_type', $payment->IncomeType ?? '') }}");

});
</script>



