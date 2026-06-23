@extends('backend.partials.master')

@section('title')
Sold Flat Report
@endsection

@section('maincontent')
<main class="content">
    <style>
        div.dataTables_wrapper div.dataTables_length select {
   
            width: 45%;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
           
            padding: 0px;
        }   
    </style>
<div class="container-fluid p-0">

    <div class="row mb-3">
        <div class="col-auto">
            <h3><strong>Sold Flat Report</strong> </h3>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('reports.rera_report') }}" class="row mb-3">
       
            <div class="col-md-3 d-flex align-items-center">
                        <label class="me-3 mb-0">From </label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control">
            </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <div class="col-md-3 d-flex align-items-center">
                <label class="me-3 mb-0">To </label>&nbsp;
                <input type="date" name="to_date" value="{{ $toDate }}" class="form-control">
            </div>&nbsp;&nbsp;
            <div class="col-md-3">
                <button class="btn btn-primary">Search</button>
            </div>
        
    </form>

    {{-- SOLD FLATS --}}
    <div class="card mb-4">
        <div class="card-body">
            <h4>Sold Flats Details</h4>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Agreement No</th>
                        <th>Agreement Date</th>
                        <th>Wing</th>
                        <th>Flat Type</th>
                        <th>Flat No</th>
                        <th>Area</th>
                        <th>Agreement Amount</th>
                        <th>Received</th>
                        <th>Total Received</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalAgreement = $totalReceived = $totalBalance = 0;
                    @endphp

                    @foreach($soldFlats as $key => $flat)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $flat->CutomerName }}</td>
                        <td>{{ $flat->agreement_no }}</td>
                        <td>{{ date('d-m-Y', strtotime($flat->reg_date)) }}</td>
                        <td>{{ $flat->Wing }}</td>
                        <td>{{ $flat->FlatType }}</td>
                        <td>{{ $flat->FlatNo }}</td>
                        <td>{{ $flat->Area }}</td>
                        <td>{{ number_format($flat->agreementAmt,2) }}</td>
                        <td>{{ number_format($flat->received_this_qtr,2) }}</td>
                        <td>{{ number_format($flat->total_received,2) }}</td>
                        <td>{{ number_format($flat->balance,2) }}</td>
                    </tr>

                    @php
                        $totalAgreement += $flat->agreementAmt;
                        $totalReceived += $flat->total_received;
                        $totalBalance += $flat->balance;
                    @endphp
                    @endforeach

                    <tr class="fw-bold">
                        <td colspan="8" class="text-end">Total</td>
                        <td>{{ number_format($totalAgreement,2) }}</td>
                        <td></td>
                        <td>{{ number_format($totalReceived,2) }}</td>
                        <td>{{ number_format($totalBalance,2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- UNSOLD FLATS --}}
    <div class="card">
        <div class="card-body">
            <h4>Unsold Flats Details</h4>

          <table id="bootstrap-table" class="table table-striped" style="width:100%">
    <thead>
        <tr>
            <th>Sr. No.</th>
            <th>Flat Type</th>
            <th>Flat No</th>
            <th>Carpet Area (sq.m)</th>
            <th>Unit Consideration (ASR)</th>
        </tr>
    </thead>

    <tbody>
        @php $totalAmt = 0; @endphp
        @forelse($unsoldFlats as $key => $flat)
            @php
                $unitAmt = (float) $flat->Area * (float) $flat->gov_rate;
                $totalAmt += $unitAmt;
            @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $flat->FlatType }}</td>
                <td>{{ $flat->FlatNo }}</td>
                <td>{{ $flat->Area }}</td>
                <td>{{ number_format($unitAmt,2) }}</td>
            </tr>
        @empty
    <tr>
    <td class="text-center">Record Not Found</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
        @endforelse
    </tbody>

    @if(count($unsoldFlats))
    <tfoot>
        <tr class="fw-bold">
            <td colspan="4" class="text-end">Total</td>
            <td>{{ number_format($totalAmt,2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>


        </div>
    </div>

</div>
</main>
@endsection
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#bootstrap-table').DataTable({
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        paging: true,
        info: true,
        responsive: true,

        order: [[1, 'asc']],

        columnDefs: [
            { orderable: false, targets: [-1] }
        ],

        language: {
            emptyTable: "Record Not Found"
        }
    });
});
</script>