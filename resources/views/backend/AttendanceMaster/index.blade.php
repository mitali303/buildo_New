@extends('backend.partials.master')

@section('title')
Attendance Master
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

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h1 class="h3 d-inline align-middle">
                Attendance Master Table
            </h1>

            <div class="d-flex gap-2 flex-wrap">

                @if (hasPermission('create_AttendanceMaster') == true)
                    <a href="{{ route('AttendanceMaster.create') }}"
                       class="btn btn-primary px-2 px-md-3 py-1 py-md-2">

                        <i class="fas fa-plus"></i>
                        Attendance Master
                    </a>
                @endif

                <!-- IMPORT BUTTON -->
                <button class="btn btn-success px-2 px-md-3 py-1 py-md-2"
                        data-bs-toggle="modal"
                        data-bs-target="#importModal">

                    <i class="fas fa-file-excel"></i>
                    Import Excel
                </button>

            </div>

        </div>
        {{-- IMPORT ERRORS --}}
@if(session('import_errors'))

<div class="alert alert-danger">

    <h5 class="mb-3">

        Attendance Import Errors

        <span class="badge bg-danger">

            {{ count(session('import_errors')) }}

        </span>

    </h5>

    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle">

            <thead class="table-dark">

                <tr>

                    <th>#</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Date</th>
                    <th>Error Message</th>

                </tr>

            </thead>

            <tbody>

                @foreach(session('import_errors') as $key => $error)

                    <tr>

                        <td>
                            {{ $key + 1 }}
                        </td>

                        <td>
                            {{ $error['emp_id'] ?? '-' }}
                        </td>

                        <td>

                            <span class="fw-bold text-primary">

                                {{ $error['emp_name'] ?? 'N/A' }}

                            </span>

                        </td>

                        <td>

                            @if(!empty($error['date']))

                                {{ \Carbon\Carbon::parse($error['date'])->format('d-m-Y') }}

                            @else

                                -

                            @endif

                        </td>

                        <td>

                            <span class="text-danger fw-bold">

                                {{ $error['error'] ?? '-' }}

                            </span>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endif

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
        <th>Employee Name</th>
        <th>Date</th>
        <th>In Time</th>
        <th>Out Time</th>
        <th>Late Min</th>
        <th>Early Dep</th>
        <th>Work Hr</th>
        <th>OT Hr</th>
        <th>Action</th>
    </tr>

    <!-- <tr>
        <th></th>

        <th>
            <input type="text" id="search_employee_name"
                   class="form-control"
                   placeholder="Search Employee">
        </th>

        <th>
            <input type="date" id="search_date"
                   class="form-control">
        </th>

        <th>
            <input type="text" id="search_intime"
                   class="form-control"
                   placeholder="Search In Time">
        </th>

        <th>
            <input type="text" id="search_outtime"
                   class="form-control"
                   placeholder="Search Out Time">
        </th>

        <th>
            <input type="text" id="search_late_mins"
                   class="form-control"
                   placeholder="Search Late Min">
        </th>

        <th>
            <input type="text" id="search_early_dep"
                   class="form-control"
                   placeholder="Search Early Dep">
        </th>

        <th>
            <input type="text" id="search_work_hr"
                   class="form-control"
                   placeholder="Search Work Hr">
        </th>

        <th>
            <input type="text" id="search_ot_hr"
                   class="form-control"
                   placeholder="Search OT Hr">
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



<!-- IMPORT MODAL -->

<div class="modal fade" id="importModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="{{ route('attendance.import') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">
                        Import Attendance Excel
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Select Excel File
                        </label>

                        <input type="file"
                               name="file"
                               class="form-control"
                               accept=".xls,.xlsx"
                               required>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Close

                    </button>

                    <button type="submit"
                            class="btn btn-success">

                        <i class="fas fa-upload"></i>
                        Import

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection



<script>
document.addEventListener("DOMContentLoaded", function () {

    var datatablesButtons = $("#datatables-buttons").DataTable({

        responsive   : false,
            scrollX: true,
        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('AttendanceMaster') }}",
            data: function (d) {

                d.employee_name = $('#search_employee_name').val();
                d.date          = $('#search_date').val();
                d.intime        = $('#search_intime').val();
                d.outtime       = $('#search_outtime').val();
                d.late_mins     = $('#search_late_mins').val();
                d.early_dep     = $('#search_early_dep').val();
                d.work_hr       = $('#search_work_hr').val();
                d.ot_hr         = $('#search_ot_hr').val();

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
                data: 'date',
                name: 'date'
            },

            {
                data: 'intime',
                name: 'intime'
            },

            {
                data: 'outtime',
                name: 'outtime'
            },

            {
                data: 'late_mins',
                name: 'late_mins'
            },

            {
                data: 'early_dep',
                name: 'early_dep'
            },

            {
                data: 'work_hr',
                name: 'work_hr'
            },

            {
                data: 'ot_hr',
                name: 'ot_hr'
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
                    columns: [0,1,2,3,4,5,6,7,8]
                }
            },

            {
                extend: 'excel',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8]
                }
            },

            {
                extend: 'pdf',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8]
                }
            },

            {
                extend: 'print',
                exportOptions: {
                    columns: [0,1,2,3,4,5,6,7,8]
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

    // Search Inputs Trigger

    $('#search_employee_name,#search_date,#search_intime,#search_outtime,#search_late_mins,#search_early_dep,#search_work_hr,#search_ot_hr')
    .on('keyup change', function () {

        datatablesButtons.draw();

    });

});
</script>