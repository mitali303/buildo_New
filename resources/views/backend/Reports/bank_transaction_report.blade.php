@extends('backend.partials.master')

@section('title')
Bank Transaction Report
@endsection

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

<h3 class="mb-3"><strong>Bank Transaction Report</strong></h3>

<div class="card">
<div class="card-body">

<div class="row mb-3">
    <div class="col-md-2">
        <input type="text" id="fdate" class="form-control datepicker"
               value="{{ date('d-m-Y') }}" readonly>
    </div>

    <div class="col-md-2">
        <input type="text" id="tdate" class="form-control datepicker"
               value="{{ date('d-m-Y') }}" readonly>
    </div>

    <div class="col-md-3">
        <select id="account_no" class="form-control">
            <option value="">Select</option>
            @foreach($accounts as $acc)
                <option value="{{ $acc->ID }}">
                    {{ $acc->ACNo }} - {{ $acc->Name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2" style="width: 15%;">
        <button class="btn btn-success" onclick="Getdata()">Show Report</button>
    </div>
    <div class="col-md-2"style="width: 8%;">
    <button class="btn btn-primary" onclick="printReport()">Print</button>
</div>

<div class="col-md-2">
    <button class="btn btn-info" onclick="exportExcel()">Export to Excel</button>
</div>
</div>

<div id="ShowCashbook"></div>

</div>
</div>

</div>
</main>
@endsection

@push('scripts')
<script>


function Getdata()
{
    $.post("{{ route('bank.transaction.report.data') }}", {
        _token: "{{ csrf_token() }}",
        fdate: $('#fdate').val(),
        tdate: $('#tdate').val(),
        account_no: $('#account_no').val()
    }, function(data){
        $('#ShowCashbook').html(data);
    });
}

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

// Print report
// Print report
function printReport() {
    var divContents = document.getElementById("ShowCashbook").innerHTML;

    var a = window.open('', '', 'height=600,width=900');

    a.document.write(`
        <html>
        <head>
            <title>Bank Transaction Report</title>

            <link rel="stylesheet" href="{{ asset('css/app.css') }}">

            <style>

                body {
                    font-family: Arial, sans-serif;
                    font-size: 13px;
                    margin: 20px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                table th, table td {
                    border: 1px solid #000;
                    padding: 6px 8px;
                    text-align: left;
                }

                table th {
                    background: #f2f2f2;
                    font-weight: bold;
                }

                tr:nth-child(even) {
                    background: #fafafa;
                }

                @media print {

                    body {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }

                    .no-print {
                        display: none !important;
                    }

                    table th {
                        background: #ddd !important;
                    }
                }

            </style>

        </head>

        <body onload="window.print(); window.close();">
            ${divContents}
        </body>
        </html>
    `);

    a.document.close();
}

// Export to Excel
function exportExcel() {

    var table = document.querySelector("#ShowCashbook table");

    if (!table) {
        alert("No data available!");
        return;
    }

    var csv = [];

    for (var i = 0; i < table.rows.length; i++) {
        var row = [];
        var cols = table.rows[i].querySelectorAll("td, th");

        for (var j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }

        csv.push(row.join(","));
    }

    var csvFile = new Blob([csv.join("\n")], {
        type: "text/csv;charset=utf-8;"
    });

    var downloadLink = document.createElement("a");
    downloadLink.href = URL.createObjectURL(csvFile);
    downloadLink.download = "Bank_Transaction_Report.csv";
    downloadLink.click();
}
</script>

@endpush
