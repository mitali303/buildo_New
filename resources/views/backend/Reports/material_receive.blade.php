@extends('backend.partials.master')
@section('title')
    Material Received
@endsection
<style>
    #datatables-buttons th:nth-child(2),
#datatables-buttons td:nth-child(2) {
    white-space: nowrap !important;
}
    @media (max-width: 768px) {
    #datatables-buttons_wrapper .dataTables_length,
    #datatables-buttons_wrapper .dataTables_filter {
        width: 100%;
        float: none;
        text-align: left;
        margin-bottom: 10px;
    }

    #datatables-buttons_wrapper .dt-buttons .btn {
        padding: 4px 8px;
        font-size: 12px;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Material Received</h1> 
        </div>

        <div class="row mb-3">

            <div class="col-6 col-md-3">
                <label>From Date</label>
                <input type="date" id="from_date" class="form-control"
                    value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
            </div>

            <div class="col-6 col-md-3">
                <label>To Date</label>
                <input type="date" id="to_date" class="form-control"
                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>

            <div class="col-12 col-md-3 d-flex justify-content-center justify-content-md-start align-items-end gap-2 mt-2 mt-md-0">
                <button id="filter" class="btn btn-primary">Search</button>
                <button id="reset" class="btn btn-secondary">Reset</button>
            </div>

        </div>
        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Date</th>
                                    <th>Order No.</th>
                                    <th>From Site</th>
                                    <th>To Site</th>
                                    <th>Material</th>
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

    let table = $("#datatables-buttons").DataTable({
        responsive   : false,
            scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('report.material_recieve') }}",
            data: function (d) {
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'Date', name:'Date' },
            { data: 'Srno', name:'Srno' },
            { data: 'from_site', name:'from_site' },
            { data: 'To_site', name:'To_site' },
            { data: 'materials', orderable:false, searchable:false }
        ],
        buttons: ['copy','print'],
        drawCallback: function () {
            feather.replace();
        }
    });

    // Apply filter
    $('#filter').click(function () {
        table.draw();
    });

    // Reset filter
    $('#reset').click(function () {
        $('#from_date').val('');
        $('#to_date').val('');
        table.draw();
    });

});
</script>
