@extends('backend.partials.master')
@section('title')
   Call Status
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <a href="{{route('CallStatus.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Call Status</a>
        <!-- @if (hasPermission('create_CallStatus') == true)
        <a href="{{route('CallStatus.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Call Status</a>
        @endif -->
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Call Status Table</h1>
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
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                <tr>
                                    <th></th>

                                    <th>
                                        <input type="text" class="form-control form-control-sm" placeholder="Search Name">
                                    </th>

                                    <th>
                                        <input type="text" class="form-control form-control-sm" placeholder="Search Type">
                                    </th>

                                    <th>
                                        <select class="form-select form-select-sm">
                                            <option value="">All</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </th>

                                    <th></th>
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

    var table = $("#datatables-buttons").DataTable({

        responsive: true,
        processing: true,
        serverSide: true,
        orderCellsTop: true,
        fixedHeader: true,

        ajax: "{{ route('CallStatus') }}",

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'type', name: 'type' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],

        dom:
            "<'row'<'col-md-6'l><'col-md-6 text-end'B>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-md-5'i><'col-md-7'p>>",

        buttons: [
            'copy',
            'excel',
            'pdf',
            'print',
            // {
            //     extend: 'colvis',
            //     text: 'Columns'
            // }
        ],

        lengthMenu: [
            [10,25,50,100,-1],
            [10,25,50,100,"All"]
        ],

        drawCallback: function () {
            feather.replace();
        }
    });

    // Column Wise Search
    $('#datatables-buttons thead tr:eq(1) th').each(function (i) {

        $('input, select', this).on('keyup change', function () {

            if (table.column(i).search() !== this.value) {
                table.column(i).search(this.value).draw();
            }

        });

    });

});
</script>
