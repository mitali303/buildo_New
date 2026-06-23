@extends('backend.partials.master')

@section('title', !empty($scheme) ? 'Edit Site Details' : 'Create Site Details')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ !empty($scheme) ? 'Edit Site Details' : 'Create Site Details' }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ !empty($scheme) ? route('Scheme.update') : route('Scheme.store') }}" method="POST">
                    @csrf
                    @if(!empty($scheme))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $scheme->ID }}">
                    @endif

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Site Name <span class="text-danger">*</span></label>
                            <input type="text" name="SiteName"
                                   id="SiteName"
                                   value="{{ old('SiteName', $scheme->Name ?? '') }}"
                                   class="form-control @error('SiteName') is-invalid @enderror"
                                   placeholder="Site Name"
                                   onblur="checkNameExists()">
                            @error('SiteName') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Address</label>
                            <textarea name="Address" rows="2"
                                      class="form-control @error('Address') is-invalid @enderror"
                                      placeholder="Address">{{ old('Address', $scheme->Address ?? '') }}</textarea>
                            @error('Address') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" name="Location"
                                   value="{{ old('Location', $scheme->Location ?? '') }}"
                                   class="form-control @error('Location') is-invalid @enderror"
                                   placeholder="Location">
                            @error('Location') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="row">
                        {{-- Only show dropdown if Client_Id is 'infyconst' --}}
                        @if(session('Client_Id') === 'infyconst')
                        <div class="mb-3 col-md-4" x-data="{ selected: '{{ old('cperson', $scheme->cperson ?? '') }}' }">
                            <label class="form-label">Company / Owner Name</label>
                            <template x-if="selected !== 'OTHER'">
                                <select class="form-control" name="cperson" id="cperson" x-model="selected" @change="selected = $event.target.value">
                                    <option value="">Select</option>
                                    <option value="OTHER">OTHER</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->cperson }}" {{ (old('cperson', $scheme->cperson ?? '') == $owner->cperson) ? 'selected' : '' }}>
                                            {{ $owner->cperson }}
                                        </option>
                                    @endforeach
                                </select>
                            </template>
                            <template x-if="selected === 'OTHER'">
                                <input type="text" name="cperson" class="form-control" placeholder="Enter Company Name" x-model="selected">
                            </template>
                            @error('cperson') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        @endif

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="ContactPerson"
                                   value="{{ old('ContactPerson', $scheme->contactperson ?? '') }}"
                                   class="form-control @error('ContactPerson') is-invalid @enderror"
                                   placeholder="Contact Person">
                            @error('ContactPerson') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="ContactNo"
                                   value="{{ old('ContactNo', $scheme->cnumber ?? '') }}"
                                   class="form-control @error('ContactNo') is-invalid @enderror"
                                   placeholder="Contact Number">
                            @error('ContactNo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Email </label>
                            <input type="email" name="Email"
                                   value="{{ old('Email', $scheme->Email ?? '') }}"
                                   class="form-control @error('Email') is-invalid @enderror"
                                   placeholder="Email">
                            @error('Email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Cash in Hand</label>
                            <input type="number" name="CashInHand"
                                   value="{{ old('CashInHand', $cashBalance ?? '') }}"
                                   class="form-control @error('CashInHand') is-invalid @enderror"
                                   placeholder="Cash in Hand">
                            @error('CashInHand') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($scheme) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('Scheme') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@endsection
