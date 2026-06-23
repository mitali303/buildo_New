@extends('backend.partials.master')

@section('title', !empty($agencys) ? 'Edit Agency' : 'Create Agency')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ !empty($agencys) ? 'Edit Agency' : 'Create Agency' }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ !empty($agencys) ? route('Agency.update') : route('Agency.store') }}"
                      method="POST">
                    @csrf
                    @if(!empty($agencys))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $agencys->ID }}">
                    @endif

                    {{-- ROW 1: Type, Name, Contact No --}}
                    <div class="row">
                        
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="Name"
                                   value="{{ old('Name', $agencys->Name ?? '') }}"
                                   class="form-control @error('Name') is-invalid @enderror"
                                   placeholder="Name">
                            @error('Name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Contact No <span class="text-danger">*</span></label>
                            <input type="text" name="ContactNo"
                                   value="{{ old('ContactNo', $agencys->ContactNo ?? '') }}"
                                   class="form-control @error('ContactNo') is-invalid @enderror"
                                   placeholder="Contact No">
                            @error('ContactNo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    
                        <div class="mb-3 col-md-4">
                             <label class="form-label">Address<span class="text-danger">*</span></label>
                            <textarea name="Address" rows="2"
                                      class="form-control @error('Address') is-invalid @enderror"
                                      placeholder="Address">{{ old('Address', $agencys->Address ?? '') }}</textarea>
                            @error('Address') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    {{-- ROW 2 : Rate Table --}}
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Category</th>
                                            <th>Male Rate</th>
                                            <th>Female Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        {{-- Mistri --}}
                                        <tr>
                                            <td><strong>Mistri</strong></td>
                                            <td>
                                                <input type="number" step="1" name="MistriMaleRate"
                                                    value="{{ old('MistriMaleRate', $agencys->Mistri_m_rate ?? '') }}"
                                                    class="form-control">
                                                @error('MistriMaleRate') <small class="text-danger">{{ $message }}</small> @enderror

                                            </td>
                                            <td>
                                                <input type="number" step="1" name="MistriFemaleRate"
                                                    value="{{ old('MistriFemaleRate', $agencys->Mistri_f_rate ?? '') }}"
                                                    class="form-control">
                                                    @error('MistriFemaleRate') <small class="text-danger">{{ $message }}</small> @enderror
                                            </td>
                                        </tr>

                                        {{-- Labour --}}
                                        <tr>
                                            <td><strong>Labour</strong></td>
                                            <td>
                                                <input type="number" step="1" name="LabourMaleRate"
                                                    value="{{ old('LabourMaleRate', $agencys->Labour_m_rate ?? '') }}"
                                                    class="form-control">
                                                    @error('LabourMaleRate') <small class="text-danger">{{ $message }}</small> @enderror
                                            </td>
                                            <td>
                                                <input type="number" step="1" name="LabourFemaleRate"
                                                    value="{{ old('LabourFemaleRate', $agencys->Labour_f_rate ?? '') }}"
                                                    class="form-control">
                                                    @error('LabourFemaleRate') <small class="text-danger">{{ $message }}</small> @enderror
                                            </td>
                                        </tr>

                                        {{-- Thekedar --}}
                                        <tr>
                                            <td><strong>Thekedar</strong></td>
                                            <td>
                                                <input type="number" step="1" name="ThekedarMaleRate"
                                                    value="{{ old('ThekedarMaleRate', $agencys->Thekedar_m_rate ?? '') }}"
                                                    class="form-control">
                                                    @error('ThekedarMaleRate') <small class="text-danger">{{ $message }}</small> @enderror
                                            </td>
                                            <td>
                                                <input type="number" step="1" name="ThekedarFemaleRate"
                                                    value="{{ old('ThekedarFemaleRate', $agencys->Thekedar_f_rate ?? '') }}"
                                                    class="form-control">
                                                    @error('ThekedarFemaleRate') <small class="text-danger">{{ $message }}</small> @enderror
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($agencys) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('Agency') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>
@endsection
<!-- searchable dropdown script start-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single-type"));
    });
</script>
<!-- searchable dropdown script end-->