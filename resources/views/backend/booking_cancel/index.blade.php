@extends('backend.partials.master')
@section('title')
Bank Account
@endsection
<style>
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
        @if (hasPermission('create_cancel_booking'))
        <a href="{{route('booking_cancel.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Booking Cancel</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Booking Cancel</h1>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>Fine Amount</th>
                                    <th>Returned Amount</th>
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
            responsive: false,
            scrollX: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('booking_cancel') }}", // Your route
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'CustomerID',
                    name: 'Name'
                },
                {
                    data: 'CustomerID',
                    name: 'Address'
                },
                {
                    data: 'flatID',
                    name: 'Contact'
                },
                {
                    data: 'FineAmt',
                    name: 'Fine Amount'
                },
                {
                    data: 'amt_pay',
                    name: 'Returned Amount'
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            lengthChange: true,
            buttons: ['copy', 'print'],
            drawCallback: function() {
                feather.replace(); // draw feather icons after each render
            }
        });
        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>