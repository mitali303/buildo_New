@extends('backend.partials.master')

@section('title', 'Customer Payment Report')

@section('maincontent')
<main class="content">

    <style>
        .dishes-scroll {
    width: 100%;
    overflow-x: auto;
}
   @media print {

    body {
        margin: 0;
        padding: 0;
    }

    #print-area {
        width: 100%;
    }

    table {
        width: 100% !important;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 5px;
        font-size: 12px;
    }

    .dataTables_length,
    .dataTables_filter,
    .dataTables_paginate,
    .dataTables_info,
    .dt-buttons {
        display: none !important;
    }
} 
@media (max-width: 575.98px) {

    .dataTables_length,
    .dataTables_filter,
    .dt-buttons {
        width: 100% !important;
        display: block !important;
        margin-bottom: 8px !important;
        text-align: left !important;
    }

    .dataTables_filter input {
        width: 120px !important;
    }

    .dt-buttons {
        display: flex !important;
        gap: 5px;
    }

    .dt-buttons .btn {
        font-size: 10px !important;
        padding: 3px 6px !important;
    }
}
</style>
    <div class="container-fluid p-0">

        <div class="row mb-3">
            <div class="col">
                <h3>Customer Payment Report</h3>
            </div>
           
        </div>
        <div id="print-area">
        <div class="card">
            <div class="card-body">
                <div class="dishes-scroll">
                <table id="customer-payment-table" class="table table-striped table-hover w-100">
                    <thead >
                        <tr>
                            <th>#</th>
                            <th>Customer Name</th>
                            <th>Flat No</th>
                            <th>Wing</th>
                            <th>Total Cost</th>
                            <th>Extra Work</th>
                            <th>Refund</th>
                            <th>Grand Total</th>
                            <th>Bank Sanction</th>
                            <th>Bank Paid</th>
                            <th>Bank Pending</th>
                            <th>Self Payment</th>
                            <th>Self Paid</th>
                            <th>Self Pending</th>
                            <th>Total Paid</th>
                            <th>Total Pending</th>
                            
                        </tr>
                    </thead>
                    <tfoot class="fw-bold">
<tr>
    <td colspan="7" class="text-end">Total :</td>
    <td id="ft-grand"></td>
    <td id="ft-bank-sanction"></td>
    <td id="ft-bank-paid"></td>
    <td id="ft-bank-pending"></td>
    <td id="ft-self"></td>
    <td id="ft-self-paid"></td>
    <td id="ft-self-pending"></td>
    <td id="ft-paid"></td>
    <td id="ft-pending"></td>
</tr>
</tfoot>
                </table>
                </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
$(function () {

    let table = $('#customer-payment-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('reports.customer_payment.customer') }}",
        order: [[0, 'asc']],
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'customer_name' },
            { data: 'flat_no' },
            { data: 'wing' },
            { data: 'total_cost' },
            { data: 'extra_work' },
            { data: 'refund' },
            { data: 'grand_total' },
            { data: 'bank_sanction' },
            { data: 'bank_paid' },
            { data: 'bank_pending' },
            { data: 'self_payment' },
            { data: 'self_paid' },
            { data: 'self_pending' },
            { data: 'total_paid' },
            { data: 'total_pending' }
        ],
        // DOM layout: length menu (l), buttons (B), filter (f), table (t), info (i), pagination (p)
        dom:
    "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'f>>" +
    "<'row mb-2'<'col-md-12'B>>" +
    "<'row'<'col-sm-12'tr>>" +
    "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",
       buttons: [
    {
        extend: 'excelHtml5',
        title: 'Customer Payment Report',
        text: 'Export to Excel',
        className: 'btn btn-secondary btn-sm',
    },
    {
        extend: 'print',
        title: 'Customer Payment Report',
        text: 'Print',
        className: 'btn btn-secondary btn-sm',

        customize: function (win) {

            $(win.document.body).css({
                'font-size': '12px',
                'padding': '20px'
            });

            $(win.document.body).find('h1').css({
                'text-align': 'center',
                'font-size': '20px',
                'margin-bottom': '20px'
            });

            $(win.document.body).find('table').css({
                'width': '100%',
                'border-collapse': 'collapse'
            });

            $(win.document.body).find('table th').css({
                'border': '1px solid black',
                'padding': '8px',
                'background-color': '#f2f2f2',
                'color': '#000',
                'font-weight': 'bold',
                'text-align': 'center'
            });

            $(win.document.body).find('table td').css({
                'border': '1px solid black',
                'padding': '6px',
                'color': '#000'
            });
        }
    }
],
        drawCallback: function () {
            feather.replace();
        }
    });

});


</script>
@endpush
