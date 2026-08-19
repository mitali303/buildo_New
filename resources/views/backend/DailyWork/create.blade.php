@extends('backend.partials.master')
@section('title', !empty($dailyWork) ? 'Edit Daily Work' : 'Create Daily Work')
@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">
            <h1 class="h3 mb-4">
                {{ !empty($dailyWork) ? 'Edit Daily Work' : 'Create Daily Work' }}
            </h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ !empty($dailyWork) ? route('DailyWork.update') : route('DailyWork.store') }}" method="POST" enctype="multipart/form-data">

                @csrf
                    @if(!empty($dailyWork))
                        <input type="hidden"  name="id"  value="{{ $dailyWork->ID }}">
                    @endif
                <div class="row g-3">
                        {{-- Date --}}
                        <div class="col-md-6">
                                <label class="form-label">
                                    Date <span class="text-danger">*</span>
                                </label>
                                    <input type="date"name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date',$dailyWork->date ?? date('Y-m-d')) }}">
                                    @error('date')
                                        <small class="text-danger">
                                        {{ $message }}
                                        </small>
                                    @enderror
                        </div>

                            {{-- Scheme --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    Scheme <span class="text-danger">*</span>
                                </label>
                                <select name="ClientID"  id="scheme" class="form-select @error('ClientID') is-invalid @enderror">
                                    <option value="">-- Select Scheme --</option>
                                    @foreach($schemes as $scheme)
                                        <option value="{{ $scheme->ID }}" {{ old('ClientID',$dailyWork->ClientID ?? '') == $scheme->ID ? 'selected':'' }} >
                                            {{ $scheme->Name }}
                                        </option>
                                    @endforeach
                                </select>
                                    @error('ClientID')
                                        <small class="text-danger">
                                            {{ $message }}
                                        </small>
                                    @enderror
                            </div>
                        {{-- Hidden Scheme Name --}}
                            <input type="hidden" name="sitename" id="sitename" value="{{ old('sitename',$dailyWork->sitename ?? '') }}">
                        {{-- Work Image --}}
                            <div class="col-md-6">
                                <label class="form-label">
                                    Work Image <span class="text-danger">*</span>
                                </label>

                                <input type="file"
                                    name="img"
                                    class="form-control @error('img') is-invalid @enderror"
                                    accept="image/jpeg,image/jpg,image/png,image/webp">

                                <small class="text-muted">
                                    JPG, JPEG, PNG, WEBP only. Maximum size: 1 MB.
                                </small>

                                @error('img')
                                    <small class="text-danger d-block">
                                        {{ $message }}
                                    </small>
                                @enderror

                                {{-- Existing Image on Edit --}}
                                <!-- @if(!empty($dailyWork->img))
                                    <div class="mt-3">
                                        <label class="form-label d-block">
                                            Current Image
                                        </label>

                                        <img src="{{ asset('storage/' . $dailyWork->img) }}"
                                            alt="Work Image"
                                            class="img-thumbnail"
                                            style="width: 150px; height: 100px; object-fit: cover;">
                                    </div>
                                @endif -->
                            </div>
                            {{-- Work Report --}}
                    <div class="col-md-12">
                            <label class="form-label">
                                Work Report <span class="text-danger">*</span>
                            </label>
                            <textarea name="workdone" rows="5" class="form-control @error('workdone') is-invalid @enderror"  
                                placeholder="Enter Work Report">{{ old('workdone',$dailyWork->workdone ?? '') }}</textarea>
                                @error('workdone')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror
                    </div>
                    
                </div>

                <div class="pt-4">
                        <button type="submit"  class="btn btn-primary me-2">
                            {{ !empty($dailyWork) ? 'Update' : 'Create' }}
                        </button>
                        <a href="{{ route('DailyWork') }}" class="btn btn-secondary">Cancel</a>     
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


$(document).ready(function(){

    $('#scheme').change(function(){

        let name = $("#scheme option:selected").text();

        $("#sitename").val(name);

    });


    // Edit time selected scheme name set

    let selectedName = $("#scheme option:selected").text();

    if(selectedName != '-- Select Scheme --')
    {
        $("#sitename").val(selectedName);
    }


});

</script>

@endpush