@extends('backend.partials.master')
@section('title')
    Return Material
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_Return_Material'))
        <a href="{{route('Rejected_Material.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Return Material</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Return Material</h1> 
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
            ajax: "{{ route('Rejected_Material') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Date', name: 'Date' },
                { data: 'srno', name: 'srno' },
                 { data: 'vendor_name', name: 'vendor_name' },
    { data: 'scheme_name', name: 'scheme_name' },
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
