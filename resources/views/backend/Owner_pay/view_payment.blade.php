@extends('backend.partials.master')

@section('title')
    Site Work Payment
@endsection

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_purchase_order'))
        <a href="{{route('Site_work_order.create')}}" class="btn btn-primary float-end mt-n1">
            <i class="fas fa-plus"></i> New Site Work Order
        </a>
        @endif

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Site Work Payment</h1> 
        </div>
        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Payment By</th>
                                    <th>Amount Paid</th>
                                    <th>Tax</th>
                                    <th>TDS</th>
                                    <th>Payable Amount</th>
                                    <th>Narration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
document.addEventListener("DOMContentLoaded", function() {

    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
    url: "{{ route('Site_work_pay.viewPayment.data', $id) }}",
    type: "GET"
},

        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Date' },
            { data: 'payment_method' },
            { data: 'amt_pay' },
            { data: 'TaxAmt' },
            { data: 'TDSAmt' },
            { data: 'Payable' },
            { data: 'paydetail' },
            { data: 'actions' }
        ],
        lengthChange: true,
        buttons: ['copy', 'print'],
        drawCallback: function () {
            feather.replace();
        }
    });

    datatablesButtons.buttons().container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");

});
</script>
@endsection
