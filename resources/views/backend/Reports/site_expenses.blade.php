@extends('backend.partials.master')

@section('title')
    Site Expenses Report
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
            <div class="col-auto d-none d-sm-block">
                <h3><strong>Site Expenses Report</strong></h3>
            </div>
            <div class="col-auto ms-auto text-end mt-n1">
                <button class="btn btn-success" onclick="printReport()">Print</button>

                  <button type="button" class="btn btn-info" onclick="exportExcel()">Export to Excel</button>
            </div>
        </div>
          <form action="{{ route('reports.site_expenses_report') }}" method="GET" class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label>From:</label>
                        <input type="date" name="FromDate" class="form-control "  value="{{ $fromDate }}">
                    </div>
                    <div class="col-md-2">
                        <label>To:</label>
                        <input type="date" name="ToDate" class="form-control "  value="{{ $toDate }}">
                    </div>
                    <div class="col-md-3">
                        <label>Type:</label>
                        <select name="typesrch" class="form-control chosen-select">
                            <option value="">Select Type</option>
                            <option value="all" {{ request('typesrch') == 'all' ? 'selected' : '' }}>All</option>
                            @foreach($expTypes as $type)
                                <option value="{{ $type }}" {{ request('typesrch') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
    <div id="print-area">
        <div class="card">
            <div class="card-body">
              
               
                <table class="table table-striped table-hover">
                    <thead >
                        <tr>
                            <th>Sr.No</th>
                            <th>Date</th>
                            <th>Paid To</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Payment Method</th>
                            <th>Scheme</th>
                            <th>Narration</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0; @endphp
                        @foreach($db_records as $rec)
                            @php 
                                $i++;
                            @endphp
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ date('d-m-Y', strtotime($rec->Date)) }}</td>
                                <td>{{ $rec->title }}</td>
                                <td>{{ $rec->amt_pay }}</td>
                                <td>{{ $rec->Exp_type }}</td>
                                <td>{{ $rec->payment_method == 'cash' ? 'Cash' : ($accounts[$rec->account_no]->Name ?? '') . ' (' . $rec->cheque_no . ')' }}</td>
                                <td>{{ $schemes[$rec->schemeID]->Name ?? '' }}</td>
                                <td>{{ $rec->narration }}</td>
                               
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3"><strong>Grand Total</strong></td>
                            <td><strong>{{ $grandTotal }}</strong></td>
                            <td colspan="5"></td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        $('.date').datepicker();
        $('[data-toggle="tooltip"]').tooltip();
    });

    function printReport() {
        window.print();
    }

    function exportExcel() {
        let fdate = $('input[name="FromDate"]').val();
        let tdate = $('input[name="ToDate"]').val();

        window.location.href =
            "{{ route('reports.site_expenses_report') }}?type=excel&FromDate=" + fdate + "&ToDate=" + tdate;
    }
</script>
@endsection
