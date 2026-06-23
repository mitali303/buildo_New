@extends('backend.partials.master')

@section('title')
Employee Advance
@endsection

@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">

        {{-- ✅ Add Button --}}
        @if (hasPermission('create_EmployeeAdvance'))
        <a href="{{ route('EmployeeAdvance.create') }}" class="btn btn-primary float-end mt-n1">
            <i class="fas fa-plus"></i> New Employee Advance
        </a>
        @endif

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Employee Advance Table</h1>
        </div>

        <div class="card">
            <div class="card-body">

                <table id="datatables-buttons" class="table table-striped w-100">
                    <thead>
                        {{-- ✅ Header --}}
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Advance</th>
                            <th>EMI Amount</th>
                            <th>Total Installments</th>
                            <th>Action</th>
                        </tr>

                        {{-- ✅ Search Row --}}
                        <tr>
                            <th></th>
                            <th><input type="text" placeholder="Search Date" class="form-control form-control-sm"/></th>
                            <th><input type="text" placeholder="Search Employee" class="form-control form-control-sm"/></th>
                            <th><input type="text" placeholder="Search Advance" class="form-control form-control-sm"/></th>
                            <th><input type="text" placeholder="Search EMI" class="form-control form-control-sm"/></th>
                            <th><input type="text" placeholder="Search Installments" class="form-control form-control-sm"/></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>

    </div>
</main>

@endsection


{{-- ✅ DATATABLE SCRIPT --}}
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    var table = $('#datatables-buttons').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('EmployeeAdvance') }}",

        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Date', name: 'employee_advance.Date' },
            { data: 'employee_name', name: 'staff.Name' }, // ✅ FIXED
            { data: 'advance', name: 'employee_advance.advance' },
            { data: 'emi_amount', name: 'employee_advance.emi_amount' },
            { data: 'total_installments', name: 'employee_advance.total_installments' },
            { data: 'actions', orderable: false, searchable: false }
        ],

        drawCallback: function () {
            if (typeof feather !== "undefined") {
                feather.replace();
            }
        }
    });

    // Column search
    $('#datatables-buttons thead tr:eq(1) th').each(function (i) {
        var input = $(this).find('input');

        if (input.length) {
            $(input).on('keyup change', function () {
                table.column(i).search(this.value).draw();
            });
        }
    });

});
</script>
@endpush