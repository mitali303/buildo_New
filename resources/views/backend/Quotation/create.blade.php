@extends('backend.partials.master')

@section('title')
    Create Quotation
@endsection

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

<div class="mb-3">
    <h1 class="h3 d-inline align-middle">
        {{ !empty($old) ? 'Edit Quotation' : 'Create Quotation' }}
    </h1>
</div>

<div class="row">
<div class="col-md-12">

<div class="card">
<div class="card-body">

<form action="{{ !empty($old) ? route('Quotation.update') : route('Quotation.store') }}" method="POST" id="quotation-form">
    @csrf

    @if(!empty($old))
        @method('PUT')
        <input type="hidden" name="id" value="{{ $old->id }}">
    @endif

<div class="row">

    {{-- Estimate (drives everything) --}}
    <div class="mb-3 col-md-4">
        <label class="form-label">Estimate <small class="text-danger">*</small></label>

        <select name="estimate_id" id="estimate_id" class="form-control choices-single @error('estimate_id') is-invalid @enderror">
            <option value="">Select Approved Estimate</option>
            @foreach($estimates as $estimate)
                <option value="{{ $estimate->id }}"
                    data-firm-name="{{ $estimate->firm->firm_name ?? '-' }}"
                    data-customer-name="{{ $estimate->customer->name ?? '-' }}"
                    {{ old('estimate_id',$old->estimate_id ?? '')==$estimate->id ? 'selected' : '' }}>
                    {{ $estimate->estimate_no }} — {{ $estimate->firm->firm_name ?? '' }}
                </option>
            @endforeach
        </select>

        @error('estimate_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- Firm - READ ONLY, always pulled from the selected estimate --}}
    <div class="mb-3 col-md-3">
        <label class="form-label">Firm</label>
        <input type="text" id="firm_name_display" class="form-control bg-light" readonly
               value="{{ $old->firm->firm_name ?? '' }}" placeholder="Auto-filled from Estimate">
    </div>

    {{-- Customer - READ ONLY too, for reference --}}
    <div class="mb-3 col-md-3">
        <label class="form-label">Customer</label>
        <input type="text" id="customer_name_display" class="form-control bg-light" readonly
               value="{{ $old->customer->name ?? '' }}" placeholder="Auto-filled from Estimate">
    </div>

    {{-- Date --}}
    <div class="mb-3 col-md-2">
        <label class="form-label">Date <small class="text-danger">*</small></label>
        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
               value="{{ old('date',$old->date ?? date('Y-m-d')) }}">
        @error('date')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- Type --}}
    <div class="mb-3 col-md-3">
        <label class="form-label">Quotation Type <small class="text-danger">*</small></label>

        <select name="type" id="quotation_type" class="form-control choices-single @error('type') is-invalid @enderror">
            <option value="">Select Type</option>
            <option value="1" {{ old('type',$old->type ?? '')=='1' ? 'selected' : '' }}>Supply</option>
            <option value="2" {{ old('type',$old->type ?? '')=='2' ? 'selected' : '' }}>Installation</option>
        </select>

        @error('type')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- Status --}}
    <div class="mb-3 col-md-3">
        <label class="form-label">Status <small class="text-danger">*</small></label>

        <select name="status" class="form-control choices-single @error('status') is-invalid @enderror">
            <option value="1" {{ old('status',$old->status ?? '1')=='1' ? 'selected' : '' }}>Draft</option>
            <option value="2" {{ old('status',$old->status ?? '')=='2' ? 'selected' : '' }}>Sent</option>
            <option value="3" {{ old('status',$old->status ?? '')=='3' ? 'selected' : '' }}>Approved</option>
            <option value="0" {{ old('status',$old->status ?? '')=='0' ? 'selected' : '' }}>Cancelled</option>
        </select>

        @error('status')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

</div>

<hr>

{{-- ============ ESTIMATE WORKS (Installation type only, read-only) ============ --}}
<div id="works-section" style="display:none;">
    <h5 class="mb-3">Estimate Works <span class="text-muted small">(from the selected estimate — read only)</span></h5>
    <div id="works-display"></div>

    <div class="card bg-light border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-2">
            <span class="fw-bold">Works Total</span>
            <span class="fw-bold text-secondary" id="works-total-display">₹ 0.00</span>
        </div>
    </div>
</div>

