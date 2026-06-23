@extends('backend.partials.master')

@section('title', !empty($transfer) ? 'Edit Purchase Invoice' : 'Create Material Transfer')
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
        <h1 class="h3 mb-3">{{ !empty($transfer) ? 'Edit Material Transfer' : 'Create Material Transfer' }}</h1>
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
                <form action="{{ !empty($transfer) ? route('Transfer_Material.update', $transfer->ID) : route('Transfer_Material.store') }}" method="POST">
                    @csrf
                    @if(!empty($transfer))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $transfer->ID }}">
                    @endif

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                               <div class="input-group flatpickr-container">
                                <input type="text" name="Date"
                                    id="datepicker"
                                    class="form-control @error('Date') is-invalid @enderror"
                                    placeholder="Select date"
                                    value="{{ old('Date', isset($transfer->Date) 
                                            ? \Carbon\Carbon::parse($transfer->Date)->format('d-m-Y') 
                                            : now()->format('d-m-Y')) }}">
                            </div>
                            @error('Date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Inv No.</label>
                               <input type="text" name="Invno" class="input-sm form-control" value="{{ $nextsrno }}" readonly>
                            @error('Invno') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                       <div class="col-md-4" id="scheme">
                            <label class="form-label">From Scheme <span class="text-danger">*</span></label>

                            <select name="from_scheme" id="from_scheme"
                                    class="form-control choices-single-from_scheme"
                                    data-placeholder="Select from scheme"
                                    >
                                <option value="">Select</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->ID }}"
                                            {{ old('from_scheme', $transfer->from_site ?? $schemes[0]->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('from_scheme') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4" id="scheme">
                            <label class="form-label">To Scheme <span class="text-danger">*</span></label>

                                @php
                                    $fromSchemeId = $transfer->From_site ?? session('selected_scheme_id');
                                @endphp

                                <select name="to_scheme" id="to_scheme"
                                        class="form-control choices-single-to_scheme"
                                        data-placeholder="Select to scheme">

                                    <option value="">Select</option>

                                    @foreach($allschemes as $scheme)
                                        @if($scheme->ID != $fromSchemeId)  {{-- 🔥 Main condition --}}
                                            <option value="{{ $scheme->ID }}"
                                                {{ old('to_scheme', $transfer->To_site ?? '') == $scheme->ID ? 'selected' : '' }}>
                                                {{ $scheme->Name }}
                                            </option>
                                        @endif
                                    @endforeach

                                </select>
                            @error('to_scheme') <small class="text-danger">{{ $message }}</small> @enderror
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
                                                        $fieldName = explode('.', $key)[0];
                                                        $label = $fieldLabels[$fieldName] ?? ucfirst($fieldName);
                                                    @endphp
                                                    @foreach ($messages as $msg)
                                                        <li>{{ str_replace($key, $label, $msg) }}</li>
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
                                            <th style="min-width: 120px;">Unit</th>
                                            <th style="min-width: 90px;">Quantity</th>
                                            <th style="min-width: 90px;">Available Quantity</th>
                                            <th style="min-width: 100px;">Rate</th>
                                            <th style="min-width: 120px;">Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $rowCount = old('material_group')
                                            ? count(old('material_group'))
                                            : (!empty($products) ? count($products) : 1);
                                    @endphp

                                    @for($i = 0; $i < $rowCount; $i++)

                                        @php
                                            $hasProduct = isset($products[$i]);

                                            $materialName = old("material_group.$i")
                                                ?? ($hasProduct ? ($materialIdToName[$products[$i]->matrialID] ?? '') : '');

                                            $types = $materialNameToTypes[$materialName] ?? collect();

                                            $type = old("type.$i")
                                                ?? ($hasProduct ? ($materialIdToType[$products[$i]->matrialID] ?? '') : '');

                                            $unit = old("unit.$i")
                                                ?? ($types->where('Type', $type)->first()->Unit ?? '');

                                            $qty = old("quantity.$i")
                                                ?? ($hasProduct ? $products[$i]->qty : '');

                                            $avl_qty = old("avl_quantity.$i")
                                                ?? ($hasProduct ? $products[$i]->qty : '');
                                                

                                            $rate = old("rate.$i")
                                                ?? ($hasProduct ? $products[$i]->rate : '');

                                            $total = old("total.$i")
                                                ?? ($hasProduct ? $products[$i]->Amount : '');
                                        @endphp

                                        <tr style="font-size:13px;">
                                            <td>
                                                <select name="material_group[]" class="form-control choices-single-material">
                                                    <option value="">Select</option>
                                                    @foreach($materials as $mat)
                                                        <option value="{{ $mat->Name }}"
                                                            {{ $mat->Name == $materialName ? 'selected' : '' }}>
                                                            {{ $mat->Name }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <input type="hidden" name="material_id[]"
                                                    class="material_id"
                                                    value="{{ old('material_id.'.$i, $hasProduct ? $products[$i]->matrialID : '') }}">
                                            </td>

                                            <td>
                                                <select name="type[]" class="form-control mat-type">
                                                    <option value="">Select Type</option>
                                                    @foreach($types as $t)
                                                        <option value="{{ $t->Type }}"
                                                            data-unit="{{ $t->Unit }}"
                                                            {{ $t->Type == $type ? 'selected' : '' }}>
                                                            {{ $t->Type }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td><input type="text" name="unit[]" class="form-control mat-unit" value="{{ $unit }}" readonly></td>
                                           <td>
                                            <input type="text" name="quantity[]" class="form-control qty" value="{{ $qty }}" oninput="calculateRow(this); validateQty(this)">
                                        </td>
                                            <td><input type="text" name="avl_quantity[]" class="form-control avl_qty" value="{{ $avl_qty }}"></td>

                                            <td><input type="text" name="rate[]" class="form-control rate" value="{{ $rate }}" oninput="calculateRow(this)"></td>
                                            <td><input type="text" name="total[]" class="form-control total" value="{{ $total }}" readonly></td>

                                            <td>
                                                <button type="button" class="btn btn-sm removeRow">
                                                    <i data-feather="trash"></i>
                                                </button>
                                            </td>
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
                    <div class="row mt-2">
                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Amount Before Tax<span class="text-danger">*</span></label>
                                <input name="total_taxable" id="total_taxable"
                                    type="text" onchange="calculateGrandTotal(this)"
                                    class="form-control"
                                    readonly
                                    style="background-color:#f5f5f5;"
                                    value="{{ old('total_taxable', $transfer->total ?? '0.00') }}">
                            @error('total_taxable') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                    <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Loading/Unloading<span class="text-danger">*</span></label>
                               <input name="loading" id="loading"
                                value="{{ old('loading', $transfer->loading ?? '0') }}"
                                type="text" oninput="calculateGrandTotal(this)"
                                class="input-sm form-control">
                            @error('loading') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                               <label class="mb-2 d-block">Transportation<span class="text-danger">*</span></label>
                               <input name="transport" id="transport"
                                value="{{ old('transport', $transfer->transport ?? '0') }}"
                                type="text" oninput="calculateGrandTotal(this)"
                                class="input-sm form-control">
                            @error('transport') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-3">
                                <label class="mb-2 d-block">Round<span class="text-danger">*</span></label>
                               <input name="round" id="round"
                                value="{{ old('round', $transfer->round ?? '0') }}"
                                type="text" oninput="calculateGrandTotal(this)"
                                class="input-sm form-control">
                            @error('round') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                     <div class="mb-3 col-md-3">
                            <label class="mb-2 d-block">Grand Total<span class="text-danger">*</span></label>
                            <input name="gtotal" id="gtotal"
                                value="{{ old('gtotal', $transfer->gtotal ?? '0') }}"
                                type="text" 
                                class="input-sm form-control"
                                readonly
                                style="background-color:#f5f5f5;">
                            @error('gtotal') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div><br>

                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($transfer) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('Transfer_Material') }}" class="btn btn-secondary">Cancel</a>
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
    xhr.open("POST", "{{ route('upload.attachment') }}", true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                li.innerText = file.name ;

                const removeBtn = document.createElement('button');
                removeBtn.innerText = '❌';
                removeBtn.type = 'button';
                removeBtn.classList.add('btn', 'btn-sm', 'ms-2');
                removeBtn.onclick = function () {
                    fetch("{{ route('delete.image') }}", {
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
<script>
/* ---------------------------------------
   ROW TOTAL = QTY × RATE
--------------------------------------- */
function calculateRow(el) {
    const row = el.closest('tr');

    const qty  = parseFloat(row.querySelector('.qty')?.value || 0);
    const rate = parseFloat(row.querySelector('.rate')?.value || 0);

    const rowTotal = qty * rate;

    row.querySelector('.total').value = rowTotal.toFixed(2);

    updateAmountBeforeTax();
}

/* ---------------------------------------
   AMOUNT BEFORE TAX = SUM OF ROW TOTALS
--------------------------------------- */
function updateAmountBeforeTax() {
    let sum = 0;

    document.querySelectorAll('#materialTable tbody .total').forEach(input => {
        sum += parseFloat(input.value || 0);
    });

    document.getElementById('total_taxable').value = sum.toFixed(2);

    calculateGrandTotal();
}

/* ---------------------------------------
   GRAND TOTAL CALCULATION
--------------------------------------- */
function calculateGrandTotal() {
    const amountBeforeTax = parseFloat(document.getElementById('total_taxable')?.value || 0);
    const loading         = parseFloat(document.getElementById('loading')?.value || 0);
    const transport       = parseFloat(document.getElementById('transport')?.value || 0);
    const round           = parseFloat(document.getElementById('round')?.value || 0);

    const grandTotal = amountBeforeTax + loading + transport + round;

    document.getElementById('gtotal').value = grandTotal.toFixed(2);
}

/* ---------------------------------------
   AUTO RECALC ON PAGE LOAD (EDIT MODE)
--------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {
    updateAmountBeforeTax();
});

/* ---------------------------------------
   RECALC WHEN EXTRA CHARGES CHANGE
--------------------------------------- */
['loading', 'transport', 'round'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', calculateGrandTotal);
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // Add Row
    document.getElementById('addRow').addEventListener('click', function () {
        fetch('{{ route('Transfer_Material.addRow') }}', {
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
            const newRow = tbody.lastElementChild;

    initMaterialSelect2(newRow); // ONLY this

            if (typeof feather !== 'undefined') feather.replace();
        });
    });

    // Remove Row
    document.querySelector('#materialTable tbody').addEventListener('click', function (e) {
        if (e.target.closest('.removeRow')) {
            const row = e.target.closest('tr');
            if (document.querySelectorAll('#materialTable tbody tr').length > 1) {
                row.remove();
            }
        }
    });
});

$(document).on('input', '.qty, .rate', function () {
    chk_qty(this);
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
        const unit = $(this).find('option:selected').data('unit') || '';
        $(this).closest('tr').find('.mat-unit').val(unit);
        chk_qty(this);
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
        new Choices(document.querySelector(".choices-single-to_scheme"));
        new Choices(document.querySelector(".choices-single-from_scheme"));
        // document.querySelectorAll(".choices-single-material").forEach(el => new Choices(el));
    });
    
    function initMaterialSelect2(context = document) {
    $(context).find('.choices-single-material').each(function () {

        if ($(this).hasClass("select2-hidden-accessible")) {
            $(this).select2('destroy');
        }

        $(this).select2({
            placeholder: "Select Material",
            width: '100%'
        });
    });
}

$(document).ready(function () {
    initMaterialSelect2(document);
});
</script>

<script>
function chk_qty(el) {

    const row = el.closest("tr");

    const materialName = row.querySelector('[name="material_group[]"]')?.value;
    const typeName     = row.querySelector('[name="type[]"]')?.value;

    if (!materialName || !typeName) {
        row.querySelector('[name="avl_quantity[]"]').value = "";
        row.querySelector('.material_id').value = "";
        return;
    }

    // 🔹 STEP 1: Get material ID using Name + Type
    fetch("{{ route('material.getId') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            material_name: materialName,
            type_name: typeName
        })
    })
    .then(res => res.json())
    .then(mat => {

        if (!mat.material_id) {
            row.querySelector('[name="avl_quantity[]"]').value = 0;
            row.querySelector('.material_id').value = "";
            return;
        }

        // ✅ SET MATERIAL ID ON THE SAME ROW
        row.querySelector('.material_id').value = mat.material_id;

        // 🔹 STEP 2: Get stock
        return fetch("{{ route('material.getStock') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                matid: mat.material_id,
                edit_id: "{{ $transfer->ID ?? '' }}"
            })
        });
    })
    .then(res => res.json())
    .then(stock => {
        row.querySelector('[name="avl_quantity[]"]').value = stock.qty;
    })
    .catch(err => {
        console.error("Stock fetch error:", err);
    });
}

$(document).on("change", ".mat-type", function () {
    chk_qty(this);
});

$(document).on("blur", ".mat-unit", function () {
    chk_qty(this);
});

</script>
<script>
function validateQty(el) {

    let row = el.closest('tr');

    let qty = parseFloat(row.querySelector('.qty').value) || 0;

    let avlQty = parseFloat(row.querySelector('.avl_qty').value) || 0;

    if (qty > avlQty) {

        alert('Quantity should not be greater than available quantity.');

        row.querySelector('.qty').value = '';

        row.querySelector('.total').value = '';

        el.focus();
    }
}
  </script>