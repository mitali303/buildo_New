@extends('backend.partials.master')

@section('title')
    Material Request
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">Material Request</h1>
            <a href="{{ route('material_request.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Material Request
            </a>
        </div>
        <div class="card">
            <div class="card-body">
                <table id="material-request-table" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <!-- <th>ID</th> -->
                            <th>ClientID</th>
                            <th>Date</th>
                            <th>Request No</th>
                            <th>Description</th>
                            <th>Remark</th>
                            <!-- <th>User ID</th> -->
                            <!-- <th>Created</th> -->
                            <!-- <th>Last Edited</th> -->
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = $('#material-request-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '{{ route('material_request.list') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                // { data: 'ID', name: 'ID' },
                { data: 'ClientID', name: 'ClientID' },
                { data: 'Date', name: 'Date' },
                { data: 'request_no', name: 'request_no' },
                { data: 'description', name: 'description' },
                { data: 'remark', name: 'remark' },
                // { data: 'userID', name: 'userID' },
                // { data: 'Created', name: 'Created' },
                // { data: 'LastEdited', name: 'LastEdited' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            drawCallback: function () {
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        });

        $(document).on('click', '.delete-confirm', function (e) {
            e.preventDefault();
            const formId = $(this).data('id');
            if (confirm('Are you sure you want to delete this record?')) {
                $('#' + formId).submit();
            }
        });
    });
</script>
@endpush
