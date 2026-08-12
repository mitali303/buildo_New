@extends('backend.partials.master')
@section('title')
    Call Status Create
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Create Call Status</h1>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="@if(!empty($old)){{ route('CallStatus.update') }}@else{{ route('CallStatus.store') }}@endif" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!empty($old))
                                @method('PUT')
                                <input type="hidden" name="id" id="id" value="{{ $old->id }}"/>
                            @endif
                            <div class="row">


                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="name">Name <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $old->name ?? '') }}" name="name" id="name"
                                        placeholder="Call Status Name" >
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>


                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="type">Type  <small class="text-danger">*</small></label>
                                    <select  id="type" name="type" class="form-control choices-single-cat @error('category') is-invalid @enderror" >
                                    <option value="">Select Category</option>
                                    <option value="Lead" {{ old('type', $old->type ?? '') == 'Lead' ? 'selected' : '' }}>Lead</option>
                                    <option value="Company" {{ old('type', $old->type ?? '') == 'Company' ? 'selected' : '' }}>Company</option>
                                    <option value="Both" {{ old('type', $old->type ?? '') == 'Both' ? 'selected' : '' }}>Both</option>

                                    </select>
                                    @error('type')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>


                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputStatus">Status <small class="text-danger">*</small></label>
                                    <select id="inputStatus" name="status" class="form-control choices-single" >
                                        <option value="1" {{ old('status', $old->status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status', $old->status ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($old) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('CallStatus') }}" class="btn btn-secondary">Cancel</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
<script>
   document.addEventListener("DOMContentLoaded", function() {
       // Choices.js
       
       new Choices(document.querySelector(".choices-single-cat"));
      
   });
</script>