@extends('backend.partials.master')
@section('title')
  Employee Advance
@endsection
<style>
    #datatables-buttons th:nth-child(2),
#datatables-buttons td:nth-child(2) {
    white-space: nowrap !important;
}
    @media (max-width: 768px) {
    #datatables-buttons_wrapper .dataTables_length,
    #datatables-buttons_wrapper .dataTables_filter {
        width: 100%;
        float: none;
        text-align: left;
        margin-bottom: 10px;
    }

    #datatables-buttons_wrapper .dt-buttons .btn {
        padding: 4px 8px;
        font-size: 12px;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_EmployeeAdvance') == true)
        <a href="{{route('EmployeeAdvance.create')}}" class="btn btn-sm btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Employee Advance</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Employee Advance Table</h1>
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
                                    <th>Employee</th>
                                    <th>Advance</th>
                                    <th>EMI Amount</th>
                                    <th>Remaining Balance</th>
                                    <th>Total Installments</th>
                                    <th>Action</th>
                                </tr>
                                 <!-- <tr>
                                <th></th>

                                <th>
                                    <input type="date" id="search_date" class="form-control">
                                </th>

                                <th>
                                    <input type="text" id="search_employee" placeholder="Search Employee" class="form-control">
                                </th>

                                <th>
                                    <input type="text" id="search_advance" placeholder="Search Advance" class="form-control">
                                </th>

                                <th>
                                    <input type="text" id="search_emi_amount" placeholder="Search EMI Amount" class="form-control">
                                </th>

                                <th>
                                    <input type="text" id="search_remaining_amount" placeholder="Search Remaining Balance" class="form-control">
                                </th>

                                <th>
                                    <input type="text" id="search_total_installments" placeholder="Search Total Installments" class="form-control">
                                </th>

                                <th></th>
                            </tr> -->
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

    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive   : false,
            scrollX: true,
        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('EmployeeAdvance') }}",
            data: function (d) {
                d.date = $('#search_date').val();
                d.employee_name = $('#search_employee').val();
                d.advance = $('#search_advance').val();
                d.emi_amount = $('#search_emi_amount').val();
                d.remaining_amount = $('#search_remaining_amount').val();
                d.total_installments = $('#search_total_installments').val();
            }
        },

        columns: [
            {
                data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'date', name: 'date' },
            { data: 'employee_name', name: 'employee_name' },
            { data: 'advance', name: 'advance' },
            {  data: 'emi_amount', name: 'emi_amount' },
            { data: 'remaining_amount', name: 'remaining_amount' },
            { data: 'total_installments', name: 'total_installments'},
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],

         dom:
            "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'f>>" +
            "<'row mb-2'<'col-md-12'B>>" +
            "<'row'<'col-sm-10'tr>>" +
            "<'row mt-1'<'col-md-4'i><'col-md-7'p>>",

        buttons: [
            {
                extend: 'copy',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6]
                }
            },
            {
                extend: 'excel',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6]
                }
            },
            {
                extend: 'pdf',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6]
                }
            },
            {
                extend: 'print',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6]
                }
            },
            
        ],

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],

        drawCallback: function () {
            feather.replace();
        }
    });

    // Search Filters
    $('#search_date, #search_employee, #search_advance, #search_emi_amount, #search_remaining_amount, #search_total_installments')
        .on('keyup change', function () {
            datatablesButtons.draw();
        });

});
</script>
