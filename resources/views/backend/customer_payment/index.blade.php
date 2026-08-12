@extends('backend.partials.master')

@section('title', 'Customer Payment')

@section('maincontent')
<main class="content">

    <style>
    .dishes-scroll {
        display: grid;
        gap: 15px;
        overflow-y: auto;
        padding: 10px;
    }
    </style>
    <div class="container-fluid p-0">

        <div class="row mb-3">
            <div class="col">
                <h3>Customer Payment Entry</h3>
            </div>
            <div class="col text-end">
                @if(hasPermission('create_customer_payment'))
                    <a href="{{ route('customer_payment.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Receive Payment
                    </a>
                @endif
            </div>
        </div>
        <!-- <div class="row mb-3">

            <div class="col-md-3">
                <label>From Date</label>
                <input type="date" id="from_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label>To Date</label>
                <input type="date" id="to_date" class="form-control">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button id="filterBtn" class="btn btn-primary me-2">Filter</button>
                <button id="resetBtn" class="btn btn-secondary">Reset</button>
            </div>

        </div> -->
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
                            <th>Payment By</th>
                            <th>Action</th>
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
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
$(function () {

    // let today = new Date();
    // let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    // function formatDate(date) {
    //     let m = '' + (date.getMonth() + 1);
    //     let d = '' + date.getDate();
    //     let y = date.getFullYear();

    //     if (m.length < 2) m = '0' + m;
    //     if (d.length < 2) d = '0' + d;

    //     return [y, m, d].join('-');
    // }

    // $('#from_date').val(formatDate(firstDay));
    // $('#to_date').val(formatDate(today));

    let table = $('#customer-payment-table').DataTable({
        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('customer_payment') }}",
            // data: function (d) {
            //     d.from_date = $('#from_date').val();
            //     d.to_date   = $('#to_date').val();
            // }
        },

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
            { data: 'total_pending' },
            { data: 'payment_by' },
            { data: 'actions', orderable:false, searchable:false }
        ],

        drawCallback: function () {
            feather.replace();
        }
    });

    $('#filterBtn').click(function(){
        table.ajax.reload();
    });

    $('#resetBtn').click(function(){
        $('#from_date').val(formatDate(firstDay));
        $('#to_date').val(formatDate(today));
        table.ajax.reload();
    });

});
</script>
@endpush
