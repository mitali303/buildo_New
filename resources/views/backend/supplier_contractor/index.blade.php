@extends('backend.partials.master')
@section('title')
    Contractor/Supplier
@endsection
<style>
    /* Mobile / Small screen */
    @media (max-width: 768px) {

        .dataTables_wrapper .dataTables_length {
            width: 100% !important;
            display: block !important;
            float: none !important;
            text-align: left !important;

            /* Show entries खाली space */
            margin-bottom: 18px !important;
        }

        .dataTables_wrapper .dataTables_filter {
            width: 100% !important;
            display: block !important;
            float: none !important;
            text-align: left !important;

            /* Search च्या खाली space */
            margin-top: 0 !important;
            margin-bottom: 15px !important;
        }

        /* Show entries select */
        .dataTables_wrapper .dataTables_length select {
            margin-left: 5px !important;
            margin-right: 5px !important;
        }

        /* Search input */
        .dataTables_wrapper .dataTables_filter input {
            width: 200px !important;
            max-width: calc(100% - 70px) !important;
            margin-left: 5px !important;
        }
    }
      @media (max-width: 768px) {
    .new-supplier-btn {
        font-size: 10px !important;
        padding: 4px 7px !important;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_supplier_contractor'))
        <a href="{{route('SupplierContractor.create')}}" class="btn btn-primary float-end mt-n1 new-supplier-btn"><i class="fas fa-plus"></i> New Suppler/Contractor</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Contractor/Supplier</h1>
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>SR NO.</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Email</th>
                                    <th>ContactPerson</th>
                                    <th>ContactNumber</th>
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
@push('scripts')
<script>
$(document).ready(function () {

    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive:false,
            scrollX:true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('SupplierContractor') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Name', name: 'Name' },
                { data: 'Address', name: 'Address' },
                { data: 'Email', name: 'Email' },
                { data: 'ContactPerson', name: 'ContactPerson' },
                { data: 'ContactNumber', name: 'ContactNumber' },
              {
                data: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center',
                title: 'Action'
            }
        ],
                    dom: '<"row mb-3"<"col-md-6"l><"col-md-6"f>>' +
                            'rt' +
                            '<"row mt-3"<"col-md-6"i><"col-md-6"p>>',
        drawCallback: function () {
            // optional if no icons
            // feather.replace();
        }
    });

});
</script>
@endpush