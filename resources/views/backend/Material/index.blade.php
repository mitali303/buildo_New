@extends('backend.partials.master')
@section('title')
    Material Account
@endsection
<style>
    @media (max-width: 768px) {
    .dataTables_length,
    .dataTables_filter {
        width: 100% !important;
        float: none !important;
        text-align: left !important;
    }

    .dataTables_length { margin-bottom: 18px !important; }
    .dataTables_filter { margin-bottom: 15px !important; }

    .dataTables_length select { margin: 0 5px !important; }
    .dataTables_filter input {
        width: 200px !important;
        max-width: 90% !important;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_Material'))
        <a href="{{route('Material.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Material Loan</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Material</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body w-100 overflow-hidden">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>SR NO.</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Unit Of Measure</th>
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
                        pagingType: 'simple_numbers',
            ajax: "{{ route('Material') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Name', name: 'Name' },
                { data: 'Type', name: 'Type' },
                { data: 'Unit', name: 'Unit' },
                { data: 'actions', name: 'actions' }
            ],
            lengthChange: true,
            buttons: ['copy', 'print'],
            drawCallback: function () {
                feather.replace(); // draw feather icons after each render
                $("#datatables-buttons_wrapper").addClass("w-100 overflow-hidden");
                $("#datatables-buttons_wrapper .dataTables_paginate")
                    .addClass("float-none d-flex flex-wrap justify-content-end align-items-center gap-1 w-100 text-wrap");
                $("#datatables-buttons_wrapper .dataTables_paginate .pagination")
                    .addClass("d-flex flex-wrap justify-content-end w-100 mb-0");
            }
        });
        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>