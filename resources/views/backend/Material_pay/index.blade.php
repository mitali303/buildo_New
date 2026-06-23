@extends('backend.partials.master')
@section('title')
    Site Work Order
@endsection
<style>
.nowrap { white-space: nowrap; }
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_material_payment'))
        <a href="{{route('Material_pay.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Material Payment</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Material Payment</h1> 
        </div>

        <div class="row mb-3">

            <div class="col-md-3">
                <label>From Date</label>
                <input type="date" id="from_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label>To Date</label>
                <input type="date" id="to_date" class="form-control">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button id="filterBtn" class="btn btn-primary me-2">Filter</button>
                <button id="resetBtn" class="btn btn-secondary">Reset</button>
            </div>

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
                                    <th>Contractor</th>
                                    <th>Scheme</th>
                                    <th>Total</th>
                                    <th>Amount Paid</th>
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
@endsection
<script>
document.addEventListener("DOMContentLoaded", function() {

    // ===============================
    // DEFAULT DATES (Start of Month → Today)
    // ===============================
    let today = new Date();
    let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    function formatDate(date) {
        let month = '' + (date.getMonth() + 1);
        let day = '' + date.getDate();
        let year = date.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    $('#from_date').val(formatDate(firstDay));
    $('#to_date').val(formatDate(today));

    // ===============================
    // DATATABLE
    // ===============================
    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('Material_pay') }}",
            data: function (d) {
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Date' , className: 'nowrap'},
            { data: 'PurchaseFrom' },
            { data: 'scheme' },
            { data: 'invoice_total' },
            { data: 'amt_pay' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        lengthChange: true,
        buttons: ['copy', 'print'],
        drawCallback: function () {
            feather.replace();
        }
    });

    datatablesButtons.buttons()
        .container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");

    // FILTER
    $('#filterBtn').on('click', function(){
        datatablesButtons.ajax.reload();
    });

    // RESET (Back to Default Dates)
    $('#resetBtn').on('click', function(){
        $('#from_date').val(formatDate(firstDay));
        $('#to_date').val(formatDate(today));
        datatablesButtons.ajax.reload();
    });

});
</script>
