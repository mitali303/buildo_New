@extends('backend.partials.master')

@section('title')
User Shift Assignment
@endsection

@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">

        <!-- HEADER (LIKE UNIT PAGE STYLE) -->
        <div class="row mb-3 align-items-center">

            <div class="col-md-6">
                <h1 class="h3 mb-0">User Shift Assignment Table</h1>
            </div>

            <div class="col-md-6 text-end">

                @if (hasPermission('create_UserShift') == true)
                <a href="{{ route('UserShift.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Assign Shift
                </a>
                @endif

            </div>

        </div>

        <!-- TABLE -->
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">

                        <table id="datatables-buttons" class="table table-striped" style="width:100%">

                            <thead>

                                <!-- HEADER ROW -->
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Shift</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                                <!-- FILTER ROW -->
                                <tr>
                                    <th></th>

                                    <th>
                                        <input type="text" placeholder="Search Employee" class="form-control form-control-sm"/>
                                    </th>

                                    <th>
                                        <input type="text" placeholder="Search Shift" class="form-control form-control-sm"/>
                                    </th>

                                    <th>
                                        <input type="date" class="form-control form-control-sm"/>
                                    </th>

                                    <th>
                                        <input type="date" class="form-control form-control-sm"/>
                                    </th>

                                    <th>
                                        <select class="form-control form-control-sm">
                                            <option value="">Status</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
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
document.addEventListener("DOMContentLoaded", function() {

    var datatablesButtons = $("#datatables-buttons").DataTable({

        responsive: true,
        processing: true,
        serverSide: true,

        ajax: "{{ route('UserShift') }}",

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },

            { data: 'employee', name: 'employee' },

            { data: 'shift_name', name: 'shift_name' },

            { data: 'from_date', name: 'from_date' },

            { data: 'to_date', name: 'to_date' },

            { data: 'status', name: 'status' },

            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],

        dom: "<'row'<'col-md-6'l><'col-md-6 text-end'B>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",

        buttons: [
    {
        extend: 'copy',
        exportOptions: {
            columns: [0,1,2,3,4,5],
            format: {
                header: function (data, columnIdx) {
                    return $('#datatables-buttons thead tr:first th')
                        .eq(columnIdx)
                        .text();
                }
            }
        }
    },
    {
        extend: 'excel',
        exportOptions: {
            columns: [0,1,2,3,4,5],
            format: {
                header: function (data, columnIdx) {
                    return $('#datatables-buttons thead tr:first th')
                        .eq(columnIdx)
                        .text();
                }
            }
        }
    },
    {
        extend: 'pdf',
        exportOptions: {
            columns: [0,1,2,3,4,5],
            format: {
                header: function (data, columnIdx) {
                    return $('#datatables-buttons thead tr:first th')
                        .eq(columnIdx)
                        .text();
                }
            }
        }
    },
    {
        extend: 'print',
        exportOptions: {
            columns: [0,1,2,3,4,5],
            format: {
                header: function (data, columnIdx) {
                    return $('#datatables-buttons thead tr:first th')
                        .eq(columnIdx)
                        .text();
                }
            }
        }
    }
],

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],

        drawCallback: function () {
            feather.replace();
        }
    });

    $('#datatables-buttons thead tr:eq(1) th').each(function (i) {

        var input = $(this).find("input, select");

        if (input.length) {

            $(input).on("keyup change clear", function () {

                if (datatablesButtons.column(i).search() !== this.value) {
                    datatablesButtons.column(i).search(this.value).draw();
                }
            });
        }
    });

});
</script>