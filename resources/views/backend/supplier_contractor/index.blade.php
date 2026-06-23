@extends('backend.partials.master')
@section('title')
    Contractor/Supplier
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_supplier_contractor'))
        <a href="{{route('SupplierContractor.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Suppler/Contractor</a>
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
        responsive: true,
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

        drawCallback: function () {
            // optional if no icons
            // feather.replace();
        }
    });

});
</script>
@endpush