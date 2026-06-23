@extends('backend.partials.master')

@section('title')
    Stock Report
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

        <div class="row mb-2 mb-xl-3">
          
             <div class="col">
                <h3 class="mb-0"><strong>Stock Report</strong></h3>
            </div>

            <!-- Buttons -->
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-success" onclick="printReport()">Print</button>
                <button type="button" class="btn btn-info" onclick="exportExcel()">Export to Excel</button>
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
                                    <th>#</th>
                                    <th>Material Name</th>
                                    <th>Type</th>
                                    <th>Unit</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($stockData as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['type'] }}</td>
                                        <td>{{ $row['unit'] }}</td>
                                        <td>{{ $row['stock'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#datatables-buttons').DataTable({
        responsive: true,
        fixedHeader: true
    });
});

function printReport() {
    window.print();
}

// 🔹 Export Excel (same page)
function exportExcel() {
    let fdate = $('input[name="FromDate"]').val();
    let tdate = $('input[name="ToDate"]').val();

    window.location.href =
        "{{ route('reports.stock_report') }}?type=excel&FromDate=" + fdate + "&ToDate=" + tdate;
}

</script>
@endpush
