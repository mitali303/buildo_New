@extends('backend.partials.master')

@section('title', !empty($invoice) ? 'Edit Rejected Material' : 'Create Rejected Material')
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
        <h1 class="h3 mb-3">{{ !empty($invoice) ? 'Edit Return Material' : 'Create Return Material' }}</h1>

        <div class="card">
            <div class="card-body">
                

                <form action="{{ !empty($invoice) ? route('Rejected_Material.update') : route('Rejected_Material.store') }}" method="POST">
                    @csrf
                    @if(!empty($invoice))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $invoice->ID }}">
                    @endif

                    <div class="row">
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
                            <label class="form-label">SR No.</label>
                               <input type="text" name="srno" readonly class="input-sm form-control" value="{{ old('srno', !empty($invoice) ? $invoice->srno : ($nextsrno ?? '')) }}">
                            @error('srno') <small class="text-danger">{{ $message }}</small> @enderror
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
                                            {{ old('Destination', $invoice->destination ?? $schemes[0]->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                       <div class="col-md-4" id="po_div">
                            <label class="form-label">Purchase From <span class="text-danger">*</span></label>

                            {{-- <select name="purchasefrom" id="purchasefrom"
                                    class="form-control choices-single-purchasefrom"
                                    data-placeholder="Select purchasefrom"
                                    onchange="GetInvoice()" 
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
                            data-placeholder="Select purchasefrom" onchange="GetInvoice()" >

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


                        <div class="mb-3 col-md-4" id="Inv_div">
                            <label class="form-label">Invoice No.<span class="text-danger">*</span></label>
                            <select name="inv_no" id="inv_no" class="form-control" onchange="GetInvoiceDetails();">
    <option value="">Select</option>

    @if(isset($invoiceNumbers))
        <option value="{{ $invoiceNumbers->ID }}"
            {{ old('inv_no', $Invno ?? '') == $invoiceNumbers->ID ? 'selected' : '' }}>
            {{ $invoiceNumbers->Invno }}
        </option>
    @endif
</select>

                @error('inv_no') <small class="text-danger">{{ $message }}</small> @enderror
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
                                            'return_qty'     => 'Return Quantity',
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
                                            <th style="min-width: 90px;">Return Quantity</th>
                                            <th style="min-width: 120px;">Unit</th>
                                            <th style="min-width: 100px;">Rate</th>
                                            <th style="min-width: 100px;">Disc %</th>
                                            <th style="min-width: 120px;">Taxable Amount</th>
                                            <th style="min-width: 70px;">CGST %</th>
                                            <th style="min-width: 70px;">SGST %</th>
                                            <th style="min-width: 70px;">IGST %</th>
                                            <th style="min-width: 120px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                     @php
    $rowCount = old('material_group') ? count(old('material_group')) : count($products ?? []);
@endphp

@for ($i = 0; $i < max(1, $rowCount); $i++)
    @php
        $hasProduct = isset($products[$i]);
        $materialName = old("material_group.$i") ?? ($hasProduct ? ($materialIdToName[$products[$i]->Material] ?? '') : '');
        $types = $materialNameToTypes[$materialName] ?? collect();
        $type = old("type.$i") ?? ($hasProduct ? ($materialIdToType[$products[$i]->Material] ?? '') : '');
        $unit = old("unit.$i") ?? ($types->where('Type', $type)->first()->Unit ?? ($hasProduct ? $products[$i]->Unit ?? '' : ''));

        $qty         = old("quantity.$i") ?? ($hasProduct ? $products[$i]->Qty : '');
        $returnQty   = old("return_qty.$i") ?? ($hasProduct ? $products[$i]->rejected_qty : '');
        $rate        = old("rate.$i") ?? ($hasProduct ? $products[$i]->Rate : '');
        $disc        = old("discount.$i") ?? ($hasProduct ? $products[$i]->Disc : '');
        $taxable     = old("taxable.$i") ?? ($hasProduct ? $products[$i]->Amount : '');
        $cgst        = old("cgst.$i") ?? ($hasProduct ? $products[$i]->CGST : '');
        $sgst        = old("sgst.$i") ?? ($hasProduct ? $products[$i]->SGST : '');
        $igst        = old("igst.$i") ?? ($hasProduct ? $products[$i]->IGST : '');
        $total       = old("total.$i") ?? ($hasProduct ? $products[$i]->Total : '');
    @endphp

    <tr>
        <input type="hidden" name="detail_id[]" value="{{ $products[$i]->ID ?? '' }}">

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
        <td>
            <input type="text" name="quantity[]" class="form-control qty" value="{{ $qty }}" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="text" name="return_qty[]" class="form-control return-qty" value="{{ $returnQty }}" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="text" name="unit[]" class="form-control mat-unit" value="{{ $unit }}" readonly>
        </td>
        <td>
            <input type="text" name="rate[]" class="form-control rate" value="{{ $rate }}" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="text" name="discount[]" class="form-control discount" value="{{ $disc }}" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="text" name="taxable[]" class="form-control taxable" value="{{ $taxable }}" readonly>
        </td>
        <td>
            <input type="text" name="cgst[]" class="form-control cgst" value="{{ $cgst }}" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="text" name="sgst[]" class="form-control sgst" value="{{ $sgst }}" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="text" name="igst[]" class="form-control igst" value="{{ $igst }}" oninput="calculateRow(this)">
        </td>
        <td>
            <input type="text" name="total[]" class="form-control total" value="{{ $total }}" readonly>
        </td>
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
                    <input type="hidden" name="cnt" id="cnt">


                    {{-- Submit --}}  
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($invoice) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('Rejected_Material') }}" class="btn btn-secondary">Cancel</a>
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
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-purchasefrom"));
        new Choices(document.querySelector(".choices-single-destination"));
        // document.querySelectorAll(".choices-single-material").forEach(el => new Choices(el));
    });

    function initMaterialSelect2(context = document) {
    $(context).find('.choices-single-material').each(function () {

        // prevent double init
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
    initMaterialSelect2();
});
</script>
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

   function GetInvoice() {
    let purchaseFrom = document.getElementById('purchasefrom').value;
    let schemeID     = document.getElementById('Destination').value;
    let invSelect    = document.getElementById('inv_no');

    if (!purchaseFrom || !schemeID) {
        invSelect.innerHTML = '<option value="">Select</option>';
        return;
    }

    fetch("{{ route('Rejected_Material.getInvoice') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            SchemeID: schemeID,
            purchaseFrom: purchaseFrom,
            currentInvId: OLD_INV_ID   // ✅ FIX
        })
    })
    .then(res => res.json())
    .then(data => {
        invSelect.innerHTML = data.html;

        // ✅ GUARANTEED reselect
        requestAnimationFrame(() => {
            if (OLD_INV_ID) {
                invSelect.value = OLD_INV_ID;
            }
        });
    })
    .catch(console.error);
}


    </script>

