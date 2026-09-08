@extends('backend.partials.master')

@section('title', !empty($addbill) ? 'Edit Add bill' : 'Create Add bill')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">{{ !empty($addbill) ? 'Edit Add bill' : 'Create Add bill' }}</h1>

        <div class="row">
            <div class="col-md-12">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Something went wrong!</strong>
                    <ul style="margin-top:5px;">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="card">
                    <div class="card-body">

                        <form action="{{ !empty($addbill) ? route('Add_bill.update') : route('Add_bill.store') }}"
                            method="POST">
                            @csrf
                            @if(!empty($addbill))
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $addbill->ID }}">
                            @endif

                            <div class="row gy-3">

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>

                                    <div class="input-group flatpickr-container">
                                        <input type="text"
                                            name="Date"
                                            id="datepicker"
                                            class="form-control @error('Date') is-invalid @enderror"
                                            placeholder="Select date"
                                            value="{{ old('Date', !empty($addbill) ? \Carbon\Carbon::parse($addbill->Date)->format('d-m-Y') : \Carbon\Carbon::now()->format('d-m-Y')) }}">
                                    </div>

                                    @error('Date')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <input type="hidden" id="tp" name="tp" value="Addition">

                                <div class="col-md-4" id="scheme">
                                    <label class="form-label">Scheme <span class="text-danger">*</span></label>

                                    <select name="Destination" id="Destination"
                                        class="form-control choices-single-destination"
                                        data-placeholder="Select Destination">
                                        <option value="">Select</option>
                                        @foreach($allschemes as $scheme)
                                        <option value="{{ $scheme->ID }}"
                                            {{ old('Destination', $addbill->scheme ?? $allschemes[0]->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
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
                                        data-placeholder="Select Wing">
                                        <option value="">Select</option>
                                        @foreach($wings as $wing)
                                        <option value="{{ $wing }}"
                                            {{ old('Wing', $addbill->Wing ?? '') == $wing ? 'selected' : '' }}>
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
                                    <input type="text" id="CustomerName" name="CustomerName" class="form-control" value="{{ old('CustomerName', $addbill->customer ?? '') }}" readonly>
                                    @error('CustomerName') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Flat Category<span class="text-danger">*</span></label>
                                    <input type="text" id="FlatType" name="FlatType" class="form-control" readonly>
                                    @error('FlatType') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Narration</label>
                                    <input type="text" id="billNo" name="billNo"
                                        value="{{ old('billNo', $billdet->billNo ?? '') }}"
                                        class="form-control">
                                    @error('billNo') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Amount<span class="text-danger">*</span></label>
                                    <input type="text" id="Amount" name="Amount"
                                        value="{{ old('Amount', $addbill->Amount ?? '') }}"
                                        class="form-control">
                                    @error('Amount') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Attachment</label>

                                    <input type="file" id="fileInput" name="images[]" multiple class="form-control" />

                                    @php
                                    $tempFiles = session('temp_bill_uploads', []);
                                    @endphp

                                    <ul id="fileList" class="mt-3 list-unstyled">

                                        {{-- 🔹 SESSION FILES (validation failed) --}}
                                        @foreach($tempFiles as $img)
                                        <li>
                                            <a href="{{ asset('Uploads/addbill/'.$img) }}" target="_blank">{{ $img }}</a>
                                            <button type="button"
                                                class="btn btn-sm ms-2 text-danger remove-temp"
                                                data-file="{{ $img }}">❌</button>

                                            <input type="hidden" name="uploadfile[]" value="{{ $img }}">
                                        </li>
                                        @endforeach

                                        {{-- 🔹 EXISTING FILES (edit mode) --}}
                                        @if(!empty($existingImages))
                                        @foreach($existingImages as $img)
                                        <li>
                                            <a href="{{ asset('Uploads/addbill/'.$img) }}" target="_blank">{{ $img }}</a>
                                            <button type="button"
                                                class="btn btn-sm ms-2 text-danger remove-existing"
                                                data-file="{{ $img }}">❌</button>

                                            <input type="hidden" name="uploadfile[]" value="{{ $img }}">
                                        </li>
                                        @endforeach
                                        @endif

                                    </ul>

                                </div>

                            </div>
                            <br>
                            <button type="submit" class="btn btn-primary">{{ !empty($addbill) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('Add_bill') }}" class="btn btn-secondary">Cancel</a>
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
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');

        input.addEventListener('change', function() {
            for (let file of input.files) {
                upload(file);
            }
            // Clear input to allow re-selecting same files
            input.value = "";
        });

        function upload(file) {
            const li = document.createElement('li');
            li.innerText = file.name + ' - Uploading...';
            fileList.appendChild(li);

            const formData = new FormData();
            formData.append("file", file);
            formData.append("_token", '{{ csrf_token() }}');

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "{{ route('upload.attachmentbill') }}", true);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const res = JSON.parse(xhr.responseText);
                    li.innerText = file.name;

                    const removeBtn = document.createElement('button');
                    removeBtn.innerText = '❌';
                    removeBtn.type = 'button';
                    removeBtn.classList.add('btn', 'btn-sm', 'ms-2');
                    removeBtn.onclick = function() {
                        fetch("{{ route('delete.imagebill') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: res.filename
                            })
                        }).then(() => li.remove());
                    };
                    li.appendChild(removeBtn);

                    // Add hidden input for later form submission
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'uploadfile[]';
                    hidden.value = res.filename;
                    input.closest('form').appendChild(hidden);
                    console.log("Appending to form:", input.closest('form'));
                    console.log("Hidden input added:", hidden);

                } else {
                    li.innerText = file.name + ' ❌ Upload failed';
                }
            };

            xhr.send(formData);
        }
    });
