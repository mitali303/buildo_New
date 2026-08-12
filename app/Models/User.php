<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'ID'; // primary key define 
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'UserID',
        'Password',
        'Role',
        'ClientID',
        'access_type',
        'scheme', // JSON
         'total_salary',
        'perhour_salary',
        'overtime_salary_perhour',
        'designation',
        'account_no',
        'IFSC',
        'bank_name',
        'PF',
        'PF_No',
        'ESI',
        'ESI_No',
        'allowance_amount',
        'pf_amount',
        'hra_allowance_amount',
        'emp_id',
        'status',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];
    protected $casts = [
        'Password' => 'hashed',
    ];

    // Password hash setter
//    public function setPasswordAttribute($value)
// {
//     $this->attributes['Password'] = Hash::make($value);
// }

    // JSON based schemes getter
    public function getSchemesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
    public function isAdmin(): bool
    {
        return $this->Role == 1;
    }
    public function isSupervisor(): bool
{
    return $this->Role == 2;
}
}