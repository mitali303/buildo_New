@extends('backend.partials.master')

@section('title', 'Invoice-wise Payments')
<style>
@media (max-width: 768px) {

    /* DataTable Show आणि Search एकाखाली एक */
    #invoicewise-table_wrapper .dataTables_length,
    #invoicewise-table_wrapper .dataTables_filter {
        width: 100% !important;
        float: none !important;
        text-align: left !important;
        margin-bottom: 10px !important;
    }

    #invoicewise-table_wrapper .dataTables_filter {
        margin-top: 5px !important;
    }

    /* Mobile horizontal scroll */
    #invoicewise-table_wrapper .dataTables_scrollBody {
        overflow-x: auto !important;
    }

    #invoicewise-table {
        min-width: 900px !important;
    }
}
</style>
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
         scrollX: true,
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
