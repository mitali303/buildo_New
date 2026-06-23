@extends('backend.partials.master')
@section('title')
    User Create
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Create User</h1>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="@if(!empty($user)){{route('users.update')}}@else{{route('users.store')}}@endif" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!empty($user))
                             @method('PUT')
                                <input type="hidden" name="id" id="id" value="{{$user->ID}}"/>
                            @endif
                            <div class="row"> 
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputEmail4">Name <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control @error('Name') is-invalid @enderror" value="{{ old('name', $user->Name ?? '') }}" name="name" id="name" placeholder="Name" >
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputEmail4">Username <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control no-uppercase @error('UserID') is-invalid @enderror" value="{{ old('username', $user->UserID ?? '') }}" name="username" id="username" placeholder="Username">
                                    @error('username')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                              
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="password">Password 
                                        @if(!empty($user)) 
                                            <small class="text-danger">If you don't want to change, leave blank</small> 
                                        @else
                                            <small class="text-danger">*</small>
                                        @endif
                                    </label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password">
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputPassword4">Role <small class="text-danger">*</small></label>
                                    <select class="form-control choices-single" name="role_id">
                                        <optgroup label="Select Role">
                                            @foreach($roles as $rl) 
                                                <option value="{{ $rl->id }}"
                                                {{ (string) old('role_id', $user->Role ?? '') == (string) $rl->id ? 'selected' : '' }}>
                                                {{ $rl->name }}
                                            </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    @error('role_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                 <div class="mb-3 col-md-4">
                                    <label class="form-label" for="schemes">Schemes <small class="text-danger">*</small></label>
                                 @php
                                    $user_schemes = [];

                                    if (!empty($user)) {
                                        // Use $user->scheme (the actual column)
                                        $schemes_json = $user->scheme ?? '[]';
                                        $user_schemes = json_decode($schemes_json, true) ?? [];
                                    }

                                    // convert everything to string so in_array works
                                    $user_schemes = array_map('strval', $user_schemes);
                                    @endphp

                                    <select class="form-control choices-multiple" name="schemes[]" multiple>
                                        @foreach($schemes as $scheme)
                                            <option value="{{ $scheme->ID }}"
                                                {{ in_array((string)$scheme->ID, old('schemes', $user_schemes)) ? 'selected' : '' }}>
                                                {{ $scheme->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('schemes')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            <div class="mb-3 col-md-4">
                                <label class="form-label">Access Type <small class="text-danger">*</small></label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="access_type" id="access_web" value="web"
                                            {{ old('access_type', $user->access_type ?? '') == 'web' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="access_web">Web</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="access_type" id="access_mobile" value="mobile"
                                            {{ old('access_type', $user->access_type ?? '') == 'mobile' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="access_mobile">Mobile</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="access_type" id="access_both" value="both"
                                            {{ old('access_type', $user->access_type ?? '') == 'both' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="access_both">Both</label>
                                    </div>
                                </div>
                                @error('access_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($user) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('users') }}" class="btn btn-secondary">Cancel</a>
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
        new Choices(document.querySelector(".choices-single_status"));
    });
</script>
<!-- searchable dropdown script end-->
 <script>
document.addEventListener("DOMContentLoaded", function() {
    new Choices(document.querySelector(".choices-multiple"), {
        removeItemButton: true,
    });
});
</script>