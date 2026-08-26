@extends('backend.partials.master')
@section('title')
    Labour Work
@endsection
<style>
.nowrap { white-space: nowrap; }

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
      #datatables-buttons_wrapper td:nth-child(2),
    #datatables-buttons_wrapper th:nth-child(2) {
        white-space: nowrap;
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_labour_work_payment'))
        <!-- <a href="{{route('Labour_work_pay.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Labour Work</a> -->
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Labour Work</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                @php
                    $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
                    $today = \Carbon\Carbon::now()->format('Y-m-d');
                @endphp
                <div class="row mb-3">

                    <!-- <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date" id="from_date" value="{{ $startOfMonth }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date" id="to_date" value="{{ $today }}" class="form-control">
                    </div> -->

                    @php
                    use App\Models\Backend\Agency;

                    $agencies = Agency::orderBy('Name')
                    ->get();
                    @endphp
                    <div class="col-md-3">
                        <label>Agency</label>
                        <select id="agency_filter" class="form-control">
                            <option value="">All Agencies</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->ID }}">{{ $agency->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                        <div class="col-md-3 d-flex align-items-end g-2">
                            <button id="filterBtn" class="btn btn-primary me-2">Filter</button>
                            <button id="resetBtn" class="btn btn-secondary">Reset</button>
                        </div>

                </div> 

                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr No</th>
                                    <th>Work Date</th>
                                    <th>Scheme</th>
                                    <th>Agency</th>
                                    <th>Grand Total</th>
                                    <th>Paid Amount</th>
                                    <th>Pending Amount</th>
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
<script>
document.addEventListener("DOMContentLoaded", function () {

    // initialise DataTable
    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive   : false,
        scrollX: true,
        processing   : true,
        serverSide   : true,

        ajax: {
            url: "{{ route('Labour_work_pay') }}",
            data: function(d){
                // d.from_date = $('#from_date').val();
                // d.to_date   = $('#to_date').val();
                d.agency    = $('#agency_filter').val();
            }
        },

        columns : [
            { data: 'DT_RowIndex', orderable:false, searchable:false },

            { data: 'latest_date', name: 'Date' },

            { data: 'Scheme', searchable:false },
            { data: 'Agency', searchable:false },

            { data: 'total_gtotal', name: 'gtotal' },

            { data: 'paid_amount', searchable:false, orderable:false },
            { data: 'pending_amount', searchable:false, orderable:false },

            { data: 'actions', orderable:false, searchable:false }
        ],

        drawCallback : function () {
            feather.replace();
        }
    });


    // ✅ FILTER BUTTON
    // $('#filterBtn').on('click', function(){
    //     datatablesButtons.ajax.reload();
    // });

    // // ✅ RESET BUTTON
    // $('#resetBtn').on('click', function(){
    //     $('#from_date').val('');
    //     $('#to_date').val('');
    //     $('#agency_filter').val('');
    //     datatablesButtons.ajax.reload();
    // });
// FILTER
    $('#filterBtn').on('click', function(){

        datatablesButtons.ajax.reload();

    });

    // RESET
    $('#resetBtn').on('click', function(){

        $('#agency_filter').val('');

        datatablesButtons.ajax.reload();

    });


});
</script>