</script>
<script>
    let flatChoices;

    document.addEventListener("DOMContentLoaded", function() {

        // You can keep Choices for other dropdowns
        new Choices('.choices-single-destination', {
            shouldSort: false
        });
        new Choices('.choices-single-Wing', {
            shouldSort: false
        });

        // THIS is the important one
        flatChoices = new Choices('.choices-single-FlatNo', {
            shouldSort: false,
            placeholderValue: 'Select Flat No'
        });
    });
</script>

<script>
    $(document).ready(function() {

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
    $(document).ready(function() {

        /* ==========================
           Wing → Load Flats
        ========================== */
        $('#Wing').on('change', function() {

            let wing = $(this).val();

            // reset flat dropdown + fields
            flatChoices.clearChoices();
            $('#FlatID').val('');
            $('#CustomerName').val('');
            $('#FlatType').val('');

            if (!wing) {
                flatChoices.setChoices([{
                    value: '',
                    label: 'Select Flat No',
                    disabled: true
                }]);
                return;
            }

            $.ajax({
                url: "{{ route('get.flatnos.by.wing') }}",
                type: "GET",
                dataType: "json",
                data: {
                    wing: wing
                },

                success: function(response) {

                    if (!response || response.length === 0) {
                        flatChoices.setChoices([{
                            value: '',
                            label: 'No Flats Found',
                            disabled: true
                        }]);
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
        $('#FlatNo').on('change', function() {

            let flatId = $(this).val();

            $('#FlatID').val(flatId);
            $('#CustomerName').val('');
            $('#FlatType').val('');

            if (!flatId) return;

            $.ajax({
                url: "{{ route('get.flat.details') }}",
                type: "GET",
                dataType: "json",
                data: {
                    flat_id: flatId
                },

                success: function(res) {
                    $('#CustomerName').val(res.customer_name);
                    $('#FlatType').val(res.flat_type);
                }
            });
        });

    });
</script>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {

        let editWing = "{{ old('Wing', $addbill->Wing ?? '') }}";
        let editFlatId = "{{ old('FlatID', $addbill->FlatNo ?? '') }}";

        if (editWing) {
            $('#Wing').val(editWing).trigger('change');

            setTimeout(function() {
                flatChoices.setChoiceByValue(editFlatId);
                $('#FlatID').val(editFlatId);
                $('#FlatNo').trigger('change');
            }, 800);
        }

    });
</script>

@endpush