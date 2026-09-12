@extends('backend.partials.master')

@section('title','Labour Payment')
<style>
@media (max-width: 768px) {
    #payment-table_wrapper .dataTables_length,
    #payment-table_wrapper .dataTables_filter {
        float: none !important;
        width: 100% !important;
        text-align: left !important;
        margin-bottom: 8px;
    }

    #payment-table_wrapper .dataTables_filter input {
        width: 150px !important;
    }

    #payment-table_wrapper {
        overflow-x: auto;
    }
    #payment-table {
        min-width: 900px !important;
        white-space: nowrap !important;
    }
    
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-3">
            <div class="col-6">
                <h1 class="h3 mb-0">Labour Payment</h1>
            </div>

            <div class="col-6 text-end">
                <a href="{{ route('Labour_work_pay') }}" class="btn btn-secondary">
                    Back
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="payment-table" class="table table-striped w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Payment By</th>
                                    <th>Amount Paid</th>
                                    <th>Bank Charge</th>
                                    <th>Payable</th>
                                    <th>Narration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <!-- FOOTER -->
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th id="ft-gtotal"></th>     <!-- below Date -->
                                    <th></th>
                                    <th id="ft-paid"></th>       <!-- below Amount Paid -->
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th id="ft-pending"></th>    <!-- below Action -->
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {

let table = $("#payment-table").DataTable({

    processing: true,
    serverSide: true,
    responsive   : false,
            scrollX: true,

    ajax: {
        url: "{{ route('Labour_work_pay.viewPayment.data',$ids) }}",
        type: "GET"
    },

    columns: [
        { data: 'DT_RowIndex', orderable:false, searchable:false },
        { data: 'Date' },
        { data: 'payment_method' },
        { data: 'amt_pay' },
        { data: 'bankcharge' },
        { data: 'Payable' },
        { data: 'paydetail' },
        { data: 'actions', orderable:false, searchable:false }
    ],

    footerCallback: function (row, data) {

        let totalPaid = 0;

        data.forEach(function(row){
            totalPaid += parseFloat(row.amt_pay) || 0;
        });

        let gtotal = {{ $gtotal }};
        let pending = gtotal - totalPaid;
        if(pending < 0) pending = 0;

        $('#ft-gtotal').html(
            '<strong>GT: ₹'+gtotal.toFixed(2)+'</strong>'
        );

        $('#ft-paid').html(
            '<strong>Paid: ₹'+totalPaid.toFixed(2)+'</strong>'
        );

        $('#ft-pending').html(
            '<strong class="text-danger">Pending: ₹'+pending.toFixed(2)+'</strong>'
        );
    },

    drawCallback: function () {
        feather.replace();
    }
});

});

</script>
@endsection
