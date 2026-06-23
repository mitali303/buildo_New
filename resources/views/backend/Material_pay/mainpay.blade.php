@extends('backend.partials.master')

@section('title', 'Invoice Payments')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">Invoice Payment List</h1>

            <a href="{{ route('Material_pay') }}" class="btn btn-sm btn-secondary">
                ← Back
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="mainpay-table" class="table table-striped w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Scheme</th>
                            <th>Paid Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
$(function () {

    $('#mainpay-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('Material_pay.mainpay') }}",
            data: {
                invoice: "{{ request('invoice') }}" // 🔥 IMPORTANT
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'Date', name: 'Date' },
            { data: 'PurchaseFrom', name: 'PurchaseFrom' },
            { data: 'scheme', name: 'scheme' },
            { data: 'amt_pay', name: 'amt_pay' },
            { data: 'actions', orderable:false, searchable:false }
        ],
        drawCallback: function () {
            feather.replace();
        }
    });

});
</script>
@endpush
