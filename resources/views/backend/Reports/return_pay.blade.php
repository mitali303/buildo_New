@extends('backend.partials.master')
@section('title')
    Return Payment
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
<main class="content">
    <div class="container-fluid p-0">
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Return Payment</h1> 
        </div>
       <div class="row mb-3">
                <div class="col-md-2">
                    <input type="text" id="fdate" class="form-control datepicker"
                        value="{{ date('d-m-Y') }}" readonly>
                </div>

                <div class="col-md-2">
                    <input type="text" id="tdate" class="form-control datepicker"
                        value="{{ date('d-m-Y') }}" readonly>
                </div>

                <div class="col-md-2" style="width: 13%;">
                    <button class="btn btn-success" onclick="Getdata()">Show</button>
                </div>
                <div class="col-md-2"style="width: 8%;">
                <button class="btn btn-primary" onclick="printReport()">Print</button>
            </div>

            <div class="col-md-2">
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
                                        <th>Date</th>
                                        <th>Payment Method</th>
                                        <th>Return Type</th>
                                        <th>Amount</th>
                                        <th>Scheme</th>
                                        <th>Narration</th>
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

    // Flatpickr
    flatpickr("#fdate", {
        dateFormat: "d-m-Y",
        defaultDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1)
    });

    flatpickr("#tdate", {
        dateFormat: "d-m-Y",
        defaultDate: new Date()
    });

    // DataTable
    table = $("#datatables-buttons").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('report.return_pay') }}",
            data: function (d) {
                d.fdate = $('#fdate').val();
                d.tdate = $('#tdate').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Date' },
            { data: 'payment_method' },
            { data: 'Type_Payment' },
            { data: 'amt_pay' },
            { data: 'schemeID' },
            { data: 'narration' },
        ],
        drawCallback: function () {
            feather.replace();
        }
    });
});

// 🔍 Reload with date filter
function Getdata() {
    table.ajax.reload();
}

// 🖨️ PRINT ONLY TABLE
function printReport() {
    window.print();
}

// 📊 EXPORT TO EXCEL
function exportExcel() {
    let fdate = $('#fdate').val();
    let tdate = $('#tdate').val();

    window.location.href =
        "{{ route('report.return_pay') }}" +
        "?export=excel&fdate=" + fdate + "&tdate=" + tdate;
}
</script>

