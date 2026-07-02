@extends('backend.partials.master')
@section('title')
   Salary Master
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Salary Master</h1>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="@if(!empty($old)){{ route('SalaryMaster.update') }}@else{{ route('SalaryMaster.store') }}@endif" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!empty($old))
                                @method('PUT')
                                <input type="hidden" name="id" id="id" value="{{ $old->id }}"/>
                            @endif
                            <div class="row">


                               <div class="mb-3 col-md-4">
                                    <label class="form-label" for="date">Date<small class="text-danger">*</small></label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror"
                                        value="{{ old('date', $old->Date ?? date('Y-m-d')) }}"
                                        name="date" id="date" placeholder="Date">
                                    @error('date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

<div class="mb-3 col-md-4">
    <label class="form-label">Month <small class="text-danger">*</small></label>
    <select name="month" class="form-control" required>
        <option value="">-- Select Month --</option>
        @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" 
                {{ old('month', $old->month ?? '') == $m ? 'selected' : '' }}>
                {{ date('F', mktime(0, 0, 0, $m, 10)) }}
            </option>
        @endfor
    </select>
    @error('month')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

<div class="mb-3 col-md-4">
    <label class="form-label">Year <small class="text-danger">*</small></label>
    <input type="number" name="year" class="form-control"
        value="{{ old('year', $old->year ?? date('Y')) }}" required>
    @error('year')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>


                                @php
                                    $selecteduser = old('emp_id', $old->emp_id ?? '');
                                @endphp
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Employee<small class="text-danger">*</small></label>
                                    <select name="emp_id" id="emp_id" class="form-control" required>
                                        
                                        <option value="" disabled {{ empty($selecteduser) ? 'selected' : '' }}>
                                            -- Select Employee --
                                        </option>

                                        @foreach($staffs as $user)
                                            <option value="{{ $user->ID }}"
                                                {{ (string)$user->ID === (string)$selecteduser ? 'selected' : '' }}>
                                                {{ $user->Name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

<div class="mb-3 col-md-4">
        <label class="form-label">Salary No <small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('salary_no') is-invalid @enderror"
            value="{{ old('salary_no', $old->salary_no ?? '') }}"
            name="salary_no" placeholder="Salary No" required>
        @error('salary_no')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>


    <div class="mb-3 col-md-4">
        <label class="form-label">Gross Salary <small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('gross') is-invalid @enderror"
            value="{{ old('gross', $old->gross ?? '') }}"
            name="gross" placeholder="Gross Salary" id="gross" required>
        @error('gross')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
                                <!-- Task Description -->
    <div class="mb-3 col-md-4">
        <label class="form-label">Basic Salary <small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('basic_salary') is-invalid @enderror"
            value="{{ old('basic_salary', $old->basic_salary ?? '') }}"
            name="basic_salary" placeholder="Basic Salary" required>
        @error('basic_salary')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

     <div class="mb-3 col-md-4">
        <label class="form-label">PF <small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('pf') is-invalid @enderror"
            value="{{ old('pf', $old->pf ?? '') }}"
            name="pf" placeholder="PF" required>
        @error('pf')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label">ESI <small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('esi') is-invalid @enderror"
            value="{{ old('esi', $old->esi ?? '') }}"
            name="esi" placeholder="ESI" required>
        @error('esi')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

     <div class="mb-3 col-md-4">
        <label class="form-label">Advance EMI<small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('advance_emi') is-invalid @enderror"
            value="{{ old('advance_emi', $old->advance_emi ?? '') }}"
            name="advance_emi" placeholder="Advance EMI" required>
        @error('advance_emi')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

      <div class="mb-3 col-md-4">
        <label class="form-label">Net Salary<small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('net_salary') is-invalid @enderror"
            value="{{ old('net_salary', $old->net_salary ?? '') }}"
            name="net_salary" placeholder="Net Salary" required>
        @error('net_salary')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

      {{-- <div class="mb-3 col-md-4">
        <label class="form-label">Net Salary<small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('net_salary') is-invalid @enderror"
            value="{{ old('net_salary', $old->net_salary ?? '') }}"
            name="net_salary" placeholder="Net Salary" required>
        @error('net_salary')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div> --}}

    

   <div class="mb-3 col-md-4">
    <label class="form-label">Payment Method<small class="text-danger">*</small></label>
    <select id="payment_method" name="payment_method" class="form-control choices-single" required>
        <option value="" disabled selected>Select Payment Method</option>
        <option value="cash" {{ old('payment_method', $old->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
        <option value="cheque" {{ old('payment_method', $old->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
        <option value="DD" {{ old('payment_method', $old->payment_method ?? '') == 'DD' ? 'selected' : '' }}>DD</option>
    </select>
    @error('payment_method')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

<div class="mb-3 col-md-4">
    <label class="form-label">Account No <small class="text-danger">*</small></label>
    <div id="Ac">
        <select name="account_no" id="account_no" class="form-control">
            <option value="">Select</option>
        </select>
    </div>
</div>

<div class="mb-3 col-md-4">
    <label class="form-label">Balance</label>
    <input type="text" readonly id="balanceamt" name="balanceamt" class="form-control">
</div>

<div class="mb-3 col-md-4" id="cheque_no_div" style="display: none;">
    <label class="form-label">Cheque No<small class="text-danger">*</small></label>
    <input type="number" class="form-control @error('cheque_no') is-invalid @enderror"
           value="{{ old('cheque_no', $old->cheque_no ?? '') }}"
           name="cheque_no" placeholder="Cheque No">
    @error('cheque_no')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

<script>
    const paymentSelect = document.getElementById('payment_method');
    const chequeDiv = document.getElementById('cheque_no_div');

    function toggleChequeField() {
        if (paymentSelect.value === 'cheque') {
            chequeDiv.style.display = 'block';
            chequeDiv.querySelector('input').setAttribute('required', 'required');
        } else {
            chequeDiv.style.display = 'none';
            chequeDiv.querySelector('input').removeAttribute('required');
        }
    }

    // Initial check in case old value is 'cheque'
    toggleChequeField();

    // Listen to changes
    paymentSelect.addEventListener('change', toggleChequeField);
</script>




      <div class="mb-3 col-md-4">
        <label class="form-label">Amount <small class="text-danger">*</small></label>
        <input type="number" class="form-control @error('amount') is-invalid @enderror"
            value="{{ old('amount', $old->amount ?? '') }}"
            name="amount" placeholder="Amount" id="amount" required>
        @error('amount')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

      <div class="mb-3 col-md-4">
        <label class="form-label">Narration  <small class="text-danger">*</small></label>
        <input type="text" class="form-control @error('narration') is-invalid @enderror"
            value="{{ old('narration ', $old->narration ?? '') }}"
            name="narration" placeholder="Narration" required>
        @error('amount')
        <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($old) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('SalaryMaster') }}" class="btn btn-secondary">Cancel</a>
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
document.addEventListener("DOMContentLoaded", function () {

    document.getElementById('emp_id').addEventListener('change', fetchSalary);
    document.querySelector('[name="month"]').addEventListener('change', fetchSalary);
    document.querySelector('[name="year"]').addEventListener('input', fetchSalary);

    function fetchSalary() {

        let empId = document.getElementById('emp_id').value;
        let month = document.querySelector('[name="month"]').value;
        let year  = document.querySelector('[name="year"]').value;

        if (empId && month && year) {

            fetch(`../get-employee-salary/${empId}?month=${month}&year=${year}`)
            .then(res => res.json())
            .then(data => {

                if (data.status) {

                    let monthly = parseFloat(data.daily_wage); // actually monthly salary
                    let totalDays = 30; // or dynamic based on month
                    let present = parseFloat(data.present);

                    // ✅ correct calculation
                    let perDay = monthly / totalDays;
                    let gross = perDay * present;

                    document.getElementById('gross').value = gross;

                    calculateNet(gross);
                }
            });
        }
    }

    function calculateNet(gross) {

        let pf = parseFloat(document.querySelector('[name="pf"]').value) || 0;
        let esi = parseFloat(document.querySelector('[name="esi"]').value) || 0;
        let advance = parseFloat(document.querySelector('[name="advance_emi"]').value) || 0;

        let net = gross - (pf + esi + advance);

        document.getElementById('amount').value = net;
        document.querySelector('[name="net_salary"]').value = net;
    }

    // ✅ Recalculate when deductions change
    document.querySelectorAll('[name="pf"], [name="esi"], [name="advance_emi"]').forEach(el => {
        el.addEventListener('input', function () {
            let gross = parseFloat(document.getElementById('gross').value) || 0;
            calculateNet(gross);
        });
    });

});

$(document).ready(function () {

    $(document).on('change', '#emp_id, [name="month"], [name="year"]', function () {
        fetchSalary();
    });

});
</script>


<script>


// 🔹 Account change → get balance
$(document).on('change', '#account_no', function () {
    getBalance();
});


// 🔹 Get balance function
function getBalance() {

    let accId = $("#account_no").val();
    let date  = $("#date").val();
    let iid = $("#id").length ? $("#id").val() : 0;

    if (!accId) {
        $("#balanceamt").val("");
        return;
    }

    $.ajax({
        url: "{{ route('ajax.getBalance') }}",
        type: "POST",
        data: {
            matid: accId,
            date: date,
            iid: iid,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            $("#balanceamt").val(response.balance);
        }
    });
}


// 🔹 If date changes → refresh balance
$(document).on('change', '#date', function () {
    getBalance();
});

$(document).ready(function () {

    $("form").on("submit", function (e) {

        let netSalary = parseFloat($('[name="net_salary"]').val()) || 0;
        let amount    = parseFloat($('#amount').val()) || 0; // optional
        let balance   = parseFloat($('#balanceamt').val()) || 0;

        // use net_salary OR amount (based on your logic)
        let finalAmount = netSalary || amount;

        if (finalAmount > balance) {
            e.preventDefault(); // ❌ stop form submit

            alert("❌ Insufficient Balance! Salary amount is greater than available balance.");

            return false;
        }

    });

});


let oldPaymentMethod = "{{ old('payment_method', $old->payment_method ?? '') }}";
let oldAccountNo     = "{{ old('account_no', $old->account_no ?? '') }}";

// 🔹 Load accounts (COMMON function)
function loadAccounts(pay_method, selectedAccount = null) {

    $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: pay_method,
            selected_id: selectedAccount, // ✅ IMPORTANT
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {

            $("#Ac").html(response.html);

            // ✅ just trigger balance (no val setting here)
            getBalance();
        }
    });
}


// 🔹 On page load (EDIT)
$(document).ready(function () {

    if (oldPaymentMethod) {
        loadAccounts(oldPaymentMethod, oldAccountNo); // ✅ pass here
    }

});


// 🔹 On user change
$(document).on('change', '#payment_method', function () {

    let pay_method = $(this).val();

    loadAccounts(pay_method); // ❌ no oldAccount here

});
</script>