{{-- ============ MATERIAL SELECTION (always shown; required for Supply) ============ --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Material Selection</h5>
    <button type="button" class="btn btn-sm btn-primary" id="add-material-btn">
        <i class="fas fa-plus"></i> Add Material
    </button>
</div>

@error('materials')
    <div class="text-danger mb-3">{{ $message }}</div>
@enderror

<div id="materials-container"></div>

<button type="button" class="btn btn-sm btn-outline-secondary mb-3 d-none" id="no-material-hint"></button>

<div class="card bg-light border-0 mb-2">
    <div class="card-body d-flex justify-content-between align-items-center py-2">
        <span class="fw-bold">Material Total</span>
        <span class="fw-bold text-secondary" id="material-total-display">₹ 0.00</span>
    </div>
</div>

<div class="card bg-primary bg-opacity-10 border-0 mb-4">
    <div class="card-body d-flex justify-content-between align-items-center py-3">
        <span class="h6 mb-0">Grand Total</span>
        <span class="h4 mb-0 text-primary" id="grand-total">₹ 0.00</span>
    </div>
</div>

<div>
<button type="submit" class="btn btn-primary">
    {{ !empty($old) ? 'Update' : 'Save' }} Quotation
</button>

<a href="{{ route('Quotation') }}" class="btn btn-secondary">
    Cancel
</a>
</div>

</form>

</div>
</div>

</div>
</div>

</div>
</main>
@endsection

{{-- Read-only work card template (Installation type) --}}
<template id="work-display-template">
    <div class="card mb-2 border-secondary-subtle">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-bold work-title"></div>
                    <div class="small text-muted work-description"></div>
                    <div class="small text-muted">Qty: <span class="work-qty"></span></div>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Sale Price</div>
                    <div class="fw-bold work-sale-price"></div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- Material row card template (editable) --}}
<template id="material-row-template">
    <div class="card material-row mb-3">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary material-index">Material 1</span>
                <a href="#" class="text-danger remove-material-btn" title="Remove this material">
                    <i class="fas fa-trash"></i> Remove
                </a>
            </div>

            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Item</label>
                    <select class="form-control item-select" data-field="item_id">
                        <option value="">Select Item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}"
                                    data-rate="{{ $item->sale_rate }}"
                                    data-gst="{{ $item->gst_percent }}">
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Qty</label>
                    <input type="number" step="0.01" min="0.01" class="form-control calc-input" data-field="qty" value="1">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Rate</label>
                    <input type="number" step="0.01" min="0" class="form-control calc-input" data-field="rate" value="0">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">GST %</label>
                    <input type="number" step="0.01" min="0" class="form-control calc-input" data-field="gst_percent" value="0">
                </div>

            </div>

            <div class="row g-3 mt-1">
                <div class="col-6">
                    <div class="bg-light rounded p-2 text-center">
                        <div class="small text-muted">Amount (before GST)</div>
                        <div class="fw-bold row-amount">0.00</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="bg-primary bg-opacity-10 rounded p-2 text-center">
                        <div class="small text-muted">Total (with GST)</div>
                        <div class="fw-bold text-primary row-total">0.00</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
