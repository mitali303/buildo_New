@extends('backend.partials.master')

@section('title', !empty($partners) ? 'Edit Partner / Loan / Investor' : 'Create Partner / Loan / Investor')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3">{{ !empty($partners) ? 'Edit Partner / Loan / Investor' : 'Create Partner / Loan / Investor' }}</h1>
        <div class="card">
            <div class="card-body">
                <form action="{{ !empty($partners) ? route('PartnerLoanInvestor.update') : route('PartnerLoanInvestor.store') }}"
                      method="POST">
                        @csrf
                        @if(!empty($partners))
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $partners->ID }}">
                        @endif
                    {{-- ROW 1: Type, Name, Contact No --}}
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="Type" class="form-control @error('Type') is-invalid @enderror choices-single-type">
                                <option value="">Select</option>
                                <option value="PARTNER" {{ old('Type', $partners->Type ?? '') == 'PARTNER' ? 'selected' : '' }}>PARTNER</option>
                                <option value="INVESTOR" {{ old('Type', $partners->Type ?? '') == 'INVESTOR' ? 'selected' : '' }}>INVESTOR</option>
                                <option value="LOAN" {{ old('Type', $partners->Type ?? '') == 'LOAN' ? 'selected' : '' }}>LOAN</option>
                            </select>
                            @error('Type') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="Name"
                                   value="{{ old('Name', $partners->Name ?? '') }}"
                                   class="form-control @error('Name') is-invalid @enderror"
                                   placeholder="Name">
                            @error('Name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label class="form-label">Contact No <span class="text-danger">*</span></label>
                            <input type="text" name="ContactNo"
                                   value="{{ old('ContactNo', $partners->ContactNo ?? '') }}"
                                   class="form-control @error('ContactNo') is-invalid @enderror"
                                   placeholder="Contact No">
                            @error('ContactNo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    {{-- ROW 2: Email, Address --}}
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="Email" value="{{ old('Email', $partners->Email ?? '') }}"
                                   class="form-control @error('Email') is-invalid @enderror" placeholder="Email">
                            @error('Email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Address</label>
                            <textarea name="Address" rows="2" class="form-control @error('Address') is-invalid @enderror"
                                      placeholder="Address">{{ old('Address', $partners->Address ?? '') }}</textarea>
                            @error('Address') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    {{-- Submit --}}
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ !empty($partners) ? 'Update' : 'Create' }}
                            </button>
                            <a href="{{ route('PartnerLoanInvestor') }}" class="btn btn-secondary">Cancel</a>
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