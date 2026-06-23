@extends('backend.partials.master')
@section('title')
    {{ $type === 'investor' ? 'Investor' : 'Partner' }} Payment
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_investors_payment'))
        <a href="{{route('Partner_pay.create', ['type' => $type])}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New {{ $type === 'investor' ? 'Investor' : 'Partner' }} Payment</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">{{ $type === 'investor' ? 'Investor' : 'Partner' }} Payment</h1> 
        </div>
        {{-- <div class="row mb-3">

            <div class="col-md-3">
                <label>From Date</label>
                <input type="date" id="from_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label>To Date</label>
                <input type="date" id="to_date" class="form-control">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button id="filterBtn" class="btn btn-primary me-2">Filter</button>
                <button id="resetBtn" class="btn btn-secondary">Reset</button>
            </div>

        </div> --}}
        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                          <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ $type === 'investor' ? 'Investor' : 'Partner' }}</th>
                                <th>Credit</th>
                                <th>Debit</th>
                                <th>Balance</th>
                                <th>Last Payment Date</th>
                                <th>Actions</th>
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

//     // ===============================
//     // DEFAULT DATES (Start of Month → Today)
//     // ===============================
    let today = new Date();
    let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    function formatDate(date) {
        let month = '' + (date.getMonth() + 1);
        let day = '' + date.getDate();
        let year = date.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    $('#from_date').val(formatDate(firstDay));
    $('#to_date').val(formatDate(today));

    // ===============================
    // DATATABLE
    // ===============================
    var datatablesButtons = $("#datatables-buttons").DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('Partner_pay',['type' => $type]) }}",
            data: function(d) {
                d.from_date = $('#from_date').val();
                d.to_date   = $('#to_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'partner' },
            { data: 'credit' },
            { data: 'debit' },
            { data: 'balance', searchable: false },
            { data: 'last_payment_date' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        lengthChange: true,
        buttons: ['copy', 'print'],
        drawCallback: function () {
            feather.replace();
        }
    });

    datatablesButtons.buttons()
        .container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");

    // FILTER
    // $('#filterBtn').on('click', function(){
    //     datatablesButtons.ajax.reload();
    // });

    // RESET (Back to Default)
    // $('#resetBtn').on('click', function(){
    //     $('#from_date').val(formatDate(firstDay));
    //     $('#to_date').val(formatDate(today));
    //     datatablesButtons.ajax.reload();
    // });

});
</script>
