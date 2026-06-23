

<tr class="invoice-row" style="font-size:13px;">
    {{-- Checkbox --}}
    <td>
        <input type="checkbox"
            class="select-row"
            value="{{ $scopeVal }}"
            {{ $isChecked ? 'checked' : '' }}
            onchange="toggleRow(this)">
    </td>

    {{-- id --}}
    <td>
        <input type="hidden" name="id[]" value="{{ $id }}">
        <input type="text" class="form-control" value="{{ $id }}" readonly>
    </td>

    {{-- Payable --}}
    <td>
        <input type="text" name="payamount[]" class="form-control payamount"
            value="{{ $payamount }}" readonly>
    </td>

    {{-- Amount --}}
    <td>
        <input type="text" name="amt[]" class="form-control amt"
            value="{{ $amtVal }}"
            {{ $isChecked ? '' : 'disabled' }}
            oninput="calculateBalance(this)">
    </td>

    {{-- Extra --}}
    <td>
        <input type="text" name="extra[]" class="form-control amt"
            value="{{ $extra }}"
            {{ $isChecked ? '' : 'disabled' }}
            oninput="calculateBalance(this)">
    </td>

    {{-- Balance --}}
    <td>
        <input type="text" name="balance[]" class="form-control balance"
            value="{{ $balanceVal }}" readonly>
    </td>
</tr>
