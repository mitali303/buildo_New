@extends('backend.partials.master')
@section('title')
Salary Master
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_SalaryMaster') == true)
        <a href="{{route('SalaryMaster.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i>Salary Master</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Salary Master Table</h1>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
                                    <th>Gross</th>
                                    <th>Advance EMI</th>
                                    <th>Net Salary</th>
                                    <th>Action</th>
                                </tr>

                                <tr>
                                    <th></th>

                                    <th>
                                        <input type="text"
                                            id="search_employee_name"
                                            class="form-control form-control-sm"
                                            placeholder="Employee Name">
                                    </th>

                                    <th>
                                        <input type="text"
                                            id="search_gross"
                                            class="form-control form-control-sm"
                                            placeholder="Gross">
                                    </th>

                                    <th>
                                        <input type="text"
                                            id="search_advance_emi"
                                            class="form-control form-control-sm"
                                            placeholder="Advance EMI">
                                    </th>

                                    <th>
                                        <input type="text"
                                            id="search_net_salary"
                                            class="form-control form-control-sm"
                                            placeholder="Net Salary">
                                    </th>

                                    <th>
                                        <button type="button"
                                                id="resetFilters"
                                                class="btn btn-sm btn-secondary">
                                            Reset
                                        </button>
                                    </th>
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
@push('scripts')
<script>

document.addEventListener("DOMContentLoaded", function () {

    var datatablesButtons = $("#datatables-buttons").DataTable({

        responsive: true,
        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('SalaryMaster') }}",

            data: function (d) {

                d.employee_name = $('#search_employee_name').val();
                d.gross         = $('#search_gross').val();
                d.advance_emi   = $('#search_advance_emi').val();
                d.net_salary    = $('#search_net_salary').val();
            }
        },

        columns: [

            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },

            {
                data: 'employee_name',
                name: 'employee_name'
            },

            {
                data: 'gross',
                name: 'gross'
            },

            {
                data: 'advance_emi',
                name: 'advance_emi'
            },

            {
                data: 'net_salary',
                name: 'net_salary'
            },

            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }
        ],

        dom:
            "<'row'<'col-md-6'l><'col-md-6 text-end'B>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-md-5'i><'col-md-7'p>>",

        buttons: [

            {
                extend: 'copy',
                exportOptions: {
                    columns: [1,2,3,4]
                }
            },

            {
                extend: 'excel',
                exportOptions: {
                    columns: [1,2,3,4]
                }
            },

            {
                extend: 'pdf',
                exportOptions: {
                    columns: [1,2,3,4]
                }
            },

            {
                extend: 'print',
                exportOptions: {
                    columns: [1,2,3,4]
                }
            },

            {
               
                text: 'Columns',
                columns: [1,2,3,4]
            }
        ],

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],

        pageLength: 10,

        drawCallback: function () {

            feather.replace();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Search Filters
    |--------------------------------------------------------------------------
    */

    $('#search_employee_name').on('keyup change', function () {

        datatablesButtons.draw();
    });

    $('#search_gross').on('keyup change', function () {

        datatablesButtons.draw();
    });

    $('#search_advance_emi').on('keyup change', function () {

        datatablesButtons.draw();
    });

    $('#search_net_salary').on('keyup change', function () {

        datatablesButtons.draw();
    });

    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */

    $('#resetFilters').on('click', function () {

        $('#search_employee_name').val('');
        $('#search_gross').val('');
        $('#search_advance_emi').val('');
        $('#search_net_salary').val('');

        datatablesButtons.search('').columns().search('').draw();
    });

    /*
    |--------------------------------------------------------------------------
    | Delete Confirmation
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.delete-confirm', function (e) {

        e.preventDefault();

        let formId = $(this).data('id');

        if (confirm('Are you sure you want to delete this record?')) {

            $('#' + formId).submit();
        }
    });

});

</script>
@endpush