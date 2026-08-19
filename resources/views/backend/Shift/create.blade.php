@extends('backend.partials.master')
@section('title')
    Task Shift
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle"> {{ !empty($old) ? 'Edit Shift' : 'Create Shift' }}</h1>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="@if(!empty($old)){{ route('Shift.update') }}@else{{ route('Shift.store') }}@endif" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!empty($old))
                                @method('PUT')
                                <input type="hidden" name="id" id="id" value="{{ $old->id }}"/>
                            @endif
                            <div class="row">


                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="shift">Shift name <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control @error('shift') is-invalid @enderror"
                                        value="{{ old('shift', $old->shift ?? '') }}" name="shift" id="shift"
                                        placeholder="Shift name" >
                                    @error('shift')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                 <div class="mb-3 col-md-4">
                                    <label class="form-label" for="shift_intime">In Time<small class="text-danger">*</small></label>
                                    <input type="time" class="form-control @error('shift_intime') is-invalid @enderror"
                                        value="{{ old('shift_intime', $old->shift_intime ?? '') }}" name="shift_intime" id="shift_intime"
                                        placeholder="In Time" >
                                    @error('shift_intime')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="shift_outtime">Out Time<small class="text-danger">*</small></label>
                                    <input type="time" class="form-control @error('shift_outtime') is-invalid @enderror"
                                        value="{{ old('shift_outtime', $old->shift_outtime ?? '') }}" name="shift_outtime" id="shift_outtime"
                                        placeholder="Out Time" >
                                    @error('shift_outtime')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>


                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($old) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('Shift') }}" class="btn btn-secondary">Cancel</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
