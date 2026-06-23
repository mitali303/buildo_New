@extends('backend.partials.master')
@section('title')
    Site Work Order
@endsection
<style>
.nowrap { white-space: nowrap; }
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_site_work_order'))
        <a href="{{route('Site_work_order.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Site Work Order</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Site Work Order</h1> 
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
                                    <th>Contractor</th>
                                    <th>Scheme</th>
                                    <th>Total</th>
                                    <th>TDS</th>
                                    <th>Retaintion Amt</th>
                                    <th>Payable Total</th>
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
        var datatablesButtons = $("#datatables-buttons").DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('Site_work_order') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Date', name: 'Date', className: 'nowrap' },
                { data: 'ContractorID', name: 'ContractorID', },
                { data: 'SiteLocation', name: 'SiteLocation', },
                { data: 'Total', name: 'Total' },
                { data: 'TDSAmt', name: 'TDS' },
                { data: 'retain_amt', name: 'retain_amt' },
                { data: 'gtotal', name: 'gtotal' },
                { data: 'actions', name: 'actions' }
            ],
            lengthChange: true,
            buttons: ['copy', 'print'],
            drawCallback: function () {
                feather.replace(); // draw feather icons after each render
            }
        });
        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>
