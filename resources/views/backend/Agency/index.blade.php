@extends('backend.partials.master')
@section('title')
    Agency
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_agency'))
        <a href="{{route('Agency.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Agency</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Agency</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Name</th>
                                    <th>Contact No.</th>
                                    <th>Address</th>
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
document.addEventListener("DOMContentLoaded", function () {

    /* initialise DataTable */
    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive   : true,
        processing   : true,
        serverSide   : true,
        ajax         : "{{ route('Agency') }}",

        columns : [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'Name',       name: 'Name' },
            { data: 'ContactNo',  name: 'ContactNo' },
            { data: 'Address',    name: 'Address' },
            { data: 'actions',    name: 'actions', orderable:false, searchable:false }
        ],

        lengthChange : true,
        buttons      : ['copy', 'print'],

        /* ←───────────────  place the block HERE  ───────────────→ */
        drawCallback : function () {
            feather.replace();                 // your existing icon refresh

            /* one delegated handler for every redraw */
            $('#datatables-buttons')
                .off('change', '.flag-toggle')   // remove previous handlers
                
        }
        /* ←──────────  drawCallback ends  ──────────→ */
    });

    /* move the export buttons just like before */
    datatablesButtons.buttons()
        .container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
});
</script>
