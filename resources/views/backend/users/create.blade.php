@extends('backend.partials.master')
@section('title')
    User Create
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Create User</h1>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="@if(!empty($user)){{route('users.update')}}@else{{route('users.store')}}@endif" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!empty($user))
                             @method('PUT')
                                <input type="hidden" name="id" id="id" value="{{$user->ID}}"/>
                            @endif
                            <div class="row"> 
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputEmail4">Name <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control @error('Name') is-invalid @enderror" value="{{ old('name', $user->Name ?? '') }}" name="name" id="name" placeholder="Name" >
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputEmail4">Username <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control no-uppercase @error('UserID') is-invalid @enderror" value="{{ old('username', $user->UserID ?? '') }}" name="username" id="username" placeholder="Username">
                                    @error('username')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                              
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="password">Password 
                                        @if(!empty($user)) 
                                            <small class="text-danger">If you don't want to change, leave blank</small> 
                                        @else
                                            <small class="text-danger">*</small>
                                        @endif
                                    </label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password">
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputPassword4">Role <small class="text-danger">*</small></label>
                                    <select class="form-control choices-single" name="role_id">
                                        <optgroup label="Select Role">
                                            @foreach($roles as $rl) 
                                                <option value="{{ $rl->id }}"
                                                {{ (string) old('role_id', $user->Role ?? '') == (string) $rl->id ? 'selected' : '' }}>
                                                {{ $rl->name }}
                                            </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    @error('role_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                 <div class="mb-3 col-md-4">
                                    <label class="form-label" for="schemes">Schemes <small class="text-danger">*</small></label>
                                 @php
                                    $user_schemes = [];

                                    if (!empty($user)) {
                                        // Use $user->scheme (the actual column)
                                        $schemes_json = $user->scheme ?? '[]';
                                        $user_schemes = json_decode($schemes_json, true) ?? [];
                                    }

                                    // convert everything to string so in_array works
                                    $user_schemes = array_map('strval', $user_schemes);
                                    @endphp

                                    <select class="form-control choices-multiple" name="schemes[]" multiple>
                                        @foreach($schemes as $scheme)
                                            <option value="{{ $scheme->ID }}"
                                                {{ in_array((string)$scheme->ID, old('schemes', $user_schemes)) ? 'selected' : '' }}>
                                                {{ $scheme->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('schemes')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label">Access Type <small class="text-danger">*</small></label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="access_type" id="access_web" value="web"
                                            {{ old('access_type', $user->access_type ?? '') == 'web' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="access_web">Web</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="access_type" id="access_mobile" value="mobile"
                                            {{ old('access_type', $user->access_type ?? '') == 'mobile' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="access_mobile">Mobile</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="access_type" id="access_both" value="both"
                                            {{ old('access_type', $user->access_type ?? '') == 'both' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="access_both">Both</label>
                                    </div>
                                </div>
                                @error('access_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            
                            <br><br>

                        <h3>Employee Payroll Module :</h3>
                        <br><br>
                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="total_salary">Total Salary<small class="text-danger">*</small></label>
                           <input type="number" class="form-control @error('total_salary') is-invalid @enderror"
                              name="total_salary" id="total_salary" placeholder="Total Salary"
                              value="{{ old('total_salary', !empty($user) ? $user->total_salary : '') }}">
                           @error('total_salary')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="perhour_salary">Per Hour Salary<small class="text-danger">*</small></label>
                           <input type="text" class="form-control @error('perhour_salary') is-invalid @enderror"
                              name="perhour_salary" readonly id="perhour_salary" placeholder="Per Hour Salary"
                              value="{{ old('perhour_salary', !empty($user) ? $user->perhour_salary : '') }}">
                           @error('perhour_salary')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="overtime_salary_perhour">Overtime salary per hour<small class="text-danger">*</small></label>
                           <input type="text" class="form-control @error('overtime_salary_perhour') is-invalid @enderror"
                              name="overtime_salary_perhour" id="overtime_salary_perhour" placeholder="Overtime salary per hour"
                              value="{{ old('overtime_salary_perhour', !empty($user) ? $user->overtime_salary_perhour : '') }}">
                           @error('overtime_salary_perhour')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                       <div class="form-group col-md-4">
                    <label>Designation <span class="required text-danger">*</span></label>

                    @php
                        $selectedDesignation = old('designation', !empty($user) ? trim($user->designation) : '');

                        $designations = DB::table('user')
                            ->whereNotNull('designation')
                            ->where('designation', '!=', '')
                            ->select('designation')
                            ->distinct()
                            ->orderBy('designation')
                            ->get();
                    @endphp

                            <select id="designation"
                                    name="designation"
                                    class="form-control choices-single"
                                    onchange="menuhideDesignation();">

                                <option value="">Select Designation</option>

                                <option value="Addnew">Add New</option>

                                @foreach ($designations as $desig)

                                    <option value="{{ trim($desig->designation) }}"
                                        {{ $selectedDesignation == trim($desig->designation) ? 'selected' : '' }}>
                                        {{ $desig->designation }}
                                    </option>

                                @endforeach

                            </select>

                            <div id="designation_input_container" style="margin-top:5px;"></div>

                            @error('designation')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>


                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="account_no">Account Number<small class="text-danger">*</small></label>
                           <input type="text" class="form-control @error('account_no') is-invalid @enderror"
                              name="account_no" id="account_no" placeholder="Account Number"
                              value="{{ old('account_no', !empty($user) ? $user->account_no : '') }}">
                           @error('account_no')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="IFSC">IFSC Code<small class="text-danger">*</small></label>
                           <input type="text" class="form-control @error('IFSC') is-invalid @enderror"
                              name="IFSC" id="IFSC" placeholder="IFSC Code"
                              value="{{ old('IFSC', !empty($user) ? $user->IFSC : '') }}">
                           @error('IFSC')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="bank_name">Bank Name<small class="text-danger">*</small></label>
                           <input type="text" class="form-control @error('bank_name') is-invalid @enderror"
                              name="bank_name" id="bank_name" placeholder="Bank Name"
                              value="{{ old('bank_name', !empty($user) ? $user->bank_name : '') }}">
                           @error('bank_name')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="PF">PF <small class="text-danger">*</small></label>
                           <select id="PF" name="PF" class="form-control choices-single">
                           <option value="NO" {{ (string) old('PF', !empty($user) ? $user->PF : '') === 'NO' ? 'selected' : '' }}>NO</option>
                           <option value="Yes" {{ (string) old('PF', !empty($user) ? $user->PF : '') === 'Yes' ? 'selected' : '' }}>Yes</option>
                           </select>
                           @error('PF')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4" id="pf_no_field" style="display: none;">
                           <label class="form-label" for="PF_No">PF No <small class="text-danger">*</small></label>
                           <input type="text" class="form-control @error('PF_No') is-invalid @enderror"
                              name="PF_No" id="PF_No" placeholder="PF No"
                              value="{{ old('PF_No', !empty($user) ? $user->PF_No : '') }}">
                           @error('PF_No')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="ESI">ESI <small class="text-danger">*</small></label>
                           <select id="ESI" name="ESI" class="form-control choices-single">
                           <option value="NO" {{ (string) old('ESI', !empty($user) ? $user->ESI : '') === 'NO' ? 'selected' : '' }}>NO</option>
                           <option value="Yes" {{ (string) old('ESI', !empty($user) ? $user->ESI : '') === 'Yes' ? 'selected' : '' }}>Yes</option>
                           </select>
                           @error('ESI')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4" id="esi_no_field" style="display: none;">
                           <label class="form-label" for="ESI_No">ESI No <small class="text-danger">*</small></label>
                           <input type="text" class="form-control @error('ESI_No') is-invalid @enderror"
                              name="ESI_No" id="ESI_No" placeholder="ESI No"
                              value="{{ old('ESI_No', !empty($user) ? $user->ESI_No : '') }}">
                           @error('ESI_No')
                           <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="allowance_amount">
                             Other Allowance Amount <small class="text-danger">*</small>
                           </label>

                           <input type="number" step="0.01"
                              class="form-control @error('allowance_amount') is-invalid @enderror"
                              name="allowance_amount"
                              id="allowance_amount"
                              placeholder="Allowance Amount"
                              value="{{ old('allowance_amount', !empty($user) ? $user->allowance_amount : '') }}">

                           @error('allowance_amount')
                              <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="pf_amount">
                              PF Amount <small class="text-danger">*</small>
                           </label>

                           <input type="number" step="0.01"
                              class="form-control @error('pf_amount') is-invalid @enderror"
                              name="pf_amount"
                              id="pf_amount"
                              placeholder="PF Amount"
                              value="{{ old('pf_amount', !empty($user) ? $user->pf_amount : '') }}">

                           @error('pf_amount')
                              <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="hra_allowance_amount">
                              HRA Allowance Amount <small class="text-danger">*</small>
                           </label>

                           <input type="number" step="0.01"
                              class="form-control @error('hra_allowance_amount') is-invalid @enderror"
                              name="hra_allowance_amount"
                              id="hra_allowance_amount"
                              placeholder="HRA Allowance Amount"
                              value="{{ old('hra_allowance_amount', !empty($user) ? $user->hra_allowance_amount : '') }}">

                           @error('hra_allowance_amount')
                              <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>

                       

                        <div class="mb-3 col-md-4">
                           <label class="form-label" for="emp_id">
                              Employee ID <small class="text-danger">*</small>
                           </label>

                           <input type="text"
                              class="form-control @error('emp_id') is-invalid @enderror"
                              name="emp_id"
                              id="emp_id"
                              placeholder="Employee ID"
                              value="{{ old('emp_id', !empty($user) ? $user->emp_id : '') }}">

                           @error('emp_id')
                              <small class="text-danger">{{ $message }}</small>
                           @enderror
                        </div>
                        </div>
                            <button type="submit" class="btn btn-primary">{{ !empty($user) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('users') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection


<!-- searchable dropdown script start-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single_status"));
    });
</script>
<!-- searchable dropdown script end-->
 <script>
document.addEventListener("DOMContentLoaded", function() {
    new Choices(document.querySelector(".choices-multiple"), {
        removeItemButton: true,
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const totalSalary = document.getElementById("total_salary");
    const perHour = document.getElementById("perhour_salary");

    function calculatePerHour() {
        let salary = parseFloat(totalSalary.value);

        if (!isNaN(salary)) {
            let hourly = salary / (26 * 8);
            perHour.value = hourly.toFixed(2);
        } else {
            perHour.value = "";
        }
    }

    totalSalary.addEventListener("keyup", calculatePerHour);
    totalSalary.addEventListener("change", calculatePerHour);

    calculatePerHour();
});
</script>
<script>
function togglePFNo() {
    const pf = document.getElementById('PF').value;
    const field = document.getElementById('pf_no_field');

    if (pf === 'Yes') {
        field.style.display = 'block';
    } else {
        field.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    togglePFNo();

    document.getElementById('PF').addEventListener('change', togglePFNo);
});
</script>