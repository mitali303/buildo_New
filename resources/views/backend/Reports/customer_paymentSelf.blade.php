@extends('backend.partials.master')

@section('title', 'Self Payment')

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

    <div class="row mb-3">
        <div class="col">
            <h3>Self Payment</h3>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table id="customer-payment-table" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Flat No</th>
                        <th>Wing</th>
                        <th>Grand Total</th>
                        <th>Total Paid Amt</th>
                        <th>Total Pending Amt</th>
                        <th>Payment By</th>
                        
                    </tr>
                </thead>
            </table>
            </div>
        </div>
    </div>

</div>
</main>
@endsection

@push('scripts')
<script>
$(function () {
    $('#customer-payment-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('reports.customer_payment.self') }}?type={{ request('type') }}&SCHEME={{ request('SCHEME') }}",
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'customer_name' },
            { data: 'flat_no' },
            { data: 'wing' },
            { data: 'grand_total' },

            { data: 'total_paid' },
            { data: 'total_pending' },
            { data: 'payment_by' },
            
        ],
        drawCallback: function () {
            feather.replace();
        }
    });
});
</script>
@endpush
