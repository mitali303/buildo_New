@extends('backend.partials.master')
@section('title')
    Purchase Order
@endsection
<style>
    #datatables-buttons th:nth-child(2),
#datatables-buttons td:nth-child(2) {
    white-space: nowrap !important;
}
<style>
    @media (max-width: 768px) {
    #datatables-buttons_wrapper .dataTables_length,
    #datatables-buttons_wrapper .dataTables_filter {
        width: 100%;
        float: none;
        text-align: left;
        margin-bottom: 10px;
    }

    #datatables-buttons_wrapper .dt-buttons .btn {
        padding: 4px 8px;
        font-size: 12px;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_purchase_invoice'))
        <a href="{{route('PurchaseInvoice.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Material Inward</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Material Inward</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Order No.</th>
                                    <th>Vendor</th>
                                    <th>Site Location</th>
                                    <th>GrandTotal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Datatables with Buttons
        var datatablesButtons = $("#datatables-buttons").DataTable({
            responsive: false,
        scrollX: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('PurchaseInvoice') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Date', name: 'Date' },
                { data: 'Invno', name: 'Invno' },
                 { data: 'vendor_name', name: 'vendor_name' },
    { data: 'scheme_name', name: 'scheme_name' },
                { data: 'gtotal', name: 'gtotal' },
                { data: 'actions', name: 'actions' }
            ],
            dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>' +
                'rt' +
                '<"row mt-3"<"col-md-6"i><"col-md-6"p>>',
            lengthChange: true,
            buttons: ['copy', 'print'],
            drawCallback: function () {
                feather.replace(); // draw feather icons after each render
            }
        });
        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>
