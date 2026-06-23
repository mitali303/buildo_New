@extends('backend.partials.master')
@section('title')
    Material Transfer
@endsection
<style>
.nowrap { white-space: nowrap; }
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_material_transfer'))
        <a href="{{route('Transfer_Material.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Material Transfer</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Material Transfer</h1> 
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
                                    <th>SR NO.</th>
                                    <th>From Site</th>
                                    <th>To Site</th>
                                    <th>Material</th>
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
            ajax: "{{ route('Transfer_Material') }}", // Your route
            columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'Date', name: 'Date'},
                    { data: 'Srno', name: 'Srno', searchable: false },
                   { data: 'from_site', searchable: false },
                    { data: 'To_site', searchable: false },
                    { data: 'materials', name: 'materials', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
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
