@extends('backend.partials.master')

@section('title')
Attendance Master
@endsection

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        @if (hasPermission('create_AttendanceMaster'))
        <a href="{{route('AttendanceMaster.create')}}" class="btn btn-primary float-end mt-n1">
            <i class="fas fa-plus"></i> Attendance Master
        </a>
        @endif

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Attendance Master Table</h1>
        </div>

        <div class="card">
            <div class="card-body">

                <table id="datatables-buttons" class="table table-striped w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee Name</th>
                            <th>Date</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Leave</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>

    </div>
</main>
@endsection

<script>
document.addEventListener("DOMContentLoaded", function() {

    $("#datatables-buttons").DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('AttendanceMaster') }}",

        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'emp_id' },
            { data: 'date' },
            { data: 'present' },
            { data: 'absent' },
            { data: 'emp_leave' },
            { data: 'actions', orderable: false, searchable: false }
        ],

        dom: "<'row'<'col-md-6'l><'col-md-6 text-end'B>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",

        buttons: ['copy', 'excel', 'pdf', 'print', 'colvis'],

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],

        drawCallback: function () {
            feather.replace();
        }
    });

});
</script>