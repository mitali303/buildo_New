@extends('backend.partials.master')
@section('title')
    Staff
@endsection
<style>
    @media (max-width: 768px) {
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        width: 100% !important;
        float: none !important;
        text-align: left !important;
        margin-bottom: 15px !important;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_staff'))
        <a href="{{route('Staff.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Staff</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Staff</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatables-buttons" class="table table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>SR NO.</th>
                                        <th>Name</th>
                                        <th>Contact No.</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Status</th>
                                        <th>Salary Type</th>
                                        <th>Salary</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
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
        responsive: false,
        processing: true,
        serverSide: true,

        ajax: "{{ route('Staff') }}",

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Name', name: 'Name' },
            { data: 'ContactNo', name: 'ContactNo' },
            { data: 'Department', name: 'Department' },
            { data: 'Designation', name: 'Designation' },
            { data: 'Status', name: 'Status' },
            { data: 'SalaryType', name: 'SalaryType' },
            { data: 'DailyWage', name: 'DailyWage' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],

        lengthChange: true,

          dom: '<"row"<"col-12"l>>' +
     '<"row"<"col-12"f>>' +
     'rt' +
     '<"row mt-3"<"col-12 col-md-6"i><"col-12 col-md-6"p>>',

        buttons: ['copy', 'print'],

        drawCallback: function () {
            feather.replace();
        }
    });

    datatablesButtons.buttons()
        .container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");

});
</script>