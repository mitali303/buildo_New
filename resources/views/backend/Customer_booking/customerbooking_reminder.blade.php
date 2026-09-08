@extends('backend.partials.master')

@section('title', 'Reminder Letter')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="row mb-3">
            <div class="col-12">
                <h4>Select Date Range</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ route('customer_booking.reminder.letter') }}" method="POST">
                            @csrf

                            <input type="hidden" name="id" value="{{ $booking->ID }}">

                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1">From :</div>
                                <div class="col-md-2">
                                    <input type="text"
                                        name="fdate"
                                        id="fdate"
                                        class="form-control date"
                                        readonly
                                        value="{{ now()->startOfMonth()->format('d-m-Y') }}">
                                </div>

                                <div class="col-md-1">To :</div>
                                <div class="col-md-2">
                                    <input type="text"
                                        name="tdate"
                                        id="tdate"
                                        class="form-control date"
                                        readonly
                                        value="{{ now()->format('d-m-Y') }}">
                                </div>

                                <div class="col-md-2">Work Completion % :</div>
                                <div class="col-md-2">
                                    <input type="text"
                                        name="workcompletion"
                                        class="form-control"
                                        placeholder="Enter %">
                                </div>

                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success" style="width:104%;">
                                        Reminder Letter Date
                                    </button>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

@stack('scripts')

@push('scripts')
<script>
    $(document).ready(function() {
        $('.date').datepicker({
            dateFormat: 'dd-mm-yy'
        });
    });
</script>
@endpush