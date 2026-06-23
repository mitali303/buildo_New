@extends('backend.partials.master')
@section('title', 'Customer Refund')

@section('maincontent')
<main class="content">
  <div class="container-fluid p-0">
    <div class="mb-3">
      <h1 class="h3 d-inline align-middle">Customer Refund</h1>
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
            <form action="@if(!empty($postdated)){{ route('customer_refund.update') }}@else{{ route('customer_refund.store') }}@endif"
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
                  <select name="schem" id="schem" class="form-control choices-single" required  onchange="GetbookCustomer();">
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
            
                 <div class="mb-3 col-md-3" id="cust_div">
                      <label class="form-label">Customer<small class="text-danger">*</small></label></label>
                       <div class="" id="custDiv">
				                  <select name="customer" id="customer"  class="form-control choices-single" data-placeholder="Select" onchange="getTotalPaid();">
                          <option value="">Select Customer</option>
                            @if (!empty($postdated))
                                @foreach ($booking_cust as $book_rec)
                                    <option value="{{ $book_rec->ID }}"
                                        {{ ($postdated->bookingcustomer == $book_rec->ID) ? 'selected' : '' }}>
                                        {{ $book_rec->CutomerName }}
                                    </option>
                                @endforeach
                            @else
                                @foreach ($booking_cust as $book_rec)
                                    <option value="{{ $book_rec->ID }}"
                                        {{ ($db_record->bookingcustomer == $book_rec->ID) ? 'selected' : '' }}>
                                        {{ $book_rec->CutomerName }}
                                    </option>
                                @endforeach
                            @endif

                          </select>
                        </div>
                  
                  </div>
                  <div class="col-md-3" >
                    <label class="form-label">Total Paid Amount:</label><br>
                        <div class="" id="paidamt">                 
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
                          <option value="cash" {{ old('payment_method', $postdated->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                          <option value="cheque" {{ old('payment_method', $postdated->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                          <option value="e-payment" {{ old('payment_method', $postdated->payment_method ?? '') == 'e-payment' ? 'selected' : '' }}>E‑payment</option>
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
                      </td>
                      
                      <td id="bankvalue" style="<?php echo $PayStyle ?>">
                        <input type="text" style="width: 100px"  <?php //echo $notread;?> name="bnk_charge" id="bnk_charge"   value="{{ old('bnk_charge', $postdated->amt_pay ?? '') }}"  onkeyup="cal_total();"  class=" form-control"/>
                      </td>
			                <td id="totalamt" style="<?php echo $PayStyle ?>">
                        <input type="text" style="width: 100px" readonly <?php //echo $notread;?> name="total_pay" id="total_pay"   value="{{ old('total_pay', $postdated->amt_pay ?? '') }}"  onkeyup="chkamt();cal_total();"  class=" form-control"/>
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
              <a href="{{ route('customer_refund') }}" class="btn btn-secondary">Cancel</a>
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
function GetbookCustomer(val) {
    $.ajax({
        url: "{{ route('get.booking.customer') }}",
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
function getTotalPaid() {
    let cust = $('#customer').val();
    let uid  = $('#Uid').val() || '000';
    let schm = $('#schem').val();

    if (!cust) {
        $('#paidamt').html('');
        return;
    }

    $.ajax({
        url: "{{ route('get.total.paid') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            bookingID: cust,
            Uid: uid,
            Schm: schm
        },
        success: function (data) {
            $('#paidamt').html(data.totalPaid);
        }
    });
}


function check_type() {
    var pay_method = $('#Pay_type').val();
    var task = $('#Task').val();

    // AJAX call to Laravel route
    $.ajax({
        url: '{{ route("ajax.getAccountOptions") }}',
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
        url: '{{ route("get.balance") }}',
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


</script>
<script>
window.onload = function () {
  getBalance();
   getTotalPaid();
    //console.log("Page fully loaded");
};
</script>


@endsection