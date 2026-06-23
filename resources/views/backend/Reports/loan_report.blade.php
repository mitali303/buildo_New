@extends('backend.partials.master')
@section('title')
  Loan Payment Report
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

    .dataTables_length,
    .dataTables_filter,
    .dataTables_paginate,
    .dataTables_info,
    .dt-buttons {
        display: none !important;
    }
}

/* Show Entries Dropdown */
.dataTables_wrapper .dataTables_length select {
    width: 70px !important;
    height: 36px !important;
    padding: 4px 8px !important;
    border: 1px solid #ced4da !important;
    border-radius: 4px !important;
    background-color: #fff !important;
}

.dataTables_wrapper .dataTables_length label {
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>


<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3><strong>Loan Payment Report</strong></h3>
            </div>
            <form action="" method="GET" class="mb-3 row g-2">
            <div class="col-md-2">
                <input type="date" name="FromDate" class="form-control " value="{{ request('FromDate', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}" >
            </div>
            <div class="col-md-2">
                <input type="date" name="ToDate" class="form-control " value="{{ request('ToDate', \Carbon\Carbon::now()->format('Y-m-d')) }}" >
            </div>
            <div class="col-md-5">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>

            <div class="col-md-2"style="width: 8%;">
                <button class="btn btn-success" onclick="printReport()">Print</button>
            </div>

            <div class="col-md-2">
              <button type="button" class="btn btn-info" onclick="exportExcel()">Export to Excel</button>

            </div>
        </form>
            
            <!-- <div class="col-auto ms-auto text-end">
                @if (hasPermission('create_loan_management'))
                    <a href="{{ route('loan_management.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                @endif
            </div> -->
        </div>

        
    <div id="print-area">
        <div class="row">
        <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped" id="bootstrap-table">
                    <thead >
                        <tr>
                            <th>Sr.No</th>
                            <th>Name</th>
                            <th>Total Credit Amount</th>
                            <th>Total Debit Amount</th>
                            <th>Interest Credit</th>
                            <th>Interest Debit</th>
                            <th>Balance</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i = 1;
                            $credittotl = 0;
                            $debittotl = 0;
                            $intrestcredit = 0;
                            $intrestdebit = 0;
                            $totalbal = 0;
                        @endphp

                        @foreach($loanData as $data)
                            @php
                                $credittotl += $data['loanAmtTaken'];
                                $debittotl += $data['loanAmtGive'];
                                $intrestcredit += $data['interestRec'];
                                $intrestdebit += $data['interestPaid'];
                                $totalbal += $data['balance'];
                            @endphp
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>
                                    <a href="{{ route('reports.loan_detail_report', ['customer' => $data['customer']->ID]) }}">
                                        {{ $data['customer']->Name }}
                                    </a>
                                </td>

                                <td><span style="color:#289A47">{{ $data['loanAmtTaken'] }}</span></td>
                                <td><span style="color:#E80000">{{ $data['loanAmtGive'] }}</span></td>
                                <td><span style="color:#289A47">{{ $data['interestRec'] }}</span></td>
                                <td><span style="color:#E80000">{{ $data['interestPaid'] }}</span></td>
                                <td><span style="color:{{ $data['balance'] > 0 ? '#289A47' : '#E80000' }}">{{ $data['balance'] }}</span></td>
                                
                            </tr>
                        @endforeach

                        <tr>
                            <td></td>
                            <td>Grand Total</td>
                            <td>{{ $credittotl }}</td>
                            <td>{{ $debittotl }}</td>
                            <td>{{ $intrestcredit }}</td>
                            <td>{{ $intrestdebit }}</td>
                            <td>{{ $totalbal }}</td>
                        </tr>
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
        order: [[1, 'asc']], // sort by Sr.No
        columnDefs: [
            { orderable: false, targets: [-1] } // disable Action column sorting
        ]
    });

   
});


</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInputs = [
        
        '#FromDate',
        '#ToDate',
    ];

    dateInputs.forEach(selector => {
        const el = document.querySelector(selector);
        if (el) {
            el.addEventListener('click', () => {
                if (el.showPicker) el.showPicker();
                else el.focus();
            });
        }
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
        "{{ route('reports.loan_payment_report') }}?type=excel&FromDate=" + fdate + "&ToDate=" + tdate;
}

</script>
