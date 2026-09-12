@extends('backend.partials.master')
@section('title', 'Loan Management')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
      <div class="mb-3">
       <h1 class="h3 d-inline align-middle">Loan Management</h1>
      </div>
<style>
.pdtl{
	display:none;
}
</style>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          @php
          $PayStyle=$PStyle=$Style="display: none;";
          @endphp
          <div class="card-body">
            <form action="@if(!empty($loans)){{ route('loan_management.update') }}@else{{ route('loan_management.store') }}@endif"
                  method="POST" enctype="multipart/form-data">
                  @csrf
                  @if(!empty($loans))
                    @method('PUT')
                    {{--<input type="hidden" name="ID" value="{{ $loans->ID }}">--}}
                  @endif
              {{--<input type="hidden" name="purchasesid" value="{{ $purchases->id }}">--}}
              <div class="row">
                <div class="mb-3 col-md-3">
                    <label class="form-label" for="date">Date <small class="text-danger">*</small></label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror"  name="date" id="date" value="{{ old('date', $loans->Date ?? date('Y-m-d')) }}" required>
                    @error('date')
                      <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="mb-3 col-md-3">
                    <label class="form-label d-block">Type<small class="text-danger">*</small></label>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="paytype" id="Paid" value="Paid"
                              {{ old('paytype', $loans->paytype ?? '') == 'Paid' ? 'checked' : '' }}
                              onchange="updateAmountTitle(); getAmt();">
                        <label class="form-check-label">Given</label>
                      </div>
                      <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="paytype" id="Received" value="Received" {{ old('paytype', $loans->paytype ?? '') == 'Received' ? 'checked' : '' }}
                                onchange="updateAmountTitle(); getAmt();">
                          <label class="form-check-label">Taken</label>
                      </div>
                        @error('paytype')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      <input type="hidden"  id="tp" name="tp" value="<?php echo $Paytype1; ?>">
                </div>
                <div class="mb-3 col-md-3">
                  <label for="customer" class="form-label">Name <small class="text-danger">*</small></label>
                  <select name="customer" id="customer" class="form-control choices-single" required  onchange="getAmt();">
                    <option disabled selected value="">Select </option>
                    @foreach($partners_loan as $partners)
                      <option value="{{ $partners->ID }}"
                        {{ old('customer', $loans->customer ?? '') == $partners->ID ? 'selected' : '' }}>
                        {{ $partners->Name }}
                      </option>
                    @endforeach
                  </select>
                  @error('customer')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
                <div class="mb-3 col-md-3">
                  <label for="pending_amt" class="form-label">Pending Amount </label>
                  <div class="col-md-5" id="fcatdiv"> <lable  id="pendingAmt"  name="pendingAmt"> </lable>
				          </div>
                </div>
                <div class="mb-3 col-md-3">
                  <label for="customer" class="form-label">Scheme <small class="text-danger">*</small></label>
                  <select name="scheme" id="scheme" class="form-control choices-single" required>
                    <option disabled selected value="">Select </option>
                    @foreach($schemes as $scheme)
                      <option value="{{ $scheme->ID }}"
                        {{ session('selected_scheme_id') == $scheme->ID ? 'selected' : '' }}>
                        {{ $scheme->Name }}
                      </option>
                    @endforeach
                  </select>
                  @error('scheme')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
                <div class="mb-3 col-md-3">
                  <label for="transType" class="form-label">Transaction Type </label>
                    <select name="transType" id="transType" class="form-control choices-single" >
                      <option disabled selected value="">Select </option>
                      <option value="0" {{ old('transType', $loans->transType ?? '') == '0' ? 'selected' : '' }}>Payment</option>
                      <option value="1" {{ old('transType', $loans->transType ?? '') == '1' ? 'selected' : '' }}>Interest</option>
                    </select>
                    @error('transType')
                      <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
              </div>
              <div class="row">
                <table class="table" id="mytable">
                  <thead>
                    <tr>
                      <th>Payment Method <small class="text-danger">*</small></th>
                      <th>Account No.</th>
                      <th  id="balanctitle">Balance</th>
                      <th>Cheque No / Transaction ID</th>
                      <th id="amount_title">Amount</th>
                      <th style="width: 12%;<?php echo $PayStyle ?>" id="banktitle" >Bank Charges</th>
			                <th style="width: 18%;<?php echo $PayStyle ?>" id="totalvalue"> Total Pay</th>
			                <th style="width: 20%;" class="pdtl">Bank Detail</th>
                       <th>Narration</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div id="Div_paytype"> 
                          <select name="Pay_type" id="Pay_type" class="form-control" onChange="checkType()">
                            <option value="">Select Payment Method</option>
                            <option value="cash" {{ old('payment_method', $loans->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="cheque" {{ old('payment_method', $loans->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="e-payment" {{ old('payment_method', $loans->payment_method ?? '') == 'e-payment' ? 'selected' : '' }}>E‑payment</option>
                          </select>
                        </div>
                      </td>
                      <td>
                        <div id="Ac" style="">
                          <select name="account_no" id="account_no" class="form-control" onchange="getBalance();">
                            <option value="">Select Account</option>
                            @foreach($banks as $bank)
                              <option value="{{ $bank->ID }}"
                                {{ old('account_no', $loans->account_no ?? '') == $bank->ID ? 'selected' : '' }}>
                                {{ $bank->Name }}
                              </option>
                            @endforeach
                          </select>
                        </div>
                      </td>
                      <td id="balancevalue">
                        <input type="text" name="balance" id="balance" class="form-control" value="" >
                      </td>
                      <td>
                        <input type="text" name="cheque_no" id="cheque_no" class="form-control" value="{{ old('cheque_no', $loans->cheque_no ?? '') }}">
                      </td>
                      <td>
                        <input type="number"  name="amount_pay" id="amount_pay" class="form-control " value="{{ old('amount_pay', $loans->amt_pay ?? '') }}" onkeyup="chek_amt();">
                        @error('amount_pay')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <input type="hidden"  style=""name="amount_pay_old" id="amount_pay_old"  value="<?php //echo $pendingAmt;?>" onkeyup="" class="required input-md form-control "/>
                      </td>
                      <td id="bankvalue" style="<?php echo $PayStyle ?>">
                        <input type="text"  name="bankcharge" id="bankcharge" class="form-control " value="{{ old('bankcharge', ($loans->amt_pay ?? 0) + ($loans->bankcharge ?? 0)) }}" onkeyup="cal_total();">
                        @error('bankcharge')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </td>
                      <td id="totalamt" style="<?php echo $PayStyle ?>">
                        <input type="text"  name="total_pay" id="total_pay" class="form-control @error('bankcharge') is-invalid @enderror" value="{{ old('amount', $loans->bankcharge ?? '') }}">
                        @error('bankcharge')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </td>
                      <td class="pdtl">
                          <input type="text" name="paydetail" id="paydetail" class="form-control"
                                value="{{ old('paydetail', $loans->paydetail ?? '') }}">
                      </td>
                      <td>
                        <input type="text" name="narration" id="narration" class="form-control" value="{{ old('narration', $loans->narration ?? '') }}">
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <br>
              <button type="submit" class="btn btn-primary">{{ empty($loans) ? 'Create' : 'Update' }}</button>
              <a href="{{ route('loan_management') }}" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@section('scripts')
<script>
function checkType() {
    var pay_method = $('#Pay_type').val().toLowerCase(); // normalize to lowercase
    var task = $('#Task').val();

    // AJAX call to Laravel route
    $.ajax({
        url: '{{ route("ajax.paytypeaccounts") }}',
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
     getType();

   
}



function getType() {
	  
	  var pay_method=$('#Pay_type').val();
	  var type=$('#account_no').val();
	if(pay_method=="e-Payment" && $('#tp').val()=='Paid')
	{
	 $('#banktitle').show();
	  $('#totalvalue').show();
	  $('#bankvalue').show();
	   $('#totalamt').show();
	  $('#cheque_no').removeAttr("readonly");
	   $('#bnk_charge').addClass("required");
	}else{
	 
	
	$('#banktitle').hide();
	$('#totalvalue').hide();
	 $('#bnk_charge').val(0);
	  $('#bnk_charge').removeClass("required");
	  $('#bankvalue').hide();
	   $('#totalamt').hide();
	  
	if(pay_method=="cash")
	
	{
	  $('#cheque_no').removeClass("required");
	
	  $('#cheque_no').val('');
		$('#cheque_no').prop('readonly', true);
	
	}
	
	else
	{

		$('#cheque_no').removeAttr("readonly");
	}
	
}
  if(pay_method=="e-Payment" && $('#tp').val()=='Received')
	{
	$(".pdtl").show(); $('#paydetail').addClass("required");
	}else {
		$(".pdtl").hide(); $('#paydetail').val(''); $('#paydetail').removeClass("required");
	}
}	 

function getAmt() {
    let customerId = $("#customer").val();
    let payId = $("#loan_id").val() ?? null;

    if (!customerId) return;

    $.ajax({
        url: "{{ route('loan.getPending') }}",
        type: "GET",
        data: {
            customer_id: customerId,
            pay_id: payId
        },
        success: function (data) {
            $("#pendingAmt").text(data.pending_text);
            $("#amount_pay_old").val(data.pending_value);
        }
    });
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
function updateAmountTitle() {
    let paytype = $('input[name="paytype"]:checked').val();

    if (paytype === 'Paid') {
        $('#amount_title').text('Amount Paid');
        $('#tp').val('Paid');
    } 
    else if (paytype === 'Received') {
        $('#amount_title').text('Amount Received');
        $('#tp').val('Received');
    }
}
$('#Paid').on('click', function () {
    if (this.checked) {
        $('#tp').val('Paid');
        $("#amtlbl").html('Amount Paid');
        $('#balanctitle').show();
        $('#balancevalue').show();
        $('#taken').hide();

        $.ajax({
            url: "{{ route('get.pay.type') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                type: "GetPay_type"
            },
            success: function (data) {
                $("#Div_paytype").html(data);
                check_type(); // <-- rename this to checkType();
            }
        });
    }
});


$('#Received').on('click', function () {
    if (this.checked) {

        $('#tp').val('Received');
        $("#amtlbl").html('Amount Received');
        $('#balanctitle').hide();
        $('#balancevalue').hide();
        $('#taken').show();

        $.ajax({
            url: "{{ route('get.receivepaytype') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                type: "GetPaytype"
            },
            success: function (data) {
                $("#Div_paytype").html(data);
                check_type();
            }
        });
    }
});

function chek_amt() {
    var amount = $('#amount_pay').val();
    var pendingAmt = $('#amount_pay_old').val();
    var balance = $('#balance').val();
    var paymenType = $('#tp').val();

    if (paymenType === 'Paid') {

        if (parseFloat(balance) >= parseFloat(amount)) {

            if (parseFloat(pendingAmt) !== 0) {
                if (parseFloat(amount) > parseFloat(pendingAmt)) {
                    $("#amount_pay").val('');
                    $("#amount_pay").focus();
                    $("#other").show().html("<label>Please Check Total Amount!</label>");
                    $("#submit").hide();
                    return;
                }
            }

            $("#submit").show();
            $("#other").hide();

        } else {
            $("#other").show().html(
                "<label>You don't have enough balance to make this payment!</label>"
            );
            $("#submit").hide();
        }

    } else {

        if (parseFloat(pendingAmt) !== 0) {
            if (parseFloat(amount) > parseFloat(pendingAmt)) {
                $("#amount_pay").val('');
                $("#amount_pay").focus();
                $("#other").show().html("<label>Please Check Total Amount!</label>");
                $("#submit").hide();
                return;
            }
        }

        $("#submit").show();
        $("#other").hide();
    }

    cal_total();
}



</script>
<script>
window.onload = function () {
  getBalance();
  updateAmountTitle();

  
};
</script>
@endsection