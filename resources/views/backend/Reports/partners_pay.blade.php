@extends('backend.partials.master')
@section('title')
    {{ ucfirst($type) }} Payment
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

    .dataTables_filter,
    .dataTables_length,
    .dataTables_paginate,
    .dataTables_info,
    .dt-buttons,
    button,
    input,
    label {
        display: none !important;
    }
}
</style>


<main class="content">
    <div class="container-fluid p-0">
        
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">{{ ucfirst($type) }} Payment</h1> 
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

                        <div class="col-md-6 align-self-end">
                            <button id="filter" class="btn btn-primary">Search</button>
                            <button id="reset" class="btn btn-secondary">Reset</button>
                            <button class="btn btn-primary" onclick="printReport()">Print</button>

                            <button class="btn btn-info" onclick="exportExcel()">Export to Excel</button>
                        </div>
                    </div>
                    
        

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">

                    

                        <div id="print-area">
                            <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>{{ ucfirst($type) }}</th>
                                    <th>Credit</th>
                                    <th>Debit</th>
                                    <th>Balance</th>
                                    <th>Last Payment Date</th>
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

    // 🔹 Get Today & First Day of Month
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

    // 🔹 Set Default Dates
    $('#from_date').val(formatDate(firstDay));
    $('#to_date').val(formatDate(today));

    table = $("#datatables-buttons").DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('report.partner_pay', ['type' => $type]) }}",
            data: function (d) {
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'partner' },
            { data: 'credit' },
            { data: 'debit' },
            { data: 'balance' },
            { data: 'last_payment_date' },
        ],
        drawCallback: function () {
            feather.replace();
        }
    });

    $('#filter').click(() => table.draw());

    $('#reset').click(() => {
        $('#from_date').val(formatDate(firstDay));
        $('#to_date').val(formatDate(today));
        table.draw();
    });

});
// 🖨️ PRINT ONLY TABLE
function printReport() {
    window.print();
}

// 📊 EXPORT TO EXCEL
function exportExcel() {
    let from = $('#from_date').val();
    let to   = $('#to_date').val();

    window.location.href =
        "{{ route('report.partner_pay', ['type' => $type]) }}" +
        "?export=excel&from_date=" + from + "&to_date=" + to;
}
</script>


