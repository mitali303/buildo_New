@extends('backend.partials.master')
@section('title', 'Customer Payment')

@section('maincontent')
<main class="content">
  <div class="container-fluid p-0">
    <div class="mb-3">
      <h1 class="h3 d-inline align-middle">Customer Payment</h1>
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


          <div class="card-body">
            <form action="@if(!empty($customerpayment)){{ route('customer_payment.update') }}@else{{ route('customer_payment.store') }}@endif"
                  method="POST" enctype="multipart/form-data">
              @csrf
              
              @if(!empty($customerpayment))
                @method('PUT')
                <input type="hidden" name="ID" value="{{ $customerpayment->ID }}">
              @endif
              {{--<input type="hidden" name="purchasesid" value="{{ $purchases->id }}">--}}
              <div class="row">
                <div class="mb-3 col-md-3">
                    <label class="form-label" for="date">Date <small class="text-danger">*</small></label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror"  name="date" id="date" value="{{ old('date', $customerpayment->Date ?? date('Y-m-d')) }}" required>
                    @error('date')
                      <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3 col-md-3">
                  <label for="scheme" class="form-label">Scheme <small class="text-danger">*</small></label>
                  <select name="scheme" id="scheme" class="form-control choices-single" required  onchange="GetFlatDetail();">
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
            
                  <div class="mb-3 col-md-3" id="Type_div">
                      <label for="wing" class="form-label">Wing <small class="text-danger">*</small></label>
                         <select name="typesrch" id="typesrch" class="input-sm form-control chosen-select  @error('typesrch') is-invalid @enderror" data-placeholder="Select" onchange="wing();" >
                          <option value="">Select Wing</option>
                          @php
                              if(!empty($customerpayment)){

                                  $type_details = \DB::table('flats_details')
                                                  ->select('Wing')
                                                  ->where('scheme_ID', $customerpayment->schemeID)
                                                  ->groupBy('Wing')
                                                  ->get();

                              }else{
                                    $type_details = \DB::table('flats_details')
                                                  ->select('Wing')
                                                  ->where('scheme_ID', $clientId)
                                                  ->groupBy('Wing')
                                                  ->get();
                              }
                            
                          @endphp

                          @foreach($type_details as $type_rec)
                              <option value="{{ $type_rec->Wing }}" 
                                  {{ old('typesrch', $customerpayment->Wing ?? '') == $type_rec->Wing ? 'selected' : '' }}>
                              {{ $type_rec->Wing }}
                              </option>
                          @endforeach
                      </select>
                      @error('typesrch')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                      <input type="hidden" id="schemeH" name="schemeH" value="{{ $clientId ?? 0 }}">
                  </div>
                  
                  <div class="mb-3 col-md-3" >
                     <label for="flat_no" class="form-label">Flat No <small class="text-danger">*</small></label>
                     <div id="fcatdiv">
                      <select name="FlatID" id="FlatID" class="input-sm form-control chosen-select @error('FlatID') is-invalid @enderror" data-placeholder="Select Flat No" onchange="getCustomerDetails();">
                        <option>Select Flat</option>
                         @foreach($flats as $flat)
                          <option value="{{ $flat->ID }}" 
                             {{ old('FlatID', $customerpayment->FlatID ?? '') == $flat->ID ? 'selected' : '' }}>
                              {{ $flat->FlatNo }}
                          </option>
                        @endforeach

                           
                      </select>
                      @error('FlatID')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                  </div>


                    
              </div>

              <div class="row" >
                  
                    <div class="col-md-3" id="cust_div">
                      <label class="form-label">Customer</label>
                      <input type="text" readonly  class="input-sm form-control" id="Customer"  name="Customer" value="{{ $booking_cust->CutomerName ?? ''}}"  />
                  
                  </div>
                    <input type="hidden"   class=" form-control" id="CustomerID"  name="CustomerID" value="{{ $booking_cust->ID  ?? ''}}"  />
                    
                      
                  <div class="col-md-3" >
                    <label class="form-label">Pending Amount:</label><br>
                        <lable id="pendingAmt"  name="pendingAmt"></lable>
                  </div>

                 
                  <div class="col-md-3" id="bill">
                    <label class="form-label">Payment</label><br>
				            <select name="bill_pay" id="bill_pay" class="input-sm form-control chosen-select required" data-placeholder="Select">
						          <option value="">Select</option> 
                      <option value="1" {{ old('bill_pay', $customerpayment->bill_payment ?? '') == '1' ? 'selected' : '' }}>Extra Work Payment</option>  
						          <option value="0" {{ old('bill_pay', $customerpayment->bill_payment ?? '') == '0' ? 'selected' : '' }} >Flat Payment</option>  
                    </select>
                </div>
                  <div class="col-md-3" id="bill">
                    <label class="form-label">Payment By</label><br>
				            <select name="payement_by" id="payement_by" class="input-sm form-control chosen-select required" data-placeholder="Select">
						          <option value="">Select</option> 
                      <option value="self" {{ old('payement_by', $customerpayment->payement_by ?? '') == 'self' ? 'selected' : '' }}>Self</option>  
						          <option value="bank" {{ old('payement_by', $customerpayment->bank ?? '') == 'bank' ? 'selected' : '' }} >Bank Payment</option>  
                    </select>
                </div>
                            
                  
				    </div>
             

              <div class="row">
                <table class="table" id="mytable">
                  <thead>
                    <tr>
                     
                      <th>Payment Method <small class="text-danger">*</small></th>
                      <th>Receipt No</th>
                      <th>Account No<small class="text-danger">*</small></th>
                      <th>Cheque No / Transaction ID</th>
                      <th>Amount<small class="text-danger">*</small></th>
                      <th>Narration</th>
                      
                      
                      
                    </tr>
                  </thead>
                  <tbody>
                    <tr>

                      <td>
                        <select name="Pay_type" id="Pay_type" class="form-control @error('Pay_type') is-invalid @enderror" onChange="check_type();">
                          <option value="">Select Payment Method</option>
                          <option value="cash" {{ old('payment_method', $customerpayment->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                          <option value="cheque" {{ old('payment_method', $customerpayment->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                          <option value="e-payment" {{ old('payment_method', $customerpayment->payment_method ?? '') == 'e-payment' ? 'selected' : '' }}>E‑payment</option>
                        </select>
                        @error('Pay_type')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                      </td>
                      <td>
                        <input type="text" style=""   readonly name="receipt_no" id="receipt_no"  value="{{ old('receipt_no', $customerpayment->receipt_no ?? ($orderNo ?? '')) }}" class=" form-control required"/>
                      </td>
                      <td>
                        <div id="Ac" style="">
                        <select name="account_no" id="account_no" class="form-control @error('account_no') is-invalid @enderror" >
                          <option value="">Select Account</option>
                          @foreach($banks as $bank)
                            <option value="{{ $bank->ID }}"
                              {{ old('account_no', $customerpayment->account_no ?? '') == $bank->ID ? 'selected' : '' }}>
                              {{ $bank->Name }}
                            </option>
                          @endforeach
                        </select>
                        @error('account_no')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                      </td>
                      <td>
                        <input type="text" name="cheque_no" id="cheque_no" class="form-control" value="{{ old('cheque_no', $customerpayment->cheque_no ?? '') }}">
                      </td>
                      
                      
                      <td>
                        <input type="number" name="amount_pay" id="amount_pay"
                            class="form-control @error('amount_pay') is-invalid @enderror"
                            value="{{ old('amount_pay', $customerpayment->amt_pay ?? '') }}" onkeyup="chek_amt();">

                            <input type="hidden"  style=""name="amount_pay_old" id="amount_pay_old"  value="<?php //echo $pendingAmt;?>" onkeyup="" class=" input-md form-control "/>

                        @error('amount_pay')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </td>
                      <td>
                        <textarea type="text" style="height: 34px;"    class=" input-medium form-control"  id="narration"  name="narration" >{{ old('narration', $customerpayment->narration ?? '') }}</textarea>
                      </td>
                      
                    </tr>
                  </tbody>
                </table>
              </div>

              <br>
              <button type="submit" class="btn btn-primary">{{ empty($customerpayment) ? 'Create' : 'Update' }}</button>
              <a href="{{ route('customer_payment') }}" class="btn btn-secondary">Cancel</a>
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
function GetFlatDetail() {
    let scheme = $('#scheme').val();

    $.ajax({
        url: "{{ route('get.wing.no') }}",
        type: "POST",
        data: {
            schmid: scheme,
            _token: "{{ csrf_token() }}"
        },
        success: function (data) {
            $("#Type_div").html(data);
        }
    });
}

function wing() {
    var wing = $('#typesrch').val();
    var scheme = $("#schemeH").val();

    $.ajax({
        url: '{{ route("booking.getFlats") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            schmid: scheme,
            wing: wing
        },
        success: function(data) {
            $("#fcatdiv").html(data);
            $('.chosen-select').chosen(); // reinitialize
        }
    });
}

function getCustomerDetails() {
    var flatID = $("#FlatID").val();

    $.ajax({
        url: "{{ route('flats.getCustomer') }}", // Laravel route
        type: 'POST',
        data: {
            flatID: flatID,
            _token: "{{ csrf_token() }}" // CSRF token for Laravel
        },
        success: function(data) {
            $('#Customer').val(data.customer_name);
            $('#CustomerID').val(data.customer_id);
            $('#pendingAmt').html(data.pending_amount);

            getAmt();
        }
    });
}
function check_type() {
    let pay_method = $('#Pay_type').val();

    $.ajax({
        url: "{{ route('ajax.account.by.payment') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            pay_method: pay_method
        },
        success: function (response) {
            $('#Ac').html(response);
            getBalance();
        }
    });
    if(pay_method=="cash")
	{	$('#cheque_no').val('');
		$('#cheque_no').prop('readonly', true);
	    $('#cheque_no').removeClass("required");
	
	}
	
	else
	{
	 
		$('#account_no').removeAttr("readonly");
		$('#cheque_no').removeAttr("readonly");
	}
}
function getAmt() {
    let flatID = $("#FlatID").val();
    let Task   = $("#Task").val();

    let PayID = (Task === 'update' || Task === 'view')
        ? $("#Uid").val()
        : '00000';

    $.ajax({
        url: "{{ route('ajax.get.booking.pending') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            flatID: flatID,
            PayID: PayID
        },
        success: function (data) {
            $("#pendingAmt").html(data);
            $("#amount_pay_old").val(data);
        }
    });
}
</script>
<script>
window.onload = function () {
  getAmt();
   
};
</script>

@endsection