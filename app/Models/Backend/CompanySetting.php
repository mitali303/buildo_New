<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

     protected $fillable = [
        'company_name',
        'logo_path',
        'icon_path',
        'address',
        'gstin',
        'mobile_number',
        'state',
        'state_code',
        'accountholser_name',
        'bank_name',
        'account_number',
        'ifsc_code',
        'Branch',
        'enotify_token',
        'web_url',
        'email_enabled',
        'wp_enabled',
    ];
}
