@extends('backend.partials.master')

@section('title')
    Late Mark Calculation
@endsection

@section('maincontent')

<main class="content">

    <div class="container-fluid p-0">

        @if (hasPermission('create_LateMarkCalculation'))

            <a href="{{ route('LateMarkCalculation.create') }}"
               class="btn btn-primary float-end mt-n1">

                <i class="fas fa-plus"></i> New Late Mark

            </a>

        @endif

        <div class="mb-3">

            <h1 class="h3 d-inline align-middle">
                Late Mark Calculation
            </h1>

        </div>

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        <table id="datatables-buttons"
                               class="table table-striped"
                               style="width:100%">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Late Mark Count</th>
                                    <th>Total Late Time</th>
                                    <th>Amount Reduce</th>
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


@push('scripts')
<script>

document.addEventListener("DOMContentLoaded", function () {

    var table = $("#datatables-buttons").DataTable({

        responsive: true,
        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('LateMarkCalculation') }}",

            data: function (d) {

                d.employee         = $('#search_employee').val();
                d.month            = $('#search_month').val();
                d.year             = $('#search_year').val();
                d.late_mark_count  = $('#search_late_mark_count').val();
                d.total_late_time  = $('#search_total_late_time').val();
                d.amount_reduce    = $('#search_amount_reduce').val();
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
                data: 'employee',
                name: 'employee.name'
            },

            {
                data: 'month',
                name: 'month'
            },

            {
                data: 'year',
                name: 'year'
            },

            {
                data: 'late_mark_count',
                name: 'late_mark_count'
            },

            {
                data: 'total_late_time',
                name: 'total_late_time'
            },

            {
                data: 'amount_reduce',
                name: 'amount_reduce'
            },

            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }
        ],

        dom:
    "<'row'<'col-md-4'l><'col-md-4'B><'col-md-4 text-end'f>>" +
    "<'row'<'col-sm-12'tr>>" +
    "<'row'<'col-md-5'i><'col-md-7'p>>",

        buttons: [

            {
                extend: 'copy',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },

            {
                extend: 'excel',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },

            {
                extend: 'pdf',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            },

            {
                extend: 'print',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
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
    | Column Search
    |--------------------------------------------------------------------------
    */

    $('#search_employee').on('keyup change', function () {
        table.draw();
    });

    $('#search_month').on('keyup change', function () {
        table.draw();
    });

    $('#search_year').on('keyup change', function () {
        table.draw();
    });

    $('#search_late_mark_count').on('keyup change', function () {
        table.draw();
    });

    $('#search_total_late_time').on('keyup change', function () {
        table.draw();
    });

    $('#search_amount_reduce').on('keyup change', function () {
        table.draw();
    });

    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */

    $('#resetFilters').click(function () {

        $('#search_employee').val('');
        $('#search_month').val('');
        $('#search_year').val('');
        $('#search_late_mark_count').val('');
        $('#search_total_late_time').val('');
        $('#search_amount_reduce').val('');

        table.draw();
    });

});


/*
|--------------------------------------------------------------------------
| Delete Confirmation
|--------------------------------------------------------------------------
*/

// $(document).on('click', '.delete-confirm', function (e) {

//     e.preventDefault();

//     let formId = $(this).data('id');

//     if (confirm('Are you sure you want to delete this record?')) {

//         $('#' + formId).submit();
//     }
// });

</script>
@endpush