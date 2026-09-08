@extends('backend.partials.master')
@section('title')
Company Settings
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Company Settings</h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('companysettings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <!-- Company Name -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Company Name <small class="text-danger">*</small></label>
                                    <input type="text" name="company_name" class="form-control" value="{{ $setting->company_name }}">
                                    @error('company_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Address -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control">{{ $setting->address }}</textarea>
                                    @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- GSTIN -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">GSTIN</label>
                                    <input type="text" name="gstin" class="form-control" value="{{ $setting->gstin }}" maxlength="15">
                                    @error('gstin') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Mobile Number -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="mobile_number" class="form-control" value="{{ $setting->mobile_number }}" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                                    @error('mobile_number') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- State -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">State</label>
                                    <input type="text" name="state" class="form-control" value="{{ $setting->state }}">
                                    @error('state') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- State Code -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">State Code</label>
                                    <input type="text" name="state_code" class="form-control" value="{{ $setting->state_code }}" maxlength="2" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                                    @error('state_code') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Account Holder Name -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Account Holder Name</label>
                                    <input type="text" name="accountholser_name" class="form-control" value="{{ $setting->accountholser_name }}">
                                    @error('accountholser_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Bank Name -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ $setting->bank_name }}">
                                    @error('bank_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Account Number -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" value="{{ $setting->account_number }}" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                                    @error('account_number') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- IFSC Code -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">IFSC Code</label>
                                    <input type="text" name="ifsc_code" class="form-control" value="{{ $setting->ifsc_code }}" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');">
                                    @error('ifsc_code') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Branch -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Branch</label>
                                    <input type="text" name="Branch" class="form-control" value="{{ $setting->Branch }}">
                                    @error('Branch') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Enotify Token -->
                                <!--<div class="mb-3 col-md-3">-->
                                <!--    <label class="form-label">Enotify Token</label>-->
                                <!--    <input type="text" name="enotify_token" class="form-control" value="{{ $setting->enotify_token }}">-->
                                <!--    @error('enotify_token') <small class="text-danger">{{ $message }}</small> @enderror-->
                                <!--</div>-->

                                <!-- Enotify Token -->
                                <!--<div class="mb-3 col-md-3">-->
                                <!--    <label class="form-label">Web Url <small class="text-primary">i.e https://wms.com</small></label>-->
                                <!--    <input type="text" name="web_url" class="form-control" value="{{ $setting->web_url }}">-->
                                <!--    @error('web_url') <small class="text-danger">{{ $message }}</small> @enderror-->
                                <!--</div>-->

                                <!-- Email for Sending -->
                                <div class="mb-3 col-md-3">
                                    <label class="form-label">Send Email</label>
                                    <input class="form-check-input" type="checkbox" name="email_enabled" id="email_enabled" {{ $setting->email_enabled ? 'checked' : '' }}>
                                    @error('email_enabled') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- WhatsApp for Sending -->
                                <!--<div class="mb-3 col-md-3">-->
                                <!--    <label class="form-label">Send WhatsApp Msg</label>-->
                                <!--    <input class="form-check-input" type="checkbox" name="wp_enabled" id="wp_enabled" {{ $setting->wp_enabled ? 'checked' : '' }}>-->
                                <!--    @error('wp_enabled') <small class="text-danger">{{ $message }}</small> @enderror-->
                                <!--</div>-->
                            </div>

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection