@extends('backend.partials.master')
@section('title')
    Scheme Details
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
         @if (hasPermission('create_scheme_detail'))
        <a href="{{route('Scheme.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Scheme Details</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Scheme Details</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Contact No.</th>
                                    <th>Contact Person</th>
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
            ajax: "{{ route('Scheme') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Name', name: 'Name' },
                { data: 'Location', name: 'Location' },
                { data: 'cnumber', name: 'cnumber' },
                { data: 'contactperson', name: 'contactperson' },
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
