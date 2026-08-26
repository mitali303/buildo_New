@extends('backend.partials.master')
@section('title')
    Site Work Order
@endsection
<style>
.nowrap {
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
        @if (hasPermission('create_site_work_order'))
        <a href="{{route('Site_work_order.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Site Work Order</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Site Work Order</h1> 
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
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Contractor</th>
                                    <th>Scheme</th>
                                    <th>Total</th>
                                    <th>TDS</th>
                                    <th>Retaintion Amt</th>
                                    <th>Payable Total</th>
                                    <th>Paid Amount</th>
                                    <th>pending payable</th>
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

    // ✅ Set Default Dates
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

    // ✅ DataTable
    var datatablesButtons = $("#datatables-buttons").DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: true,
        orderCellsTop: true,
        fixedHeader: true,

        ajax: {
            url: "{{ route('Site_work_pay') }}",
            data: function(d) {
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },

        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Date', className: 'nowrap' },
            { data: 'ContractorID' },
            { data: 'SiteLocation' },
            { data: 'Total' },
            { data: 'TDSAmt' },
            { data: 'retain_amt' },
            { data: 'gtotal' },
            { data: 'amount_pay' },   // ✅ ADD THIS
            { data: 'pending payable'},
            { data: 'actions', orderable: false, searchable: false }
        ],

        drawCallback: function () {
            feather.replace();
        }
    });

    // ✅ Filter Button
    $('#filterBtn').on('click', function() {
        datatablesButtons.ajax.reload();
    });

    // ✅ Reset Button (back to month start → today)
    $('#resetBtn').on('click', function() {
        $('#from_date').val(formatDate(firstDay));
        $('#to_date').val(formatDate(today));
        datatablesButtons.ajax.reload();
    });

});
</script>
