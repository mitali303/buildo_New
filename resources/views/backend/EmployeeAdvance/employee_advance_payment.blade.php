@extends('backend.partials.master')
@section('title')
   Employee Advance Payment
@endsection
@section('maincontent')

                        <main class="content">
                            <div class="container-fluid p-0">

                                <div class="mb-3">
                                    <h1 class="h3 d-inline align-middle">Create Employee Advance Payment
                        </h1>
                                </div>

                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <form action="{{ route('EmployeeAdvancePayment.store') }}" method="POST">
                                                        @csrf
                                                    @if(!empty($old))
                                                        <input type="hidden" name="id" id="id" value="{{ $old->id }}"/>
                                                    @endif
                                                    <div class="row">

                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="emp_id">Employee Name<small class="text-danger">*</small></label>
                            <input type="text" class="form-control" value="{{ $user->Name ?? '' }}" name="emp_id" id="emp_id"
                                readonly>
                            @error('emp_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                                <div class="mb-3 col-md-4">
                                <label class="form-label" for="advance">Advance<small class="text-danger">*</small></label>
                                <input type="number" class="form-control @error('advance') is-invalid @enderror"
                                    value="{{ old('advance', $old->advance ?? '') }}" name="advance" id="advance"
                                    placeholder="Advance" readonly>
                                @error('advance')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            @php
                            use App\Models\backend\Empadv_Pay;

                                $paid = \App\Models\backend\Empadv_Pay::where('parent_id', $old->id)->sum('amt_pay');
                                $remaining = $old->advance - $paid;
                            @endphp


                            <div class="mb-3 col-md-4">
                                <label class="form-label" for="remaining">Remaining<small class="text-danger">*</small></label>
                                <input type="number" class="form-control @error('remaining') is-invalid @enderror"
                                    value="{{ old('remaining', $remaining ?? '') }}" name="remaining" id="remaining"
                                    placeholder="remaining" readonly>
                                @error('remaining')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                                                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="date">Date<small class="text-danger">*</small></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                name="date" id="date"
                                value="{{ old('date', isset($old->Date) ? $old->Date : date('Y-m-d')) }}"
                                placeholder="Date">
                            @error('date')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                             <div class="mb-3 col-md-4">
                        <label class="form-label">Payment Method<small class="text-danger">*</small></label>
                        <select name="payment_method" id="payment_method" class="form-control">
                        <option value="">Select</option>

                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>
                            Cash
                        </option>

                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>
                            Cheque
                        </option>

                        <option value="DD" {{ old('payment_method') == 'DD' ? 'selected' : '' }}>
                            DD
                        </option>
                    </select>
                        @error('payment_method')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>


                    <div class="mb-3 col-md-4">
                        <label class="form-label">Account No<small class="text-danger">*</small></label>
                        <div id="Ac">
                            <select name="account_no" id="account_no" class="form-control">
                                <option value="">Select</option>
                            </select>
                        </div>
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
                            <label class="form-label" for="amt_pay">Amount Pay<small class="text-danger">*</small></label>
                            <input type="number" class="form-control @error('amt_pay') is-invalid @enderror"
                                value="{{ old('amt_pay') }}" name="amt_pay" id="amt_pay"
                                placeholder="Remaining Amount" >
                            @error('amt_pay')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="narration">Narration<small class="text-danger">*</small></label>
                            <input type="text" class="form-control @error('narration') is-invalid @enderror"
                                value="" name="narration" id="narration"
                                placeholder="Narration" >
                            @error('narration')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($old) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('EmployeeAdvance') }}" class="btn btn-secondary">Cancel</a>
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
// Payment method change → load account list
$(document).on('change', '#payment_method', function () {

    let pay_method = $(this).val();

    if (!pay_method) {
        $("#Ac").html(`
            <select name="account_no" id="account_no" class="form-control">
                <option value="">Select</option>
            </select>
        `);
        return;
    }

    $.ajax({
        url: "{{ route('ajax.getAccountList') }}",
        type: "POST",
        data: {
            pay_method: pay_method,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            $("#Ac").html(response.html);
        },
        error: function () {
            alert("Failed to load accounts");
        }
    });

});
</script>