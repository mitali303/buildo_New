@extends('backend.partials.master')

@section('title','Income Expense Report')
 
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

        <h1 class="h3 mb-3">Income Expense Report</h1>

        {{-- Filters --}} 
        <div class="row mb-3">
            <div class="col-md-3">
                <label>From Date</label>
                <input type="date" id="fdate" class="form-control"
                       value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </div>

            <div class="col-md-3">
                <label>To Date</label>
                <input type="date" id="tdate" class="form-control"
                       value="{{ now()->format('Y-m-d') }}">
            </div>

            <div class="col-md-3 align-self-end">
                <button class="btn btn-primary" id="filter">Show Report</button>
                <button class="btn btn-primary" onclick="printReport()">Print</button>
                
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body">
                <div id="print-area">
                    <table id="income-expense" class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Particulars</th>
                                <th>Payment Details</th>
                                <th>Debit</th>
                                <th>Credit</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
@push('scripts')
<script>
$(document).ready(function () {

    let table = $('#income-expense').DataTable({
        processing: true,
        serverSide: false,
        ordering: false,
        paging: false,
        searching: false,
        dom: 'Bfrtip', 
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel"></i> Export Excel',
                title: 'Income Expense Report'
            }
        ],
        ajax: {
            url: "{{ route('report.income_expense') }}",
            data: function (d) {
                d.fdate = $('#fdate').val();
                d.tdate = $('#tdate').val();
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'date' },
            { data: 'particulars', render: function(data){ return data; } },
            { data: 'payment_details' },
            { data: 'debit', render: function(data){ return data; } },
            { data: 'credit', render: function(data){ return data; } }
        ],
        
    });

    $('#filter').on('click', function () {
        table.ajax.reload();
    });

});

// 🔹 Print (same page)
function printReport() {
    window.print();
}
</script>
@endpush
