@extends('backend.partials.master')
@section('title')
  Bank Reconciliation
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3><strong></strong>Bank Reconciliation</h3>
            </div>
            <!-- <div class="col-auto ms-auto text-end mt-n1">
                
                @if (hasPermission('create_account_transfer') == true)
                    <a href="{{route('account_transfer.create')}}" class="btn btn-primary"><i class="fas fa-plus"></i>Add New</a>
                @endif
            </div> -->
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
                                    <th>Cheque No</th>
                                    <th>Bank Name</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th><input type="text" placeholder="Search Customer Name" class="form-control"/></th>
                                    <th></th>
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
@section('scripts')
<script>
$(function () {
    let table = $('#datatables-buttons').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('bank_reconciliation') }}",
      columns: [
    { data: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'Date', name: 'Date' },
    { data: 'customer_name', orderable: false, searchable: true},
    { data: 'cheque_no', name: 'cheque_no' },
    { data: 'bank_name',  orderable: false, searchable: true },
    { data: 'type', name: 'type' },
    { data: 'amount', name: 'amount' },
    { data: 'actions', orderable: false, searchable: false }
]
    });

    $(document).on('click', '.clear-cheque', function () {
        if (!confirm('Clear this cheque?')) return;

        $.post("{{ route('bank_reconciliation.clear') }}", {
            _token: "{{ csrf_token() }}",
            id: $(this).data('id'),
            table: $(this).data('table')
        }, function () {
            table.ajax.reload();
        });
    });

    $(document).on('click', '.bounce-cheque', function () {
        let reason = prompt("Bounce reason:");
        if (!reason) return;

        $.post("{{ route('bank_reconciliation.bounce') }}", {
            _token: "{{ csrf_token() }}",
            id: $(this).data('id'),
            table: $(this).data('table'),
            reason: reason
        }, function () {
            table.ajax.reload();
        });
    });
});

</script>
@endsection

