<input type="hidden" name="Bookid" value="{{ $booking->ID }}">
<input type="hidden" name="flatID" value="{{ $flat->ID }}">
<input type="hidden" name="cancelid" value="{{ $cancelId }}">

<h3>Flat Details</h3>

<table class="table table-bordered mytbl">
    <tr>
        <td>Flat No</td>
        <td>{{ $flat->FlatNo }}</td>
        <td>Floor</td>
        <td>{{ $flat->Floor }}</td>
    </tr>
    <tr>
        <td>Flat Category</td>
        <td>{{ $flat->FlatType }}</td>
        <td>Flat Attribute</td>
        <td>{{ $flat->FlatAttribute }}</td>
    </tr>
    <tr>
        <td>Flat Area</td>
        <td>{{ $flat->Area }}</td>
        <td>Terrace Area</td>
        <td>{{ $flat->Terrace }}</td>
    </tr>
    <tr>
        <td>Total Sq Ft</td>
        <td>{{ $flat->TotalSqFt }}</td>
        <td>Total Received</td>
        <td>{{ $bookingPay }}</td>
    </tr>
</table>

<hr>

<div class="row">
    <div class="col-md-6">
        <label>Fine Amount</label>
        <input type="text"
               name="fineamt"
               id="fineamt"
               class="form-control"
               value="{{ $bc_record->FineAmt ?? '' }}"
               onkeyup="calcdue(this.value)">
               @error('fineamt')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
    </div>

    <div class="col-md-6">
        <label>Due Amount</label>
        <input type="text"
               name="dueamt"
               id="dueamt"
               class="form-control"
               readonly
               value="{{ $pendingAmt }}">
               @error('dueamt')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
    </div>
    <input type="hidden" id="original_due" value="{{ $pendingAmt }}">

</div>

<hr>

<h4>Payment Details</h4>

<table id="pmttble" class="table table-bordered table-hover" style="width:95%;">
    <thead>
        <tr>
            <th style="width:15%;">Payment Method</th>
            <th style="width:15%;">Account No.</th>
            <th style="width:15%;" id="balance_th">Balance</th>
            <th style="width:12%;">Amount Paid</th>
            <th style="width:15%;">Cheque / Txn ID</th>
            <th style="width:12%; display:none;" id="banktitle">Bank Charges</th>
            <th style="width:12%; display:none;" id="bankdetails">Bank Details</th>
            <th style="width:10%;">Payable</th>
            <th style="width:20%;">Narration</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            {{-- Payment Method --}}
            <td>
                <select name="Pay_type" id="Pay_type" class="form-control" onchange="check_type()" >
                    <option value="">SELECT</option>
                    <option value="cash" {{ ($bc_record->payment_method ?? '')=='cash'?'selected':'' }}>Cash</option>
                    <option value="cheque" {{ ($bc_record->payment_method ?? '')=='cheque'?'selected':'' }}>Cheque</option>
                    <option value="e-Payment" {{ ($bc_record->payment_method ?? '')=='e-Payment'?'selected':'' }}>E-Payment</option>
                    <option value="RTGS" {{ ($bc_record->payment_method ?? '')=='RTGS'?'selected':'' }}>RTGS</option>
                </select>
                @error('Pay_type')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </td>
            {{-- Account --}}
            <td>
                <div id="Ac">
                    <select id="account_no" name="account_no" class="form-control" onchange="getBalance()">
                        <option value="">Select</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->ID }}"
                                {{ ($bc_record->account_no ?? '') == $acc->ID ? 'selected' : '' }}>
                                {{ $acc->Name }} ({{ substr($acc->ACNo, -3) }})
                            </option>
                        @endforeach
                    </select>
                    @error('account_no')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </td>

            {{-- Balance --}}
            <td>
                <input type="text" readonly class="form-control"
                       id="balanceamt" name="balanceamt">
                @error('balanceamt')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </td>

            {{-- Amount Paid --}}
            <td>
                <input type="text"
                       name="amount_pay"
                       id="amount_pay"
                       class="form-control"
                       value="{{ $bc_record->amt_pay ?? $pendingAmt }}"
                       oninput="calculatePayable()">

                <input type="hidden"
                       name="amount_pay_old"
                       id="amount_pay_old"
                       value="{{ $pendingAmt }}">

                @error('amount_pay')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </td>

            {{-- Cheque / Txn --}}
            <td>
                <input type="text"
                       name="cheque_no"
                       id="cheque_no"
                       class="form-control"
                       value="{{ $bc_record->cheque_no ?? '' }}">
                @error('cheque_no')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </td>

            {{-- Bank Charges --}}
            <td id="bankvalue" style="display:none;">
                <input type="text"
                       name="bnk_charge"
                       id="bnk_charge"
                       class="form-control"
                       value="{{ $bc_record->bankcharge ?? 0 }}"
                       oninput="calculatePayable()">
                
            </td>

            {{-- Bank Details --}}
            <td id="bankvaluedet" style="display:none;">
                <textarea name="paydetail"
                          id="paydetail"
                          class="form-control"
                          style="height:34px;">{{ $bc_record->paydetail ?? '' }}</textarea>
            </td>

            {{-- Payable --}}
            <td>
                <input type="text"
                       readonly
                       name="payable"
                       id="payable"
                       class="form-control">
            </td>

            {{-- Narration --}}
            <td>
                <textarea name="narration"
                          id="narration"
                          class="form-control"
                          style="height:34px;">{{ $bc_record->narration ?? '' }}</textarea>
            </td>
        </tr>
    </tbody>
