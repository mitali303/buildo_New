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

        /* Date & Due Date */
        #datatables-buttons th:nth-child(2),
        #datatables-buttons td:nth-child(2),
        #datatables-buttons th:nth-child(8),
        #datatables-buttons td:nth-child(8) {
            white-space: nowrap !important;
            min-width: 100px !important;
            width: 100px !important;
        }
    }
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_add_bill'))
        <a href="{{route('Add_bill.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Add bill</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Customer Add bill</h1>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Customer Name</th>
                                    <th>Flat No</th>
                                    <th>Scheme</th>
                                    <th>Type</th>
                                    <th>Total Amount</th>
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
            ajax: "{{ route('Add_bill') }}", // Your route
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'Date',
                    name: 'Date'
                },
                {
                    data: 'CustomerName',
                    name: 'CustomerName'
                },
                {
                    data: 'FlatNo',
                    name: 'FlatNo'
                },
                {
                    data: 'scheme_name',
                    name: 'scheme.Name'
                },
                {
                    data: 'type',
                    name: 'Type'
                },
                {
                    data: 'Amount',
                    name: 'Total Amount'
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