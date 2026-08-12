@extends('backend.partials.master')

@section('title')
    Quotations
@endsection

@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">
        <a href="{{ route('Quotation.create') }}"class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Quotation</a>
        <!-- @if(hasPermission('create_Quotation'))
            <a href="{{ route('Quotation.create') }}"
               class="btn btn-primary float-end mt-n1">
                <i class="fas fa-plus"></i> New Quotation
            </a>
        @endif -->

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">
                Quotations
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
                                    <th>#</th>
                                    <th>Quotation No.</th>
                                    <th>Firm</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                                <tr>
                                    <th></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Search No."></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Search Firm"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Search Customer"></th>
                                    <th></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Search Date"></th>
                                    <th></th>
                                    <th>
                                        <select class="form-select form-select-sm">
                                            <option value="">All</option>
                                            <option value="1">Draft</option>
                                            <option value="2">Sent</option>
                                            <option value="3">Approved</option>
                                            <option value="0">Cancelled</option>
                                        </select>
                                    </th>
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

    var table = $("#datatables-buttons").DataTable({

        responsive: true,
        processing: true,
        serverSide: true,
        orderCellsTop: true,
        fixedHeader: true,

        ajax: "{{ route('Quotation') }}",

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'quotation_no', name: 'quotation_no' },
            { data: 'firm_name', name: 'firm_name', orderable: false },
            { data: 'customer_name', name: 'customer_name', orderable: false },
            { data: 'type', name: 'type' },
            { data: 'date', name: 'date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],

        dom:
            "<'row'<'col-md-6'l><'col-md-6 text-end'B>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-md-5'i><'col-md-7'p>>",

        buttons: ['copy', 'excel', 'pdf', 'print', {  text: 'Columns' }],

        lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],

        drawCallback: function () {
            feather.replace();
        }

    });

    $('#datatables-buttons thead tr:eq(1) th').each(function (i) {
        $('input, select', this).on('keyup change', function () {
            if (table.column(i).search() !== this.value) {
                table.column(i).search(this.value).draw();
            }
        });
    });

});

</script>