</table>
<script>
function check_type() {

    if (!$('#Pay_type').length) return;

    var pay_method = $('#Pay_type').val();

    $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: pay_method,
            selected_id: window.selectedAccountId || null, // 🔥 KEY LINE
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {

            // Replace account dropdown
            $("#Ac").html(response.html);

            // 🔥 account is now selected → fetch balance
            getBalance();
        }
    });

    $('#banktitle,#bankvalue,#bankdetails,#bankvaluedet').hide();

    if (pay_method === "e-Payment" || pay_method === "RTGS") {
        $('#cheque_no').prop('readonly', false);
    } else {
        $('#cheque_no').prop('readonly', pay_method === "cash");
    }
}

</script>
<script>
function getBalance() {

    let accId = $("#account_no").val();
    // let date  = $("#datepicker").val();

    if (!accId) {
        $("#balanceamt").val("");
        return;
    }

    $.ajax({
        url: "{{ route('ajax.getBalance') }}",
        type: "POST",
        data: {
            matid: accId,
            // date: date,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            $("#balanceamt").val(response.balance);
            validateAmountVsBalance();
            calculatePayable();
        }
    });
}
</script>
<script>
function calculatePayable() {

    let amount  = parseFloat($("#amount_pay").val()) || 0;
    let bankch  = parseFloat($("#bnk_charge").val()) || 0;

    let payable = amount + bankch;

    if (payable < 0) payable = 0;

    $("#payable").val(payable.toFixed(2));
}
</script>
<script>
function validateAmountVsBalance() {

    let balance = parseFloat($("#balanceamt").val()) || 0;
    let amount  = parseFloat($("#amount_pay").val()) || 0;

    if (balance > 0 && amount > balance) {
        alert("Amount Paid cannot be greater than Balance Amount");
        $("#amount_pay").val(balance.toFixed(2));
    }
}
</script>
<script>
function calcdue(fineVal) {

    let originalDue = parseFloat($("#original_due").val()) || 0;
    let fine        = parseFloat(fineVal) || 0;

    // prevent negative due
    let newDue = originalDue - fine;
    if (newDue < 0) newDue = 0;

    // update Due Amount
    $("#dueamt").val(newDue.toFixed(2));

    // auto-set amount paid to new due
    $("#amount_pay").val(newDue.toFixed(2));

    // recalc payable
    calculatePayable();
}
</script>

<script>
    window.selectedAccountId = "{{ $bc_record->account_no ?? '' }}";

    // 🔔 Debug: alert when this AJAX view is injected
    // alert('Selected Account ID: ' + window.selectedAccountId);
</script>

