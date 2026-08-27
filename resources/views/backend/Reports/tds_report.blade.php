@extends('backend.partials.master')

@section('title','TDS Report')
<style>
@media (max-width: 768px) {
    #vendor {
        font-size: 13px;
        padding-left: 6px;
        padding-right: 4px;
    }
}
@media (max-width: 768px) {
    .dataTables_length {
        text-align: left !important;
        float: left !important;
        width: auto !important;
        margin-bottom: 10px;
    }

    .dataTables_length label {
        text-align: left !important;
    }
}
</style>
@section('maincontent')
    <main class="content">
            <div class="container-fluid p-0">

                <h1 class="h3 mb-3">TDS Report</h1>

                    {{-- Filters --}}
                    <div class="row mb-3">
                        <div class="col-4 col-md-3">
                            <input type="date" id="fdate" class="form-control"
                                value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-4 col-md-3">
                            <input type="date" id="tdate" class="form-control"
                                value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-4 col-md-3 ">
                            <select id="vendor" class="form-control">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->ID }}">{{ $v->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mt-2">
                            <button class="btn btn-primary" id="filter">Show</button>
                        </div>
                    </div>

                    {{-- SUMMARY TABLE --}}
                    <table class="table table-bordered mb-4">
                        <thead>
                            <tr>
                                <th>Calculated TDS</th>
                                <th>TDS Amount</th>
                                <th>Paid TDS</th>
                                <th>Pending TDS</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td id="calculated"></td>
                            <td id="tds_amount"></td>
                            <td id="paid"></td>
                            <td id="pending"></td>
                        </tr>
                        </tbody>
                    </table>

                {{-- DETAIL TABLE --}}
                <div class="table-responsive w-100">
                    <table id="tds-table" class="table table-striped w-100">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Particulars</th>
                            <th>Payment Details</th>
                            <th>Total TDS</th>
                        </tr>
                        </thead>
                    </table>
                </div>

        <div class="text-end mt-2">
            <b>Total TDS Amount: <span id="grandTotal"></span></b>
        </div>

    </div>
</main>
@endsection

@push('scripts')
<script>
$(function(){

let table = $('#tds-table').DataTable({
    serverSide: false,
    processing: true,
    searching: false,
    responsive: false,
    scrollX: true,
    lengthChange: true,
    dom: 'ltip',

    ajax: {
        url: "{{ route('report.tds') }}",
        data: function (d) {
            d.fdate  = $('#fdate').val();
            d.tdate  = $('#tdate').val();
            d.vendor = $('#vendor').val();
        },
        dataSrc: function (json) {
            $('#calculated').html(json.summary.calculated_tds);
            $('#tds_amount').html(json.summary.tds_amount);
            $('#paid').html(json.summary.paid_tds);
            $('#pending').html(json.summary.pending_tds);
            $('#grandTotal').html(json.grandTotal);
            return json.data;
        }
    },

    columns: [
        { data: null, render: (d, t, r, m) => m.row + 1 },
        { data: 'particulars' },
        { data: 'payment_details' },
        { data: 'tds' }
    ]
});

$('#filter').click(function () {
    if (!$('#vendor').val()) {
        alert('Please select vendor');
        return;
    }
    table.ajax.reload();
});

$('#filter').click(()=>table.ajax.reload());

});
</script>
@endpush
