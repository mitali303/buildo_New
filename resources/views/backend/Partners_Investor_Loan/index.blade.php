@extends('backend.partials.master')
@section('title')
    Partners/Loan/Investors
@endsection
<style>
    @media (max-width: 768px) {
    #datatables-buttons_wrapper .dataTables_length,
    #datatables-buttons_wrapper .dataTables_filter {
        width: 100%;
        float: none;
        text-align: left;
        margin-bottom: 10px;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_partner_loan_investor'))
        <a href="{{route('PartnerLoanInvestor.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Partners/Loan/Investors</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Partners/Loan/Investors</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Name</th>
                                    <th>Contact No.</th>
                                    <th>Address</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
<script>
document.addEventListener("DOMContentLoaded", function () {

    /* initialise DataTable */
    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive   : false,
        scrollX: true,
        processing   : true,
        serverSide   : true,
        ajax         : "{{ route('PartnerLoanInvestor') }}",

        columns : [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'Name',       name: 'Name' },
            { data: 'ContactNo',  name: 'ContactNo' },
            { data: 'Address',    name: 'Address' },
            { data: 'Email',      name: 'Email' },
            { data: 'Type',       name: 'Type' },
            { data: 'CFlag',      name: 'CFlag', orderable:false, searchable:false }, // ⬅ checkbox column
            { data: 'actions',    name: 'actions', orderable:false, searchable:false }
        ],

        lengthChange : true,
        buttons      : ['copy', 'print'],

        /* ←───────────────  place the block HERE  ───────────────→ */
        drawCallback : function () {
            feather.replace();                 // your existing icon refresh

            /* one delegated handler for every redraw */
            $('#datatables-buttons')
                .off('change', '.flag-toggle')   // remove previous handlers
                .on('change', '.flag-toggle', function () {

                    const $box = $(this);
                    const id   = $box.data('id');
                    const flag = $box.is(':checked') ? 1 : 0;

                    $.post("{{ route('PartnerLoanInvestor.toggle') }}", {
                        _token : "{{ csrf_token() }}",
                        id     : id,
                        flag   : flag
                    }).fail(() => {
                        toastr.error('Could not update status');
                        // Roll back the checkbox if the save fails
                        $box.prop('checked', !flag);
                    });
                });
        }
        /* ←──────────  drawCallback ends  ──────────→ */
    });

    /* move the export buttons just like before */
    datatablesButtons.buttons()
        .container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
});
</script>
