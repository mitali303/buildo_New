@extends('backend.partials.master')

@section('title', !empty($workoflbrs) ? 'Edit Labour Work' : 'Create Labour Work')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ !empty($workoflbrs) ? 'Edit Labour Work' : 'Create Labour Work' }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ !empty($workoflbrs) ? route('Labour_Work.update') : route('Labour_Work.store') }}"
                      method="POST">
                    @csrf
                    @if(!empty($workoflbrs))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $workoflbrs->ID }}">
                    @endif

                    {{-- ROW 1: Type, Name, Contact No --}}
                    <div class="row">

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>

                            <div class="input-group flatpickr-container">
                                <input type="text"
                                    name="Date"
                                    id="datepicker"
                                    class="form-control @error('Date') is-invalid @enderror"
                                    placeholder="Select date"
                                        value="{{ old('Date', !empty($workoflbrs) ? \Carbon\Carbon::parse($workoflbrs->Date)->format('d-m-Y') : \Carbon\Carbon::now()->format('d-m-Y')) }}">
                            </div>

                            @error('Date') 
                                <small class="text-danger">{{ $message }}</small> 
                            @enderror
                        </div>

                        <div class="col-md-4" id="scheme">
                            <label class="form-label">Scheme <span class="text-danger">*</span></label>

                            <select name="Destination" id="Destination"
                                    class="form-control choices-single-destination"
                                    data-placeholder="Select Destination"
                                    >
                                <option value="">Select</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->ID }}"
                                            {{ old('Destination', $workoflbrs->schemeID ?? $schemes[0]->ID ?? '') == $scheme->ID ? 'selected' : '' }}>
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                            </select>
                            @error('Destination') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4" id="agencyblock">
                            <label class="form-label">Agency <span class="text-danger">*</span></label>

                            <select name="agency" id="agency"
                                    class="form-control choices-single-agency"
                                    data-placeholder="Select agency" onchange="agencyChanged(this.value)"
                                    >
                                <option value="">Select</option>
                                    @foreach($agencies as $agency)
                                        <option value="{{ $agency->ID }}"
                                            {{ old('agency', $workoflbrs->Agency_ID ?? '') == $agency->ID ? 'selected' : '' }}>
                                            {{ $agency->Name }}
                                        </option>
                                    @endforeach
                            </select>

                            <div class="mt-1">
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#agencyModal">
                                    <i class="fas fa-plus-circle"></i> Add New Agency
                                </button>
                            </div>

                            @error('agency') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                    </div>


                    {{-- ROW 2 : Rate Table --}}
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered text-center mt-3">
                                <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Count</th>
                                    <th>Rate</th>
                                    <th>Total</th>
                                </tr>
                                </thead>

                                <tbody>

                                {{-- ================= MISTRI ================= --}}
                                <tr>
                                    <td rowspan="2">Mistri</td>
                                    <td>Male</td>
                                    <td><input name="num_mistri_male" id="mm_count" class="form-control calc" oninput="calculate()" value="{{ old('num_mistri_male', $workoflbrs->num_mistri_male ?? 0) }}">
                                @error('num_mistri_male') <small class="text-danger">{{ $message }}</small> @enderror</td>
                                    <td><input id="mm_rate" class="form-control" readonly></td>
                                    <td><input id="mm_total" class="form-control" readonly></td>
                                </tr>

                                <tr>
                                    <td>Female</td>
                                    <td><input name="num_mistri_female" id="mf_count" class="form-control calc" oninput="calculate()" value="{{ old('num_mistri_female', $workoflbrs->num_mistri_female ?? 0) }}">
                                @error('num_mistri_female') <small class="text-danger">{{ $message }}</small> @enderror</td>
                                    <td><input id="mf_rate" class="form-control" readonly></td>
                                    <td><input id="mf_total" class="form-control" readonly></td>
                                </tr>

                                {{-- ================= LABOUR ================= --}}
                                <tr>
                                    <td rowspan="2">Labour</td>
                                    <td>Male</td>
                                    <td><input name="num_labour_male" id="lm_count" class="form-control calc" oninput="calculate()" value="{{ old('num_labour_male', $workoflbrs->num_labour_male ?? 0) }}">
                                @error('num_labour_male') <small class="text-danger">{{ $message }}</small> @enderror</td>
                                    <td><input id="lm_rate" class="form-control" readonly></td>
                                    <td><input id="lm_total" class="form-control" readonly></td>
                                </tr>

                                <tr>
                                    <td>Female</td>
                                    <td><input name="num_labour_female" id="lf_count" class="form-control calc" oninput="calculate()" value="{{ old('num_labour_female', $workoflbrs->num_labour_female ?? 0) }}">
                                @error('num_labour_female') <small class="text-danger">{{ $message }}</small> @enderror</td>
                                    <td><input id="lf_rate" class="form-control" readonly></td>
                                    <td><input id="lf_total" class="form-control" readonly></td>
                                </tr>

                                {{-- ================= THEKEDAR ================= --}}
                                <tr>
                                    <td rowspan="2">Thekedar</td>
                                    <td>Male</td>
                                    <td><input name="num_thekedar_male" id="tm_count" class="form-control calc" oninput="calculate()" value="{{ old('num_thekedar_male', $workoflbrs->num_thekedar_male ?? 0) }}">
                                @error('num_thekedar_male') <small class="text-danger">{{ $message }}</small> @enderror</td>
                                    <td><input id="tm_rate" class="form-control" readonly></td>
                                    <td><input id="tm_total" class="form-control" readonly></td>
                                </tr>

                                <tr>
                                    <td>Female</td>
                                    <td><input name="num_thekedar_female" id="tf_count" class="form-control calc" oninput="calculate()" value="{{ old('num_thekedar_female', $workoflbrs->num_thekedar_female ?? 0) }}">
                                @error('num_thekedar_female') <small class="text-danger">{{ $message }}</small> @enderror</td>
                                    <td><input id="tf_rate" class="form-control" readonly></td>
                                    <td><input id="tf_total" class="form-control" readonly></td>
                                </tr>

                                </tbody>
                                </table>

                                <h4 class="text-end">
                                Grand Total : ₹ <span id="gtotal">0</span>
                                </h4>

                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($workoflbrs) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('Labour_Work') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<!-- Agency Modal -->
