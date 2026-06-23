<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    use HasFactory;

    protected $table = 'scheme_step1'; 
    public $timestamps = false;

    // Users relation
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'scheme_user', 'scheme_id', 'user_id');
    }
}