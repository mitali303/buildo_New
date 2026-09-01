@extends('backend.partials.master')
@section('title', 'Stamp / Other Expenses')

@section('maincontent')
<main class="content">
  <div class="container-fluid p-0">
    <div class="mb-3">
      <h1 class="h3 d-inline align-middle">Stamp / Other Expenses</h1>
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
            <form action="@if(!empty($postdated)){{ route('stamp_other_expenses.update') }}@else{{ route('stamp_other_expenses.store') }}@endif"
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
                  <select name="scheme" id="scheme" class="form-control choices-single" required  onchange="GetbookCustomer();">
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
              <div class="mb-3 col-md-3">
                  <label for="exptype" class="form-label">Expense Type <small class="text-danger">*</small></label>

                  <!-- Dropdown for existing types -->
                  <select name="exptype" id="exptype" class="form-control" onchange="checkOtherExpense(this.value)">
                      <option value="">Select Expense Type</option>
                      <option value="OTHER" style="font-weight:bold;color:#0d6efd;">
                ➕ ADD NEW
            </option>

                      @php
                          $defaultExpenses = ['Stamp Duty', 'Registration', 'GST', 'ITAX'];

                          // Get all custom expenses from DB (exclude default ones)
                          $customExpenses = DB::table('stampotherexpenses')
                                              ->select('Exp_type')
                                              ->whereNotIn('Exp_type', $defaultExpenses)
                                              ->groupBy('Exp_type')
                                              ->pluck('Exp_type')
                                              ->toArray();

                          $allExpenses = array_merge($defaultExpenses, $customExpenses);
                      @endphp

                      @foreach($allExpenses as $expense)
                          <option value="{{ $expense }}" 
                              {{ old('exptype', $postdated->Exp_type ?? '') == $expense ? 'selected' : '' }}>
                              {{ $expense }}
                          </option>
                      @endforeach
                  </select>

                  <!-- Input for adding new expense type -->
                  <div id="newExptypeDiv" style="margin-top:5px; display:none;">
                      <input type="text" name="new_exptype" id="newExptypeInput" class="form-control" placeholder="Enter new expense type" value="{{ old('new_exptype') }}">
                  </div>

                   @error('exptype')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
              </div>

           <div class="mb-3 col-md-3">
    <label for="title" class="form-label">Title</label>

    <div id="titleWrapper">
        <select name="title" id="title" class="form-control" onchange="checkOtherTitle(this)">
            <option value="">Select Title</option>
            <option value="OTHER" style="font-weight:bold;color:#0d6efd;">
                ➕ ADD NEW
            </option>

            @foreach($titles as $t)
                <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
        </select>
    </div>
</div>
                    
              </div><br>

              <div class="row">
                <div class="table-responsive">
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

                        <option value="cash"
                        {{ old('Pay_type', $postdated->payment_method ?? '') == 'cash' ? 'selected' : '' }}>
                        Cash
                        </option>

                        <option value="cheque"
                        {{ old('Pay_type', $postdated->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>
                        Cheque
                        </option>

                        <option value="e-payment"
                        {{ old('Pay_type', $postdated->payment_method ?? '') == 'e-payment' ? 'selected' : '' }}>
                        E-payment
                        </option>

                        </select>

                        @error('Pay_type')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror

                        </td>
                                @error('amount_pay')
                            <div class="text-danger">
                                {{ $message }}
                            </div>
                        @enderror            
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
              </div>
              <input type='hidden' name="Uid" id="Uid" value="{{ $db_record->ID ?? '000' }}">

              <br>
              <button type="submit" class="btn btn-primary">{{ empty($postdated) ? 'Create' : 'Update' }}</button>
              <a href="{{ route('stamp_other_expenses') }}" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  
</main>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@section('scripts')
<script>
function GetbookCustomer(val) {
    $.ajax({
        url: "{{ route('get.stampexpense') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            scheme_id: val
        },
        success: function (data) {
            $("#custDiv").html(data);
        }
    });
}
//  function GetEmp()
// 	{
// 		GetInputFild();
// 	}
	
	
	
// 	function GetInputFild()
// 	{
// 	  var value=jQuery("#exptype").val();
	 
//       if(value==='OTHER')
//       {
				 
// 				$(".return").hide();
// 				$(".transfer").hide();
// 				$(".non_other").hide();
// 				$("#otherDate").show();
//         $("#type_div").html("<input class='form-control required'  type='text' id='exptype' name='exptype'  />");
			
//       }
				
// 	}
// function checkOtherExpense(value) {
//     if(value === 'OTHER'){
//         // Hide dropdown, show input
//         document.getElementById('exptype').style.display = 'none';
//         document.getElementById('newExptypeDiv').style.display = 'block';
//         document.getElementById('newExptypeInput').value = '';
//         document.getElementById('newExptypeInput').focus();
//     }
// }
function checkOtherExpense(value) {
    if(value === 'OTHER'){
        document.getElementById('exptype').style.display = 'none';
        document.getElementById('newExptypeDiv').style.display = 'block';
        document.getElementById('newExptypeInput').focus();
    }
}
// Optional: If user cancels input, show dropdown again
document.getElementById('newExptypeInput')?.addEventListener('blur', function() {
    if(this.value.trim() === ''){
        document.getElementById('exptype').style.display = 'block';
        document.getElementById('newExptypeDiv').style.display = 'none';
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
        url: '{{ route("ajax.getStampExpenses") }}',
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
$(document).ready(function() {

    // $('#exptype').select2({
    //     tags: true,
    //     placeholder: "Select or type expense type",
    //     allowClear: true
    // });

    $('#title').select2({
        tags: true,
        placeholder: "Select or type title",
        allowClear: true
    });
    @if(old('new_exptype'))
    $('#exptype').hide();
    $('#newExptypeDiv').show();
@endif

});
</script>

<script>
function checkOtherTitle(select) {

    let wrapper = document.getElementById('titleWrapper');

    if (select.value === 'OTHER') {

        // replace dropdown with input (SAME PLACE)
        wrapper.innerHTML = `
            <input type="text"
                   name="title"
                   class="form-control"
                   placeholder="Enter new title"
                   autofocus>
        `;
    }
}
</script>
@endsection