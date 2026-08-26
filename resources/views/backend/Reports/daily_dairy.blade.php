@extends('backend.partials.master')

@section('title','Daily Diary Report')

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
@media (max-width: 768px) {

    #daily-diary_wrapper .dataTables_length,
    #daily-diary_wrapper .dataTables_filter {
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
            <h1 class="h3 d-inline align-middle">Daily Diary Report</h1>
        </div>
{{-- Date Filter --}}
        <!-- <div class="row mb-3">
            <div class="col-md-3">
                <label>Date</label>
                <input type="date" id="date" class="form-control"
                       value="{{ now()->format('Y-m-d') }}">
            </div>

            <div class="col-md-3 align-self-end">
                <button class="btn btn-primary" id="filter">Show</button>
                <button class="btn btn-primary" onclick="printReport()">Print</button>

            </div>
        </div> -->
        

        

        <div class="card">
            <div class="card-body">
                <div id="print-area">
                    <table id="daily-diary" class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Particulars</th>
                                <th>Payment Details</th>
                                <th>Debit</th>
                                <th>Credit</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    let table = $('#daily-diary').DataTable({
        responsive   : false,
            scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('report.daily_diary') }}",
            data: function (d) {
                d.date = $('#date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'drcr', orderable:false, searchable:false },
            { data: 'particulars' },
            { data: 'payment_details' },
            { data: 'debit' },
            { data: 'credit' },
        ]
        
    });

    $('#filter').click(function () {
        table.draw();
    });
});

// 🔹 Print (same page)
function printReport() {
    window.print();
}
</script>
@endpush
