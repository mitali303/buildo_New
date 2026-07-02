@extends('backend.partials.master')
@section('title')
  Stamp / Other Expenses
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3><strong></strong>Stamp / Other Expenses</h3>
            </div>
            <div class="col-auto ms-auto text-end mt-n1">
                
                @if (hasPermission('create_stamp_other_expenses') == true)
                    <a href="{{route('stamp_other_expenses.create')}}" class="btn btn-primary"><i class="fas fa-plus"></i>Add New Record</a>
                @endif
            </div>
        </div>

        <div class="row mb-3">

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
                                    <th>Paid To</th>
                                    <th> Amount</th>
                                    <th> Type</th>
                                    <th>Payment Method</th>
                                    <th>Scheme</th>
                                    <th>Narration</th>
                                    <th>Action</th>
                                </tr>
                                <!-- <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><input type="text" placeholder="Search Scheme" class="form-control"/></th>
                                    <th></th>
                                    <th></th>
                                   
                                </tr> -->
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
    responsive: true,
    orderCellsTop: true,
    fixedHeader: true,

    ajax: {
        url: "{{ route('stamp_other_expenses') }}",
        data: function(d){
            d.from_date = $('#from_date').val();
            d.to_date   = $('#to_date').val();
        }
    },

    columns: [
        { data: 'DT_RowIndex', orderable:false, searchable:false },
        { data: 'date', name:'Date' },
        { data: 'title', name:'title' },
        { data: 'amt_pay', name:'amt_pay' },
        { data: 'Exp_type', name:'Exp_type' },
        { data: 'payment_method', name:'payment_method' },
        { data: 'schemes_name', name:'schemes.Name' },
        { data: 'narration', name:'narration' },
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

$('#datatables-buttons thead tr:eq(1) th').each(function (i) {
    $('input', this).on('keyup change', function () {
        table.column(i).search(this.value).draw();
    });
});

});

</script>



