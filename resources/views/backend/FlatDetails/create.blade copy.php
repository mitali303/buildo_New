@php
    use App\Models\Backend\Scheme_Flat;
@endphp
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
</style>

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ !empty($flat) ? 'Edit Flat Details' : 'Create Flat Details' }}</h1>
        <div class="card">
            <div class="card-body">
                <form action="{{ !empty($flat) ? route('Flat.update') : route('Flat.store') }}" method="POST">
                            @csrf
                            @if(!empty($flat))
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $flat->ID }}">
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Scheme <span class="text-danger">*</span></label>
                                        <select class="form-control choices-single-scheme choices-single_status @error('scheme') is-invalid @enderror"
                                                    name="scheme" id="scheme" required onchange="fetchSchemeDetails(this.value)">
                                            <option value="">Select Scheme</option>
                                                @foreach($schemes as $scheme)
                                                    <option value="{{ $scheme->ID }}"
                                                        {{ old('scheme', $staff->scheme ?? '') == $scheme->ID ? 'selected' : '' }}>
                                                        {{ $scheme->Name }}
                                                    </option>
                                                @endforeach
                                        </select>
                                        @error('scheme') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Address</label>
                                    <textarea readonly id="address" name="Address" rows="2" class="form-control @error('Address') is-invalid @enderror"
                                        placeholder="Address">{{ old('Address', $flat->Address ?? '') }}</textarea>
                                    @error('Address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Location <span class="text-danger">*</span></label>
                                    <input readonly type="text" id="location" name="Location"
                                        value="{{ old('Location', $flat->Location ?? '') }}"
                                        class="form-control @error('Location') is-invalid @enderror"
                                        placeholder="Location">
                                    @error('Location') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input readonly type="email" id="email" name="Email" value="{{ old('Email', $flat->Email ?? '') }}"
                                        class="form-control @error('Email') is-invalid @enderror" placeholder="Email">
                                    @error('Email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                                    <input readonly type="text" id="cperson" name="ContactPerson" value="{{ old('ContactPerson', $flat->contactperson ?? '') }}"
                                        class="form-control @error('ContactPerson') is-invalid @enderror"
                                        placeholder="Contact Person">
                                        @error('ContactPerson')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
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
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        @php
                                            $category_array = ["Residential", "Commercial", "Plotting", "Row Houses", "OTHER"];
                                            $selected_categories = explode(',', $flat->Category ?? '');
                                            $custom_categories = collect($selected_categories)->diff($category_array)->values();
                                            $other_value = $custom_categories->first() ?? '';
                                        @endphp

                                        <button type="button" id="toggleCategoryBox" class="btn btn-outline-secondary w-100 text-left">
                                            Select Property Category
                                        </button>

                                        <div id="categoryDropdownBox" class="border rounded p-2 bg-white position-absolute w-50 shadow" style="display:none; max-height:200px; overflow-y:auto; z-index:10;">
                                            @foreach ($category_array as $category)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="category[]" value="{{ $category }}"
                                                        id="cat_{{ Str::slug($category) }}"
                                                        {{ in_array($category, $selected_categories) ? 'checked' : '' }}
                                                        {{ $category == 'OTHER' ? 'onclick=toggleOtherCategory(this)' : '' }}>
                                                    <label class="form-check-label" for="cat_{{ Str::slug($category) }}">{{ $category }}</label>
                                                </div>
                                            @endforeach

                                        @foreach ($custom_categories as $customCat)
                                                @if(trim($customCat) !== '')
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="category[]" value="{{ $customCat }}" checked>
                                                        <label class="form-check-label">{{ $customCat }}</label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                </div>

                                {{-- Other Category --}}
                            <div id="otherCategoryBox" class="mb-3 col-md-4" style="{{ in_array('OTHER', $selected_categories) ? '' : 'display: none;' }}">
                                <label class="form-label">Other Category</label>
                                <input type="text" name="OtherCat" id="OtherCat" class="form-control" placeholder="Enter other category"
                                    value="{{ $other_value }}"
                                    onkeyup="updateOtherCheckboxValue();">
                            </div>

                                <div class="col-md-4 goleft"><label class="form-label">Total Area In Sq.Ft.:</label>
                                    <input type="text" id="area" name="area" class="form-control number" placeholder="Enter Total Area In Sq.Ft."
                                        value="{{ old('area', $flat->Area ?? '') }}"
                                        onkeyup="calccost();" onblur="calccost();" />
                                </div>
                            </div>
                                @if(session('selected_scheme_id') == 'infyconst')
                                    <div class="row">
                                        <div class="col-md-4"><label class="form-label">TDS %</label>
                                            <input name="tds" id="tds" type="text"  placeholder="TDS %"
                                                class="number form-control"
                                                value="{{ old('tds', $flat->tds ?? '') }}"
                                                onkeyup="calccost();" onblur="calccost();" />
                                        </div>
                                    </div>
                                    <br>

                                    <div class="row">
                                        <div class="col-md-4"><label class="form-label">Service Tax %</label>
                                            <input name="Stax" id="Stax" type="text"  placeholder="Service Tax %"
                                                class="number form-control"
                                                value="{{ old('Stax', $flat->ServiceTax ?? '') }}"
                                                onkeyup="calccost();" onblur="calccost();" />
                                        </div>
                                    </div>
                                    <br>
                                @endif
                                <div class="row">
                                    <div class="col-md-4"><label class="form-label">Project / Contract Detail</label>
                                        <textarea name="detail" rows="3" class="required form-control" 
                                            {{ $readonly ?? '' }}  placeholder="Project / Contract Details">{{ old('detail', $flat->Detail ?? '') }}</textarea>
                                    </div>

                                    <div class="col-md-4"><label class="form-label">Government Rate Sq.Ft.</label>
                                        <input name="gov_rate" id="gov_rate" type="text"  placeholder="Government Rate Sq.FT."
                                            class="number form-control"
                                            value="{{ old('gov_rate', $flat->gov_rate ?? '') }}"
                                            {{ $readonly ?? '' }} />
                                    </div>

                                    <div class="col-md-4"><label class="form-label">Project Builtup Area Sq.Ft.</label>
                                        <input name="builtuparea" id="builtuparea" type="text"  placeholder="Project Builtup Area"
                                            class="number form-control"
                                            value="{{ old('builtuparea', $flat->BuiltupArea ?? '') }}"
                                            {{ $readonly ?? '' }} />
                                    </div>
                                </div>
                                @php
                                    $amenities_array = ["Balcony", "Terrace Balcony", "Parking", "Lift", "Security", "OTHER"];
                                    $selected_amenities = explode(",", $flat->Amenities ?? '');
                                    $custom_amenities = collect($selected_amenities)->diff($amenities_array)->values();
                                @endphp
                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Amenities</label>
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
                                </div>
                                {{-- Other Amenities Textbox --}}
                                <div class="mb-3 col-md-4" id="OtherAmenities_div" style="display: {{ in_array('OTHER', $selected_amenities) ? 'block' : 'none' }};">
                                    <label class="form-label">Other Amenities</label>
                                    <input type="text" name="OtherAmenities" id="OtherAmenities" class="form-control"
                                        placeholder="Enter other amenities" value="{{ $custom_amenities->first() ?? '' }}"
                                        onkeyup="OtherValue()">
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Upload Amenities Images</label>
                                    <!-- File input -->
                                    <input type="file" id="fileInput" name="images[]" multiple class="form-control" />
                                    <!-- Upload status display -->
                                    <ul id="fileList" class="mt-3 list-unstyled"></ul>
                                </div>
                            </div>
                            @if(session('selected_scheme_id') == 'infyconst')
                                <div class="row">
                                    <div class="mb-3 col-md-4 "> <label class="form-label">Rate Per Sqft</label>
                                        <input name="rate" id="rate" type="text" class="number form-control" 
                                            value="{{ old('rate', $flat->Rate ?? '') }}" placeholder="Rate Per SQFT"
                                            onkeyup="calccost();" onblur="calccost();" 
                                            {{ $readonly ?? '' }}>
                                    </div>

                                    <div class="mb-3 col-md-4"><label class="form-label"> Total Cost </label>
                                        <input name="cost" id="cost" type="text" readonly class="form-control" placeholder="Total Const"
                                            value="{{ old('cost', $flat->TotalCost ?? '') }}">
                                    </div>

                                    <div class="mb-3 col-md-4"><label class="form-label">Final Cost </label>
                                            <input name="fcost" id="fcost" type="text" readonly class="form-control" placeholder="Final Cost"
                                                value="{{ old('fcost', $flat->FinalCost ?? '') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-4"><label class="form-label">Project/Contract Upload: </label>
                                        <input type="file" id="projFileInput" multiple class="form-control" />
                                        <ul id="projFileList" class="mt-2 list-unstyled"></ul>
                                        <div id="projHiddenInputs"></div>
                                    </div>
                                </div>
                            @endif
                            @php
                                $schemeId = old('Uid', request('Uid'));
                                $flatRows = Scheme_Flat::where('scheme_ID', $schemeId)->get();
                                if ($flatRows->isEmpty()) {
                                $flatRows = collect([['Type' => '', 'NoOfFlat' => '']]);
                                    }
                            @endphp

                    <input type="hidden" id="cnt1" value="{{ count($flatRows) }}">
                        <div class="row justify-content-center">
                            <div class="col-md-8"> {{-- Adjust width as needed --}}
                                <table id="myTable1" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Type (like 1bhk,1 guntha etc)</th>
                                            <th>No Of flats/plots/row houses</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="p_scents1">
                                        @foreach($flatRows as $index => $row)
                                            <tr id="prod_{{ $loop->iteration }}">
                                                <td id="ftypedv{{ $loop->iteration }}">
                                                    <select name="ftype{{ $loop->iteration }}" class="form-control chosen-select" id="ftype{{ $loop->iteration }}"
                                                        onchange="checkexists('prod_{{ $loop->iteration }}'); Getother('prod_{{ $loop->iteration }}');">
                                                        <option value="">Select</option>
                                                        @php
                                                            $existingTypes = ['bhk', '1bhk', '2bhk', '3bhk', '4bhk'];
                                                            $allTypes = Scheme_Flat::select('Type')->groupBy('Type')->pluck('Type')->toArray();
                                                            $customTypes = array_diff($allTypes, $existingTypes);
                                                        @endphp
                                                        @foreach(array_merge($existingTypes, $customTypes) as $type)
                                                            <option value="{{ $type }}" @if($row['Type'] == $type) selected @endif>{{ $type }}</option>
                                                        @endforeach
                                                            <option value="Other">Other</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="flatcnt{{ $loop->iteration }}" id="flatcnt{{ $loop->iteration }}"
                                                    class="form-control number" value="{{ $row['NoOfFlat'] }}">
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
                        <!-- <div class="col-md-7">
                                <button type="button" onclick="CountFlat('{{ request('Task') }}')" class="btn btn-primary nextBtn">Show Flats Details</button>
                            </div> -->

                        <div id="flatdetails" class="mt-3"></div>
                            {{-- Submit --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">{{ !empty($flat) ? 'Update' : 'Create' }}</button>
                                        <a href="{{ route('Flat') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                </form>
            </div>
        </div>
    </div>
</main>

@endsection
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
                removeBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'ms-2');
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
                document.querySelector('form').appendChild(hidden);

            } else {
                li.innerText = file.name + ' ❌ Upload failed';
            }
        };

        xhr.send(formData);
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById('projFileInput');
    const fileList = document.getElementById('projFileList');
    const hiddenInputs = document.getElementById('projHiddenInputs');

    input.addEventListener('change', function () {
        for (let file of input.files) {
            upload(file);
        }
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
                li.innerText = file.name;

                const removeBtn = document.createElement('button');
                removeBtn.innerText = '❌';
                removeBtn.classList.add('btn', 'btn-sm', 'btn-danger', 'ms-2');
                removeBtn.onclick = function () {
                    fetch("{{ route('delete.image') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ name: res.filename })
                    }).then(() => {
                        li.remove();
                        hidden.remove();
                    });
                };

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'uploadfile1[]';
                hidden.value = res.filename;
                hidden.dataset.filename = res.filename;

                li.appendChild(removeBtn);
                hiddenInputs.appendChild(hidden);
            } else {
                li.innerText = file.name + ' ❌ Upload failed';
            }
        };

        xhr.send(formData);
    }
});
</script>
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
    let SchmID = (taskVal === 'update' || taskVal === 'view') ? $("#Uid").val() : '0000';

    if (totalflat > 0) {
        $("#flatdetails").hide();
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
                $("#flatdetails").html(res).show();
                $(".nextBtn").prop("disabled", false);
                addHiddenFields();
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


<!-- searchable dropdown script start-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-scheme"));
    });
</script>
<!-- searchable dropdown script end-->
