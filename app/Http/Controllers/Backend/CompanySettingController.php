<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\CompanySetting;

class CompanySettingController extends Controller
{
    public function edit()
    {
        $setting = CompanySetting::first();
        return view('backend.companysettings.companysettings', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'address' => 'nullable|string',
            'gstin' => 'nullable|string',
            'mobile_number' => 'nullable',
            'state' => 'nullable|string',
            'state_code' => 'nullable|string',
            'accountholser_name' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'Branch' => 'nullable|string',
            'enotify_token' => 'nullable|string',
            'web_url' => 'nullable|string',
            'email_enabled' => 'nullable',
            'wp_enabled' => 'nullable',
        ]);

        $setting = CompanySetting::first();

        if ($request->hasFile('logo')) {
            $fileName = time() . '.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/logos'), $fileName);
            $setting->logo_path = 'uploads/logos/' . $fileName;
        }

        if ($request->hasFile('favicon')) {
            $fileName = time() . '.' . $request->favicon->extension();
            $request->favicon->move(public_path('uploads/logos/favicon'), $fileName);
            $setting->icon_path = 'uploads/logos/favicon/' . $fileName;
        }

        $setting->update([
            'company_name' => $request->company_name,
            'address' => $request->address,
            'gstin' => $request->gstin,
            'mobile_number' => $request->mobile_number,
            'state' => $request->state,
            'state_code' => $request->state_code,
            'accountholser_name' => $request->accountholser_name,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'Branch' => $request->Branch,
            'enotify_token' => $request->enotify_token,
            'web_url' => $request->web_url,
            'email_enabled' => $request->has('email_enabled'),
            'wp_enabled' => $request->has('wp_enabled'),
        ]);

        return redirect()->back()->with('success', 'Company settings updated successfully!');
    }
}
