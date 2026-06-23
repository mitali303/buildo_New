@extends('backend.partials.master')
@section('title')
    Salary Account
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_SalaryMaster'))
        <a href="{{route('SalaryMaster.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Salary</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Salary</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>SR NO.</th>
                                <th>Salary No</th>
                                <th>Staff Name</th>
                                <th>Month</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
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
    document.addEventListener("DOMContentLoaded", function() {

    $("#datatables-buttons").DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('SalaryMaster') }}",

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'salary_no', name: 'salary_no' },
            { data: 'staff_name', name: 'staff.name' }, // ✅ NEW
            { data: 'month_name', name: 'month', searchable: false }, // ✅ added
            { data: 'amount', name: 'amount' },
            { data: 'payment_method', name: 'payment_method' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],

        drawCallback: function () {
            feather.replace();
        }
    });

});
</script>