<div class="modal fade" id="agencyModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Create Agency</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <form id="agencyForm" method="POST" action="{{ route('Agency.store') }}">
            @csrf
            <input type="hidden" name="from_labour_work" value="1">

            <div class="row">

                <!-- Name -->
                <div class="col-md-4 mb-3">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="Name"
                           value="{{ old('Name') }}"
                           class="form-control @error('Name') is-invalid @enderror">

                    @error('Name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Contact -->
                <div class="col-md-4 mb-3">
                    <label>Contact No <span class="text-danger">*</span></label>
                    <input type="text"
                           name="ContactNo"
                           value="{{ old('ContactNo') }}"
                           class="form-control @error('ContactNo') is-invalid @enderror">

                    @error('ContactNo')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- Address -->
                <div class="col-md-4 mb-3">
                    <label>Address <span class="text-danger">*</span></label>
                    <input type="text"
                           name="Address"
                           value="{{ old('Address') }}"
                           class="form-control @error('Address') is-invalid @enderror">

                    @error('Address')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Male Rate</th>
                        <th>Female Rate</th>
                    </tr>
                </thead>

                <tbody>

                <!-- Mistri -->
                <tr>
                    <td>Mistri</td>
                    <td>
                        <input name="MistriMaleRate"
                               value="{{ old('MistriMaleRate') }}"
                               class="form-control @error('MistriMaleRate') is-invalid @enderror">

                        @error('MistriMaleRate')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </td>

                    <td>
                        <input name="MistriFemaleRate"
                               value="{{ old('MistriFemaleRate') }}"
                               class="form-control @error('MistriFemaleRate') is-invalid @enderror">

                        @error('MistriFemaleRate')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </td>
                </tr>

                <!-- Labour -->
                <tr>
                    <td>Labour</td>
                    <td>
                        <input name="LabourMaleRate"
                               value="{{ old('LabourMaleRate') }}"
                               class="form-control @error('LabourMaleRate') is-invalid @enderror">

                        @error('LabourMaleRate')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </td>

                    <td>
                        <input name="LabourFemaleRate"
                               value="{{ old('LabourFemaleRate') }}"
                               class="form-control @error('LabourFemaleRate') is-invalid @enderror">

                        @error('LabourFemaleRate')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </td>
                </tr>

                <!-- Thekedar -->
                <tr>
                    <td>Thekedar</td>
                    <td>
                        <input name="ThekedarMaleRate"
                               value="{{ old('ThekedarMaleRate') }}"
                               class="form-control @error('ThekedarMaleRate') is-invalid @enderror">

                        @error('ThekedarMaleRate')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </td>

                    <td>
                        <input name="ThekedarFemaleRate"
                               value="{{ old('ThekedarFemaleRate') }}"
                               class="form-control @error('ThekedarFemaleRate') is-invalid @enderror">

                        @error('ThekedarFemaleRate')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </td>
                </tr>

                </tbody>
            </table>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    Save Agency
                </button>
            </div>

        </form>

      </div>
    </div>
  </div>
</div>

@endsection
<!-- searchable dropdown script start-->
 @if(!empty($workoflbrs))
<script>
document.addEventListener("DOMContentLoaded", function(){
    agencyChanged(document.getElementById('agency').value);
});
</script>
@endif
@if ($errors->any())
<script>
document.addEventListener("DOMContentLoaded", function(){
    new bootstrap.Modal(
        document.getElementById('agencyModal')
    ).show();
});
</script>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-Destination"));
        new Choices(document.querySelector(".choices-single-agency"));
    });

    document.addEventListener('DOMContentLoaded', function () {
        const fp = flatpickr("#datepicker", {
            dateFormat: "d-m-Y",
            allowInput: true,
            clickOpens: false, // disable open on input click
        });

        // Trigger calendar when either input or icon is clicked
        document.getElementById('datepicker').addEventListener('click', () => fp.open());
        document.getElementById('calendar-icon').addEventListener('click', () => fp.open());
    });
