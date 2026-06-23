@extends('backend.partials.master')
@section('title', 'Customer Payment Report')

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

    <h3 class="mb-3">Customer Payment Report</h3>

    <div class="card">
        <div class="card-body">

            {{-- CUSTOMER FILTER --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Select Customer</label>
                    <select id="customer_id" class="form-control choices-single-customer">
                        <option value="">Select Customer</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->ID }}">
                                {{ $cust->CutomerName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table table-bordered" id="reportTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Receipt No</th>
                            <th>Payment Method</th>
                            <th>Payment By</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

</div>
</main>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-customer"));
    });

$(document).ready(function () {

    $('#customer_id').change(function () {

        let customerId = $(this).val();

        if (!customerId) return;

        $.ajax({
            url: "{{ route('customer.payment.report.data') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                customer_id: customerId
            },
            success: function (data) {

            let rows = '';
            let total = 0; // ✅ total variable

            if (data.length === 0) {
                rows = `<tr><td colspan="6" class="text-center">No Data Found</td></tr>`;
            }

            data.forEach(function (row) {

                let amount = parseFloat(row.amount) || 0;
                total += amount; // ✅ accumulate total

                rows += `
                    <tr>
                        <td>${row.date ?? ''}</td>
                        <td>${row.receipt_no ?? ''}</td>
                        <td>${row.payment_method ?? ''}</td>
                        <td>${row.payment_by ?? ''}</td>
                        <td>${row.type ?? ''}</td>
                        <td>${amount.toFixed(2)}</td>
                    </tr>
                `;
            });

            // ✅ ADD TOTAL ROW
            rows += `
                <tr style="background:#f1f1f1; font-weight:bold;">
                    <td colspan="5" class="text-end">Total Paid</td>
                    <td>${total.toFixed(2)}</td>
                </tr>
            `;

            $('#reportTable tbody').html(rows);
        }
        });

    });

});
</script>
@endsection