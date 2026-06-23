<!-- <tr>
    <td style="min-width:175px;">
        <select name="material_group[]" class="form-control choices-single-material" data-placeholder="Select Material">
            <option value="">select</option>
            @foreach($materials as $mat)
                <option value="{{ $mat->Name }}">{{ $mat->Name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="type[]" class="form-control form-control-sm mat-type">
            <option value="">Select Type</option>
        </select>
    </td>
    <td><input type="text" name="quantity[]" class="form-control form-control-sm qty" min="0" oninput="calculateRow(this)"></td>
    <td><input type="text" name="unit[]" class="form-control form-control-sm mat-unit" readonly></td>
    <td><input type="text" name="rate[]" class="form-control form-control-sm rate" step="0.01" min="0" oninput="calculateRow(this)"></td>
    <td><input type="text" name="discount[]" class="form-control form-control-sm discount" step="0.01" min="0" oninput="calculateRow(this)"></td>
    <td><input type="text" name="taxable[]" class="form-control form-control-sm taxable" readonly></td>
    <td><input type="text" name="cgst[]" class="form-control form-control-sm cgst" step="0.01" min="0" onchange="calculateRow(this)"></td>
    <td><input type="text" name="sgst[]" class="form-control form-control-sm sgst" step="0.01" min="0" onchange="calculateRow(this)"></td>
    <td><input type="text" name="igst[]" class="form-control form-control-sm igst" step="0.01" min="0" onchange="calculateRow(this)"></td>
    <td><input type="text" name="total[]" class="form-control form-control-sm total" readonly></td>
    <td><button type="button" class="btn btn-sm  removeRow"><i data-feather='trash'></i></button></td>
</tr> -->


<tr style="font-size:13px;">
    <td style="min-width:175px;">
        <select name="material_group[]" class="form-control choices-single-material">
            <option value="">select</option>
            @foreach($materials as $mat)
                <option value="{{ $mat->Name }}">{{ $mat->Name }}</option>
            @endforeach
        </select>
    </td>
    <td><select name="type[]" class="form-control mat-type"><option value="">Select Type</option></select></td>
    <td><input type="text" name="quantity[]" class="form-control qty" oninput="calculateRow(this)"></td>
    <td><input type="text" name="unit[]" class="form-control mat-unit" readonly></td>
    <td><input type="text" name="rate[]" class="form-control rate" oninput="calculateRow(this)"></td>
    <td><input type="text" name="discount[]" class="form-control discount" value="0" oninput="calculateRow(this)"></td>
    <td><input type="text" name="taxable[]" class="form-control taxable" readonly></td>
    <td><input type="text" name="cgst[]" class="form-control cgst" value="0" onchange="calculateRow(this)"></td>
    <td><input type="text" name="sgst[]" class="form-control sgst" value="0" onchange="calculateRow(this)"></td>
    <td><input type="text" name="igst[]" class="form-control igst" value="0" onchange="calculateRow(this)"></td>
    <td><input type="text" name="total[]" class="form-control total" readonly></td>
    <td><button type="button" class="btn btn-sm removeRow"><i data-feather='trash'></i></button></td>
</tr>