</script>

<script>
let rates = {};

// called from onchange
function agencyChanged(id)
{
    if(id === ''){
        return;
    }

    $.get("{{ route('agency.getRates','') }}/" + id, function (res) {

        rates = res;

        mm_rate.value = res.Mistri_m_rate;
        mf_rate.value = res.Mistri_f_rate;

        lm_rate.value = res.Labour_m_rate;
        lf_rate.value = res.Labour_f_rate;

        tm_rate.value = res.Thekedar_m_rate;
        tf_rate.value = res.Thekedar_f_rate;

        calculate();
    });
}

// called when counts change
function calculate()
{
    let mm = (parseFloat(mm_count.value) || 0) * (parseFloat(rates.Mistri_m_rate) || 0);
    let mf = (parseFloat(mf_count.value) || 0) * (parseFloat(rates.Mistri_f_rate) || 0);

    let lm = (parseFloat(lm_count.value) || 0) * (parseFloat(rates.Labour_m_rate) || 0);
    let lf = (parseFloat(lf_count.value) || 0) * (parseFloat(rates.Labour_f_rate) || 0);

    let tm = (parseFloat(tm_count.value) || 0) * (parseFloat(rates.Thekedar_m_rate) || 0);
    let tf = (parseFloat(tf_count.value) || 0) * (parseFloat(rates.Thekedar_f_rate) || 0);

    mm_total.value = mm.toFixed(2);
    mf_total.value = mf.toFixed(2);

    lm_total.value = lm.toFixed(2);
    lf_total.value = lf.toFixed(2);

    tm_total.value = tm.toFixed(2);
    tf_total.value = tf.toFixed(2);

    let grand = mm + mf + lm + lf + tm + tf;

    document.getElementById('gtotal').innerText = grand.toFixed(2);
}
</script>

<!-- searchable dropdown script end-->