<script>
// Always update CNT based on actual table rows
function updateRowCount() {
    let rowCount = document.querySelectorAll("#materialTable tbody tr").length;
    document.getElementById("cnt").value = rowCount;
    console.log("CNT UPDATED:", rowCount);
}

// Update CNT on page load
document.addEventListener("DOMContentLoaded", updateRowCount);

// Update CNT after invoice rows load
function GetInvoiceDetails() { 
    let invID = document.getElementById("inv_no").value;

    if (!invID) return;

    fetch("{{ route('Rejected_Material.getInvoiceDetails') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ invID })
    })
    .then(res => res.json())
    .then(data => {

        let tbody = document.querySelector("#materialTable tbody");
        tbody.innerHTML = "";

        data.rows.forEach(row => {
            let tr = `
            <tr>
                <td><input type="text" name="material_group[]" class="form-control" value="${row.material_name}" readonly></td>
                <input type="hidden" name="material_id[]" class="form-control" value="${row.material_id}" readonly>
                <td><input type="text" name="type[]" class="form-control" value="${row.type}" readonly></td>
                <td><input type="text" name="quantity[]" class="form-control qty" value="${row.qty}" oninput="calculateRow(this)" readonly></td>
                <td><input type="text" name="return_qty[]" class="form-control return-qty" oninput="calculateRow(this)" value="0"></td>
                <td><input type="text" name="unit[]" class="form-control" value="${row.unit}" readonly></td>
                <td><input type="text" name="rate[]" class="form-control rate" value="${row.rate}" oninput="calculateRow(this)"></td>
                <td><input type="text" name="discount[]" class="form-control discount" value="${row.disc}" oninput="calculateRow(this)"></td>
                <td><input type="text" name="taxable[]" class="form-control taxable" value="${row.amount}" readonly></td>
                <td><input type="text" name="cgst[]" class="form-control cgst" value="${row.cgst}" oninput="calculateRow(this)"></td>
                <td><input type="text" name="sgst[]" class="form-control sgst" value="${row.sgst}" oninput="calculateRow(this)"></td>
                <td><input type="text" name="igst[]" class="form-control igst" value="${row.igst}" oninput="calculateRow(this)"></td>
                <td><input type="text" name="total[]" class="form-control total" value="0" readonly></td>
            </tr>
            `;

            tbody.insertAdjacentHTML("beforeend", tr);
        });

        updateRowCount(); // FIXED — update hidden CNT
    });
}
</script>
<script>


function makeFieldsReadonly(tr) {
    tr.querySelectorAll("input, select").forEach(inp => {
        if (!inp.name.includes("return_qty")) {
            inp.setAttribute("readonly", true);
            inp.classList.add("bg-light");
        }
    });
}

function calculateRow(input) {
    let tr = input.closest("tr");

    let qty     = parseFloat(tr.querySelector(".qty").value)     || 0;
    let rQty    = parseFloat(tr.querySelector(".return-qty").value) || 0;
    let rate    = parseFloat(tr.querySelector(".rate").value)    || 0;
    let disc    = parseFloat(tr.querySelector(".discount").value)|| 0;
    let cgst    = parseFloat(tr.querySelector(".cgst").value)    || 0;
    let sgst    = parseFloat(tr.querySelector(".sgst").value)    || 0;
    let igst    = parseFloat(tr.querySelector(".igst").value)    || 0;

    if (rQty > qty) {
        tr.querySelector(".return-qty").value = qty;
        rQty = qty;
    }

    // FULL values first
    let grossFull     = qty * rate;
    let discFull      = (grossFull * disc) / 100;
    let taxableFull   = grossFull - discFull;

    // Return values proportionate
    let ratio   = qty === 0 ? 0 : (rQty / qty);

    let taxable = taxableFull * ratio;

    let cgstAmt = taxable * (cgst / 100);
    let sgstAmt = taxable * (sgst / 100);
    let igstAmt = taxable * (igst / 100);

    let total = taxable + cgstAmt + sgstAmt + igstAmt;

    tr.querySelector(".taxable").value = taxable.toFixed(2);
    // tr.querySelector(".total").value   = total.toFixed(2);
    tr.querySelector(".total").value = Math.round(total);

}

document.addEventListener("DOMContentLoaded", function () {
    if (
        document.getElementById('purchasefrom').value &&
        document.getElementById('Destination').value
    ) {
        GetInvoice();
    }
});

</script>

<script>
    const OLD_INV_ID = "{{ old('inv_no', $Invno ?? '') }}";
</script>

