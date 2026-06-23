@extends('backend.partials.master')
@section('title')
    {{ $type === 'investor' ? 'Investor' : 'Partner' }} Payment
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_purchase_order'))
        <a href="{{route('Partner_pay.create', ['type' => $type])}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New {{ $type === 'investor' ? 'Investor' : 'Partner' }} Payment</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">{{ $type === 'investor' ? 'Investor' : 'Partner' }} Payment Table</h1> 
        </div>
<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('Partner_pay',['type' => $type]) }}" class="btn btn-secondary">
        Back
    </a>
    </div>
        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>{{ $type === 'investor' ? 'Investor' : 'Partner' }}</th>
                                    <th>Payment Method</th>
                                    <th>Credits</th>
                                    <th>Debits</th>
                                    <th>Narration</th>
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
        // Datatables with Buttons
        var datatablesButtons = $("#datatables-buttons").DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('Partner_pay.mainpay',['type' => $type, 'partner' => $partner]) }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Date', name: 'Date' },
                { data: 'partners', name: 'partners' },
                { data: 'payment_method', name: 'payment_method' },
                { data: 'credit', name: 'credit', searchable: false },
                { data: 'debit', name: 'debit', searchable: false },
                { data: 'narration', name: 'narration' },
                { data: 'actions', name: 'actions' }
            ],
            lengthChange: true,
            buttons: ['copy', 'print'],
            drawCallback: function () {
                feather.replace(); // draw feather icons after each render
            }
        });
        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>
