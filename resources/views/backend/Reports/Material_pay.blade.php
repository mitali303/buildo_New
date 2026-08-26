@extends('backend.partials.master')
@section('title')
    Site Work Order
@endsection
@section('maincontent')

<style>
@media print {

    body * {
        visibility: hidden !important;
    }

    #print-area,
    #print-area * {
        visibility: visible !important;
    }

    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .no-print,
    .dt-buttons,
    .dataTables_filter,
    .dataTables_length,
    .dataTables_paginate,
    .dataTables_info {
        display: none !important;
    }
}

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

<main class="content">
    <div class="container-fluid p-0">
       
        <!--<div class="row mb-3">-->
        <!--    <div class="col-md-2"style="width: 8%;">-->
        <!--        <button class="btn btn-primary" onclick="printReport()">Print</button>-->
        <!--    </div>-->

        <!--    <div class="col-md-2">-->
        <!--        <button class="btn btn-info" onclick="exportExcel()">Export to Excel</button>-->
        <!--    </div>-->
        <!--</div>-->

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Supplier Report</h1> 
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

            <div class="col-md-6 d-flex align-items-end">
                <button class="btn btn-primary me-2" onclick="filterData()">Filter</button>
                <button class="btn btn-secondary me-2" onclick="resetFilter()">Reset</button>
                <button class="btn btn-primary me-2" onclick="printReport()">Print</button>
                <button class="btn btn-info me-2" onclick="exportExcel()">Export to Excel</button>
            </div>

        </div>
        <div id="print-area">
        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Date</th>
                                    <th>Contractor</th>
                                    <th>Scheme</th>
                                    <th>Total</th>
                                    <th>Amount Paid</th>
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
let table;

document.addEventListener("DOMContentLoaded", function () {

    let today = new Date();
    let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    function formatDate(date) {
        let m = '' + (date.getMonth() + 1);
        let d = '' + date.getDate();
        let y = date.getFullYear();

        if (m.length < 2) m = '0' + m;
        if (d.length < 2) d = '0' + d;

        return [y, m, d].join('-');
    }

    $('#from_date').val(formatDate(firstDay));
    $('#to_date').val(formatDate(today));

    table = $("#datatables-buttons").DataTable({
        processing: true,
        responsive   : false,
            scrollX: true,
        serverSide: true,
        ajax: {
            url: "{{ route('report.Material_pay') }}",
            data: function(d){
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'Date' },
            { data: 'PurchaseFrom' },
            { data: 'scheme' },
            { data: 'invoice_total' },
            { data: 'amt_pay' },
        ],
        drawCallback: function () {
            feather.replace();
        }
    });
});

// filter
function filterData(){
    table.ajax.reload();
}

// reset
function resetFilter(){
    $('#from_date').val('');
    $('#to_date').val('');
    table.ajax.reload();
}
function printReport() {
    window.print();
}

// 🔹 EXPORT TO EXCEL
function exportExcel() {
    let from = $('#from_date').val();
    let to   = $('#to_date').val();

    window.location.href = "{{ route('report.Material_pay') }}?type=excel&from_date="+from+"&to_date="+to;
}
</script>

