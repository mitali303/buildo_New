@extends('backend.partials.master')

@section('title', !empty($cancelbook) ? 'Edit Booking Cancel' : 'Create Booking Cancel')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">{{ !empty($cancelbook) ? 'Edit Booking Cancel' : 'Create Booking Cancel' }}</h1>

        <div class="row">
            <div class="col-md-12">

            <!-- @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Something went wrong!</strong>
                    <ul style="margin-top:5px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif -->
                <div class="card">
                    <div class="card-body">

                        <form action="{{ !empty($cancelbook) ? route('booking_cancel.update') : route('booking_cancel.store') }}"
                              method="POST">
                            @csrf
                            @if(!empty($cancelbook))
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $cancelbook->ID }}">
                            @endif

                            <div class="row gy-3">
                                <div class="col-md-4" id="scheme">
                                    <label class="form-label">Scheme <span class="text-danger">*</span></label>

                                    <select name="Destination" id="Destination"
                                            class="form-control choices-single-destination"
                                            data-placeholder="Select Destination"
                                            >
                                        <option value="">Select</option>
                                            @foreach($allschemes as $scheme)
                                                <option value="{{ $scheme->ID }}"
                                                    {{ old('Destination', $cancelbook->SchemID ?? $allschemes[0]->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                                    {{ $scheme->Name }}
                                                </option>
                                            @endforeach
                                    </select>
                                    @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Customers <span class="text-danger">*</span></label>

                                    <select name="CustomerID"
                                            id="CustomerID"
                                            class="form-control choices-single-customer"
                                            onchange="getbookingdetail(this.value); getBookingDate(this.value);">

                                        <option value="">Select Customer</option>

                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->CutomerName }}"
                                                {{ old('CustomerID', $bookingCustomer->CutomerName ?? '') == $customer->CutomerName ? 'selected' : '' }}>
                                                {{ $customer->CutomerName }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('CustomerID')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <div class="input-group flatpickr-container">
                                        <input type="text" name="Date" readonly
                                            id="datepicker"
                                            class="form-control @error('Date') is-invalid @enderror"
                                            placeholder="Select date"
                                            value="{{ old('Date', isset($cancelbook->Date) ? \Carbon\Carbon::parse($cancelbook->Date)->format('d-m-Y') : '') }}"
                                        >
                                    </div>
                                    @error('Date') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div id="mydivcust"></div>
                            </div>
                            <br>
                            <button type="submit" class="btn btn-primary">{{ !empty($cancelbook) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('booking_cancel') }}" class="btn btn-secondary">Cancel</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
@push('scripts')
<script>
let bookingDatePicker; // ✅ GLOBAL VARIABLE

document.addEventListener("DOMContentLoaded", function () {

    bookingDatePicker = flatpickr("#datepicker", {
        dateFormat: "d-m-Y",
        allowInput: false
    });

});
</script>

<script>
function getBookingDate(customer) {

    if (!customer) return;

    $.post("{{ route('ajax.booking.date') }}", {
        cust_name: customer,
        _token: "{{ csrf_token() }}"
    }, function (res) {

        $("#datepicker").val(res.minDate);
    });
}

function getbookingdetail(customer) {
    let scheme = $("#Destination").val();
    if (!scheme || !customer) return;

    $.post("{{ route('ajax.booking.detail') }}", {
        _token: "{{ csrf_token() }}",
        schmid: scheme,
        cust: customer,
        cancelid: "{{ $cancelbook->ID ?? '' }}"
    }, function (data) {

        $("#mydivcust").html(data);

        // 🔥 Pay_type & selectedAccountId now exist
        if ($('#Pay_type').length) {
            check_type();
        }
    });
}

</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    let scheme   = "{{ old('Destination', $cancelbook->SchemID ?? '') }}";
    let customer = "{{ old('CustomerID', $bookingCustomer->CutomerName ?? '') }}";

    if (scheme && customer) {

        // set scheme
        $('#Destination').val(scheme);

        // set customer
        $('#CustomerID').val(customer);

        // 🔥 trigger ajax manually
        getbookingdetail(customer);
        getBookingDate(customer);
    }
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-destination"));
        new Choices(document.querySelector(".choices-single-customer"));
    });
</script>
@endpush

