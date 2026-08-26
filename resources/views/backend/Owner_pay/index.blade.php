@extends('backend.partials.master')
@section('title')
    Owner Payment
@endsection
<style>
.nowrap { white-space: nowrap; }

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
        @if (hasPermission('create_owner_payment'))
        <a href="{{route('Owner_Pay.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Owner Payment</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Owner Payment</h1> 
        </div>
        <div class="row mb-3">

            <div class="col-md-3 col-6">
                <label>From Date</label>
                <input type="date" id="from_date" class="form-control">
            </div>

            <div class="col-md-3 col-6">
                <label>To Date</label>
                <input type="date" id="to_date" class="form-control">
            </div>

            <div class="col-md-3 col-12 mt-2 mt-md-0 text-center">
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
                                    <th>Sr No</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Payment Method</th>
                                    <th>Total Amt</th>
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
document.addEventListener("DOMContentLoaded", function () {

    // ✅ Default Dates
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

    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive   : false,
            scrollX: true,
        processing   : true,
        serverSide   : true,

        ajax: {
            url: "{{ route('Owner_Pay') }}",
            data: function(d){
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },

        columns : [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'Date' },
            { data: 'type' },
            { data: 'payment_method' },
            { data: 'total_pay' },
            { data: 'actions', orderable:false, searchable:false }
        ],

        drawCallback : function () {
            feather.replace();
        }
    });

    // ✅ Filter
    $('#filterBtn').on('click', function(){
        datatablesButtons.ajax.reload();
    });

    // ✅ Reset
    $('#resetBtn').on('click', function(){
        $('#from_date').val(formatDate(firstDay));
        $('#to_date').val(formatDate(today));
        datatablesButtons.ajax.reload();
    });

});
</script>