<tr class="invoice-row" style="font-size:13px;">
    
    {{-- Scope Details --}}
    <td>
        <input type="text" name="scope[]" class="form-control scope"
               value="{{ $scope ?? '' }}">
    </td>

    {{-- Lump-Sum --}}
    <td class="text-center">
        <input type="checkbox" name="lumpsum[]" class="ls-check"
               {{ !empty($lumpsum) ? 'checked' : '' }}>
    </td>

    {{-- Quantity --}}
    <td>
        <input type="number" name="qty[]" class="form-control qty"
               value="{{ $qty ?? '' }}"
               {{ !empty($lumpsum) ? 'readonly hidden' : '' }}
               oninput="calculateAmount(this)">
    </td>

    {{-- Unit --}}
    <td id="unit-container">
    <select name="unit[]" class="form-control unit unit-select choices-single-unit"
        onchange="unitCheck(this)"
        {{ !empty($lumpsum) ? 'readonly hidden' : '' }}>

        <option value="">Select Unit</option>

        {{-- Show only non-null units --}}
        @foreach($units as $u)
            @if(!empty($u->Unit))
                <option value="{{ $u->Unit }}"
                    {{ ($unit ?? '') == $u->Unit ? 'selected' : '' }}>
                    {{ $u->Unit }}
                </option>
            @endif
        @endforeach

        <option value="OTHER">Other</option>
    </select>
</td>


    {{-- Rate --}}
    <td>
        <input type="number" name="rate[]" class="form-control rate"
               value="{{ $rate ?? '' }}"
               {{ !empty($lumpsum) ? 'readonly hidden' : '' }}
               oninput="calculateAmount(this)">
    </td>

    {{-- Lump-Sum Amount --}}
    <td>
        <input type="number" name="ls_amount[]" class="form-control ls-amount"
               value="{{ $ls_amount ?? '' }}"
               {{ empty($lumpsum) ? 'readonly' : '' }}
               oninput="updateLSAmount(this)">
    </td>

    {{-- Amount --}}
    <td>
        <input type="number" name="amount[]" class="form-control amount"
               value="{{ $amount ?? '' }}"
               readonly>
    </td>

    {{-- Remove Row --}}
    <td>
        <button type="button" class="btn btn-sm removeRow">
            <i data-feather='trash'></i>
        </button>
    </td>
</tr>
