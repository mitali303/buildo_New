@extends('backend.partials.master')

@section('title')
    {{ isset($materialRequest) ? 'Edit Material Request' : 'Create Material Request' }}
@endsection

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">{{ isset($materialRequest) ? 'Edit Material Request' : 'New Material Request' }}</h1>
            <a href="{{ route('material_request.list') }}" class="btn btn-secondary">Back</a>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ isset($materialRequest) ? route('material_request.update', $materialRequest->ID) : route('material_request.store') }}" method="POST">
                        @csrf
                        @if(isset($materialRequest))
                            @method('PUT')
                        @endif
                    @if(isset($materialRequest) && $materialRequest->ID)
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">ID</label>
                                <input type="text" class="form-control" value="{{ $materialRequest->ID }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ClientID</label>
                                <input type="text" class="form-control" value="{{ $materialRequest->ClientID ?? '' }}" readonly>
                            </div>
                        </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', isset($materialRequest) ? $materialRequest->Date : now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Request No</label>
                            <input type="text" name="request_no" class="form-control" value="{{ old('request_no', isset($materialRequest) ? $materialRequest->request_no : '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description', isset($materialRequest) ? $materialRequest->description : '') }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Remark</label>
                            <input type="text" name="remark" class="form-control" value="{{ old('remark', isset($materialRequest) ? $materialRequest->remark : '') }}">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