document.addEventListener("DOMContentLoaded", function () {

    new Choices(document.querySelector('[name="estimate_id"]'));
    new Choices(document.querySelector('[name="type"]'));
    new Choices(document.querySelector('[name="status"]'));

    const estimateSelectRaw = document.getElementById('estimate_id');
    const typeSelectRaw = document.getElementById('quotation_type');
    const worksSection = document.getElementById('works-section');
    const worksDisplay = document.getElementById('works-display');
    const worksTemplate = document.getElementById('work-display-template');
    const materialsContainer = document.getElementById('materials-container');
    const materialTemplate = document.getElementById('material-row-template');
    const form = document.getElementById('quotation-form');

    let currentWorksTotal = 0;
    let currentWorksData = [];

    // ---------- ESTIMATE -> firm/customer readonly fill + works fetch ----------
    function fetchEstimateDetails(estimateId, applyType) {
        if (!estimateId) {
            document.getElementById('firm_name_display').value = '';
            document.getElementById('customer_name_display').value = '';
            currentWorksTotal = 0;
            currentWorksData = [];
            renderWorks();
            recalcGrandTotal();
            return;
        }

        fetch("{{ url('/Quotation/estimate-details') }}/" + estimateId)
            .then(res => res.json())
            .then(data => {
                document.getElementById('firm_name_display').value = data.firm_name;
                document.getElementById('customer_name_display').value = data.customer_name;
                currentWorksTotal = parseFloat(data.works_total) || 0;
                currentWorksData = data.works || [];
                renderWorks();
                recalcGrandTotal();
            });
    }

    function renderWorks() {
        worksDisplay.innerHTML = '';
        currentWorksData.forEach(function (work) {
            const clone = worksTemplate.content.cloneNode(true);
            clone.querySelector('.work-title').innerText = work.work_title;
            clone.querySelector('.work-description').innerText = work.description || '';
            clone.querySelector('.work-qty').innerText = work.qty;
            clone.querySelector('.work-sale-price').innerText = '₹ ' + parseFloat(work.sale_price).toFixed(2);
            worksDisplay.appendChild(clone);
        });
        document.getElementById('works-total-display').innerText = '₹ ' + currentWorksTotal.toFixed(2);
    }

    function toggleWorksSection() {
        const isInstallation = typeSelectRaw.value == '2';
        worksSection.style.display = isInstallation ? 'block' : 'none';
    }

    estimateSelectRaw.addEventListener('change', function () {
        fetchEstimateDetails(this.value);
    });

    typeSelectRaw.addEventListener('change', function () {
        toggleWorksSection();
        recalcGrandTotal();
    });

    // ---------- MATERIAL ROWS ----------
    function addMaterialRow(data = {}) {
        const clone = materialTemplate.content.cloneNode(true);
        const row = clone.querySelector('.material-row');

        row.querySelectorAll('[data-field]').forEach(function (el) {
            const field = el.dataset.field;
            if (data[field] !== undefined && data[field] !== null) {
                el.value = data[field];
            }
        });

        materialsContainer.appendChild(row);
        recalcMaterialRow(row);
        renumberMaterials();
    }

    function renumberMaterials() {
        materialsContainer.querySelectorAll('.material-row').forEach(function (row, index) {
            row.querySelector('.material-index').innerText = 'Material ' + (index + 1);
        });
    }

    function recalcMaterialRow(row) {
        const val = (field) => parseFloat(row.querySelector('[data-field="' + field + '"]').value) || 0;

        const qty = val('qty') || 1;
        const rate = val('rate');
        const gstPercent = val('gst_percent');

        const amount = qty * rate;
        const total = amount + (amount * gstPercent / 100);

        row.querySelector('.row-amount').innerText = amount.toFixed(2);
        row.querySelector('.row-total').innerText = total.toFixed(2);

        recalcGrandTotal();
    }

    function recalcGrandTotal() {
        let materialTotal = 0;
        materialsContainer.querySelectorAll('.material-row').forEach(function (row) {
            materialTotal += parseFloat(row.querySelector('.row-total').innerText) || 0;
        });

        const isInstallation = typeSelectRaw.value == '2';
        const worksPortion = isInstallation ? currentWorksTotal : 0;
        const grand = worksPortion + materialTotal;

        document.getElementById('material-total-display').innerText = '₹ ' + materialTotal.toFixed(2);
        document.getElementById('grand-total').innerText = '₹ ' + grand.toFixed(2);
    }

    materialsContainer.addEventListener('input', function (e) {
        if (e.target.classList.contains('calc-input')) {
            recalcMaterialRow(e.target.closest('.material-row'));
        }
    });

    // Auto-fill rate & gst when an item is picked
    materialsContainer.addEventListener('change', function (e) {
        if (e.target.classList.contains('item-select')) {
            const row = e.target.closest('.material-row');
            const selected = e.target.options[e.target.selectedIndex];
            const rateInput = row.querySelector('[data-field="rate"]');
            const gstInput = row.querySelector('[data-field="gst_percent"]');

            if (selected.value && (!rateInput.value || rateInput.value == '0')) {
                rateInput.value = selected.dataset.rate || 0;
            }
            if (selected.value && (!gstInput.value || gstInput.value == '0')) {
                gstInput.value = selected.dataset.gst || 0;
            }
            recalcMaterialRow(row);
        }
    });

    materialsContainer.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-material-btn');
        if (btn) {
            e.preventDefault();
            btn.closest('.material-row').remove();
            renumberMaterials();
            recalcGrandTotal();
        }
    });

    document.getElementById('add-material-btn').addEventListener('click', function () {
        addMaterialRow();
    });

    // ---------- INITIAL STATE ----------
    const existingMaterials = @json(old('materials', optional($old ?? null)->materials ?? []));

    if (existingMaterials && existingMaterials.length > 0) {
        existingMaterials.forEach(function (material) {
            addMaterialRow(material);
        });
    }

    toggleWorksSection();

    if (estimateSelectRaw.value) {
        fetchEstimateDetails(estimateSelectRaw.value);
    }

    // Re-index rows into materials[i][field] right before submit
    form.addEventListener('submit', function () {
        materialsContainer.querySelectorAll('.material-row').forEach(function (row, index) {
            row.querySelectorAll('[data-field]').forEach(function (el) {
                el.name = 'materials[' + index + '][' + el.dataset.field + ']';
            });
        });
    });

});
</script>