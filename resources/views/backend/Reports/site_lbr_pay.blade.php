@extends('backend.partials.master')
@section('title')
    Site Work Order
@endsection
@section('maincontent')

<style>
      #datatables-buttons th:nth-child(2),
#datatables-buttons td:nth-child(2) {
    white-space: nowrap !important;
}
@media print {

    /* Hide everything */
    body * {
        visibility: hidden !important;
    }

    /* Show only print area */
    #print-area,
    #print-area * {
        visibility: visible !important;
    }

    /* Remove margins */
    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    /* Hide DataTable controls */
    .dataTables_length,
    .dataTables_filter,
    .dataTables_paginate,
    .dataTables_info,
    .dt-buttons {
        display: none !important;
    }
}
@media (max-width: 768px) {

    #datatables-buttons_wrapper .dataTables_length,
    #datatables-buttons_wrapper .dataTables_filter {
        width: 100% !important;
        float: none !important;
        text-align: left !important;
        margin-bottom: 10px;
    }

}

</style>


<main class="content">
    <div class="container-fluid p-0">
        
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Site Work Order</h1> 
        </div>
        
                <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                 <div class="d-flex gap-2">
                <input type="text" id="fdate" class="form-control datepicker" style="width:150px"
                    value="{{ date('d-m-Y') }}" readonly>
                
                <input type="text" id="tdate" class="form-control datepicker" style="width:150px"
                    value="{{ date('d-m-Y') }}" readonly>
                </div>
                <div class="w-100 d-md-auto">
                <button class="btn btn-success" onclick="Getdata()">Show</button>
                
                
                <button class="btn btn-primary" onclick="printReport()">Print</button>
             
                 
                <button class="btn btn-info" onclick="exportExcel()">Export to Excel</button>
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
                                    <th>TDS</th>
                                    <th>Retaintion Amt</th>
                                    <th>Payable Total</th>
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
 // Initialize Flatpickr with d-m-Y format
    flatpickr("#fdate", {
        dateFormat: "d-m-Y",
        defaultDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1)
    });

    flatpickr("#tdate", {
        dateFormat: "d-m-Y",
        defaultDate: new Date()
    });
});

let table;

document.addEventListener("DOMContentLoaded", function () {

    table = $("#datatables-buttons").DataTable({
        processing: true,
        serverSide: true,
        responsive   : false,
            scrollX: true,
        ajax: {
            url: "{{ route('report.Site_lbr_pay') }}",
            data: function (d) {
                d.fdate = $('#fdate').val();
                d.tdate = $('#tdate').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Date' },
            { data: 'ContractorID' },
            { data: 'SiteLocation' },
            { data: 'Total' },
            { data: 'TDSAmt' },
            { data: 'retain_amt' },
            { data: 'gtotal' },
        ],
        dom:
    "<'row'<'col-md-6'l><'col-md-6 text-end'fB>>" +
    "<'row'<'col-sm-12'tr>>" +
    "<'row'<'col-md-5'i><'col-md-7'p>>",
        buttons: ['copy', 'print'],
    });

    table.buttons().container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
});

// 🔹 Reload with date filter
function Getdata() {
    table.ajax.reload();
}

// 🔹 Print (same page)
function printReport() {
    window.print();
}

// 🔹 Export Excel (same page)
function exportExcel() {
    let fdate = $('#fdate').val();
    let tdate = $('#tdate').val();

    window.location.href =
        "{{ route('report.Site_lbr_pay') }}?type=excel&fdate=" + fdate + "&tdate=" + tdate;
}
</script>


