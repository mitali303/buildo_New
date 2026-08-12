@extends('backend.partials.master')

@section('title', !empty($lead) ? 'Edit Lead' : 'Create Lead')

@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">

        <h1 class="h3 mb-4">
            {{ !empty($lead) ? 'Edit Lead' : 'Create Lead' }}
        </h1>


        <div class="card">

            <div class="card-body">


                <form action="{{ !empty($lead) ? route('leads.update',$lead->ID) : route('leads.store') }}"
                      method="POST">

                    @csrf

                    @if(!empty($lead))
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $lead->ID }}">
                    @endif



                    {{-- Lead Name --}}
                    <div class="row g-3 mb-4">

                        <div class="col-md-6">

                            <label class="form-label">
                                Lead Name <span class="text-danger">*</span>
                            </label>


                            <input type="text"
                                   name="name"
                                   value="{{ old('name',$lead->name ?? '') }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Enter Lead Name">


                            @error('name')
                                <small class="text-danger">
                                    {{ $message }}
                                </small>
                            @enderror


                        </div>

                    </div>



                    {{-- Buttons --}}
                    <div class="pt-4">

                        <button type="submit" class="btn btn-primary me-2">

                            {{ !empty($lead) ? 'Update' : 'Create' }}

                        </button>


                        <a href="{{ route('leads.index') }}"
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

@endpush