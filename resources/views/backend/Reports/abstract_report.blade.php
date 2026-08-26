@extends('backend.partials.master')
@section('title','Abstract Report')

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
.table-responsive > table {
    min-width: 650px;
}
</style>

<main class="content">
<div class="container-fluid p-0">

<h3>Abstract Report</h3>

<div class="row mb-3">
    <div class="col-6 col-md-3">
        <input type="date" id="fdate" class="form-control"
               value="{{ now()->startOfMonth()->format('Y-m-d') }}">
    </div>
   <div class="col-6 col-md-3">
        <input type="date" id="tdate" class="form-control"
               value="{{ now()->format('Y-m-d') }}">
    </div>
     <div class="col-12 col-md-3 d-flex justify-content-center justify-content-md-start align-items-end gap-2 mt-2 mt-md-0">
        <button id="filter" class="btn btn-primary">Show</button>
        <button class="btn btn-primary" onclick="printReport()">Print</button>
    </div>
</div>
<div id="print-area">
<h4>Loan Abstract</h4>
<div class="table-responsive">
<table class="table table-striped" id="loan-table">
<thead>
<tr>
    <th>Name</th>
    <th>Old Balance</th>
    <th>Credit</th>
    <th>Debit</th>
    <th>Balance</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>

<h4>Partners Abstract</h4>
<div class="table-responsive">
<table class="table table-striped" id="partner-table">
<thead>
<tr>
    <th>Name</th>
    <th>Old Balance</th>
    <th>Credit</th>
    <th>Debit</th>
    <th>Balance</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>

<h4>Expenses Abstract</h4>
<div class="table-responsive">
<table class="table table-striped" id="expense-table">
<thead>
<tr>
    <th>Particular</th>
    <th>Old Balance</th>
    <th>Credit</th>
    <th>Debit</th>
    <th>Balance</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>

<h4>Final Summary</h4>
<table class="table table-bordered">
<tbody id="summary-table"></tbody>
</table>

</div>
</main>
@endsection

@push('scripts')
<script>
$('#filter').click(function () {

    $.get("{{ route('report.abstract') }}", {
        fdate: $('#fdate').val(),
        tdate: $('#tdate').val()
    }, function (res) {

        let loanHtml = '';
        res.loan.forEach(r => { 
            loanHtml += `
            <tr>
                <td>${r.vendor.Name}</td>
                <td>${r.openBal}</td>
                <td>${r.credit}</td>
                <td>${r.debit}</td>
                <td>${r.balance}</td>
            </tr>`;
        });
        $('#loan-table tbody').html(loanHtml);

        let partnerHtml = '';
       res.partners.forEach(r => {
    partnerHtml += `
    <tr>
        <td>${r.partner ? r.partner.Name : ''}</td>
        <td>${r.openBal}</td>
        <td>${r.credit}</td>
        <td>${r.debit}</td>
        <td>${r.balance}</td>
    </tr>`;
});
        $('#partner-table tbody').html(partnerHtml);

        let expenseHtml = '';

        // OFFICE EXPENSE
        let officeOld = -parseFloat(res.office.officeExpensePrev || 0);
        let officeDebit = parseFloat(res.office.officeExpense || 0);
        let officeBalance = officeOld - officeDebit;

        expenseHtml += `
        <tr>
            <td>Office Expense</td>
            <td>${officeOld.toFixed(2)}</td>
            <td>0.00</td>
            <td>${officeDebit.toFixed(2)}</td>
            <td>${officeBalance.toFixed(2)}</td>
        </tr>`;

        // TDS
        let tdsOld = parseFloat(res.tds.tdsCreditPrev || 0) - parseFloat(res.tds.tdsDebitPrev || 0);
        let tdsCredit = parseFloat(res.tds.tdsCredit || 0);
        let tdsDebit = parseFloat(res.tds.tdsDebit || 0);
        let tdsBalance = (tdsCredit - tdsDebit) + tdsOld;

        expenseHtml += `
        <tr>
            <td>T.D.S</td>
            <td>${tdsOld.toFixed(2)}</td>
            <td>${tdsCredit.toFixed(2)}</td>
            <td>${tdsDebit.toFixed(2)}</td>
            <td>${tdsBalance.toFixed(2)}</td>
        </tr>`;

        $('#expense-table tbody').html(expenseHtml);


        $('#summary-table').html(`
            <tr><th>Old Balance</th><td>${res.summary.oldBalance}</td></tr>
            <tr><th>Total Credit</th><td>${res.summary.grandCredit}</td></tr>
            <tr><th>Total Debit</th><td>${res.summary.grandDebit}</td></tr>
            <tr><th>Balance</th><td>${res.summary.grandCredit - res.summary.grandDebit + res.summary.oldBalance}</td></tr>
        `);
    });
});

// 🔹 Print (same page)
function printReport() {
    window.print();
}
</script>
@endpush
