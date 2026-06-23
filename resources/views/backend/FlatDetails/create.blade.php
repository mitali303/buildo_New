@extends('backend.partials.master')

@section('title', !empty($flat) ? 'Edit Flat Details' : 'Create Flat Details')
<style>
    #categoryDropdownBox {
        background-color: #fff;
        border: 1px solid #ddd;
    }

    #toggleCategoryBox:after {
        content: '▼';
        float: right;
        margin-right: 10px;
    }

    .required-star {
    color: red;
    }

</style>

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ !empty($flat) ? 'Edit Flat Details' : 'Create Flat / Project Details' }}</h1>

        <div class="card">
            <div class="card-body">
<form onsubmit="return handleFlatFormSubmit(this, event);" action="{{ !empty($flat) ? route('Flat.update') : route('Flat.store') }}" method="POST">
    <input type="hidden" name="uploadfile[]">
    <input type="hidden" id="Uid" name="Uid" value="{{ $flat->ID ?? '' }}">

                    @csrf
                    @if(!empty($flat))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $flat->ID }}">
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                                    <label class="form-label">Scheme <span class="required-star">*</span></label>
                                    <select class="form-control choices-single-scheme choices-single_status @error('scheme') is-invalid @enderror"
                                            name="scheme" id="scheme" onchange="fetchSchemeDetails(this.value)">
                                        <option value="">Select Scheme</option>
                                        @foreach($schemes as $scheme)
                                            <option value="{{ $scheme->ID }}"
                                                {{ old('scheme', $flat->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                                {{ $scheme->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small id="scheme-error" class="text-danger"></small>
                                </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Address</label>
                            <textarea readonly id="address" name="Address" rows="2"
                            class="form-control @error('Address') is-invalid @enderror"
                            placeholder="Address">{{ old('Address', $flat->Address ?? '') }}</textarea>
                            @error('Address') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Location</label>
                            <input readonly type="text" id="location" name="Location"
                                value="{{ old('Location', $flat->Location ?? '') }}"
                                class="form-control @error('Location') is-invalid @enderror"
                                placeholder="Location">
                            @error('Location') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Email</label>
                            <input readonly type="email" id="email" name="Email"
                                value="{{ old('Email', $flat->Email ?? '') }}"
                                class="form-control @error('Email') is-invalid @enderror"
                                placeholder="Email">
                            @error('Email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Contact Person</label>
                            <input readonly type="text" id="cperson" name="ContactPerson"
                                value="{{ old('ContactPerson', $flat->contactperson ?? '') }}"
                                class="form-control @error('ContactPerson') is-invalid @enderror"
                                placeholder="Contact Person">
                            @error('ContactPerson') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Contact Number</label>
                            <input readonly type="text" id="cnumber" name="ContactNo"
                                value="{{ old('ContactNo', $flat->cnumber ?? '') }}"
                                class="form-control @error('ContactNo') is-invalid @enderror"
                                placeholder="Contact Number">
                            @error('ContactNo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="row">
                        {{-- Category --}}
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Category <span class="required-star">*</span></label>

                            @php
                                $category_array = ["Residential", "Commercial", "Plotting", "Row Houses", "OTHER"];
                                $selected_categories = array_filter(
                                    array_map('trim', explode(',', $flat->Category ?? '')),
                                    fn($val) => $val !== ''
                                );
                                $custom_categories = collect($selected_categories)->diff($category_array)->values();
                                $other_value = $custom_categories->first() ?? '';
                            @endphp

                            <select name="category[]" id="categorySelect"
                                class="form-control select2" multiple="multiple">

                                @foreach ($category_array as $category)
                                    <option value="{{ $category }}"
                                        {{ in_array($category, $selected_categories) ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach

                                {{-- Custom categories --}}
                                @foreach ($custom_categories as $customCat)
                                    @if(trim($customCat) !== '')
                                        <option value="{{ $customCat }}" selected>
                                            {{ $customCat }}
                                        </option>
                                    @endif
                                @endforeach

                            </select>

                            <small id="category-error" class="text-danger"></small>
                        </div>

                        {{-- Other Category --}}
                       <div id="otherCategoryBox" class="mb-3 col-md-4" style="{{ in_array('OTHER', $selected_categories) ? '' : 'display: none;' }}">
                        <label class="form-label">Other Category</label>
                        <input type="text" name="OtherCat" id="OtherCat" class="form-control" placeholder="Enter other category"
                            value="{{ $other_value }}"
                            onkeyup="updateOtherCheckboxValue();">
                    </div>

                         <div class="col-md-4 goleft"><label class="form-label">Total Area In Sq.Ft.:<span class="required-star">*</span></label>
                            <input type="text" id="area" name="area" class="form-control number" placeholder="Enter Total Area In Sq.Ft." oninput="calculateAllAmounts()"
                                value="{{ old('area', $flat->Area ?? '') }}"
                                onkeyup="calccost();" onblur="calccost();" />
                                    <small id="area-error" class="text-danger"></small>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-md-4"><label class="form-label">Project / Contract Detail</label>
                                <textarea name="detail" rows="3" class="form-control" 
                                    {{ $readonly ?? '' }}  placeholder="Project / Contract Details">{{ old('detail', $flat->Detail ?? '') }}</textarea>
                            </div>

                            <div class="col-md-4"><label class="form-label">Government Rate Sq.Ft.<span class="required-star">*</span></label>
                                <input name="gov_rate" id="gov_rate" type="text"  placeholder="Government Rate Sq.FT."
                                    class="number form-control" oninput="calculateAllAmounts()"
                                    value="{{ old('gov_rate', $flat->gov_rate ?? '') }}"
                                    {{ $readonly ?? '' }} />
                                        <small id="gov_rate-error" class="text-danger"></small>
                            </div>

                            <div class="col-md-4"><label class="form-label">Total Amount.<span class="required-star">*</span></label>
                                <input name="TotalareaAmount" id="TotalareaAmount" type="text"  placeholder="Government Rate Sq.FT."
                                    class="number form-control" oninput="calculateAllAmounts()"
                                    value="{{ old('TotalareaAmount', $flat->TotalareaAmount ?? '') }}"
                                    {{ $readonly ?? '' }} />
                                        <small id="TotalareaAmount-error" class="text-danger"></small>
                            </div>

                           <div class="col-md-4 mt-3">
                                <label class="form-label">Type <span class="required-star">*</span></label>
                                <select name="type" id="typeSelector" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="project" {{ old('type', $flat->type ?? '')=='project' ? 'selected' : '' }}>
                                        Project
                                    </option>
                                    <option value="individual" {{ old('type', $flat->type ?? '')=='individual' ? 'selected' : '' }}>
                                        Individual
                                    </option>
                                </select>
                                <small id="type-error" class="text-danger"></small>
                            </div>
                        </div>
                        <div id="projectFields" style="display:none">
                        <div class="row">
                            
                           <div class="col-md-4 mt-3"><label class="form-label">Project Builtup Area Sq.Ft.<span class="required-star">*</span></label>
                                <input name="builtuparea" id="builtuparea" type="text"  placeholder="Project Builtup Area"
                                    class="number form-control"
                                    value="{{ old('builtuparea', $flat->BuiltupArea ?? '') }}"
                                    {{ $readonly ?? '' }} />
                                        <small id="builtuparea-error" class="text-danger"></small>
                            </div>
                        
                        @php
                            $amenities_array = ["Balcony", "Terrace Balcony", "Parking", "Lift", "Security", "OTHER"];
                            $selected_amenities = explode(",", $flat->Amenities ?? '');
                            $custom_amenities = collect($selected_amenities)->diff($amenities_array)->values();
                        @endphp
                        
                        <div class="col-md-4 mt-3">
                            <label class="form-label">Amenities<span class="required-star">*</span></label>
                            <div class="position-relative">
                                <button type="button" class="btn btn-outline-secondary w-100 text-left" id="amenityDropdownBtn">
                                    Select Amenities <span class="float-end">▼</span>
                                </button>

                                <div id="ami"
                                    class="border rounded p-2 bg-white position-absolute w-100 shadow"
                                    style="display: none; max-height: 200px; overflow-y: auto; z-index: 1000;">

                                    @foreach ($amenities_array as $item)
                                        <label class="d-block px-2">
                                            <input type="checkbox" name="amenities[]" value="{{ $item }}"
                                                id="{{ $item == 'OTHER' ? 'OtherChk1' : '' }}"
                                                {{ in_array($item, $selected_amenities) ? 'checked' : '' }}>
                                            {{ $item }}
                                        </label>
                                    @endforeach

                                   @foreach ($custom_amenities as $custom)
                                        @if(trim($custom) !== '')
                                            <label class="d-block px-2">
                                                <input type="checkbox" name="amenities[]" value="{{ $custom }}" checked>
                                                {{ $custom }}
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                                <small id="amenities-error" class="text-danger"></small>
                        </div>
                        {{-- Other Amenities Textbox --}}
                        <div class="mb-3 col-md-4" id="OtherAmenities_div" style="display: {{ in_array('OTHER', $selected_amenities) ? 'block' : 'none' }};">
                            <label class="form-label">Other Amenities</label>
                            <input type="text" name="OtherAmenities" id="OtherAmenities" class="form-control"
                                placeholder="Enter other amenities"
                                value="{{ $custom_amenities->first() ?? '' }}"
                                onkeyup="OtherValue()">
                        </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label">Upload Amenities Images</label>
                        
                        <!-- File input -->
                        <input type="file" id="fileInput" name="images[]" multiple class="form-control" />

                        <!-- Upload status display -->
                        <ul id="fileList" class="mt-3 list-unstyled"></ul>
                    </div>
                    </div>
                    @php use App\Models\Backend\Scheme_Flat;
                            @endphp

                            @php
                                $schemeId = old('scheme',isset($flat) ? $flat->ID : '');
                                $flatRows = Scheme_Flat::where('scheme_ID', $schemeId)->get();
                                if ($flatRows->isEmpty()) {
                                    $flatRows = collect([['Type' => '', 'NoOfFlat' => '']]);
                                }
                            @endphp

            <input type="hidden" id="cnt1" name="cnt1" value="{{ count($flatRows) }}">

                                        <div class="row justify-content-center">
                <div class="col-md-8"> {{-- Adjust width as needed --}}
                    <table id="myTable1" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type of Flat</th>
                                <th>No Of flats/plots/row houses</th>
                                <th></th>
                            </tr>
                        </thead>
            <tbody id="p_scents1">
                @foreach($flatRows as $index => $row)
                    <tr id="prod_{{ $loop->iteration }}">
                        @php
                            $rowId = $row->ID ?? uniqid();
                        @endphp

                        <input type="hidden" name="row_ids[]" value="{{ $rowId }}">

                        <td id="ftypedv{{ $loop->iteration }}">
                            <select name="ftype{{ $rowId }}" class="form-control chosen-select" id="ftype{{ $loop->iteration }}"
                                onchange="checkexists('prod_{{ $loop->iteration }}'); Getother('prod_{{ $loop->iteration }}');">
                                <option value="">Select</option>
                                @php
                                    $existingTypes = ['bhk', '1bhk', '2bhk', '3bhk', '4bhk'];
                                    $allTypes = Scheme_Flat::select('Type')->groupBy('Type')->pluck('Type')->toArray();
                                    $customTypes = array_diff($allTypes, $existingTypes);
                                @endphp

                                @foreach(array_merge($existingTypes, $customTypes) as $type)
                                    <option value="{{ $type }}"
                                        @if(($row->Type ?? '') == $type) selected @endif>
                                        {{ $type }}
                                    </option>
                                @endforeach
                                <option value="Other">Other</option>
                            </select>
                                <small id="ftype-error-{{ $rowId }}" class="text-danger"></small>
                        </td>
                        <td>
                            <input type="text" name="flatcnt{{ $rowId }}" id="flatcnt{{ $loop->iteration }}"
                                class="form-control number" value="{{ $row->NoOfFlat ?? '' }}">
                                <small id="flatcnt-error-{{ $rowId }}" class="text-danger"></small>
                        </td>
                        <td id="rmv_{{ $loop->iteration }}">
                            <a onclick="cancelrow({{ $loop->iteration }});" class="btn btn-sm">
                               <i data-feather='trash'></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mt-3">
            <div class="col text-start">
                <button type="button" onclick="addRow1()" class="btn btn-danger btn-sm">Add New</button>
            </div>
        </div>
    </div>
</div>
</div>


            <div class="row mt-3" id="titleAmountSection" style="display:none;">
                        <div class="col-md-4 goleft"><label class="form-label">Head Room Area Sq.Ft.:<span class="required-star">*</span></label>
                            <input type="text" id="head_roomarea" name="head_roomarea" class="form-control number" placeholder="Enter Total head room area In Sq.Ft." oninput="calculateAllAmounts()"
                                value="{{ old('head_roomarea', $flat->head_roomarea ?? '') }}"
                                onkeyup="calccost();" onblur="calccost();" />
                                    <small id="head_roomarea-error" class="text-danger"></small>
                        </div>
                        <div class="col-md-4 goleft"><label class="form-label">Head Room Area Rate.:<span class="required-star">*</span></label>
                            <input type="text" id="head_rate" name="head_rate" class="form-control number" placeholder="Enter Head Room Area Rate." oninput="calculateAllAmounts()"
                                value="{{ old('head_rate', $flat->head_rate ?? '') }}"
                                onkeyup="calccost();" onblur="calccost();" />
                                    <small id="head_rate-error" class="text-danger"></small>
                        </div>
                        <div class="col-md-4 goleft"><label class="form-label">Head Room Amount.:<span class="required-star">*</span></label>
                            <input type="text" id="TotalheadAmount" name="TotalheadAmount" class="form-control number" placeholder="Enter Head Room Amount" oninput="calculateAllAmounts()"
                                value="{{ old('TotalheadAmount', $flat->TotalheadAmount ?? '') }}"
                                onkeyup="calccost();" onblur="calccost();" />
                                    <small id="TotalheadAmount-error" class="text-danger"></small>
                        </div>
                        <br><br>
                        
                        <div class="col-md-4 goleft mt-3"><label class="form-label">Final Amount:<span class="required-star">*</span></label>
                            <input type="text" id="TotalProjectAmount" name="TotalProjectAmount" class="form-control number" placeholder="Enter Total Final amount."
                                value="{{ old('TotalProjectAmount', $flat->TotalProjectAmount ?? '') }}"
                                onkeyup="calccost();" onblur="calccost();" />
                                    <small id="TotalProjectAmount-error" class="text-danger"></small>
                        </div>
                    
    <div class="col-md-8 mt-3">
        <table id="myTable2" class="table table-bordered">
            <thead>
                <tr>
                    <th>Title</th>
                    <th style="width:150px;">Amount</th>
                    <th style="width:50px;"></th>
                </tr>
            </thead>
            <tbody id="p_scents2">
                @php
                    $titles = $titleAmounts ?? collect([
                        (object)['title' => '', 'amt' => '']
                    ]);
                @endphp

                @foreach($titles as $i => $row)
                <tr id="prod2_{{ $loop->iteration }}">
                    <td>
                        <input type="text"
                               name="title{{ $loop->iteration }}"
                               id="title{{ $loop->iteration }}"
                               class="form-control"
                               value="{{ $row->title ?? '' }}">
                    </td>
                    <td>
                        <input type="text"
                               name="amt{{ $loop->iteration }}"
                               id="amt{{ $loop->iteration }}"
                               class="form-control number"
                               value="{{ $row->amt ?? '' }}"
                               oninput="recalculateTitleTotal()">
                    </td>
                    <td>
                        <a onclick="removeTitleRow({{ $loop->iteration }})" class="btn btn-sm">
                            <i data-feather="trash"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <input type="hidden" id="cnt2" name="cnt2" value="{{ count($titles) }}">

        <button type="button" class="btn btn-danger btn-sm" onclick="addTitleRow()">
            Add New
        </button>
    </div>
</div>

                                <!-- <div class="col-md-7">
                                    <button type="button" onclick="CountFlat('{{ request('Task') }}')" class="btn btn-primary nextBtn">Show Flats Details</button>
                                </div> -->

                    {{-- Submit --}}
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($flat) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('Flat') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
                <div id="flatdetails" class="mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>
</main>

@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script>
$(function () {

    const categorySelect = $('#categorySelect');
    const otherBox = $('#otherCategoryBox');
    const otherInput = $('#OtherCat');

    // ✅ INIT SELECT2 FIRST
    categorySelect.select2({
        placeholder: "Select Property Category",
        width: '100%'
    });

    // 👉 CHANGE EVENT
    categorySelect.on('change', function () {
        let selected = $(this).val() || [];

        if (selected.includes('OTHER')) {
            otherBox.show();
        } else {
            otherBox.hide();
            otherInput.val('');
        }
    });

    // 👉 CUSTOM VALUE
    let lastValue = '';

    otherInput.on('blur', function () {
        let value = $(this).val().trim();

        if (!value) return;

        // prevent duplicate
        if (value === lastValue) return;

        let exists = categorySelect.find("option[value='" + value + "']").length;

        if (!exists) {
            let newOption = new Option(value, value, true, true);
            categorySelect.append(newOption);
        }

        let selected = categorySelect.val() || [];
        selected.push(value);

        categorySelect.val(selected).trigger('change');

        lastValue = value;
    });

});
</script>

<script>
function calculateAllAmounts() {
    // ---- Area Amount ----
    const area = parseFloat(document.getElementById('area')?.value) || 0;
    const govRate = parseFloat(document.getElementById('gov_rate')?.value) || 0;
    const areaAmount = area * govRate;

    const areaAmtInput = document.getElementById('TotalareaAmount');
    if (areaAmtInput) {
        areaAmtInput.value = areaAmount > 0 ? areaAmount.toFixed(2) : '';
    }

    // ---- Head Room Amount ----
    const headArea = parseFloat(document.getElementById('head_roomarea')?.value) || 0;
    const headRate = parseFloat(document.getElementById('head_rate')?.value) || 0;
    const headAmount = headArea * headRate;

    const headAmtInput = document.getElementById('TotalheadAmount');
    if (headAmtInput) {
        headAmtInput.value = headAmount > 0 ? headAmount.toFixed(2) : '';
    }

    // ---- Final Project Amount ----
    const finalAmount = areaAmount + headAmount;
    const finalInput = document.getElementById('TotalProjectAmount');

    if (finalInput) {
        finalInput.value = finalAmount > 0 ? finalAmount.toFixed(2) : '';
    }
}

// Auto calculate on page load (edit mode)
document.addEventListener('DOMContentLoaded', calculateAllAmounts);
</script>


<script>
    
function fetchSchemeDetails(schemeId) {
    if (!schemeId) return;

fetch(`{{ url('get-scheme-details') }}/${schemeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('address').value = data.Address || '';
            document.getElementById('location').value = data.Location || '';
            document.getElementById('email').value = data.Email || '';
            document.getElementById('cperson').value = data.cperson || '';
            document.getElementById('cnumber').value = data.cnumber || '';
        })
        .catch(err => {
            console.error('Error fetching scheme details:', err);
        });
}
</script>
<script>
  function toggleOtherCategory(checkbox) {
    const otherDiv = document.getElementById('otherCategoryBox');
    const input = document.getElementById('OtherCat');

    if (checkbox.checked) {
        otherDiv.style.display = 'block';
        input.focus();
    } else {
        input.value = '';
        otherDiv.style.display = 'none';
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleCategoryBox");
    const dropdown = document.getElementById("categoryDropdownBox");

    toggleBtn.addEventListener("click", function () {
        dropdown.style.display = dropdown.style.display === "none" ? "block" : "none";
    });

    // Hide dropdown on outside click
    document.addEventListener("click", function (event) {
        if (!toggleBtn.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });

    // Show/hide Other Category div on load
    const otherCheckbox = document.querySelector('input[name="category[]"][id^="cat-other"]');
    const otherDiv = document.getElementById('OtherCat').closest('.col-md-4');
    if (otherCheckbox && otherCheckbox.checked) {
        otherDiv.style.display = 'block';
        updateOtherCheckboxValue();
    } else {
        otherDiv.style.display = 'none';
    }
});

    function updateOtherCheckboxValue() {
        const otherValue = document.getElementById('OtherCat').value;
        const otherCheckbox = document.querySelector('input[name="category[]"][id^="cat-other"]');
        if (otherCheckbox) {
            otherCheckbox.value = otherValue || 'OTHER';
        }
    }
</script>
<script>
function calccost(){
    var cost = 0;
    var tds = 0;
    var tdsAmt = 0;
    var stax = 0;
    var staxAmt = 0;
    var finalcost = 0;

    var area = $("#area").val();
    var rate = $("#rate").val();  // Make sure #rate input exists
    var tdsInper = $("#tds").val();
    var staxInper = $("#Stax").val();

    if(area !== '' && rate !== ''){
        cost = parseFloat(area) * parseFloat(rate);
        $("#cost").val(cost.toFixed(2));
    }

    if(tdsInper !== ''){
        tds = parseFloat(tdsInper) / 100;
        tdsAmt = cost * tds;
    }

    if(staxInper !== ''){
        stax = parseFloat(staxInper) / 100;
        staxAmt = cost * stax;
    }

    finalcost = cost - tdsAmt + staxAmt;
    $("#fcost").val(finalcost.toFixed(2));
}

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const amenityBtn = document.getElementById("amenityDropdownBtn");
    const amenityBox = document.getElementById("ami");
    const otherChk = document.getElementById("OtherChk1");
    const otherInput = document.getElementById("OtherAmenities");
    const otherInputDiv = document.getElementById("OtherAmenities_div");

    // Toggle dropdown
    amenityBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        amenityBox.style.display = (amenityBox.style.display === "block") ? "none" : "block";
    });

    // Close dropdown on outside click
    document.addEventListener("click", function (e) {
        if (!amenityBox.contains(e.target) && !amenityBtn.contains(e.target)) {
            amenityBox.style.display = "none";
        }
    });

    // Handle "OTHER" checkbox toggle
    if (otherChk) {
        otherChk.addEventListener("change", function () {
            if (this.checked) {
                otherInputDiv.style.display = "block";
                amenityBox.style.display = "none";
            } else {
                otherInputDiv.style.display = "none";
                otherInput.value = "";
                this.value = "OTHER";
            }
        });

        // Initial state check
        if (otherChk.checked) {
            otherInputDiv.style.display = "block";
        }
    }

    // Update "OTHER" checkbox value when typing
    if (otherInput) {
        otherInput.addEventListener("keyup", function () {
            if (otherChk) {
                otherChk.value = this.value || "OTHER";
            }
        });
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    input.addEventListener('change', function () {
        for (let file of input.files) {
            upload(file);
        }
        // Clear input to allow re-selecting same files
        input.value = "";
    });

    function upload(file) {
        const li = document.createElement('li');
        li.innerText = file.name + ' - Uploading...';
        fileList.appendChild(li);

        const formData = new FormData();
        formData.append("file", file);
        formData.append("_token", '{{ csrf_token() }}');

        const xhr = new XMLHttpRequest();
    xhr.open("POST", "{{ route('upload.image') }}", true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                li.innerText = file.name ;

                const removeBtn = document.createElement('button');
                removeBtn.innerText = '❌';
                removeBtn.type = 'button';
                removeBtn.classList.add('btn', 'btn-sm', 'ms-2');
                removeBtn.onclick = function () {
                    fetch("{{ route('delete.image') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ name: res.filename })
                    }).then(() => li.remove());
                };
                li.appendChild(removeBtn);

                // Add hidden input for later form submission
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'uploadfile[]';
                hidden.value = res.filename;
                input.closest('form').appendChild(hidden);
                console.log("Appending to form:", input.closest('form'));
                console.log("Hidden input added:", hidden);

            } else {
                li.innerText = file.name + ' ❌ Upload failed';
            }
        };

        xhr.send(formData);
    }
});
</script>
@if(!empty($amiImages))
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const fileList = document.getElementById('fileList');
        const form = document.querySelector('form');

        @foreach($amiImages as $img)
            if ("{{ $img }}".trim() !== "") {
                const li = document.createElement('li');
                li.innerText = "{{ $img }}";

                const removeBtn = document.createElement('button');
                removeBtn.innerText = '❌';
                removeBtn.type = 'button';
                removeBtn.classList.add('btn', 'btn-sm', 'ms-2');
                removeBtn.onclick = function () {
                    fetch("{{ route('delete.image') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ name: "{{ $img }}" })
                    }).then(() => li.remove());
                };
                li.appendChild(removeBtn);

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'uploadfile[]';
                hidden.value = "{{ $img }}";
                form.appendChild(hidden);

                fileList.appendChild(li);
            }
        @endforeach
    });
    </script>
@endif

<script>
function checkexists(id) {
    const rid = id.split("_")[1];
    const cnt = $("#cnt1").val();
    const selected = $("#ftype" + rid).val();

    for (let i = 1; i <= cnt; i++) {
        if (i != rid && selected == $("#ftype" + i).val()) {
            alert("You have already selected this! Please Select other");
            $("#ftype" + rid).val('').trigger("chosen:updated");
            $("#flatcnt" + rid).val('0');
            return false;
        }
    }
}

function Getother(id) {
    const rid = id.split("_")[1];
    const val = $("#ftype" + rid).val();
    if (val === "Other") {
        $("#ftypedv" + rid).html(`<input class='form-control' type='text' id='ftype${rid}' name='ftype${rid}' style='width: 220px;' />`);
    }
}

function cancelrow(rid) {
    // alert(rid);
    const count = getrows1();
    if (count > 1) {
        if (confirm("Are you sure?")) {
            $("#prod_" + rid).remove();
            $("#cnt1").val(count - 1);
        }
    } else {
        alert("Can't remove row. At least one row is required.");
    }
}


function getrows1() {
    return $("#p_scents1 tr").length;
}


function addRow1() {
    let count = parseInt($("#cnt1").val()) + 1;
    $(".addbtn").attr("disabled", true);

    $.post("{{ route('scheme.addRow') }}", {
        Count: count,
        _token: "{{ csrf_token() }}"
    }, function (data) {
        // data is HTML now
        $("#p_scents1").append(data);
        $("#cnt1").val(count);
        $(".addbtn").removeAttr("disabled");

        feather.replace();              // ✅ re-render feather icons
        $(".chosen-select").chosen();   // ✅ re-init chosen
    });
}


function CountFlat(task) {
    // alert (task);
    let cnt = $("#cnt1").val();
    let totalflat = 0;
    let ftp = '';
    const floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

    for (let i = 1; i <= cnt; i++) {
        let flat = $("#flatcnt" + i).val();
        if (floatRegex.test(flat)) {
            totalflat += parseFloat(flat);
        }
        ftp = $("#ftype" + i).val() + "##" + flat + "@@" + ftp;
    }

    let taskVal = task;
    let SchmID = (taskVal === 'update' || taskVal === 'view') ? $("#scheme").val() : '0000';

    if (totalflat > 0) {
        $(".nextBtn").prop("disabled", true);

        $.ajax({
            url: "{{ route('scheme.flatDetails') }}",
            type: "POST",
            data: {
                FlatRow: totalflat,
                FlatsTp: ftp,
                SchmID: SchmID,
                Task: taskVal,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {
                // alert ("hii");
        $("#flatdetails").html(res).css('display', 'block');
                // $("#flatdetails").html(res);
                $(".nextBtn").prop("disabled", false);
                // addHiddenFields();
            },
            error: function () {
                $("#flatdetails").html("<label class='alert alert-danger'>Something went wrong!</label>");
            }
        });

    } else {
        $("#flatdetails").html("<label class='alert alert-danger'>Plz Check - No Of flats/plots/row houses</label>");
    }
}

function updateHiddenValues(rid){
	var did = rid.split("_");
	var rid = did[1];
	var val = $("#FlatType"+rid).val()+"##"+$("#Wing"+rid).val()+"##"+$("#Floor"+rid).val()+"##"+$("#FlatNo"+rid).val()+"##"+$("#Area"+rid).val()+"##"+$("#Other1"+rid).val()+"##"+$("#Other2"+rid).val()+"##"+$("#Terrace"+rid).val()+"##"+$("#Attribute"+rid).val()+"##"+$("#TotalSqFt"+rid).val()+"##"+$("#TotalSqMtr"+rid).val();
	$("#flatdet_"+rid).val(val);
} 
</script>
<script>
function handleFlatFormSubmit(form, event) {
    event.preventDefault();

    const formData = new FormData(form);
    const actionUrl = form.action;

    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {

            const selectedType = $('#typeSelector').val();

            if (selectedType === 'project') {

                // Open flat details table
                $(form).hide();
                $('#Uid').val(response.scheme_id);
                CountFlat($('#Uid').val() ? 'update' : 'create');

            } else {

                // Redirect back to listing
                window.location.href = "{{ route('Flat') }}";

            }
        },

       error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;

                $('.text-danger').text('');
                $('.is-invalid').removeClass('is-invalid');

                $.each(errors, function(field, messages) {
                    const input = $(`[name="${field}"]`);
                    input.addClass('is-invalid');

                    // Handle dynamic ftypeXXX and flatcntXXX
                    if (/^(ftype|flatcnt)[a-zA-Z0-9]+$/.test(field)) {
                        const match = field.match(/^([a-z]+)([a-zA-Z0-9]+)$/);
                        if (match) {
                            const prefix = match[1];   // ftype or flatcnt
                            const rowId = match[2];    // dynamic ID
                            const errorId = `#${prefix}-error-${rowId}`;
                            $(errorId).text(messages[0]);
                        }
                    } else {
                        // Static fields
                        const errorId = `#${field}-error`;
                        $(errorId).text(messages[0]);
                    }
                });
            } else {
                alert('Something went wrong while saving the form.');
            }
        }
    });

    return false;
}
</script>

<script>
function saveFlatRow(rowId) {
    const schemeId = $("#scheme").val(); // ✅ Correct selector

    if (!schemeId) {
        alert("Please select a scheme first.");
        return;
    }

    const data = {
        scheme_ID: schemeId,
        ClientID: schemeId,
        FlatType: $("#FlatType" + rowId).val(),
        Wing: $("#Wing" + rowId).val(),
        Floor: $("#Floor" + rowId).val(),
        FlatNo: $("#FlatNo" + rowId).val(),
        Area: $("#Area" + rowId).val(),
        Other1: $("#Other1" + rowId).val(),
        Other2: $("#Other2" + rowId).val(),
        Terrace: $("#Terrace" + rowId).val(),
        FlatAttribute: $("#Attribute" + rowId).val(),
        TotalSqFt: $("#TotalSqFt" + rowId).val(),
        TotalSqMtr: $("#TotalSqMtr" + rowId).val(),
        _token: "{{ csrf_token() }}"
    };

    $.ajax({
        url: "{{ route('flatDetails.saveRow') }}", // Define this route
        method: 'POST',
        data: data,
        success: function(response) {
            alert("Saved successfully");
            const saveBtn = $("#saveBtn" + rowId);
             saveBtn.removeClass("btn-primary").addClass("btn-success");
             saveBtn.text("Saved");
        },
        error: function(xhr) {
            alert("Error: " + xhr.responseText);
        }
    });
}

function updateFlatRow(flatId, rowId) {
    const schemeId = $("#scheme").val();

    const data = {
        ID: flatId,
        scheme_ID: schemeId,
        ClientID: schemeId,
        FlatType: $("#FlatType" + rowId).val(),
        Wing: $("#Wing" + rowId).val(),
        Floor: $("#Floor" + rowId).val(),
        FlatNo: $("#FlatNo" + rowId).val(),
        Area: $("#Area" + rowId).val(),
        Other1: $("#Other1" + rowId).val(),
        Other2: $("#Other2" + rowId).val(),
        Terrace: $("#Terrace" + rowId).val(),
        FlatAttribute: $("#Attribute" + rowId).val(),
        TotalSqFt: $("#TotalSqFt" + rowId).val(),
        TotalSqMtr: $("#TotalSqMtr" + rowId).val(),
        _token: "{{ csrf_token() }}"
    };

    $.ajax({
        url: "{{ route('flatDetails.updateRow') }}",
        type: 'POST',
        data: data,
        success: function(res) {
            alert("Updated successfully");
            const btn = $("#saveBtn" + rowId);
            btn.removeClass('btn-primary').addClass('btn-success').text('Updated');
        },
        error: function(err) {
            alert("Error updating flat row: " + err.responseText);
        }
    });
}
function calTotalsq(i) { 
	var totalSqft=0;
   var Area=$("#Area"+i).val();
	var Terrace=$("#Terrace"+i).val();
	var Other1=$("#Other1"+i).val();
	var Other2=$("#Other2"+i).val();
	if(Area=='') Area=0;
	if(Terrace=='') Terrace=0;
	if(Other1=='') Other1=0;
	if(Other2=='') Other2=0;
totalSqft=parseFloat(Area)+parseFloat(Terrace)+parseFloat(Other1)+parseFloat(Other2);
	 
	 
	  $("#TotalSqFt"+i).val(totalSqft);
	  var cnt=$("#Rowcnt").val();
	  var totalarea=0;
	  for(var j=1; j<=cnt; j++){
		  totalarea = parseFloat(totalarea) + parseFloat($("#TotalSqFt"+j).val());
	  }
	  $("#area").val(totalarea);
  }
</script>

<script>
function toggleTypeFields(type) {
    if (type === 'project') {
        $('#projectFields').show();
        $('#titleAmountSection').hide();
        $('#flatdetails').hide();
    }
    else if (type === 'individual') {
        $('#projectFields').hide();
        $('#titleAmountSection').show();
        $('#flatdetails').hide();
    }
    else {
        $('#projectFields').hide();
        $('#titleAmountSection').hide();
        $('#flatdetails').hide();
    }
}


// ✅ delegated binding (THIS IS THE FIX)
$(document).on('change', '#typeSelector', function () {
    toggleTypeFields(this.value);
});

</script>
<script>
// document.addEventListener('change', function (e) {
//     if (e.target && e.target.id === 'typeSelector') {
//         alert('Type changed');
//         toggleTypeFields(e.target.value);
//     }
// });

document.addEventListener('DOMContentLoaded', function () {
    toggleTypeFields(document.getElementById('typeSelector').value);
});
</script>

<script>
function addTitleRow() {
    let cnt = parseInt($('#cnt2').val()) + 1;

    const row = `
        <tr id="prod2_${cnt}">
            <td>
                <input type="text" name="title${cnt}" id="title${cnt}" class="form-control">
            </td>
            <td>
                <input type="text" name="amt${cnt}" id="amt${cnt}" class="form-control number"
                       oninput="recalculateTitleTotal()">
            </td>
            <td>
                <a onclick="removeTitleRow(${cnt})" class="btn btn-sm">
                    <i data-feather="trash"></i>
                </a>
            </td>
        </tr>
    `;

    $('#p_scents2').append(row);
    $('#cnt2').val(cnt);

    feather.replace();
}

function removeTitleRow(id) {
    if ($('#p_scents2 tr').length > 1) {
        $('#prod2_' + id).remove();
        recalculateTitleTotal();
    } else {
        alert('At least one row is required.');
    }
}
</script>

<!-- searchable dropdown script start-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-scheme"));
    });
</script>
<!-- searchable dropdown script end-->
