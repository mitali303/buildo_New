@extends('backend.partials.master')

@section('title', 'Invoice-wise Payments')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">Invoice-wise Payments</h1>

        <div class="card">
            <div class="card-body">
                <table id="invoicewise-table" class="table table-striped w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Invoice Total</th>
                            <th>Paid Amount</th>
                            <th>Debit used</th>
                            <th>Extra</th>
                            <th>Total Paid</th>
                            <th>Last Payment Date</th>
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
    $('#invoicewise-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url()->current() }}",
       columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'InvoiceNo', name: 'InvoiceNo' },
            { data: 'InvoiceTotal', name: 'InvoiceTotal' },
            { data: 'PaidAmount', name: 'PaidAmount' },
            { data: 'DebitUsed', name: 'DebitUsed' }, 
            { data: 'Extra', name: 'Extra' },
            { data: 'TotalPaid', name: 'TotalPaid' },
            { data: 'LastPaymentDate', name: 'LastPaymentDate' },
            { data: 'actions', orderable:false, searchable:false }
        ],
        drawCallback: function () {
            feather.replace();
        }
    });
});
</script>
@endpush
