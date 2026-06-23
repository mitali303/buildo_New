@extends('backend.partials.master')

@section('title', !empty($invoice) ? 'Edit Site Work Order' : 'Create Site Work Order')
<style>
    .choices__list--dropdown .choices__item {
    min-width: 400px;           /* increase option width */
    white-space: nowrap;        /* prevent line breaks */
    overflow: hidden;
    text-overflow: ellipsis;    /* show "..." if too long */
}

    input[readonly] {
        background-color: #f5f5f5 !important;
        cursor: not-allowed;
    }

    input:not([readonly]) {
        background-color: #ffffff !important;
    }

</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ !empty($invoice) ? 'Edit Site Work Order' : 'Create Site Work Order' }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ !empty($invoice) ? route('Site_work_order.update') : route('Site_work_order.store') }}" method="POST">
                    @csrf
                    @if(!empty($invoice))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $invoice->ID }}">
                    @endif

                   <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>

                            <div class="input-group flatpickr-container">
                                <input type="text"
                                    name="Date"
                                    id="datepicker"
                                    class="form-control @error('Date') is-invalid @enderror"
                                    placeholder="Select date"
                                        value="{{ old('Date', !empty($workoflbrs) ? \Carbon\Carbon::parse($workoflbrs->Date)->format('d-m-Y') : \Carbon\Carbon::now()->format('d-m-Y')) }}">
                            </div>

                            @error('Date') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Inv No.</label>
                               <input type="text" name="Invno" class="input-sm form-control" value="{{ $nextInvno }}" readonly>
                            @error('Invno') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4" id="po_div">
                            <label class="form-label">Contractor <span class="text-danger">*</span></label>

                            <select name="purchasefrom" id="purchasefrom"
                                    class="form-control choices-single-purchasefrom"
                                    data-placeholder="Select purchasefrom"
                                    >
                                <option value="">Select</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->ID }}"
                                            {{ old('purchasefrom', $invoice->ContractorID ?? '') == $vendor->ID ? 'selected' : '' }}>
                                            {{ $vendor->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('purchasefrom') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4" id="worktype-container">
                            <label class="form-label">Work Type <span class="text-danger">*</span></label>

                            <select name="worktype" id="worktype"
                                    class="form-control choices-single-worktype"
                                    data-placeholder="Select Work Type"
                                    onchange="GetInputField()">

                                <option value="">Select</option>
                                <option value="OTHER">ADD NEW</option>

                                @foreach($worktypes as $type)
                                    <option value="{{ $type->worktype }}"
                                        {{ old('worktype', $invoice->worktype ?? '') == $type->worktype ? 'selected' : '' }}>
                                        {{ $type->worktype }}
                                    </option>
                                @endforeach
                            </select>

                            @error('worktype')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4" id="scheme">
                            <label class="form-label">Scheme <span class="text-danger">*</span></label>

                            <select name="Destination" id="Destination"
                                    class="form-control choices-single-destination"
                                    data-placeholder="Select Destination"
                                    >
                                <option value="">Select</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->ID }}"
                                            {{ old('Destination', $invoice->SiteLocation ?? $schemes[0]->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label"> Start Date <span class="text-danger">*</span></label>
                               <div class="input-group flatpickr-container">
                                <input type="text" name="Startdate"
                                    id="start_datepicker"
                                    class="form-control @error('Startdate') is-invalid @enderror"
                                    placeholder="Select Start date"
                                    value="{{ old('Startdate', isset($invoice->Startdate) ? \Carbon\Carbon::parse($invoice->Startdate)->format('d-m-Y') : '') }}"
                                >
                            </div>
                            @error('Startdate') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label"> Completion Date <span class="text-danger">*</span></label>
                               <div class="input-group flatpickr-container">
                                <input type="text" name="Completiondate"
                                    id="to_datepicker"
                                    class="form-control @error('Completiondate') is-invalid @enderror"
                                    placeholder="Select Completion date"
                                    value="{{ old('Completiondate', isset($invoice->Completiondate) ? \Carbon\Carbon::parse($invoice->Completiondate)->format('d-m-Y') : '') }}"
                                >
                            </div>
                            @error('Completiondate') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Narration</label>
                            <textarea name="narration" class="form-control input-sm" rows="3" readonly value="{{ old('narration', isset($invoice->narration) ? $invoice->Completiondate: '') }}"></textarea>
                            @error('narration') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>


                        <div class="mb-3 col-md-4">
                            <label class="form-label">Attachment</label>

                            <input type="file" id="fileInput" name="images[]" multiple class="form-control" />

                            <ul id="fileList" class="mt-3 list-unstyled">
                                @if(!empty($existingImages))
                                    @foreach($existingImages as $img)
                                        <li>
                                            <a href="{{ asset('Uploads/sitework/' . $img) }}" 
                                            target="_blank"
                                            style="text-decoration: underline; color: blue;">
                                                {{ $img }}
                                            </a>

                                            <button type="button" 
                                                    class="btn btn-sm ms-2 remove-existing text-danger" 
                                                    data-file="{{ $img }}">
                                                ❌
                                            </button>

                                            <input type="hidden" name="uploadfile[]" value="{{ $img }}">
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                    </div>

                    <div class="row">
                       <div class="row mt-4">
                            <div class="col-md-12" style="overflow-x: auto; width: 100%;">

                            @php
                                $fieldLabels = [
                                    'scope'      => 'Scope Details',
                                    'lumpsum'    => 'Lump-Sum',
                                    'qty'        => 'Quantity',
                                    'unit'       => 'Unit',
                                    'rate'       => 'Rate',
                                    'ls_amount'  => 'L.S Amount',
                                    'amount'     => 'Amount',
                                ];
                            @endphp
                            @if ($errors->any())
                                @php
                                    // Filter errors for table fields only
                                    $tableErrors = collect($errors->getMessages())->filter(function ($_, $key) use ($fieldLabels) {
                                        return collect(array_keys($fieldLabels))->contains(function ($prefix) use ($key) {
                                            return str_starts_with($key, $prefix . '.');
                                        });
                                    });
                                @endphp

                                @if ($tableErrors->isNotEmpty())
                                    <div class="alert alert-danger">
                                        <strong>There were errors in the table:</strong>
                                        <ul class="mb-0">
                                            @foreach ($tableErrors as $key => $messages)
                                                @php
                                                    // Example key: qty.0
                                                    [$field, $index] = array_pad(explode('.', $key), 2, null);
                                                    $rowNumber = is_numeric($index) ? $index + 1 : null;
                                                    $label = $fieldLabels[$field] ?? ucfirst($field);
                                                @endphp

                                                @foreach ($messages as $message)
                                                    <li>
                                                        @if($rowNumber)
                                                            <strong>Row {{ $rowNumber }} –</strong>
                                                        @endif
                                                        {{ $label }} {{ str_replace("$key ", '', $message) }}
                                                    </li>
                                                @endforeach
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                            @endif

                               <table class="table table-bordered table-striped" id="materialTable" style="font-size:13px;">
    <thead class="table-light">
        <tr>
            <th style="min-width: 180px;">Scope Details</th>
            <th style="min-width: 90px;">Lump-Sum</th>
            <th style="min-width: 90px;">Quantity</th>
            <th style="min-width: 120px;">Unit</th>
            <th style="min-width: 100px;">Rate</th>
            <th style="min-width: 140px;">L.S Amount</th>
            <th style="min-width: 120px;">Amount</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        @php
            $rowCount = old('scope', $items ?? []) ? count(old('scope', $items ?? [])) : 1;
        @endphp

        @for($i = 0; $i < $rowCount; $i++)
            @php
                $scope   = old("scope.$i", $items[$i]->scope ?? '');
                $ls      = old("lumpsum.$i", $items[$i]->Islumpsum ?? 0);
                $qty     = old("qty.$i", $items[$i]->Qty ?? '');
                $unit    = old("unit.$i", $items[$i]->Unit ?? '');
                $rate    = old("rate.$i", $items[$i]->Rate ?? '');
                $ls_amt  = old("ls_amount.$i", $items[$i]->lumsumAmt ?? '');
                $amount  = old("amount.$i", $items[$i]->Amount ?? '');
            @endphp

            @include('backend.Site_work_order.row', [
                'scope'     => $scope,
                'lumpsum'   => $ls,
                'qty'       => $qty,
                'unit'      => $unit,
                'rate'      => $rate,
                'ls_amount' => $ls_amt,
                'amount'    => $amount
            ])
        @endfor
    </tbody>
</table>

<div class="text-center mt-2">
    <button type="button" class="btn btn-outline-success btn-sm" id="addRow">
        <i class="bi bi-plus-circle"></i> Add Row
    </button>
</div>

                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Total Before Tax<span class="text-danger">*</span></label>
                                <input name="Total" id="Total"
                                    type="text"
                                    class="form-control"
                                    readonly
                                    
                                    value="{{ old('Total', $invoice->Total ?? '0.00') }}">
                            @error('Total') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">GST@%<span class="text-danger">*</span></label>
                                <input name="tax" id="tax"
                                    type="text"
                                    class="form-control"
                                    
                                    value="{{ old('tax', $invoice->tax ?? '0') }}">
                            @error('tax') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">TDS@%<span class="text-danger">*</span></label>
                                <input name="tds" id="tds"
                                    type="text"
                                    class="form-control"
                                    
                                    value="{{ old('tds', $invoice->tds ?? '0') }}">
                            @error('tds') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">GST Amount<span class="text-danger">*</span></label>
                                <input name="TaxAmt" id="TaxAmt"
                                    type="text"
                                    class="form-control"
                                    readonly
                                   
                                    value="{{ old('TaxAmt', $invoice->TaxAmt ?? '0.00') }}">
                            @error('TaxAmt') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">TDS Amount<span class="text-danger">*</span></label>
                                <input name="TDSAmt" id="TDSAmt"
                                    type="text"
                                    class="form-control"
                                    readonly
                                    
                                    value="{{ old('TDSAmt', $invoice->TDSAmt ?? '0.00') }}">
                            @error('TDSAmt') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Round<span class="text-danger">*</span></label>
                                <input name="round" id="round"
                                    type="text"
                                    class="form-control"
                                    
                                    value="{{ old('round', $invoice->round ?? '0.00') }}">
                            @error('round') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                       <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Grand Total<span class="text-danger">*</span></label>
                                <input name="gtotal" id="gtotal"
                                    type="text"
                                    class="form-control"
                                    readonly
                                    
                                    value="{{ old('gtotal', $invoice->gtotal ?? '0.00') }}">
                            @error('gtotal') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Retention<span class="text-danger">*</span></label>
                                <input name="retain_per" id="retain_per"
                                    type="text"
                                    class="form-control"
                                    readonly
                                    
                                    value="{{ old('retain_per', $invoice->retain_per ?? '0.00') }}">
                            @error('retain_per') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Retention Amount<span class="text-danger">*</span></label>
                                <input name="retain_amt" id="retain_amt"
                                    type="text"
                                    class="form-control"
                                    readonly
                                    
                                    value="{{ old('retain_amt', $invoice->retain_amt ?? '0.00') }}">
                            @error('retain_amt') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                    </div>
                    <br>

                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($invoice) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('Site_work_order') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@endsection
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fp = flatpickr("#datepicker", {
            dateFormat: "d-m-Y",
            allowInput: true,
            clickOpens: false, // disable open on input click
        });

        // Trigger calendar when either input or icon is clicked
        document.getElementById('datepicker').addEventListener('click', () => fp.open());
        document.getElementById('calendar-icon').addEventListener('click', () => fp.open());
    });
    document.addEventListener('DOMContentLoaded', function () {
        const fp = flatpickr("#start_datepicker", {
            dateFormat: "d-m-Y",
            allowInput: true,
            clickOpens: false, // disable open on input click
        });

        // Trigger calendar when either input or icon is clicked
        document.getElementById('start_datepicker').addEventListener('click', () => fp.open());
        document.getElementById('calendar-icon').addEventListener('click', () => fp.open());
    });
    document.addEventListener('DOMContentLoaded', function () {
        const fp = flatpickr("#to_datepicker", {
            dateFormat: "d-m-Y",
            allowInput: true,
            clickOpens: false, // disable open on input click
        });

        // Trigger calendar when either input or icon is clicked
        document.getElementById('to_datepicker').addEventListener('click', () => fp.open());
        document.getElementById('calendar-icon').addEventListener('click', () => fp.open());
    });
    function updateTotalTaxable() {
    let total = 0;
    document.querySelectorAll('.taxable').forEach(input => {
        const val = parseFloat(input.value) || 0;
        total += val;
    });
    document.getElementById('total_taxable').value = total.toFixed(2);
}
function updateGSTTotals() {
    let totalCGST = 0, totalSGST = 0, totalIGST = 0;

    document.querySelectorAll('#materialTable tbody tr').forEach(row => {
        const taxable = parseFloat(row.querySelector('.taxable')?.value || 0);
        const cgstPercent = parseFloat(row.querySelector('.cgst')?.value || 0);
        const sgstPercent = parseFloat(row.querySelector('.sgst')?.value || 0);
        const igstPercent = parseFloat(row.querySelector('.igst')?.value || 0);

        totalCGST += (taxable * cgstPercent) / 100;
        totalSGST += (taxable * sgstPercent) / 100;
        totalIGST += (taxable * igstPercent) / 100;
    });

    document.getElementById('total_cgst').value = totalCGST.toFixed(2);
    document.getElementById('total_sgst').value = totalSGST.toFixed(2);
    document.getElementById('total_igst').value = totalIGST.toFixed(2);
}

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    input.addEventListener('change', function () {
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
    xhr.open("POST", "{{ route('upload.attachmentsite') }}", true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                li.innerText = file.name ;

                const removeBtn = document.createElement('button');
                removeBtn.innerText = '❌';
                removeBtn.type = 'button';
                removeBtn.classList.add('btn', 'btn-sm', 'ms-2');
                removeBtn.onclick = function () {
                    fetch("{{ route('delete.imagesite') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ name: res.filename })
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
<!-- <script>
document.addEventListener('DOMContentLoaded', function () {
    // Add Row via AJAX
    document.getElementById('addRow').addEventListener('click', function () {
        fetch('{{ route('PurchaseOrder.addRow') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        })
        .then(response => response.text())
        .then(html => {
            const tbody = document.querySelector('#materialTable tbody');
            tbody.insertAdjacentHTML('beforeend', html);

            // Re-init Choices.js
            const lastSelect = tbody.lastElementChild.querySelector('.choices-single-material');
            if (lastSelect) {
                new Choices(lastSelect);
            }

            // Re-render feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        })
        .catch(error => console.error('Error adding row:', error));
    });

    // ✅ Event delegation for dynamically added trash buttons
    document.querySelector('#materialTable tbody').addEventListener('click', function (e) {
        if (e.target.closest('.removeRow')) {
            const row = e.target.closest('tr');
            const rowCount = document.querySelectorAll('#materialTable tbody tr').length;
            if (rowCount > 1) {
                row.remove();
            }
        }
    });
});
</script> -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Add Row
    document.getElementById('addRow').addEventListener('click', function () {
        fetch('{{ route('Site_work_order.addRow') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        })
        .then(res => res.text())
        .then(html => {
            const tbody = document.querySelector('#materialTable tbody');
            tbody.insertAdjacentHTML('beforeend', html);

            // Initialize Choices.js for the newly added row
            const newSelect = tbody.lastElementChild.querySelector('.choices-single-material');
            if (newSelect) new Choices(newSelect);

            if (typeof feather !== 'undefined') feather.replace();
        });
    });

    // Remove Row
    document.querySelector('#materialTable tbody').addEventListener('click', function (e) {
        if (e.target.closest('.removeRow')) {
            const row = e.target.closest('tr');
            if (document.querySelectorAll('#materialTable tbody tr').length > 1) {
                row.remove();
                
                recalcWorkorderTotals();
            }
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Load types on material change
    $(document).on('change', '.choices-single-material', function () {
        const $row = $(this).closest('tr');
        const $typeDropdown = $row.find('.mat-type');
        const $unitInput = $row.find('.mat-unit');

        const selectedMaterial = $(this).val();

        $typeDropdown.html('<option value="">Loading...</option>');
        $unitInput.val('');  // only reset during change

        if (selectedMaterial) {
            $.ajax({
                url: '{{ route("Site_work_order.material.getTypes") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    material: selectedMaterial
                },
                success: function (data) {
                    $typeDropdown.html('<option value="">Select Type</option>');
                    data.forEach(function (item) {
                        $typeDropdown.append(
                            `<option value="${item.Type}" data-unit="${item.Unit}">${item.Type}</option>`
                        );
                    });

                    // Auto-trigger unit if a type is already selected
                    const selectedType = $typeDropdown.data('selected');
                    if (selectedType) {
                        $typeDropdown.val(selectedType).trigger('change');
                        $typeDropdown.removeAttr('data-selected');
                    }
                }
            });
        }
    });

    // On type change, set unit
    $(document).on('change', '.mat-type', function () {
        const unit = $(this).find('option:selected').data('unit') || '';
        $(this).closest('tr').find('.mat-unit').val(unit);
    });

    // On page load (edit or validation error), simulate material change for each row
    $('#materialTable tbody tr').each(function () {
        const $row = $(this);
        const $material = $row.find('.choices-single-material');
        const $type = $row.find('.mat-type');
        const $unit = $row.find('.mat-unit');

        const currentMaterial = $material.val();
        const currentType = $type.val();

        if (currentMaterial && currentType) {
            // Set selected type as data attribute for later use in AJAX
            $type.attr('data-selected', currentType);
            $material.trigger('change');  // will auto-select type and unit
        }
    });
});
</script>

<script>
    function calculateRow(el) {
        const row = el.closest('tr');

        let qty = parseFloat(row.querySelector('.qty')?.value || 0);
        let rate = parseFloat(row.querySelector('.rate')?.value || 0);
        let discount = parseFloat(row.querySelector('.discount')?.value || 0);
        let gross = qty * rate;
        
        let taxable = gross - (gross * discount / 100);
        row.querySelector('.taxable').value = taxable.toFixed(2);

        let cgst = parseFloat(row.querySelector('.cgst')?.value || 0);
        let sgst = parseFloat(row.querySelector('.sgst')?.value || 0);
        let igst = parseFloat(row.querySelector('.igst')?.value || 0);

        if (el.classList.contains('cgst') || el.classList.contains('sgst')) {
            if (cgst > 0 || sgst > 0) {
                row.querySelector('.igst').value = 0;
            }
        } else if (el.classList.contains('igst')) {
            if (igst > 0) {
                row.querySelector('.cgst').value = 0;
                row.querySelector('.sgst').value = 0;
            }
        }

        let gstPercent = cgst + sgst + igst;
        let gstAmount = taxable * gstPercent / 100;
        let total = taxable + gstAmount;

        row.querySelector('.total').value = total.toFixed(2);
        updateTotalTaxable();
        updateGSTTotals();
        recalculateGrandTotal(el)
    }
</script>
<script>
function getVal(id) {
    return parseFloat(document.getElementById(id)?.value || 0);
}

</script>

<script>
    const materialTypeMap = @json($materialNameToTypes);
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-purchasefrom"));
        new Choices(document.querySelector(".choices-single-destination"));
        new Choices(document.querySelector(".choices-single-worktype"));
        document.querySelectorAll(".choices-single-material").forEach(el => new Choices(el));
    });

function GetInputField() {
    let value = $("#worktype").val();

    if (value === 'OTHER') {
        $("#worktype-container").html(`
            <label class="form-label">Work Type <span class="text-danger">*</span></label>
            <input type="text"
                   name="worktype"
                   class="form-control"
                   placeholder="Enter Work Type">
        `);
    }
}
function calculateAmount(el) {
    const row = el.closest('tr');

    const lsCheck = row.querySelector('.ls-check').checked;
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const rate = parseFloat(row.querySelector('.rate').value) || 0;

    if (lsCheck) return; // ignore qty*rate if lump-sum checked

    const amount = qty * rate;
    row.querySelector('.amount').value = amount.toFixed(2);
}

function updateLSAmount(el) {
    const row = el.closest('tr');
    const lsAmt = parseFloat(el.value) || 0;

    row.querySelector('.amount').value = lsAmt.toFixed(2);
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('ls-check')) {
        const row = e.target.closest('tr');

        const isLS = e.target.checked;

        row.querySelector('.qty').toggleAttribute('hidden', isLS);
        row.querySelector('.rate').toggleAttribute('hidden', isLS);
        row.querySelector('.unit').toggleAttribute('hidden', isLS);

        row.querySelector('.qty').readOnly = isLS;
        row.querySelector('.rate').readOnly = isLS;
        row.querySelector('.unit').readOnly = isLS;

        row.querySelector('.ls-amount').readOnly = !isLS;

        if (isLS) {
            row.querySelector('.amount').value =
                parseFloat(row.querySelector('.ls-amount').value || 0).toFixed(2);
        } else {
            calculateAmount(row.querySelector('.qty'));
        }
    }
});
function unitCheck(selectEl) {
    let value = selectEl.value;
    let container = selectEl.closest('td');

    if (value === "OTHER") {
        container.innerHTML = `
            <input type="text" name="unit[]" class="form-control unit"
                   placeholder="Enter Unit">
        `;
    }
}
function recalcWorkorderTotals() {
    // ------- 1) TOTAL BEFORE TAX -------
    let totalBeforeTax = 0;

    document.querySelectorAll(".amount").forEach(el => {
        let v = parseFloat(el.value) || 0;
        totalBeforeTax += v;
    });

    document.getElementById("Total").value = totalBeforeTax.toFixed(2);



    // ------- 2) GST CALCULATION -------
    let gstPercent = parseFloat(document.getElementById("tax").value) || 0;
    let gstAmount  = (totalBeforeTax * gstPercent) / 100;

    document.getElementById("TaxAmt").value = gstAmount.toFixed(2);



    // ------- 3) TDS CALCULATION -------
    let tdsPercent = parseFloat(document.getElementById("tds").value) || 0;
    let tdsAmount  = (totalBeforeTax * tdsPercent) / 100;

    document.getElementById("TDSAmt").value = tdsAmount.toFixed(2);

let retainPer = parseFloat(document.getElementById("retain_per").value) || 0;
let retainAmt = (totalBeforeTax * retainPer) / 100;

document.getElementById("retain_amt").value = retainAmt.toFixed(2);

    // ------- 4) GRAND TOTAL -------
    let round = parseFloat(document.getElementById("round").value) || 0;

    let grandTotal =
        totalBeforeTax +
        gstAmount -
        tdsAmount +
        round;

    document.getElementById("gtotal").value = grandTotal.toFixed(2);
}

document.addEventListener("input", function (e) {
    if (
        e.target.classList.contains("amount") ||
        e.target.classList.contains("qty") ||
        e.target.classList.contains("rate") ||
        e.target.classList.contains("ls-amount") ||
        e.target.id === "tax" ||
        e.target.id === "tds" ||
        e.target.id === "round"
    ) {
        recalcWorkorderTotals();
    }
});

// Recalculate when page loads

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".qty, .rate, .ls-amount").forEach(el => {
        if (el.value !== "") {
            let row = el.closest("tr");
            calculateAmount(row.querySelector(".qty"));
        }
    });

    recalcWorkorderTotals();
});
document.addEventListener("DOMContentLoaded", function () {

    $("#purchasefrom").on("change", function () {
        let vendorId = $(this).val();

        if (!vendorId) return;

        $.ajax({
            url: "{{ route('vendor.getRetain') }}",
            type: "POST",
            data: {
                vendor_id: vendorId,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {
                let retainPer = parseFloat(res.retain_per || 0);

                $("#retain_per").val(retainPer.toFixed(2));

                // calculate retention amount
                let total = parseFloat($("#Total").val()) || 0;
                let retainAmt = (total * retainPer) / 100;

                $("#retain_amt").val(retainAmt.toFixed(2));
            }
        });
    });

});

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-existing")) {

        let filename = e.target.getAttribute("data-file");
        let li = e.target.closest("li");

        fetch("{{ route('delete.imagesite') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ name: filename })
        })
        .then(() => {
            li.remove(); // remove from UI
        });
    }
});

</script>