{{-- resources/views/backend/supplier_contractor/form.blade.php --}}
@extends('backend.partials.master')

@section('title', !empty($supplier) ? 'Edit Supplier / Contractor' : 'Create Supplier / Contractor')

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-3">{{ !empty($supplier) ? 'Edit Supplier / Contractor' : 'Create Supplier / Contractor' }}</h1>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        <form  action="{{ !empty($supplier) ? route('SupplierContractor.update') : route('SupplierContractor.store') }}"
                               method="POST">
                            @csrf
                            @if(!empty($supplier))
                                @method('PUT')
                                <input type="hidden" name="id" value="{{ $supplier->ID }}">
                            @endif

                            <div class="row">

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $supplier->Name ?? '') }}" >
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select id="typeSelector" name="type" class="form-control choices-single @error('type') is-invalid @enderror" >
                                        <option value="">-- Select Type --</option>
                                        <option value="VENDOR" {{ old('type', $supplier->Type ?? '') == 'VENDOR' ? 'selected' : '' }}>SUPPLIER</option>
                                        <option value="CONTRACTOR" {{ old('type', $supplier->Type ?? '') == 'CONTRACTOR' ? 'selected' : '' }}>CONTRACTOR</option>
                                    </select>
                                    @error('type') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                           value="{{ old('address', $supplier->Address ?? '') }}" >
                                    @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                {{-- <div class="mb-3 col-md-4">
                                    <label class="form-label">Fax <span class="text-danger">*</span></label>
                                    <input type="text" name="fax" class="form-control @error('fax') is-invalid @enderror"
                                           value="{{ old('fax', $supplier->Fax ?? '') }}" >
                                    @error('fax') <small class="text-danger">{{ $message }}</small> @enderror
                                </div> --}}

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
                                           value="{{ old('contact_person', $supplier->ContactPerson ?? '') }}" >
                                    @error('contact_person') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror"
                                           value="{{ old('mobile_number', $supplier->ContactNumber ?? '') }}" >
                                    @error('mobile_number') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Email </label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $supplier->Email ?? '') }}">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Landline</label>
                                    <input type="text" name="landline" class="form-control"
                                           value="{{ old('landline', $supplier->Landline ?? '') }}">
                                    @error('landline') <small class="text-danger">{{ $message }}</small> @enderror

                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">PAN No</label>
                                    <input type="text" name="pan_no" class="form-control"
                                           value="{{ old('pan_no', $supplier->pan_no ?? '') }}">
                                           @error('pan_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">GST No</label>
                                    <input type="text" name="gst_no" class="form-control"
                                           value="{{ old('gst_no', $supplier->GST_no ?? '') }}">
                                           @error('gst_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4" id="retentionGroup">
                                    <label class="form-label">Retention % <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0"
                                           name="retention" class="form-control @error('retention') is-invalid @enderror"
                                           value="{{ old('retention', $supplier->retain_per ?? '') }}">
                                    @error('retention') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label">Narration</label>
                                    <textarea name="narration" rows="1" class="form-control">{{ old('narration', $supplier->Narration ?? '') }}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($supplier) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('SupplierContractor') }}" class="btn btn-secondary">Cancel</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Choices !== 'undefined' && document.querySelector('#typeSelector')) {
    new Choices('#typeSelector', { searchEnabled: false });
}

    const typeSelect = document.getElementById('typeSelector');
    const retentionDiv = document.getElementById('retentionGroup');

    function toggleRetention() {
    if (typeSelect.value === 'CONTRACTOR') {
        retentionDiv.classList.remove('d-none');
    } else {
        retentionDiv.classList.add('d-none');
        retentionDiv.querySelector('input').value = '';
    }
}

    toggleRetention();
    typeSelect.addEventListener('change', toggleRetention);
});
</script>
@endpush
