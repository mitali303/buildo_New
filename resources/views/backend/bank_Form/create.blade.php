@extends('backend.partials.master')

@section('title', !empty($bankform) ? 'Edit Bank Loan Form' : 'Create Bank Loan Form')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">{{ !empty($bankform) ? 'Edit Bank Loan Form' : 'Create Bank Loan Form' }}</h1>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ !empty($bankform) ? route('BankForm.update') : route('BankForm.store') }}"
                            method="POST">
                            @csrf
                            @if(!empty($bankform))
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $bankform->ID }}">
                            @endif

                            <div class="row gy-3">

                                {{-- Bank Name --}}
                                <div class="col-md-4">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name"
                                        value="{{ old('bank_name', $bankform->bank_name ?? '') }}"
                                        class="form-control @error('bank_name') is-invalid @enderror">
                                    @error('bank_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- To Name --}}
                                <div class="col-md-4">
                                    <label class="form-label">To Name <span class="text-danger">*</span></label>
                                    <input type="text" name="to_name"
                                        value="{{ old('to_name', $bankform->to_name ?? '') }}"
                                        class="form-control @error('to_name') is-invalid @enderror">
                                    @error('to_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Bank Address --}}
                                <div class="col-md-4">
                                    <label class="form-label">Bank Address <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_address"
                                        value="{{ old('bank_address', $bankform->address ?? '') }}"
                                        class="form-control @error('bank_address') is-invalid @enderror">
                                    @error('bank_address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- City --}}
                                <div class="col-md-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city"
                                        value="{{ old('city', $bankform->city ?? '') }}"
                                        class="form-control @error('city') is-invalid @enderror">
                                    @error('city') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Pincode --}}
                                <div class="col-md-4">
                                    <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="pincode"
                                        value="{{ old('pincode', $bankform->pincode ?? '') }}"
                                        class="form-control @error('pincode') is-invalid @enderror">
                                    @error('pincode') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Subject --}}
                                <div class="col-md-4">
                                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                                    <input type="text" name="subject"
                                        value="{{ old('subject', $bankform->subject	 ?? '') }}"
                                        class="form-control @error('subject') is-invalid @enderror">
                                    @error('subject') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Loan Request Detail --}}
                                <div class="col-md-12">
                                    <label class="form-label">Loan Request Details <span class="text-danger">*</span></label>
                                    <textarea name="loan_details" rows="4"
                                        class="form-control @error('loan_details') is-invalid @enderror">{{ old('loan_details', $bankform->loan_request ?? '') }}</textarea>
                                    @error('loan_details') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                            </div>
                            <br>
                            <button type="submit" class="btn btn-primary">{{ !empty($bankform) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('BankForm') }}" class="btn btn-secondary">Cancel</a>
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
        new Choices(document.querySelector(".choices-single-designation"));
    });
</script>
<!-- searchable dropdown script end-->