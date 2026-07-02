@extends('backend.partials.master')
@section('title')
  Customer Refund Report
@endsection
@section('maincontent')

<style>
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
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<main class="content">
    <div class="container-fluid p-0">
        <div class="row align-items-center mb-3">
    
    <div class="col">
        <h3 class="mb-0"><strong>Customer Refund Report</strong></h3>
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

    <div class="col-md-4 align-self-end">
        <button id="filter" class="btn btn-primary">Search</button>
        <button id="reset" class="btn btn-secondary">Reset</button>
        <button class="btn btn-success" onclick="printReport()">Print</button>
    </div>

<!-- Buttons -->
    <!--<div class="col-auto d-flex gap-2">-->
        <!--<button class="btn btn-success" onclick="printReport()">Print</button>-->
        <!-- <button type="button" class="btn btn-info" onclick="exportExcel()">Export to Excel</button> -->
    <!--</div>-->
</div>
    <!-- Title -->
    

    

</div>

        <div id="print-area">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Customer Name</th>
                                    <th>Scheme</th>
                                    <th> Amount</th>
                                    
                                </tr>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
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
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    // 🔹 Default dates (Start of month → Today)
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

    let table = $('#datatables-buttons').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        orderCellsTop: true,
        fixedHeader: true,
        dom: '<"row"<"col-md-6"l><"col-md-6"Bf>>rtip',

            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel"></i> Export Excel',
                    title: 'Customer Refund Report',
                    className: 'btn btn-info',
                    exportOptions: {
                        columns: [0,1,2,3,4]
                    }
                }
            ],
        ajax: {
            url: "{{ route('customer_refund') }}",
            data: function(d){
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },

        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'date', name:'Date' },
            { data: 'customer_name', name:'customers.CutomerName' },
            { data: 'schemes_name', name:'schemes.Name' },
            { data: 'amt_pay', name:'amt_pay' }
        ],

        drawCallback: function(){
            feather.replace();
        }
    });

    // 🔍 Search
    $('#filter').click(function(){
        table.ajax.reload();
    });

    // 🔄 Reset
    $('#reset').click(function(){
        $('#from_date').val(formatDate(firstDay));
        $('#to_date').val(formatDate(today));
        table.ajax.reload();
    });

});

function printReport() {
    window.print();
}

</script>



