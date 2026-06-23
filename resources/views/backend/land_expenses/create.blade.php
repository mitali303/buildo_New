@extends('backend.partials.master')
@section('title', 'Land Expenses')

@section('maincontent')
<main class="content">
  <div class="container-fluid p-0">
    <div class="mb-3">
      <h1 class="h3 d-inline align-middle">Land Expenses</h1>
    </div>
<style>
.pdtl{
	display:none;
}</style>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <!-- @if ($errors->any())
              <div class="alert alert-danger">
                  <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                      @endforeach
                  </ul>
              </div>
          @endif -->

        @php
        $PayStyle=$PStyle=$Style="display: none;";

        @endphp
          <div class="card-body">
            <form action="@if(!empty($postdated)){{ route('land_expenses.update') }}@else{{ route('land_expenses.store') }}@endif"
                  method="POST" enctype="multipart/form-data">
              @csrf
              
              @if(!empty($postdated))
                @method('PUT')
                <input type="hidden" name="ID" value="{{ $postdated->ID }}">
              @endif
              {{--<input type="hidden" name="purchasesid" value="{{ $purchases->id }}">--}}
              <div class="row">
                <div class="mb-3 col-md-3">
                    <label class="form-label" for="date">Date <small class="text-danger">*</small></label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror"  name="date" id="date" value="{{ old('date', $postdated->Date ?? date('Y-m-d')) }}" required>
                    @error('date')
                      <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3 col-md-3">
                  <label for="scheme" class="form-label">Scheme <small class="text-danger">*</small></label>
                  <select name="scheme" id="scheme" class="form-control choices-single" required  >
                    <option disabled selected value="">Select Scheme</option>
                   
                    @foreach($schemes as $scheme)
                      <option value="{{ $scheme->ID }}"
                       {{ (session('selected_scheme_id') == $scheme->ID) ? 'selected' : '' }}>
                          
                        {{ $scheme->Name }}
                        
                      </option>
                    
                    @endforeach
                  </select>
                  @error('schemeID')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                @php


                  $select = '';
                  $exp_array = ['land expence'];

                  $site_record = DB::table('land')
                      ->select('Exp_type')
                      ->whereNotIn('Exp_type', $exp_array)
                      ->groupBy('Exp_type')
                      ->get();
                  @endphp

              <div class="col-md-3" id="type_div">
                <label class="form-label">Expense Type <small class="text-danger">*</small></label>
                
                <select name="exptype" id="exptype"
                class="input-lg form-control chosen-select required"
                data-placeholder="Select Expenses Type"
                onchange="GetEmp();">
                
                <option value="">Select</option>
                
                <option value="Land purchase"
                {{ old('exptype', $postdated->Exp_type ?? $db_record['Exp_type'] ?? '') == 'Land purchase' ? 'selected' : '' }}>
                Land purchase
                </option>
                
                <option value="Land NA"
                {{ old('exptype', $postdated->Exp_type ?? $db_record['Exp_type'] ?? '') == 'Land NA' ? 'selected' : '' }}>
                Land NA
                </option>
                
                <option value="Land stamp expence"
                {{ old('exptype', $postdated->Exp_type ?? $db_record['Exp_type'] ?? '') == 'Land stamp expence' ? 'selected' : '' }}>
                Land stamp expence
                </option>
                
                </select>
                </div>

                <div class="mb-3 col-md-3">
                  <label for="title" class="form-label">Title </label>

                  <div  id="Titale_div">
                  <select name="title" id="title" class="form-control" onchange="checkOtherExpense(this.value)">
                      <option value="">Select</option>
                      <option value="NEW">ADD NEW</option>

                      @php
                          // Determine the Exp_type to filter records
                          $expType = !empty($postdated) ? $postdated->Exp_type : ($postdated['Exp_type'] ?? null);

                          // Fetch records only if $expType exists
                          $income_record = DB::table('land')
                                ->select('title')
                                ->distinct()
                                ->orderBy('title')
                                ->get();
                      @endphp
                        @php
                        $selectedTitle = old('title', $postdated->title ?? '');
                        @endphp
                      @foreach($income_record as $expense)
                        <option value="{{ $expense->title }}"
                        {{ $selectedTitle == $expense->title ? 'selected' : '' }}>
                        {{ $expense->title }}
                        </option>
                        @endforeach
                  </select>

                </div>
                  <!-- Input for adding new expense type -->
                  <div id="newExptypeDiv" style="margin-top:5px; display:none;">
                    <input type="text"
                    name="Exptitles"
                    id="newExptypeInput"
                    class="form-control"
                    placeholder="Enter new title"
                    value="{{ old('Exptitles') }}">                  
                </div>
                </div>

                    
              </div><br>

              <div class="row">
                <table class="table" id="mytable">
                  <thead>
                    <tr>
                     
                      <th>Payment Method <small class="text-danger">*</small></th>
                      
                      <th>Account No</th>
                      <th>Balance</th>
                      <th>Amount</th>
                      <th>Cheque No / Transaction ID</th>
                      <th style="<?php echo $PayStyle ?>" id="banktitle" >Bank Charges</th>
			                <th style="<?php echo $PayStyle ?>" id="totalvalue">Total Pay</th>
                      
                      <th>Narration</th>
                      
                      
                      
                    </tr>
                  </thead>
                  <tbody>
                    <tr>

                      <td>
                        <select name="Pay_type" id="Pay_type" class="form-control" onChange="check_type();">
                          <option value="">Select Payment Method</option>
                          <option value="cash" {{ old('Pay_type', $postdated->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                          <option value="cheque" {{ old('Pay_type', $postdated->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                          <option value="e-payment" {{ old('Pay_type', $postdated->payment_method ?? '') == 'e-payment' ? 'selected' : '' }}>E‑payment</option>
                        </select>
                        @error('payment_method')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                      </td>
                     
                      <td>
                        <div id="Ac" style="">
                        <select name="account_no" id="account_no" class="form-control" onchange="getBalance();">
                          <option value="">Select Account</option>
                          @foreach($banks as $bank)
                            <option value="{{ $bank->ID }}"
                              {{ old('account_no', $postdated->account_no ?? '') == $bank->ID ? 'selected' : '' }}>
                              {{ $bank->Name }}
                            </option>
                          @endforeach
                        </select>
                        </div>
                        
                        @error('account_no')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </td>
                      <td >
			                  <input type="text" readonly  class="form-control" id="balance"  name="balance" value="{{ old('balance', $postdated->balance ?? '') }}"  />
		                  </td>
                      <td>
                        <input type="number" name="amount_pay" id="amount_pay"
                            class="form-control @error('amount_pay') is-invalid @enderror"
                            value="{{ old('amount_pay', $postdated->amt_pay ?? '') }}" onkeyup="checkAmount();">

                            <input type="hidden"  style=""name="amount_pay_old" id="amount_pay_old"  value="<?php //echo $pendingAmt;?>" onkeyup="" class=" input-md form-control "/>

                        @error('amount_pay')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </td>
                      <td>
                        <input type="text" name="cheque_no" id="cheque_no" class="form-control" value="{{ old('cheque_no', $postdated->cheque_no ?? '') }}">
                      @error('cheque_no')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </td>
                      
                      <td id="bankvalue" style="<?php echo $PayStyle ?>">
                        <input type="text" style="width: 100px"  <?php //echo $notread;?> name="bnk_charge" id="bnk_charge"   value="{{ old('bnk_charge', $postdated->amt_pay ?? '') }}"  onkeyup="cal_total();"  class=" form-control"/>
                      </td>
			                <td id="totalamt" style="<?php echo $PayStyle ?>">
                        <input type="text" style="width: 100px" readonly <?php //echo $notread;?> name="total_pay" id="total_pay"   value="{{ old('total_pay', $postdated->amt_pay ?? '') }}"  onkeyup="cal_total();"  class=" form-control"/>
                      </td>
                      
                      <td>
                        <textarea type="text" style="height: 34px;"    class=" input-medium form-control"  id="narration"  name="narration" >{{ old('narration', $postdated->narration ?? '') }}</textarea>
                      </td>
                      
                    </tr>
                  </tbody>
                </table>
              </div>
              <input type='hidden' name="Uid" id="Uid" value="{{ $db_record->ID ?? '000' }}">

              <br>
              <button type="submit" class="btn btn-primary">{{ empty($postdated) ? 'Create' : 'Update' }}</button>
              <a href="{{ route('land_expenses') }}" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@if(session('error'))
<script>
    alert("{{ session('error') }}");
</script>
@endif
@section('scripts')
<script>
// function GetbookCustomer(val) {
//     $.ajax({
//         url: "{{ route('get.stampexpense') }}",
//         type: "POST",
//         data: {
//             _token: "{{ csrf_token() }}",
//             scheme_id: val
//         },
//         success: function (data) {
//             $("#custDiv").html(data);
//         }
//     });
// }
function GetEmp() {

    let value = $("#exptype").val();

    $.ajax({
        url: "{{ route('get.title.land.exp') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            exptype: value
        },
        success: function (data) {

           let html = `
    <option value="">Select</option>
    <option value="NEW"> ➕ ADD NEW</option>
    ${data}
`;

            $("#title").html(html);

            // safe re-init chosen
            if ($('.chosen-select').length) {
                $('.chosen-select').chosen('destroy');
                $('.chosen-select').chosen();
            }
        }
    });
}
//function checkOtherExpense(value) {

   // if (value === 'NEW') {

      //  $("#Titale_div").html(`
          //  <input type="text"
              //  name="Exptitles"
               // id="newExptypeInput"
               // class="form-control"
              //  placeholder="Enter new title"
           // />
       // `);

       // $("#newExptypeInput").focus();
   // }
//}
function checkOtherExpense(value)
{
    if(value === 'NEW'){
        $('#newExptypeDiv').show();
        $('#newExptypeInput').focus();
    }else{
        $('#newExptypeDiv').hide();
        $('#newExptypeInput').val('');
    }
}

// Optional: If user cancels input, show dropdown again
document.getElementById('newExptypeInput')?.addEventListener('blur', function () {
    if (this.value.trim() === '') {
        $('#newExptypeDiv').hide();
    }
});
  function cal_total() {
    var pay_method = $('#Pay_type').val();

    if (pay_method === "e-payment") {
        var totalpay = 0;
        var bnkcharge = parseFloat($("#bnk_charge").val()) || 0;
        var amount_pay = parseFloat($("#amount_pay").val()) || 0;
        var balance = parseFloat($("#balance").val()) || 0;

        if (bnkcharge > amount_pay) {
            alert("Bank Charges exceeds Amount Pay Amount!");
            $("#bnk_charge").val('');
            return;
        }

        totalpay = amount_pay + bnkcharge;

        if (totalpay > balance) {
            $("#other").show().html("<label>You don't have enough balance!</label>");
            $("#submit").hide();
        } else {
            $("#other").hide();
            $("#submit").show();
        }

        $("#total_pay").val(totalpay.toFixed(2));
    }
}

   

function check_type() {
    var pay_method = $('#Pay_type').val();
    var task = $('#Task').val();

    // AJAX call to Laravel route
    $.ajax({
        url: '{{ route("ajax.getLandExpenses") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            pay_method: pay_method
        },
        success: function(data) {
            $("#Ac").html(data);
            getBalance();
        }
    });

    // Toggle fields based on payment type
    if (pay_method === "e-payment") {
        $('#banktitle, #totalvalue, #bankvalue, #totalamt').show();
        $('#cheque_no').removeAttr("readonly");
        $('#bnk_charge').addClass("number");
    } else {
        $('#banktitle, #totalvalue, #bankvalue, #totalamt').hide();
        $('#bnk_charge').val(0).removeClass("number");

        if (pay_method === "cash") {
            $('#cheque_no').val('').prop('readonly', true).removeClass("required");
        } else {
            $('#cheque_no').removeAttr("readonly");
        }
    }
}
function getBalance() {
    var matid = jQuery("#account_no").val();
    var task = jQuery("#Task").val();
    var payID = (task === 'update' || task === 'view') ? jQuery("#Uid").val() : '00000';

    $.ajax({
        url: '{{ route("get.balanceExpenses") }}',
        type: 'POST',
        data: {
            Matid: matid,
            PayID: payID,
            _token: '{{ csrf_token() }}' // CSRF token for Laravel
        },
        success: function(data) {
            //jQuery("#balance").val(response.balance);
             $("#balance").val(data.balance);
        }
    });
}

function checkAmount() {
    var amount = parseFloat(document.getElementById('amount_pay').value) || 0;
    var pendingAmt = parseFloat(document.getElementById('amount_pay_old').value) || 0;
    var balance = parseFloat(document.getElementById('balance')?.value || 0);

    var otherDiv = document.getElementById('other');
    var submitBtn = document.getElementById('submit');

    if (balance >= amount) {
        if (pendingAmt !== 0 && amount > pendingAmt) {
            document.getElementById('amount_pay').value = '';
            otherDiv.style.display = 'block';
            submitBtn.style.display = 'none';
            otherDiv.innerHTML = "<label>Please Check Total Amount!!!</label>";
        } else {
            otherDiv.style.display = 'none';
            submitBtn.style.display = 'block';
        }
    } else {
        otherDiv.style.display = 'block';
        submitBtn.style.display = 'none';
        otherDiv.innerHTML = "<label>Please Check Balance Amount!!!</label>";
    }
}

function GetDetails() {
    var emp = $("#empName").val();

    $.ajax({
        url: "/get-emp-details",
        type: "POST",
        data: {
            emp: emp,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {

            showfields();

            $("#SalaryType").html(data.salary_type);
            $("#Salary").html(data.daily_wage);

            let SarType = data.salary_type;

            if (SarType === 'Monthly') {
                $("#month").show();
                $("#day").hide();
                $("#fromDate").val('');
                $("#toDate").val('');
            } else {
                $("#mth").val('');
                $("#year").val('');
                $("#month").hide();
                $("#day").show();
            }
        }
    });
}

</script>
<script>
window.onload = function () {
  getBalance();
  
};
</script>
<script>
function checkAmount() {

    var amount = parseFloat(($('#amount_pay').val() || "0").replace(/,/g, ''));
    var balance = parseFloat(($('#balance').val() || "0").replace(/,/g, ''));

    if (amount > balance) {
        $('#submit').hide();
        alert("❌ Amount entered is greater than balance (" + balance + ")");
        return false;
    } else {
        $('#submit').show();
    }
}
</script>
@endsection