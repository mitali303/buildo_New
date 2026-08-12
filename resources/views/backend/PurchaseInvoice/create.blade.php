@extends('backend.partials.master')

@section('title', !empty($invoice) ? 'Edit Material Inward' : 'Create Material Inward')
<style>
    .choices__list--dropdown .choices__item {
    min-width: 400px;           /* increase option width */
    white-space: nowrap;        /* prevent line breaks */
    overflow: hidden;
    text-overflow: ellipsis;    /* show "..." if too long */
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ !empty($invoice) ? 'Edit Material Inward' : '' }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ !empty($invoice) ? route('PurchaseInvoice.update') : route('PurchaseInvoice.store') }}" method="POST">
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
                                        value="{{ old('Date', !empty($invoice) ? \Carbon\Carbon::parse($invoice->Date)->format('d-m-Y') : \Carbon\Carbon::now()->format('d-m-Y')) }}">
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
                            <label class="form-label">Purchase From <span class="text-danger">*</span></label>

                            {{-- <select name="purchasefrom" id="purchasefrom"
                                    class="form-control choices-single-purchasefrom"
                                    data-placeholder="Select purchasefrom"
                                    >
                                <option value="">Select</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->ID }}"
                                            {{ old('purchasefrom', $invoice->purchasefrom ?? '') == $vendor->ID ? 'selected' : '' }}>
                                            {{ $vendor->Name }}
                                        </option>
                                    @endforeach
                            </select> --}}
                                                  <select name="purchasefrom" id="purchasefrom"
                            class="form-control choices-single-purchasefrom"
                            data-placeholder="Select purchasefrom">

                        <option value="">Select</option>

                        {{-- Suppliers + Contractors Combined --}}
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->ID }}"
                                {{ old('purchasefrom', $invoice->purchasefrom ?? '') == $vendor->ID ? 'selected' : '' }}>
                                {{ $vendor->Name }} 
                            </option>
                        @endforeach

                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->ID }}"
                                {{ old('purchasefrom', $invoice->purchasefrom ?? '') == $contractor->ID ? 'selected' : '' }}>
                                {{ $contractor->Name }} 
                            </option>
                        @endforeach

                    </select>
                            @error('purchasefrom') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4" id="scheme">
                            <label class="form-label">Scheme <span class="text-danger">*</span></label>

                            <select name="Destination" id="Destination"
                                    class="form-control choices-single-destination"
                                    data-placeholder="Select Destination"
                                    >
                                <option value="">Select</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->ID }}"
                                            {{ old('Destination', $invoice->destination ?? ($schemes[0]->ID ?? '')) == $scheme->ID ? 'selected' : '' }}>
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Attachment</label>
                            
                            <input type="file" id="fileInput" name="images[]" multiple class="form-control" />

                            <ul id="fileList" class="mt-3 list-unstyled">
                                @if(!empty($existingImages))
                                    @foreach($existingImages as $img)
                                        <li>
                                            <a href="{{ asset('Uploads/invattachment/' . $img) }}" 
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
                                @if ($errors->any())
                                    @php
                                        $fieldLabels = [
                                            'material_group' => 'Material',
                                            'type'           => 'Type',
                                            'quantity'       => 'Quantity',
                                            'unit'           => 'Unit',
                                            'rate'           => 'Rate',
                                            'discount'       => 'Discount',
                                            'taxable'        => 'Taxable Amount',
                                            'cgst'           => 'CGST',
                                            'sgst'           => 'SGST',
                                            'igst'           => 'IGST',
                                            'total'          => 'Total',
                                        ];

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
                                                        // Example key: sgst.0
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
                                            <th style="min-width: 180px;">Material</th>
                                            <th style="min-width: 180px;">Type</th>
                                            <th style="min-width: 90px;">Quantity</th>
                                            <th style="min-width: 120px;">Unit</th>
                                            <th style="min-width: 100px;">Rate</th>
                                            <th style="min-width: 100px;">Disc %</th>
                                            <th style="min-width: 120px;">Taxable Amount</th>
                                            <th style="min-width: 70px;">CGST %</th>
                                            <th style="min-width: 70px;">SGST %</th>
                                            <th style="min-width: 70px;">IGST %</th>
                                            <th style="min-width: 120px;">Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      @php
                                            $hasOld = old('material_group');
                                            $rowCount = $hasOld ? count(old('material_group')) : count($products ?? []);
                                        @endphp

                                        @for ($i = 0; $i < max(1, $rowCount); $i++)
                                            @php
                                                $hasProduct = isset($products[$i]);

                                                $materialName = old("material_group.$i") ?? ($hasProduct ? ($materialIdToName[$products[$i]->Material] ?? '') : '');
                                                $types = $materialNameToTypes[$materialName] ?? collect();
                                                $type = old("type.$i") ?? ($hasProduct ? ($materialIdToType[$products[$i]->Material] ?? '') : '');
                                                $unit = old("unit.$i") ?? ($types->where('Type', $type)->first()->Unit ?? '');

                                                $qty = old("quantity.$i") ?? ($hasProduct ? $products[$i]->Qty : '');
                                                $rate = old("rate.$i") ?? ($hasProduct ? $products[$i]->Rate : '');
                                                $disc = old("discount.$i") ?? ($hasProduct ? $products[$i]->Disc : '0');
                                                $amount = old("taxable.$i") ?? ($hasProduct ? $products[$i]->Amount : '');
                                                $cgst = old("cgst.$i") ?? ($hasProduct ? $products[$i]->CGST : '0');
                                                $sgst = old("sgst.$i") ?? ($hasProduct ? $products[$i]->SGST : '0');
                                                $igst = old("igst.$i") ?? ($hasProduct ? $products[$i]->IGST : '0');
                                                $total = old("total.$i") ?? ($hasProduct ? $products[$i]->Total : '');
                                            @endphp

                                            <tr style="font-size:13px;">
                                                <td>
                                                    <select name="material_group[]" class="form-control choices-single-material">
                                                        <option value="">Select</option>
                                                        @foreach($materials as $mat)
                                                            <option value="{{ $mat->Name }}" {{ $mat->Name == $materialName ? 'selected' : '' }}>{{ $mat->Name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="type[]" class="form-control mat-type">
                                                        <option value="">Select Type</option>
                                                        @foreach($types as $t)
                                                            <option value="{{ $t->Type }}" data-unit="{{ $t->Unit }}" {{ $t->Type == $type ? 'selected' : '' }}>{{ $t->Type }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="text" name="quantity[]" class="form-control qty" value="{{ $qty }}" oninput="calculateRow(this)"></td>
                                                <td><input type="text" name="unit[]" class="form-control mat-unit" value="{{ $unit }}" readonly></td>
                                                <td><input type="text" name="rate[]" class="form-control rate" value="{{ $rate }}" oninput="calculateRow(this)"></td>
                                                <td><input type="text" name="discount[]" class="form-control discount" value="{{ $disc }}" oninput="calculateRow(this)"></td>
                                                <td><input type="text" name="taxable[]" class="form-control taxable" value="{{ $amount }}" readonly></td>
                                                <td><input type="text" name="cgst[]" class="form-control cgst" value="{{ $cgst }}" oninput="calculateRow(this)"></td>
                                                <td><input type="text" name="sgst[]" class="form-control sgst" value="{{ $sgst }}" oninput="calculateRow(this)"></td>
                                                <td><input type="text" name="igst[]" class="form-control igst" value="{{ $igst }}" oninput="calculateRow(this)"></td>
                                                <td><input type="text" name="total[]" class="form-control total" value="{{ $total }}" readonly></td>
                                                <td><button type="button" class="btn btn-sm removeRow"><i data-feather='trash'></i></button></td>
                                            </tr>
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
                    <br>
                    <div class="row mt-2">
                        <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Amount Before Tax<span class="text-danger">*</span></label>
                                <input name="total_taxable" id="total_taxable"
                                    type="text" onchange="recalculateGrandTotal(this)"
                                    class="form-control"
                                    readonly
                                    style="background-color:#f5f5f5;"
                                    value="{{ old('total_taxable', $invoice->total ?? '0.00') }}">
                            @error('total_taxable') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Total CGST<span class="text-danger">*</span></label>
                                <input name="total_cgst" id="total_cgst"
                                    type="text" onchange="recalculateGrandTotal(this)"
                                    class="form-control"
                                    readonly
                                    style="background-color:#f5f5f5;"
                                    value="{{ old('total_cgst', $invoice->totcgst_amt ?? '0.00') }}">
                            @error('total_cgst') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                       <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Total SGST<span class="text-danger">*</span></label>
                                <input name="total_sgst" id="total_sgst"
                                    type="text" onchange="recalculateGrandTotal(this)"
                                    class="form-control"
                                    readonly
                                    style="background-color:#f5f5f5;"
                                    value="{{ old('total_sgst', $invoice->totsgst_amt ?? '0.00') }}">
                            @error('total_sgst') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Total IGST<span class="text-danger">*</span></label>
                                <input name="total_igst" id="total_igst"
                                    type="text" onchange="recalculateGrandTotal(this)"
                                    class="form-control"
                                    readonly
                                    style="background-color:#f5f5f5;"
                                    value="{{ old('total_igst', $invoice->totigst_amt ?? '0.00') }}">
                            @error('total_igst') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Narration</label>
                               <input name="narration" id="narration"
                                value="{{ old('narration', $invoice->narration ?? '') }}"
                                type="text"
                                class="input-sm form-control">
                            @error('total_igst') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Loading<span class="text-danger">*</span></label>
                               <input name="loading" id="loading"
                                value="{{ old('loading', $invoice->loading ?? '0') }}"
                                type="text" oninput="recalculateGrandTotal(this)"
                                class="input-sm form-control">
                            @error('loading') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Unloading<span class="text-danger">*</span></label>
                               <input name="unloading" id="unloading"
                                value="{{ old('unloading', $invoice->unloading ?? '0') }}"
                                type="text" oninput="recalculateGrandTotal(this)"
                                class="input-sm form-control">
                            @error('unloading') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Other<span class="text-danger">*</span></label>
                               <input name="other" id="other"
                                value="{{ old('other', $invoice->other ?? '0') }}"
                                type="text" oninput="recalculateGrandTotal(this)"
                                class="input-sm form-control">
                            @error('other') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Transportation<span class="text-danger">*</span></label>
                               <input name="transport" id="transport"
                                value="{{ old('transport', $invoice->transport ?? '0') }}"
                                type="text" oninput="recalculateGrandTotal(this)"
                                class="input-sm form-control">
                            @error('transport') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                 <label class="mb-2 d-block">Round<span class="text-danger">*</span></label>
                               <input name="round" id="round"
                                value="{{ old('round', $invoice->round ?? '0') }}"
                                type="text" oninput="recalculateGrandTotal(this)"
                                class="input-sm form-control">
                            @error('round') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                    </div>

                    <div class="row">
                       <div class="mb-3 col-md-3">
                            <label class="mb-2 d-block">Grand Total<span class="text-danger">*</span></label>
                            <input name="gtotal" id="gtotal"
                                value="{{ old('gtotal', $invoice->gtotal ?? '0') }}"
                                type="text" 
                                class="input-sm form-control"
                                readonly
                                style="background-color:#f5f5f5;">
                            @error('gtotal') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <br>
                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($invoice) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('PurchaseInvoice') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@endsection

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

        const cgst = parseFloat(row.querySelector('.cgst')?.value || 0);
        const sgst = parseFloat(row.querySelector('.sgst')?.value || 0);
        const igst = parseFloat(row.querySelector('.igst')?.value || 0);

        totalCGST += +(taxable * cgst / 100).toFixed(2);
        totalSGST += +(taxable * sgst / 100).toFixed(2);
        totalIGST += +(taxable * igst / 100).toFixed(2);
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
    xhr.open("POST", "{{ route('upload.attachmentinv') }}", true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                li.innerText = file.name ;

                const removeBtn = document.createElement('button');
                removeBtn.innerText = '❌';
                removeBtn.type = 'button';
                removeBtn.classList.add('btn', 'btn-sm', 'ms-2');
                removeBtn.onclick = function () {
                    fetch("{{ route('delete.imageinv') }}", {
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
        fetch('{{ route('PurchaseInvoice.addRow') }}', {
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
                if (newSelect) {
                    $(newSelect).select2({
                        width: '100%',
                        placeholder: "Select Material"
                    });
                }

            if (typeof feather !== 'undefined') feather.replace();
        });
    });

    // Remove Row
    document.querySelector('#materialTable tbody').addEventListener('click', function (e) {
        if (e.target.closest('.removeRow')) {
            const row = e.target.closest('tr');
            if (document.querySelectorAll('#materialTable tbody tr').length > 1) {
                row.remove();
                updateTotalTaxable();
                updateGSTTotals();
                recalculateGrandTotal();
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
                url: '{{ route("PurchaseInvoice.material.getTypes") }}',
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

    if (!checkDuplicateType(this)) {
        return;
    }

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
    // function calculateRow(el) {
    //     const row = el.closest('tr');

    //     let qty = parseFloat(row.querySelector('.qty')?.value || 0);
    //     let rate = parseFloat(row.querySelector('.rate')?.value || 0);
    //     let discount = parseFloat(row.querySelector('.discount')?.value || 0);
    //     let gross = qty * rate;
        
    //     let taxable = gross - (gross * discount / 100);
    //     row.querySelector('.taxable').value = taxable.toFixed(2);

    //     let cgst = parseFloat(row.querySelector('.cgst')?.value || 0);
    //     let sgst = parseFloat(row.querySelector('.sgst')?.value || 0);
    //     let igst = parseFloat(row.querySelector('.igst')?.value || 0);

    //     if (el.classList.contains('cgst') || el.classList.contains('sgst')) {
    //         if (cgst > 0 || sgst > 0) {
    //             row.querySelector('.igst').value = 0;
    //         }
    //     } else if (el.classList.contains('igst')) {
    //         if (igst > 0) {
    //             row.querySelector('.cgst').value = 0;
    //             row.querySelector('.sgst').value = 0;
    //         }
    //     }

    //     let gstPercent = cgst + sgst + igst;
    //     let gstAmount = taxable * gstPercent / 100;
    //     let total = taxable + gstAmount;

    //     row.querySelector('.total').value = total.toFixed(2);
    //     updateTotalTaxable();
    //     updateGSTTotals();
    //     recalculateGrandTotal(el)
    // }

    function calculateRow(el) {
    const row = el.closest('tr');

    let qty = parseFloat(row.querySelector('.qty')?.value || 0);
    let rate = parseFloat(row.querySelector('.rate')?.value || 0);
    let discount = parseFloat(row.querySelector('.discount')?.value || 0);

    let gross = qty * rate;
    let taxable = gross - (gross * discount / 100);
    row.querySelector('.taxable').value = taxable.toFixed(2);

    let cgstInput = row.querySelector('.cgst');
    let sgstInput = row.querySelector('.sgst');
    let igstInput = row.querySelector('.igst');

    let cgst = parseFloat(cgstInput?.value || 0);
    let sgst = parseFloat(sgstInput?.value || 0);
    let igst = parseFloat(igstInput?.value || 0);

    // 🔥 Enforce mutual exclusivity
    if (el.classList.contains('igst') && igst > 0) {
        cgst = 0;
        sgst = 0;
        cgstInput.value = 0;
        sgstInput.value = 0;
    }

    if ((el.classList.contains('cgst') || el.classList.contains('sgst')) && (cgst > 0 || sgst > 0)) {
        igst = 0;
        igstInput.value = 0;
    }

    // ✅ NOW calculate GST (after reset)
    let gstPercent = cgst + sgst + igst;
    let gstAmount = taxable * gstPercent / 100;
    let total = taxable + gstAmount;

row.querySelector('.total').value = total.toFixed(2);

    updateTotalTaxable();
    updateGSTTotals();
    recalculateGrandTotal();
}
</script>
<script>
function getVal(id) {
    return parseFloat(document.getElementById(id)?.value || 0);
}

function recalculateGrandTotal() {
    let rowTotal = 0;

    document.querySelectorAll('#materialTable tbody tr').forEach(row => {
        rowTotal += parseFloat(row.querySelector('.total')?.value || 0);
    });

    const loading = getVal('loading');
    const unloading = getVal('unloading');
    const other = getVal('other');
    const transport = getVal('transport');
    const round = getVal('round');

    const gtotal = rowTotal + loading + unloading + other + transport + round;

    document.getElementById('gtotal').value = gtotal.toFixed(2);
}

// ✅ Trigger calculation on page load — solves the issue
document.addEventListener('DOMContentLoaded', function () {
    recalculateGrandTotal();
});
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-existing")) {

        let filename = e.target.getAttribute("data-file");
        let li = e.target.closest("li");

        fetch("{{ route('delete.imageinv') }}", {
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

<script>
    const materialTypeMap = @json($materialNameToTypes);
</script>

<script>
function validateDuplicateType(currentRow) {
    let selectedPairs = [];

    document.querySelectorAll('#materialTable tbody tr').forEach(row => {
        let material = row.querySelector('.choices-single-material')?.value; // ✅ FIXED
        let type = row.querySelector('.mat-type')?.value;

        if (material && type) {
            selectedPairs.push(material + '||' + type);
        }
    });

    let currentMaterial = currentRow.querySelector('.choices-single-material')?.value; // ✅ FIXED
    let currentType = currentRow.querySelector('.mat-type')?.value;

    let key = currentMaterial + '||' + currentType;

    let count = selectedPairs.filter(x => x === key).length;

    if (count > 1) {
        alert("This Type is already selected for same Material!");
        currentRow.querySelector('.mat-type').value = "";
        calculateRow(currentRow.querySelector('.mat-type'));
    }
}
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-purchasefrom"));
        new Choices(document.querySelector(".choices-single-destination"));
        document.querySelectorAll(".choices-single-material").forEach(el => {
            $(el).select2({
                width: '100%',
                placeholder: "Select Material"
            });
        });
    });
</script>
<script>
function checkDuplicateType(selectEl) {
    let row = selectEl.closest('tr');

    let currentMaterial = row.querySelector('.choices-single-material')?.value;
    let currentType = row.querySelector('.mat-type')?.value;

    let isDuplicate = false;

    document.querySelectorAll('#materialTable tbody tr').forEach((r) => {
        if (r === row) return;

        let mat = r.querySelector('.choices-single-material')?.value;
        let type = r.querySelector('.mat-type')?.value;

        if (mat === currentMaterial && type === currentType) {
            isDuplicate = true;
        }
    });

    if (isDuplicate) {
        alert("This Material + Type already selected!");
        selectEl.value = "";
        return false;
    }

    return true;
}
</script>