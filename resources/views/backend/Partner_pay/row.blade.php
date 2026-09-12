@php
    // Detect if row is from EDIT mode or CREATE mode
    $isEdit = isset($row);

    $scopeVal    = $isEdit ? $row['scope']       : ($scope ?? '');
    $scopeText   = $isEdit ? $row['scope_text']  : ($scope ?? '');
    $payamount   = $isEdit ? $row['payamount']   : ($payamount ?? 0);
    $amtVal      = $isEdit ? $row['amt']         : ($amt ?? 0);
    $balanceVal  = $isEdit ? $row['balance']     : ($balance ?? '');
    $isChecked   = $isEdit ? $row['checked']     : false;
@endphp

<tr class="invoice-row" style="font-size:13px;">
    {{-- Checkbox --}}
    <td>
        <input type="checkbox" class="select-row" value="{{ $scopeVal }}" {{ $isChecked ? 'checked' : '' }} onchange="toggleRow(this)">
    </td>
    {{-- Scope --}}
    <td>
        <input type="hidden" name="scope[]" value="{{ $scopeVal }}">
        <input type="text" class="form-control" value="{{ $scopeText }}" readonly>
    </td>
    {{-- Payable --}}
    <td>
        <input type="text" name="payamount[]" class="form-control payamount" value="{{ $payamount }}" readonly>
    </td>
    {{-- Amount --}}
    <td>
        <input type="text" name="amt[]" class="form-control amt" value="{{ $amtVal }}" {{ $isChecked ? '' : 'disabled' }}
            oninput="calculateBalance(this)">
    </td>
    {{-- Balance --}}
    <td>
        <input type="text" name="balance[]" class="form-control balance" value="{{ $balanceVal }}" readonly>
    </td>
</tr>
