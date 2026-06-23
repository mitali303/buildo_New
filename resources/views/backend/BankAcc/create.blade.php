@extends('backend.partials.master')

@section('title', !empty($bankacc) ? 'Edit Bank Account' : 'Create Bank Account')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">{{ !empty($bankacc) ? 'Edit Bank Account' : 'Create Bank Account' }}</h1>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ !empty($bankacc) ? route('BankAcc.update') : route('BankAcc.store') }}"
                              method="POST">
                            @csrf
                            @if(!empty($bankacc))
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $bankacc->ID }}">
                            @endif

                            <div class="row gy-3">

                               {{-- Bank Name --}}
                                <div class="col-md-4">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name"
                                        value="{{ old('bank_name', $bankacc->Name ?? '') }}"
                                        class="form-control @error('bank_name') is-invalid @enderror" >
                                    @error('bank_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Account Number --}}
                                <div class="col-md-4">
                                    <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="account_no"
                                        value="{{ old('account_no', $bankacc->ACNo ?? '') }}"
                                        class="form-control @error('account_no') is-invalid @enderror" >
                                    @error('account_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Branch --}}
                                <div class="col-md-4">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <input type="text" name="branch"
                                        value="{{ old('branch', $bankacc->Branch ?? '') }}"
                                        class="form-control @error('branch') is-invalid @enderror" >
                                    @error('branch') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- IFSC --}}
                                <div class="col-md-4">
                                    <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc"
                                        value="{{ old('ifsc', $bankacc->IFSC ?? '') }}"
                                        class="form-control @error('ifsc') is-invalid @enderror" >
                                    @error('ifsc') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- Opening Balance --}}
                                <div class="col-md-4">
                                    <label class="form-label">Opening Balance <span class="text-danger">*</span></label>
                                    <input type="number" name="opening_balance" step="0.01"
                                        value="{{ old('opening_balance', $bankacc->OBalance ?? '') }}"
                                        class="form-control @error('opening_balance') is-invalid @enderror" >
                                    @error('opening_balance') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>


                            </div>
                            <br>
                            <button type="submit" class="btn btn-primary">{{ !empty($bankacc) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('BankAcc') }}" class="btn btn-secondary">Cancel</a>
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

