@extends('backend.partials.master')
@section('title', 'Customer Payment Report')

@section('maincontent')
<style>
.dataTables_length select {
    width: 65px !important;
    height: 35px;
    padding: 5px 25px 5px 8px !important;
    margin: 0 8px;
    border: 1px solid #ced4da;
    border-radius: 5px;
}

@media (max-width: 767px) {
    .customer-select {
        width: 50%;
    }
}
@media (max-width: 767px) {
    .dataTables_filter {
        text-align: left !important;
        margin: 10px 0;
    }

    .dataTables_filter input {
        margin-left: 0 !important;
        width: 100%;
    }

    .dt-buttons {
        margin-bottom: 10px;
    }
    .dataTables_length {
        text-align: left !important;
    }
}

</style>
<main class="content">
    <link rel="stylesheet"
href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

<link rel="stylesheet"
href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<div class="container-fluid p-0">

    <h3 class="mb-3">Customer Payment Report</h3>

    <div class="card">
        <div class="card-body">

            {{-- CUSTOMER FILTER --}}
            <div class="row mb-3">
                <div class="col-12 col-md-4 col-lg-4">
                    <label>Select Customer</label>
                    <select id="customer_id" class="form-control choices-single-customer customer-select">
                        <option value="">Select Customer</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->ID }}">
                                {{ $cust->CutomerName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="table-responsive">
                <table id="reportTable" class="table table-striped table-hover w-100">
    <thead>
        <tr>
            <th>Date</th>
            <th>Receipt No</th>
            <th>Payment Method</th>
            <th>Payment By</th>
            <th>Type</th>
            <th>Amount</th>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
@section('scripts')
<script>
    $(document).ready(function () {

    let table = $('#reportTable').DataTable({
        destroy: true,
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        lengthMenu: [
            [10,25,50,100,-1],
            [10,25,50,100,"All"]
        ],
        dom:
    "<'row'<'col-12'l>>" +
    "<'row'<'col-12'f>>" +
    "<'row'<'col-12'B>>" +
    "rt" +
    "<'row'<'col-12'i>>" +
    "<'row'<'col-12'p>>",

        buttons: [
            {
                extend: 'copy',
                footer: true
            },
            {
                extend: 'excel',
                footer: true
            },
            {
                extend: 'csv',
                footer: true
            },
            {
                extend: 'pdf',
                footer: true
            },
            {
                extend: 'print',
                footer: true
            }
        ],

        columnDefs: [
            { className: "text-end", targets: 5 }
        ]
    });


    $('#customer_id').change(function () {

        let customerId = $(this).val();

        if (!customerId) {
            table.clear().draw();
            return;
        }

        $.ajax({
            url: "{{ route('customer.payment.report.data') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                customer_id: customerId
            },
            success: function(data){

                table.clear();

                let total = 0;

                data.forEach(function(row){

                    let amount = parseFloat(row.amount) || 0;
                    total += amount;

                    table.row.add([
                        row.date ?? '',
                        row.receipt_no ?? '',
                        row.payment_method ?? '',
                        row.payment_by ?? '',
                        row.type ?? '',
                        amount.toFixed(2)
                    ]);
                });

                table.row.add([
                    '',
                    '',
                    '',
                    '',
                    '<b>Total Paid</b>',
                    '<b>'+total.toFixed(2)+'</b>'
                ]);

                table.draw();
                 table.page.len(-1).draw();
            }
        });

    });

});
</script>
@endsection