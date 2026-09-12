@extends('backend.partials.master')
@section('title')
    Payment Slab
@endsection
<style>
@media (max-width: 768px) {

    #datatables-buttons_wrapper .dataTables_length,
    #datatables-buttons_wrapper .dataTables_filter {
        width: 100% !important;
        float: none !important;
        text-align: left !important;
        margin-bottom: 10px !important;
    }

    #datatables-buttons_wrapper .dataTables_filter {
        margin-top: 5px !important;
    }

    #datatables-buttons_wrapper .dataTables_filter input {
        width: 150px !important;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_payment_slab') && !$hasSlab)
            <a href="{{ route('PaymentSlab.create') }}" class="btn btn-primary float-end mt-n1">
                <i class="fas fa-plus"></i> New Payment Slab
            </a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Payment Slab</h1> 
        </div>
        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>SR NO.</th>
                                    <th>pilnth</th>
                                    <th>Slab</th>
                                    <th>Bricks</th>
                                    <th>Plaster</th>
                                    <th>Floaring</th>
                                    <th>Plumbing</th>
                                    <th>Project</th>
                                    <th>Total</th>
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
             processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: true,
        orderCellsTop: true,
        fixedHeader: true,
            ajax: "{{ route('PaymentSlab') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'pilnth', name: 'pilnth' },
                { data: 'slab', name: 'slab' },
                { data: 'bricks', name: 'bricks' },
                { data: 'plaster', name: 'plaster' },
                { data: 'floaring', name: 'floaring' },
                { data: 'plumbing', name: 'plumbing' },
                { data: 'project', name: 'project' },
                { data: 'total', name: 'total' },
                { data: 'actions', name: 'actions' }
            ],
            lengthChange: true,
            buttons: ['copy', 'print'],
            drawCallback: function () {
                feather.replace(); // draw feather icons after each render
            }
        });
        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>