<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShiftAssignment extends Model
{
    use HasFactory;
    protected $table = 'user_shift_assignments';

    protected $fillable = [
        'user_id',
        'emp_id',
        'shift_id',
        'from_date',
        'to_date',
        'is_active',
        'created_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
