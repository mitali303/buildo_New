@extends('backend.partials.master')
@section('title')
Customer Booking
@endsection
@section('maincontent')
<main class="content">
    <style>
        .dishes-scroll {
            display: grid;
            gap: 15px;
            overflow-y: auto;
            padding: 10px;
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

    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto ">
                <h3><strong></strong>Customer Booking</h3>
            </div>
            <div class="col-auto ms-auto text-end mt-n1">

                @if (hasPermission('create_customer_booking') == true)
                <a href="{{route('customer_booking.create')}}" class="btn btn-primary"><i class="fas fa-plus"></i>Add New Record</a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="dishes-scroll">
                            <table id="datatables-buttons" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Sr No.</th>
                                        <th>Customer Name</th>
                                        <th>Address</th>
                                        <th> Contact</th>
                                        <th> Flat Details</th>
                                        <th> Total Flat Amount</th>
                                        <!-- <th> Downpayment</th> -->
                                        <th style="width:185px;">Action</th>
                                        <th>Payment Schedule</th>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th><input type="text" placeholder="Search Customer From" class="form-control" /></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <!-- <th></th> -->
                                        <th></th>
                                        <th></th>

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

        let table = $('#datatables-buttons').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            orderCellsTop: true,
            fixedHeader: true,
            ajax: "{{ route('customer_booking') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'CutomerName',
                    name: 'CutomerName'
                },
                {
                    data: 'Address',
                    name: 'Address'
                },
                {
                    data: 'Contact',
                    name: 'Contact'
                },
                {
                    data: 'flat_no',
                    name: 'flat_no'
                },
                {
                    data: 'TotalFlatAmt',
                    name: 'TotalFlatAmt'
                },
                // { data: 'booking', name: 'booking.amt_pay' },

                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'slabs',
                    orderable: false,
                    searchable: false
                }
            ],
            drawCallback: function() {
                // Initialize Feather icons after each draw
                feather.replace();
            }
        });


        // Column search
        $('#datatables-buttons thead tr:eq(1) th').each(function(i) {
            $('input', this).on('keyup change', function() {
                table.column(i).search(this.value).draw();
            });
        });

    });
</script>