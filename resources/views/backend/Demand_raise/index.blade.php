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

    @media (max-width: 768px) {

        #datatables-buttons th:nth-child(2),
        #datatables-buttons td:nth-child(2),
        #datatables-buttons th:nth-child(8),
        #datatables-buttons td:nth-child(8) {
            white-space: nowrap !important;
        }
    }
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_customer_demand_raise'))
        <a href="{{route('demand_raise.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Demand Raise</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Customer Demand Raise</h1>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped w-100">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Date</th>
                                    <th>Scheme</th>
                                    <th>Wing</th>
                                    <th>Flat</th>
                                    <th>Demand</th>
                                    <th>Interest</th>
                                    <th>Due Date</th>
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
@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Datatables with Buttons
        var datatablesButtons = $("#datatables-buttons").DataTable({
            responsive: false,
            scrollX: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('demand_raise') }}", // Your route
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
                    data: 'scheme_name',
                    name: 'scheme.Name'
                },
                {
                    data: 'Wing',
                    name: 'Wing'
                },
                {
                    data: 'flat_no',
                    name: 'flatno.FlatNo'
                },
                {
                    data: 'Demand_amt',
                    name: 'Demand_amt'
                },
                {
                    data: 'InterestRate',
                    name: 'InterestRate'
                },
                {
                    data: 'Due_date',
                    name: 'Due_date'
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
@endsection