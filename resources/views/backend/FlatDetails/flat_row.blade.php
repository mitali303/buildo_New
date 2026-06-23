<tr id="prod_{{ $i }}">
    <td>
        {{ $flatType }}
        <input type="hidden" name="FlatType{{ $i }}" id="FlatType{{ $i }}" value="{{ $flatType }}">
    </td>
    <td><input type="text" name="Wing{{ $i }}" id="Wing{{ $i }}" class="form-control input-sm" value="{{ $flat->Wing ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}')" onblur="updateHiddenValues('prod_{{ $i }}')"></td>
    <td><input type="text" name="Floor{{ $i }}" id="Floor{{ $i }}" class="form-control input-sm number" value="{{ $flat->Floor ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}')" onblur="updateHiddenValues('prod_{{ $i }}')"></td>
    <td><input type="text" name="FlatNo{{ $i }}" id="FlatNo{{ $i }}" class="form-control input-sm number" value="{{ $flat->FlatNo ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}')" onblur="updateHiddenValues('prod_{{ $i }}')"></td>
    <td><input type="text" name="Area{{ $i }}" id="Area{{ $i }}" class="form-control input-sm number" value="{{ $flat->Area ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}');calTotalsq({{ $i }})" onblur="updateHiddenValues('prod_{{ $i }}');calTotalsq({{ $i }})"></td>
    <td><input type="text" name="Other1{{ $i }}" id="Other1{{ $i }}" class="form-control input-sm number" value="{{ $flat->Other1 ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}');calTotalsq({{ $i }})" onblur="updateHiddenValues('prod_{{ $i }}');calTotalsq({{ $i }})"></td>
    <td><input type="text" name="Other2{{ $i }}" id="Other2{{ $i }}" class="form-control input-sm number" value="{{ $flat->Other2 ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}');calTotalsq({{ $i }})" onblur="updateHiddenValues('prod_{{ $i }}');calTotalsq({{ $i }})"></td>
    <td><input type="text" name="Terrace{{ $i }}" id="Terrace{{ $i }}" class="form-control input-sm number" value="{{ $flat->Terrace ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}');calTotalsq({{ $i }})" onblur="updateHiddenValues('prod_{{ $i }}');calTotalsq({{ $i }})"></td>
    <td><input type="text" name="Attribute{{ $i }}" id="Attribute{{ $i }}" class="form-control input-sm number" value="{{ $flat->FlatAttribute ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}')" onblur="updateHiddenValues('prod_{{ $i }}')"></td>
    <td><input type="text" name="TotalSqFt{{ $i }}" id="TotalSqFt{{ $i }}" class="form-control input-sm number" readonly value="{{ $flat->TotalSqFt ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}')" onblur="updateHiddenValues('prod_{{ $i }}')"></td>
    <td><input type="text" name="TotalSqMtr{{ $i }}" id="TotalSqMtr{{ $i }}" class="form-control input-sm number" value="{{ $flat->TotalSqMtr ?? 0 }}" onkeyup="updateHiddenValues('prod_{{ $i }}')" onblur="updateHiddenValues('prod_{{ $i }}')"></td>
    <td>
    <button type="button"
        class="btn btn-primary btn-xs"
        onclick="{{ isset($flat) && isset($flat->ID) ? "updateFlatRow('$flat->ID', $i)" : "saveFlatRow($i)" }}"
        id="saveBtn{{ $i }}">
        {{ isset($flat) && isset($flat->ID) ? 'Update' : 'Save' }}
    </button>
</td>

</tr>
<input type="hidden" id="flatdet_{{ $i }}" name="flatdet_{{ $i }}" value="">
