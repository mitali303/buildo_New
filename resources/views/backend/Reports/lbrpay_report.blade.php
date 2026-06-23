@extends('backend.partials.master')
@section('title')
    Labour Work
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
       

        <div class="row">
            <div class="col-12"> 
            
            <div class="mb-3">
                <h1 class="h3 d-inline align-middle">Labour Work</h1> 
            </div>
                <div class="row mb-3">

                    <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date" id="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date" id="to_date" class="form-control" value="{{ request('to_date', now()->format('Y-m-d')) }}">
                    </div>

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

                    <div class="col-md-3 d-flex align-items-end">
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
                                    <th>Total Paid</th>
                                    <th>Pending</th>
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
        responsive   : true,
        processing   : true,
        serverSide   : true,

        ajax: {
            url: "{{ route('reports.lbrpay_report') }}",
            data: function(d){
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
                d.agency    = $('#agency_filter').val();
            }
        },

        columns : [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'latest_date' },
            { data: 'Scheme' },
            { data: 'Agency' },
            { 
                data: 'total_gtotal',
                render: data => '₹ ' + parseFloat(data || 0).toFixed(2)
            },
            { 
                data: 'total_paid',
                render: data => '₹ ' + parseFloat(data || 0).toFixed(2)
            },
            { 
                data: 'pending',
                render: data => '₹ ' + parseFloat(data || 0).toFixed(2)
            }
        ],

        drawCallback : function () {
            feather.replace();
        }
    });


    // ✅ FILTER BUTTON
    $('#filterBtn').on('click', function(){
        datatablesButtons.ajax.reload();
    });

    // ✅ RESET BUTTON
    $('#resetBtn').on('click', function(){
        $('#from_date').val('');
        $('#to_date').val('');
        $('#agency_filter').val('');
        datatablesButtons.ajax.reload();
    });

});
</script>

