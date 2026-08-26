@extends('backend.partials.master')
@section('title')
  Customer Refund
@endsection
<style>
    #datatables-buttons th:nth-child(2),
#datatables-buttons td:nth-child(2) {
    white-space: nowrap !important;
}
    @media (max-width: 768px) {
    #datatables-buttons_wrapper .dataTables_length,
    #datatables-buttons_wrapper .dataTables_filter {
        width: 100%;
        float: none;
        text-align: left;
        margin-bottom: 10px;
    }

    #datatables-buttons_wrapper .dt-buttons .btn {
        padding: 4px 8px;
        font-size: 12px;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3><strong></strong>Customer Refund</h3>
            </div>
            <div class="col-auto ms-auto text-end mt-n1">
                
                @if (hasPermission('create_customer_refund') == true)
                    <a href="{{route('customer_refund.create')}}" class="btn btn-sm  btn-primary"><i class="fas fa-plus"></i>Add New Record</a>
                @endif
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-4 col-md-2">
                <label>From Date</label>
                <input type="date" id="from_date" class="form-control">
            </div>

            <div class="col-4 col-md-2">
                <label>To Date</label>
                <input type="date" id="to_date" class="form-control">
            </div>

            <div class="col-12 col-md-3 d-flex justify-content-center justify-content-md-start align-items-end gap-2 mt-2 mt-md-0">
                <button id="filterBtn" class="btn btn-primary me-2">Filter</button>
                <button id="resetBtn" class="btn btn-secondary">Reset</button>
            </div>

        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Customer Name</th>
                                    <th>Scheme</th>
                                    <th> Amount</th>
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
document.addEventListener("DOMContentLoaded", function() {

    let today = new Date();
    let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    function formatDate(date) {
        let m = '' + (date.getMonth() + 1);
        let d = '' + date.getDate();
        let y = date.getFullYear();

        if (m.length < 2) m = '0' + m;
        if (d.length < 2) d = '0' + d;

        return [y, m, d].join('-');
    }

    $('#from_date').val(formatDate(firstDay));
    $('#to_date').val(formatDate(today));

    let table = $('#datatables-buttons').DataTable({
        processing: true,
        serverSide: true,
        responsive   : false,
            scrollX: true,
        orderCellsTop: true,
        fixedHeader: true,

        ajax: {
            url: "{{ route('customer_refund') }}",
            data: function(d){
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },

        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'date', name:'Date' },
            { data: 'customer_name', name:'customers.CutomerName' },
            { data: 'schemes_name', name:'schemes.Name' },
            { data: 'amt_pay', name:'amt_pay' },
            { data: 'actions', orderable:false, searchable:false }
        ],

        drawCallback: function(){
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

    // column search
    $('#datatables-buttons thead tr:eq(1) th').each(function (i) {
        $('input', this).on('keyup change', function () {
            table.column(i).search(this.value).draw();
        });
    });

});
</script>



