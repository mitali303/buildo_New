@extends('backend.partials.master')
@section('title')
    Site Expenses Payment
@endsection
<style>
    #datatables-buttons th:nth-child(2),
#datatables-buttons td:nth-child(2) {
    white-space: nowrap !important;
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
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_site_expenses'))
        <a href="{{ route('Site_exp_pay.create') }}" class="btn btn-primary btn-sm float-end mt-n1">
            <i class="fas fa-plus"></i> New Site Expenses Payment
        </a>

        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Site Expenses Payment</h1> 
        </div>

        <div class="row mb-3">

        <div class="col-6 col-md-3">
            <label>From Date</label>
            <input type="date" id="from_date" class="form-control">
        </div>

        <div class="col-6 col-md-3">
            <label>To Date</label>
            <input type="date" id="to_date" class="form-control">
        </div>

        <div class="col-12 col-md-3 d-flex justify-content-center justify-content-md-start align-items-end gap-2 mt-2 mt-md-0">
            <button id="filterBtn" class="btn btn-primary">Filter</button>
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
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Exp type</th>
                                    <th>Scheme</th>
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
        responsive   : false,
            scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('Site_exp_pay') }}",
            data: function(d){
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Date' },
            { data: 'payment_method' },
            { data: 'amt_pay' },
            { data: 'Exp_type' },
            { data: 'schemeID' },
            { data: 'narration' },
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

    // RESET (Back to Default)
    $('#resetBtn').on('click', function(){
        $('#from_date').val(formatDate(firstDay));
        $('#to_date').val(formatDate(today));
        datatablesButtons.ajax.reload();
    });

});
</script>
