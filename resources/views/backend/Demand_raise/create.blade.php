@extends('backend.partials.master')

@section('title', !empty($demandraise) ? 'Edit demand raise' : 'Create demand raise')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">{{ !empty($demandraise) ? 'Edit demand raise' : 'Create demand raise' }}</h1>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ !empty($demandraise) ? route('demand_raise.update') : route('demand_raise.store') }}"
                              method="POST">
                            @csrf
                            @if(!empty($demandraise))
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $demandraise->ID }}">
                            @endif

                            <div class="row gy-3">

                               <div class="mb-3 col-md-4">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <div class="input-group flatpickr-container">
                                        <input type="text" name="Date" readonly
                                            id="datepicker"
                                            class="form-control @error('Date') is-invalid @enderror"
                                            placeholder="Select date"
                                           value="{{ old('Date', isset($demandraise->Date)  ? \Carbon\Carbon::parse($demandraise->Date)->format('d-m-Y')  : now()->format('d-m-Y')) }}"
                                        >
                                    </div>
                                    @error('Date') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4" id="scheme">
                                    <label class="form-label">Scheme <span class="text-danger">*</span></label>

                                    <select name="Destination" id="Destination"
                                            class="form-control choices-single-destination"
                                            data-placeholder="Select Destination"
                                            >
                                        <option value="">Select</option>
                                            @foreach($allschemes as $scheme)
                                                <option value="{{ $scheme->ID }}"
                                                    {{ old('Destination', $demandraise->scheme ?? $allschemes[0]->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                                    {{ $scheme->Name }}
                                                </option>
                                            @endforeach
                                    </select>
                                    @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-4" id="wing">
                                    <label class="form-label">Wing <span class="text-danger">*</span></label>

                                    <select name="Wing" id="Wing"
                                            class="form-control choices-single-Wing"
                                            data-placeholder="Select Wing"
                                            >
                                        <option value="">Select</option>
                                            @foreach($wings as $wing)
                                                <option value="{{ $wing }}"
                                                    {{ old('Wing', $demandraise->Wing ?? '') == $wing ? 'selected' : '' }}>
                                                    {{ $wing }}
                                                </option>
                                            @endforeach
                                    </select>
                                    @error('Wing') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Flat No. <span class="text-danger">*</span></label>

                                    <select name="FlatNo" id="FlatNo"
                                            class="form-control choices-single-FlatNo">
                                        <option value="">Select</option>
                                    </select>

                                    @error('FlatNo') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <input type="hidden" name="FlatID" id="FlatID">
                                <div class="col-md-4">
                                    <label class="form-label">Customer Name<span class="text-danger">*</span></label>
                                    <input type="text" id="CustomerName" name="CustomerName" class="form-control" value="{{ old('CustomerName', $demandraise->customer ?? '') }}" readonly>
                                     @error('CustomerName') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Flat Category<span class="text-danger">*</span></label>
                                    <input type="text" id="FlatType" name="FlatType" class="form-control" value="{{ old('FlatType', $demandraise->flatno->FlatType ?? '') }}" readonly>
                                     @error('FlatType') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <div class="input-group flatpickr-container">
                                        <input type="text" name="Due_date" readonly
                                            id="datepicker1"
                                            class="form-control @error('Due_date') is-invalid @enderror"
                                            placeholder="Select date"
                                            value="{{ old('Due_date', isset($demandraise->Due_date) ? \Carbon\Carbon::parse($demandraise->Due_date)->format('d-m-Y') : '') }}"
                                        >
                                    </div>
                                    @error('Due_date') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Demand Amount<span class="text-danger">*</span></label>
                                    <input type="text" id="dmdamt" name="Demand_amt"
                                        value="{{ old('Demand_amt', $demandraise->Demand_amt ?? '') }}"
                                        class="form-control">  
                                        @error('Demand_amt') <small class="text-danger">{{ $message }}</small> @enderror                              
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Rate Of Interest<span class="text-danger">*</span></label>
                                    <input type="text" id="interest" name="InterestRate"
                                        value="{{ old('InterestRate', $demandraise->InterestRate ?? '') }}"
                                        class="form-control">
                                         @error('InterestRate') <small class="text-danger">{{ $message }}</small> @enderror 
                                </div>

                            </div>
                            <br>
                            <button type="submit" class="btn btn-primary">{{ !empty($demandraise) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('demand_raise') }}" class="btn btn-secondary">Cancel</a>
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
let flatChoices;

document.addEventListener("DOMContentLoaded", function () { 

    // You can keep Choices for other dropdowns
    new Choices('.choices-single-destination', { shouldSort: false });
    new Choices('.choices-single-Wing', { shouldSort: false });

    // THIS is the important one
    flatChoices = new Choices('.choices-single-FlatNo', {
        shouldSort: false,
        placeholderValue: 'Select Flat No'
    });
});
</script>

<script>
    
$(document).ready(function () {

    // DATE
    flatpickr("#datepicker", {
        dateFormat: "d-m-Y",
        allowInput: false
    });

    // DUE DATE
    flatpickr("#datepicker1", {
        dateFormat: "d-m-Y",
        allowInput: false
    });

});
</script>

<script>
$(document).ready(function () {

    /* ==========================
       Wing → Load Flats
    ========================== */
    $('#Wing').on('change', function () {

        let wing = $(this).val();

        // reset flat dropdown + fields
        flatChoices.clearChoices();
        // $('#FlatID').val('');
        // $('#CustomerName').val('');
        // $('#FlatType').val('');

        if (!wing) {
            flatChoices.setChoices([
                { value: '', label: 'Select Flat No', disabled: true }
            ]);
            return;
        }

        $.ajax({
            url: "{{ route('get.flatnos.by.wing') }}",
            type: "GET",
            dataType: "json",
            data: { wing: wing },

            success: function (response) {

                if (!response || response.length === 0) {
                    flatChoices.setChoices([
                        { value: '', label: 'No Flats Found', disabled: true }
                    ]);
                    return;
                }

                // ✅ CORRECT WAY WITH CHOICES
                flatChoices.setChoices(
                    response.map(flat => ({
                        value: flat.ID,
                        label: flat.FlatNo
                    })),
                    'value',
                    'label',
                    true
                );
            }
        });
    });

    /* ==========================
       Flat → Customer + Category
    ========================== */
            $('#FlatNo').on('change', function () {

            let flatId = $(this).val();
            $('#FlatID').val(flatId);
            if (!flatId) return;

            $.ajax({
                url: "{{ route('get.flat.details') }}",
                type: "GET",
                data: { flat_id: flatId },

                success: function (res) {

                    console.log(res); // 🔥 check this

                    $('#CustomerName').val(res.customer_name || '');
                    $('#FlatType').val(res.flat_type || '');
                }
            });
        });
});


</script>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

    let editWing   = "{{ old('Wing', $demandraise->Wing ?? '') }}";
    let editFlatId = "{{ old('FlatID', $demandraise->FlatNo ?? '') }}";

    if (editWing) {

        $('#Wing').val(editWing).trigger('change');

        // 🔥 IMPORTANT: wait for flats to load
        let waitFlat = setInterval(function () {

            if ($('#FlatNo option').length > 1) {

                clearInterval(waitFlat);

                // set value in Choices
                flatChoices.setChoiceByValue(editFlatId);

                // 🔥 FORCE CHANGE EVENT
                $('#FlatNo').val(editFlatId).trigger('change');

                $('#CustomerName').val("{{ $demandraise->customer ?? '' }}");
                $('#FlatType').val("{{ $demandraise->flatno->FlatType ?? '' }}");
            }

        }, 300);
    }

});
</script>

@endpush
