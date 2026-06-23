@extends('backend.partials.master')
@section('title')
  Account Transfer
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3><strong></strong>Account Transfer</h3>
            </div>
            <div class="col-auto ms-auto text-end mt-n1">
                
                @if (hasPermission('create_account_transfer') == true)
                    <a href="{{route('account_transfer.create')}}" class="btn btn-primary"><i class="fas fa-plus"></i>Add New</a>
                @endif
            </div>
        </div>
        <form method="GET" action="" class="row mb-3 align-items-end">

    <div class="col-md-3">
        <label class="form-label">From</label>
        <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">To</label>
        <input type="date" name="to_date" value="{{ $toDate }}" class="form-control">
    </div>

    <div class="col-md-2">
        <button class="btn btn-primary">Search</button>
    </div>

</form>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Amount from</th>
                                    <th>Payment Method</th>
                                    <th>Balance</th>
                                    <th>Account To</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th><input type="text" placeholder="Search Account From" class="form-control"/></th>
                                    <th><input type="text" placeholder="Search payment method" class="form-control"/></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
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

    let table = $('#datatables-buttons').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        orderCellsTop: true,
        fixedHeader: true,
        ajax: {
            url: "{{ route('account_transfer') }}",
            data: function (d) {
                d.from_date = $('input[name="from_date"]').val();
                d.to_date   = $('input[name="to_date"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'date', name: 'Date' },
            { data: 'account_from_name', name: 'accountFrom.Name' },
            { data: 'payment_method', name: 'payment_method' },
            { data: 'balance', name: 'balance' },
            { data: 'account_to_name', name: 'accountTo.Name' },
            { data: 'amt_pay', name: 'amt_pay' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        drawCallback: function () {
            feather.replace();
        }
    });

    // Reload table when Search button is clicked
    $('form').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Column search
    $('#datatables-buttons thead tr:eq(1) th').each(function (i) {
        $('input', this).on('keyup change', function () {
            table.column(i).search(this.value).draw();
        });
    });

});
</script>




