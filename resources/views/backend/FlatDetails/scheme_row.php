<tr id="prod_<?php echo $uid ?>">
    <input type="hidden" name="row_ids[]" value="<?php echo $uid ?>">

    <td id="ftypedv<?php echo $uid ?>">
        <select name="ftype<?php echo $uid ?>" class="form-control chosen-select required" id="ftype<?php echo $uid ?>"
                onchange="checkexists('prod_<?php echo $uid ?>'); Getother('prod_<?php echo $uid ?>');">
            <option value="">Select</option>
            <option value="bhk">bhk</option>
            <option value="1bhk">1bhk</option>
            <option value="2bhk">2bhk</option>
            <option value="3bhk">3bhk</option>
            <option value="4bhk">4bhk</option>
            <!-- @foreach ($otherTypes as $type)
                <option value="{{ $type }}">{{ $type }}</option>
            @endforeach -->
            <option value="Other">Other</option>
        </select>
    </td>
    <td>
        <input name="flatcnt<?php echo $uid ?>" id="flatcnt<?php echo $uid ?>" type="text" class="form-control number" />
    </td>
    <td id="rmv_<?php echo $uid ?>">
        <a href="javascript:void(0);" onclick="cancelrow('<?php echo $uid ?>');" class="btn btn-sm">
            <i data-feather='trash'></i>
        </a>
    </td>
</tr>
