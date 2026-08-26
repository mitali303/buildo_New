@extends('backend.partials.master')

@section('title', 'Bank Payment')
<style>
    @media (max-width: 767px) {
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        width: 100% !important;
        float: none !important;
        text-align: left !important;
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_filter input {
        width: 100%;
        margin-left: 0 !important;
    }
}
</style>
@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

    <div class="row mb-3">
        <div class="col">
            
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table id="customer-payment-table" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Flat No</th>
                        <th>Wing</th>
                        <th>Grand Total</th>


                       
                        <th>Loan Sanction Amount</th>
                        <th>Plinth</th>
                        <th>Slab</th>
                        <th>Bricks</th>
                        <th>Plaster</th>
                        <th>Floaring</th>
                        <th>Plumbing</th>
                        <th>Project</th>
                      

                        <th>Total Paid Amt</th>
                        <th>Total Pending Amt</th>
                        <th>Payment By</th>
                        
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
$(function () {
    $('#customer-payment-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('reports.customer_payment.bank') }}?type={{ request('type') }}&SCHEME={{ request('SCHEME') }}",
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'customer_name' },
            { data: 'flat_no' },
            { data: 'wing' },
            { data: 'grand_total' },

            { data: 'loan_sanction' },
            { data: 'plinth' },
            { data: 'slab' },
            { data: 'bricks' },
            { data: 'plaster' },
            { data: 'floaring' },
            { data: 'plumbing' },
            { data: 'project' },
         

            { data: 'total_paid' },
            { data: 'total_pending' },
            { data: 'payment_by' },
            
        ],
        dom: '<"row mb-2"<"col-12"l><"col-12"f>>' +
     't' +
     '<"row mt-2"<"col-md-6"i><"col-md-6"p>>',
        drawCallback: function () {
            feather.replace();
        }
    });
});
</script>
@endpush
