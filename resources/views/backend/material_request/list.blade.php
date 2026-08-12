@extends('backend.partials.master')

@section('title')
    Material Request List
@endsection

@section('maincontent')

<main class="content">

    <div class="container-fluid p-0">

        <div class="mb-3 d-flex justify-content-between align-items-center">

            <h1 class="h3 d-inline align-middle">
                Material Request List
            </h1>

        </div>

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        <table id="datatables-buttons"
                               class="table table-striped"
                               style="width:100%">

                            <thead>

                                <tr>

                                    <th>SR NO.</th>

                                    <th>Date</th>

                                    <th>Request No</th>

                                    <th>Description</th>

                                    <th>Remark</th>

                                    <th>Items</th>

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


@section('scripts')

<script>

document.addEventListener("DOMContentLoaded", function () {

    var datatablesButtons = $("#datatables-buttons").DataTable({

        responsive: true,

        processing: true,

        serverSide: true,

        ajax: "{{ route('material-request.index') }}",

        columns: [

            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },

            {
                data: 'date',
                name: 'date'
            },

            {
                data: 'request_no',
                name: 'request_no'
            },

            {
                data: 'description',
                name: 'description'
            },

            {
                data: 'remark',
                name: 'remark'
            },

            {
                data: 'items_count',
                name: 'items_count',
                orderable: false,
                searchable: false
            },

            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }

        ],

        lengthChange: true,

        buttons: [
            'copy',
            'print'
        ],

        drawCallback: function () {
            feather.replace();
        }

    });

    datatablesButtons.buttons()
        .container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");

});

</script>

@endsection