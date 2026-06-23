@extends('backend.partials.master')
@section('title')
    Labour Work
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_labour_work'))
        <a href="{{route('Labour_Work.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Labour Work</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Labour Work</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Date</th>
                                    <th>Scheme</th>
                                    <th>Agency</th>
                                    <th>Grand Total</th>
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
        ajax         : "{{ route('Labour_Work') }}",

        columns : [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'Date', name: 'Date' },
            { data: 'Scheme', name: 'schemeID' },
             { data: 'Agency', name: 'Agency_ID' },
            { data: 'gtotal', name: 'gtotal' },
            { data: 'actions', name: 'actions', orderable:false, searchable:false }
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
