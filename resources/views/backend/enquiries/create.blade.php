@extends('backend.partials.master')

@section('title', !empty($enquiry) ? 'Edit Enquiry' : 'Create Enquiry')

@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-4">
            {{ !empty($enquiry) ? 'Edit Enquiry' : 'Create Enquiry' }}
        </h1>

        <div class="card">
            <div class="card-body">

                <form action="{{ !empty($enquiry) ? route('enquiries.update',$enquiry->ID) : route('enquiries.store') }}"
                    method="POST">

                    @csrf

                    @if(!empty($enquiry))
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $enquiry->ID }}">
                    @endif

                    <div class="row g-3">

                        {{-- Customer Name --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Customer Name <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                name="customer_name"
                                class="form-control @error('customer_name') is-invalid @enderror"
                                value="{{ old('customer_name', $enquiry->customer_name ?? '') }}"
                                placeholder="Enter Customer Name">

                            @error('customer_name')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Mobile --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Mobile Number <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                name="phone_no"
                                maxlength="10"
                                class="form-control @error('phone_no') is-invalid @enderror"
                                value="{{ old('phone_no', $enquiry->phone_no ?? '') }}"
                                placeholder="Enter Mobile Number">

                            @error('phone_no')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Email
                            </label>

                            <input type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $enquiry->email ?? '') }}"
                                placeholder="Enter Email">

                            @error('email')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Status --}}



                        <div class="col-md-6">
                            <label class="form-label">
                                Status
                            </label>

                            <select name="status"
                                class="form-select @error('status') is-invalid @enderror">

                                <option value="1"
                                    {{ old('status',$enquiry->status ?? '')=='1'?'selected':'' }}>
                                    Active
                                </option>

                                <option value="0"
                                    {{ old('status',$enquiry->status ?? '')=='0'?'selected':'' }}>
                                    Inactive
                                </option>

                            </select>

                            @error('status')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        {{-- Assign User --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Assign User <span class="text-danger">*</span>
                            </label>

                            <select name="userID"
                                class="form-select select2  @error('userID') is-invalid @enderror">

                                <option value="">-- Select User --</option>

                                <!-- @foreach($users as $user)
                                    <option value="{{ $user->ID }}"
                                        {{ old('userID',$enquiry->userID ?? '') == $user->ID ? 'selected' : '' }}>
                                        {{ $user->Name }}
                                    </option>
                                @endforeach -->
                                @foreach($users as $user)
                                <option value="{{ $user->ID }}"> {{ $user->Name }} </option>
                                @endforeach

                            </select>

                            @error('userID')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Address --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Address
                            </label>

                            <input type="text"
                                name="address"
                                class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address', $enquiry->address ?? '') }}"
                                placeholder="Enter Address">

                            @error('address')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Query --}}
                        <div class="col-md-12">
                            <label class="form-label">
                                Query
                            </label>

                            <textarea name="queries"
                                rows="4"
                                class="form-control @error('queries') is-invalid @enderror"
                                placeholder="Enter Customer Query">{{ old('queries',$enquiry->queries ?? '') }}</textarea>

                            @error('queries')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>

                    <div class="pt-4">

                        <button type="submit" class="btn btn-primary me-2">
                            {{ !empty($enquiry) ? 'Update' : 'Create' }}
                        </button>

                        <a href="{{ route('enquiries.index') }}"
                            class="btn btn-secondary">
                            Cancel
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>
</main>

@endsection

@push('scripts')
<script>
    feather.replace();
</script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "-- Select User --",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush