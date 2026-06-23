@extends('backend.partials.master')
@section('title')
    Bank Loan
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_bank_loan_request'))
        <a href="{{route('BankForm.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Bank Loan</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Bank Loan</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Bank Name</th>
                                    <th>To Name</th>
                                    <th>City</th>
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
        // Datatables with Buttons
        var datatablesButtons = $("#datatables-buttons").DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('BankForm') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'bank_name', name: 'bank_name' },
                { data: 'to_name', name: 'to_name' },
                { data: 'city', name: 'city' },
                { data: 'actions', name: 'actions' }
            ],
            lengthChange: true,
            buttons: ['copy', 'print'],
            drawCallback: function () {
                feather.replace(); // draw feather icons after each render
            }
        });
        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>