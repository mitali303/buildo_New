@extends('backend.partials.master')

@section('title')
    Land Expenses Report
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

        <div class="row mb-3">
            <div class="col-auto">
                <h3>Land Expenses Report</h3>
            </div>
        </div>

        {{-- Filter Form --}}
        <form id="filterForm" class="row g-2 mb-3">

            <div class="col-4 col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control"
                       value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>

            <div class="col-4 col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control"
                       value="{{ request('to_date', now()->format('Y-m-d')) }}">
            </div>

            <div class="col-4 col-md-2">
                <label class="form-label">Expense Type</label>
                <select name="typesrch" class="form-control">
                    <option value="">Select Type</option>
                    <option value="all">All</option>
                    <option value="Land purchase">Land purchase</option>
                    <option value="Land NA">Land NA</option>
                    <option value="Land stamp expence">Land stamp expence</option>
                </select>
            </div>

            <div class="col-12 col-md-3 d-flex justify-content-center justify-content-md-start align-items-end gap-2 mt-2 mt-md-0">
                <button type="submit" class="btn btn-primary">
                    Search
                </button>
            </div>

        </form>

        {{-- DataTable --}}
        <div class="card">
            <div class="card-body">
                <table id="datatables-buttons" class="table table-striped w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Paid To</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Payment Method</th>
                            <th>Scheme</th>
                            <th>Narration</th>
                           
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
$(document).ready(function () {

    let table = $('#datatables-buttons').DataTable({
        processing: true,
        serverSide: true,
        responsive   : false,
            scrollX: true,
        ajax: {
            url: "{{ route('reports.land_expense_report') }}",
            data: function (d) {
                d.from_date = $('input[name="from_date"]').val();
                d.to_date   = $('input[name="to_date"]').val();
                d.typesrch  = $('select[name="typesrch"]').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'date', name: 'Date' },
            { data: 'title', name: 'title' },
            { data: 'amt_pay', name: 'amt_pay' },
            { data: 'Exp_type', name: 'Exp_type' },
            { data: 'payment_method', name: 'payment_method' },
            { data: 'schemes_name', name: 'schemes.Name' },
            { data: 'narration', name: 'narration' },
           
        ],
        drawCallback: function () {
            feather.replace();
        }
    });

    // Reload table on filter submit
    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        table.draw();
    });

});
</script>
@endpush
