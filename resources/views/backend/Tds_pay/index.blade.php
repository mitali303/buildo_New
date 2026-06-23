@extends('backend.partials.master')
@section('title')
    TDS Payment
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_purchase_order'))
        <!-- <a href="{{route('Site_work_order.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New TDS Payment</a> -->
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">TDS Payment</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Contractor Name</th>
                                    <th>Calculated TDS</th>
                                    <th>TDS Amount</th>
                                    <th>Paid TDS</th>
                                    <th>Pending TDS</th>
                                    <th>Actions</th>
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
        // Datatables with Buttons
        var table = $("#datatables-buttons").DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('Tds_pay') }}",
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'contractor_name', name: 'contractor_name' },
        { data: 'calculated_tds', name: 'calculated_tds' },
        { data: 'total_tds', name: 'total_tds' },
        { data: 'paid_tds', name: 'paid_tds' },
        { data: 'pending_tds', name: 'pending_tds' },
        { data: 'actions', orderable: false, searchable: false }
    ],
    drawCallback: function () {
        feather.replace();
    }
});

        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>
