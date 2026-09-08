@extends('backend.partials.master')

@section('title', $estimate->exists ? 'Edit Estimate' : 'New Estimate')

@section('maincontent')
@php
$items = old('items', $estimate->items ?: [['description' => '', 'qty' => 1, 'rate' => 0]]);
@endphp

<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <div class="text-uppercase small fw-semibold text-primary mb-1">
                    {{ $estimate->exists ? 'Update estimate' : 'New costing sheet' }}
                </div>
                <h1 class="h2 mb-1">{{ $estimate->exists ? 'Edit Estimate' : 'Create Estimate' }}</h1>
                <p class="text-muted mb-0">Select the scheme, enter the total area and add material details.</p>
            </div>
            <a href="{{ route('Estimate') }}" class="btn btn-light">
                <i data-feather="arrow-left" class="me-1"></i> Back
            </a>
        </div>

        @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the form.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ $estimate->exists ? route('Estimate.update', ['id' => $estimate->id]) : route('Estimate.store') }}" method="POST">
            @csrf
            @if($estimate->exists)
            @method('PUT')
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <h5 class="mb-3">Project details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Scheme name <span class="text-danger">*</span></label>
                                <select name="scheme_id" class="form-select" required>
                                    <option value="">Select scheme</option>
                                    @foreach($schemes as $scheme)
                                    <option value="{{ $scheme->ID }}" @selected((string) old('scheme_id', $estimate->scheme_id) === (string) $scheme->ID)>{{ $scheme->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estimate date <span class="text-danger">*</span></label>
                                <input type="date" name="date" value="{{ old('date', optional($estimate->date)->format('Y-m-d')) }}" class="form-control form-control-lg" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Customer name</label>
                                <input name="customer_name" value="{{ old('customer_name', $estimate->customer_name) }}" class="form-control" placeholder="Customer / owner name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Site address</label>
                                <textarea name="site_address" rows="2" class="form-control">{{ old('site_address', $estimate->site_address) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total built-up area (sqft) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="built_up_area" id="built-up-area" value="{{ old('built_up_area', $estimate->built_up_area ?? '') }}" class="form-control" required placeholder="e.g. 1200">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rate per sqft (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="rate_per_sqft" id="rate-per-sqft" value="{{ old('rate_per_sqft', $estimate->rate_per_sqft ?? '') }}" class="form-control" required placeholder="e.g. 1800">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">Work / material items</h5>
                                <small class="text-muted">Add each scope item with quantity and rate.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">
                                <i data-feather="plus"></i> Add item
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle" id="items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th style="width:140px">Material type</th>
                                        <th style="width:100px">Unit</th>
                                        <th style="width:120px">Qty</th>
                                        <th style="width:160px">Rate</th>
                                        <th style="width:160px" class="text-end">Amount</th>
                                        <th style="width:48px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                    <tr>
                                        <td>
                                            <input name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" class="form-control" required placeholder="e.g. RCC work">
                                        </td>
                                        <td>
                                            <input name="items[{{ $index }}][material_type]" value="{{ $item['material_type'] ?? '' }}" class="form-control" placeholder="e.g. Cement">
                                        </td>
                                        <td>
                                            <input name="items[{{ $index }}][unit]" value="{{ $item['unit'] ?? '' }}" class="form-control" placeholder="sqft">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0.01" name="items[{{ $index }}][qty]" value="{{ $item['qty'] ?? 1 }}" class="form-control item-qty" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="items[{{ $index }}][rate]" value="{{ $item['rate'] ?? 0 }}" class="form-control item-rate" required>
                                        </td>
                                        <td class="text-end fw-semibold item-amount">₹ 0.00</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove">
                                                <i data-feather="x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <h5 class="mb-3">Estimate summary</h5>
                            <div class="small text-muted mb-3">Total estimate = built-up area × rate per sqft</div>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Area (sqft)</label>
                                    <input type="text" id="area-summary" value="0.00" class="form-control" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Rate / sqft</label>
                                    <input type="text" id="rate-summary" value="₹ 0.00" class="form-control" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Construction amount</label>
                                    <input type="text" id="base-amount" value="₹ 0.00" class="form-control" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        @foreach(\App\Models\Backend\Estimate::statusLabels() as $value => $label)
                                        <option value="{{ $value }}" @selected((string) old('status', $estimate->status) === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tax / GST (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="tax_percent" id="tax-percent" value="{{ old('tax_percent', $estimate->tax_percent ?? 0) }}" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Material total</label>
                                    <input type="text" id="material-total" value="₹ 0.00" class="form-control" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Subtotal</label>
                                    <input type="text" id="subtotal" value="₹ 0.00" class="form-control" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tax</label>
                                    <input type="text" id="tax-amount" value="₹ 0.00" class="form-control" readonly>
                                </div>
                                <div class="col-md-3 text-md-small">
                                    <label class="form-label">Grand total</label>
                                    <input type="text" id="total" value="₹ 0.00" class="form-control fw-bold text-primary" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-4 pt-3 border-top">
                            <div class="col-lg-8">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" rows="3" class="form-control" placeholder="Validity, payment terms or special conditions">{{ old('notes', $estimate->notes) }}</textarea>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <a href="{{ route('Estimate') }}" class="btn btn-light border px-5 py-2 me-2">
                                        <i data-feather="x" class="me-1"></i>
                                        Cancel
                                    </a>
                                    <button class="btn btn-primary px-5 py-2">
                                        <i data-feather="check" class="me-1"></i>
                                        {{ $estimate->exists ? 'Update Estimate' : 'Save Estimate' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</main>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.querySelector('#items-table tbody');
        const money = value => '₹ ' + Number(value || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        function recalculate() {
            const builtUpArea = parseFloat(document.querySelector('#built-up-area').value) || 0;
            const ratePerSqft = parseFloat(document.querySelector('#rate-per-sqft').value) || 0;
            const constructionAmount = builtUpArea * ratePerSqft;
            let materialTotal = 0;

            tableBody.querySelectorAll('tr').forEach(row => {
                const quantity = parseFloat(row.querySelector('.item-qty').value) || 0;
                const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
                const amount = quantity * rate;

                materialTotal += amount;
                row.querySelector('.item-amount').textContent = money(amount);
            });

            const subtotal = constructionAmount + materialTotal;
            const taxPercent = parseFloat(document.querySelector('#tax-percent').value) || 0;
            const tax = subtotal * (taxPercent / 100);

            document.querySelector('#area-summary').value = Number(builtUpArea).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
            document.querySelector('#rate-summary').value = money(ratePerSqft);
            document.querySelector('#base-amount').value = money(constructionAmount);
            document.querySelector('#material-total').value = money(materialTotal);
            document.querySelector('#subtotal').value = money(subtotal);
            document.querySelector('#tax-amount').value = money(tax);
            document.querySelector('#total').value = money(subtotal + tax);
        }

        function bindRow(row) {
            row.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', recalculate);
            });

            row.querySelector('.remove-item').addEventListener('click', () => {
                if (tableBody.querySelectorAll('tr').length > 1) {
                    row.remove();
                    renumber();
                    recalculate();
                }
            });
        }

        function renumber() {
            tableBody.querySelectorAll('tr').forEach((row, index) => {
                row.querySelectorAll('input').forEach(input => {
                    input.name = input.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                });
            });
        }

        tableBody.querySelectorAll('tr').forEach(bindRow);
        document.querySelector('#built-up-area').addEventListener('input', recalculate);
        document.querySelector('#rate-per-sqft').addEventListener('input', recalculate);
        document.querySelector('#tax-percent').addEventListener('input', recalculate);
        document.querySelector('#add-item').addEventListener('click', () => {
            const index = tableBody.querySelectorAll('tr').length;
            const row = document.createElement('tr');

            row.innerHTML = '<td><input name="items[' + index + '][description]" class="form-control" required placeholder="e.g. RCC work"></td>' +
                '<td><input name="items[' + index + '][material_type]" class="form-control" placeholder="e.g. Cement"></td>' +
                '<td><input name="items[' + index + '][unit]" class="form-control" placeholder="sqft"></td>' +
                '<td><input type="number" step="0.01" min="0.01" name="items[' + index + '][qty]" value="1" class="form-control item-qty" required></td>' +
                '<td><input type="number" step="0.01" min="0" name="items[' + index + '][rate]" value="0" class="form-control item-rate" required></td>' +
                '<td class="text-end fw-semibold item-amount">₹ 0.00</td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i data-feather="x"></i></button></td>';

            tableBody.appendChild(row);
            bindRow(row);

            if (window.feather) {
                feather.replace();
            }
        });

        recalculate();

        if (window.feather) {
            feather.replace();
        }
    });
</script>
@endsection