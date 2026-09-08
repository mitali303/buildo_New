@extends('backend.partials.master')
@section('title', 'Account transfer')

@section('maincontent')
<main class="content">
  <div class="container-fluid p-0">
    <div class="mb-3">
      <h1 class="h3 d-inline align-middle">Account transfer</h1>
    </div>
    <style>
      .pdtl {
        display: none;
      }
    </style>
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
            <form action="@if(!empty($accounttransfers)){{ route('account_transfer.update') }}@else{{ route('account_transfer.store') }}@endif"
              method="POST" enctype="multipart/form-data">
              @csrf

              @if(!empty($accounttransfers))
              @method('PUT')
              <input type="hidden" name="ID" value="{{ $accounttransfers->ID }}">
              @endif
              {{--<input type="hidden" name="purchasesid" value="{{ $purchases->id }}">--}}



              <div class="row">
                <table class="table" id="mytable">
                  <thead>
                    <tr>

                      <th>Date</th>
                      <th>Amount from<small class="text-danger">*</small></th>
                      <th id="balance_title">Balance<small class="text-danger">*</small></th>
                      <th>Account To<small class="text-danger">*</small></th>
                      <th>Amount<small class="text-danger">*</small></th>
                      <th>Payment Method <small class="text-danger">*</small></th>
                      <th>Cheque No / Transaction ID<span class="text-danger" id="cheque_field">*</span></th>

                    </tr>
                  </thead>
                  <tbody>
                    <tr>

                      <td>
                        <input type="date" name="Date" class="form-control @error('Date') is-invalid @enderror" value="{{ old('Date', $accounttransfers->Date ?? date('Y-m-d')) }}">

                        @error('Date')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror

                      </td>
                      <td>
                        <select name="account_no" id="account_no" class="form-control" required onchange="getBalance();">
                          <option value="">Select Account</option>
                          @foreach($banks as $bank)
                          <option value="{{ $bank->ID }}"
                            {{ old('account_from', $accounttransfers->account_from ?? '') == $bank->ID ? 'selected' : '' }}>
                            {{ $bank->Name }}
                          </option>
                          @endforeach
                        </select>
                        @error('account_no_to')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror

                      </td>
                      <td id="balancevalue">
                        <input type="text" name="balance" id="balance" class="form-control"
                          value="{{ old('balance', $accounttransfers->balance ?? '') }}" required>
                      </td>
                      <td>
                        <select name="account_no_to" id="account_no_to" class="form-control " required onchange="checkselected();">
                          <option value="">Select Account</option>
                          @foreach($banks as $bank)
                          <option value="{{ $bank->ID }}"
                            {{ old('account_to', $accounttransfers->account_to ?? '') == $bank->ID ? 'selected' : '' }}>
                            {{ $bank->Name }}
                          </option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <input type="number" name="total_pay" required
                          class="form-control @error('total_pay') is-invalid @enderror"
                          value="{{ old('total_pay', $accounttransfers->amt_pay ?? '') }}">

                        @error('total_pay')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror


                      </td>
                      <td>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                          <option value="">Select Payment Method</option>
                          <option value="cash" {{ old('payment_method', $accounttransfers->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                          <option value="cheque" {{ old('payment_method', $accounttransfers->payment_method ?? '') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                          <option value="e-payment" {{ old('payment_method', $accounttransfers->payment_method ?? '') == 'e-payment' ? 'selected' : '' }}>E‑payment</option>
                        </select>
                        @error('payment_method')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror

                      </td>


                      <td>
                        <input type="text" name="cheque_no" id="cheque_no" class="form-control" value="{{ old('cheque_no', $accounttransfers->cheque_no ?? '') }}">
                      </td>

                    </tr>
                  </tbody>
                </table>
              </div>

              <br>
              <button type="submit" class="btn btn-primary">{{ empty($accounttransfers) ? 'Create' : 'Update' }}</button>
              <a href="{{ route('account_transfer') }}" class="btn btn-secondary">Cancel</a>
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
  function getType() {

    var pay_method = $('#payment_method').val();
    var type = $('#account_no').val();
    if (pay_method == "e-Payment" && $('#tp').val() == 'Paid') {
      $('#banktitle').show();
      $('#totalvalue').show();
      $('#bankvalue').show();
      $('#totalamt').show();
      $('#cheque_no').removeAttr("readonly");
      $('#bnk_charge').addClass("required");
    } else {


      $('#banktitle').hide();
      $('#totalvalue').hide();
      $('#bnk_charge').val(0);
      $('#bnk_charge').removeClass("required");
      $('#bankvalue').hide();
      $('#totalamt').hide();

      if (pay_method == "cash")

      {
        $('#cheque_no').removeClass("required");

        $('#cheque_no').val('');
        $('#cheque_no').prop('readonly', true);



      } else {

        $('#cheque_no').removeAttr("readonly");
      }

    }
    if (pay_method == "e-Payment" && $('#tp').val() == 'Received') {
      $(".pdtl").show();
      $('#paydetail').addClass("required");
    } else {
      $(".pdtl").hide();
      $('#paydetail').val('');
      $('#paydetail').removeClass("required");
    }
  }

  function getAmt() {

    let customerId = $("#customer").val(); // Correct ID
    let payId = $("#loan_id").val() ?? null; // Hidden input for edit (optional)

    if (!customerId) return;

    $.ajax({
      url: "{{ route('get.account.balance') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        matid: accountId,
        payID: '0000'
      },
      success: function(res) {
        $('#balance').val(res.balance);
      }
    });

  }

  function getBalance() {

    //alert("hii");

    let matid = $("#account_no").val();
    let task = $("#Task").val();
    let payID = (task === 'update' || task === 'view') ?
      $("#Uid").val() :
      '00000';
    $.ajax({
      url: "{{ route('get.account.balance') }}",
      type: "POST",
      data: {
        _token: '{{ csrf_token() }}', // send CSRF token
        matid: matid,
        payID: payID
      },
      success: function(data) {
        $("#balance").val(data.balance);
      },
      error: function(xhr) {
        console.error(xhr.responseText);
      }
    });

  }

  function checkselected() {
    var af = $('#account_no').val();
    var at = $('#account_no_to').val();
    if (af == at) {
      alert('please select another Account');
      $('#account_no_to').val('');
      $('#account_no_to').trigger('chosen:updated');
    }
  }

  function chkbal() {
    var bal = $('#balance').val();
    var total_pay = $('#total_pay').val();

    if (parseFloat(bal) < parseFloat(total_pay)) {
      alert("Amount Exceeds Available Balance!!!!!!!");
      $('#total_pay').val('');
    }
  }
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {

    function toggleChequeField() {

      let paymentMethod = document.getElementById('payment_method').value;
      let chequeField = document.getElementById('cheque_field');
      let chequeInput = document.getElementById('cheque_no');

      if (paymentMethod === 'cheque' || paymentMethod === 'e-payment') {

        chequeField.style.display = 'table-cell';
        chequeInput.required = true;

      } else {

        chequeField.style.display = 'none';
        chequeInput.required = false;
        chequeInput.value = '';
      }
    }

    // run on change
    document.getElementById('payment_method').addEventListener('change', toggleChequeField);

    // run on page load (for edit form)
    toggleChequeField();

  });
</script>

<script>
  $(document).ready(function() {

    $('#total_pay').on('blur', function() {

      let pay = parseFloat($(this).val()) || 0;
      let balance = parseFloat($('#account_balance').val()) || 0;

      if (pay > balance) {
        alert("Amount cannot be greater than account balance");
        $(this).val('');
      }
    });

  });
</script>

@endsection