@extends('backend.partials.master')

@section('title', !empty($material) ? 'Edit Material' : 'Create Material')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

    
        <h1 class="h3 mb-4">{{ !empty($material) ? 'Edit Material' : 'Create Material' }}</h1>

        <div class="card">
            <div class="card-body">

                <form action="{{ !empty($material) ? route('Material.update') : route('Material.store') }}"
                      method="POST">
                    @csrf
                   @if(!empty($materialRows) && is_object($materialRows->first()))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $materialRows->first()->ID }}">
                    @endif


                    {{-- ===== Material Name ===== --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Material Name <span class="text-danger">*</span></label>
                            <input type="text" name="material_name"
                            value="{{ old('material_name', $material_name ?? '') }}"
                            class="form-control @error('material_name') is-invalid @enderror">
                            @error('material_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    @if ($errors->any())
    @php
        $rowErrors = [];

        foreach ($errors->getMessages() as $key => $messages) {
            // Match type.0, unit.0, type.1, unit.1
            if (preg_match('/^(type|unit)\.(\d+)/', $key, $matches)) {
                $rowIndex = (int) $matches[2] + 1; // Row number (1-based)

                foreach ($messages as $msg) {
                    $rowErrors[$rowIndex][] = $msg;
                }
            }
        }
    @endphp

    @if (!empty($rowErrors))
        <div class="alert alert-danger py-2">
            @foreach ($rowErrors as $row => $messages)
                <div class="mb-1">
                    <strong>Row {{ $row }}:</strong>
                    <ul class="mb-1 ps-3">
                        @foreach ($messages as $msg)
                            <li>{{ $msg }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
@endif


                    {{-- ===== Type / Unit Table ===== --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" id="typeUnitTable">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width:45%">Type</th>
                                    <th style="width:45%">Unit</th>
                                    <th style="width:10%"></th>
                                </tr>
                            </thead>
                            <tbody id="typeUnitBody">
                                @php
                                    $rows = old('type') ? collect(old('type'))->map(function ($type, $i) use ($errors) {
                                        return ['type' => $type, 'unit' => old('unit')[$i] ?? ''];
                                    }) : $materialRows;
                                @endphp

                                @foreach($rows as $idx => $row)
                                    <tr>
                                        {{-- TYPE --}}
                                        <td>
                                            <input type="text" name="type[]"
                                                   value="{{ is_array($row) ? ($row['type'] ?? '') : $row->Type }}"
                                                   class="form-control form-control-sm">
                                        </td>

                                        {{-- UNIT --}}
                                        <td id="unit_wrap_{{ $idx }}">
                                            @php
                                                $currentUnit = is_array($row) ? ($row['unit'] ?? '') : $row->Unit;
                                            @endphp

                                            @if($currentUnit && !in_array($currentUnit, $units->toArray()))
                                                <input type="text" name="unit[]" value="{{ $currentUnit }}"
                                                       class="form-control form-control-sm">
                                            @else
                                                <select name="unit[]"
                                                        class="form-control form-control-sm unit-select select2-unit"
                                                        onchange="toggleUnitInput(this)">
                                                    <option value="">Select</option>
                                                    <option value="OTHER">ADD NEW</option>
                                                    @foreach($units as $u)
                                                        <option value="{{ $u }}"
                                                            {{ $currentUnit == $u ? 'selected' : '' }}>
                                                            {{ $u }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </td>

                                        {{-- DELETE --}}
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="removeRow(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- ADD BUTTON CENTERED --}}
                        <div class="text-center mt-2">
                            <button type="button" class="btn btn-outline-success btn-sm" id="addRowBtn">
                                <i class="bi bi-plus-circle"></i> Add Row
                            </button>
                        </div>
                    </div>

                    {{-- ===== Form Submit ===== --}}
                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            {{ !empty($material) ? 'Update' : 'Create' }}
                        </button>
                        <a href="{{ route('Material') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
{{-- Bootstrap icons (optional) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<script>
    let globalUnits = @json($units);
document.getElementById('addRowBtn').addEventListener('click', () => {
    const tbody = document.getElementById('typeUnitBody');
    const idx   = tbody.rows.length;

    const rowHtml = `
    <tr>
        <td><input type="text" name="type[]" class="form-control form-control-sm"></td>
        <td id="unit_wrap_${idx}">
            <select name="unit[]" class="form-control form-control-sm unit-select select2-unit"
                    onchange="toggleUnitInput(this)">
                <option value="">Select</option>
                <option value="OTHER">ADD NEW</option>
                ${globalUnits.map(u => `<option value="${u}">${u}</option>`).join('')}
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
        </tr>`;
    tbody.insertAdjacentHTML('beforeend', rowHtml);
    
    initSelect2(tbody.lastElementChild);

    initUnitChoices(tbody.lastElementChild);
});

function removeRow(btn) {
    const tbody = document.getElementById('typeUnitBody');
    if (tbody.rows.length <= 1) {
        alert("At least one row is required.");
        return;
    }
    btn.closest('tr').remove();
}

function toggleUnitInput(select) {
    if (select.value === 'OTHER') {
        const cell = select.closest('td');

        cell.innerHTML = `
            <input type="text" class="form-control form-control-sm new-unit-input"
                   placeholder="Enter new unit">`;

        const input = cell.querySelector('input');
        input.focus();

        input.addEventListener('blur', function () {
            const newUnit = this.value.trim();

            if (!newUnit) return;

            // duplicate avoid
            if (!globalUnits.includes(newUnit)) {
                globalUnits.push(newUnit);
            }

            // 🔥 dropdown पुन्हा तयार करतो
            let options = `<option value="">Select</option>
                           <option value="OTHER">ADD NEW</option>`;

            globalUnits.forEach(u => {
                options += `<option value="${u}" ${u === newUnit ? 'selected' : ''}>${u}</option>`;
            });

            // 👉 dropdown परत आणतो
            cell.innerHTML = `
                <select name="unit[]" class="form-control form-control-sm unit-select select2-unit"
                        onchange="toggleUnitInput(this)">
                    ${options}
                </select>
            `;

            // re-init select2
            initSelect2(cell);
        });
    }
}
</script>
@endpush
<script>
function initUnitChoices(context = document) {
    context.querySelectorAll('.choices-single-unit').forEach(el => {
        if (!el.classList.contains('choices-initialized')) {
            new Choices(el, {
                searchEnabled: true,
                shouldSort: false,
                itemSelectText: '',
            });
            el.classList.add('choices-initialized');
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initUnitChoices();
});

function initSelect2(context = document) {
    $(context).find('.select2-unit').select2({
        width: '100%',
        placeholder: "Select Unit",
        allowClear: true
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initSelect2();
});

</script>

<script>function updateAllUnitDropdowns(selectedValue = '') {
    document.querySelectorAll('.unit-select').forEach(select => {

        select.innerHTML = `<option value="">Select</option>
                            <option value="OTHER">ADD NEW</option>`;

        globalUnits.forEach(unit => {
            const option = document.createElement('option');
            option.value = unit;
            option.textContent = unit;

            if (unit === selectedValue) {
                option.selected = true;
            }

            select.appendChild(option);
        });

        $(select).trigger('change'); // refresh select2
    });
}</script>
