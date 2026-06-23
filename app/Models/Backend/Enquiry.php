<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $table = 'enquiries';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'Created';
    const UPDATED_AT = 'LastEdited';

    protected $fillable = [
        'customer_name',
        'email',
        'phone_no',
        'address',
        'scheme_id',
        'bill_no',
        'queries',
        'status'
    ];
}