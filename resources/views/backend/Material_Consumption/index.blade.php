@extends('backend.partials.master')
@section('title')
    Material Consumption
@endsection
@section('maincontent')
<style>
    .nowrap { white-space: nowrap; }
    .inner-table {
        width: 100%;
        border-collapse: collapse;
    }
    .inner-table td {
        padding: 4px 6px;
        border-bottom: 1px solid #dee2e6;
        font-size: 13px;
    }
    .inner-table tr:last-child td {
        border-bottom: none;
    }
</style>

<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_material_consumption'))
        <a href="{{route('Material_Consumption.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Material Consumption</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Material Consumption</h1> 
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
                                    <th>Scheme</th>
                                    <th>Material - Type</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
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
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('Material_Consumption') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Date', name: 'Date', className: 'nowrap' },
                    { data: 'scheme', name: 'scheme' },
                { data: 'material', searchable: false, orderable: false },
                { data: 'Unit', searchable: false, orderable: false },
                { data: 'Qty', searchable: false, orderable: false },
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
