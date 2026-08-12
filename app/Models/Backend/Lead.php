<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lead extends Model
{
    use HasFactory;

    protected $table = "leads";

    protected $fillable = [
        'company_name',
        'lead_name',
        'mobile_1',
        'mobile_2',
        'address',
        'country',
        'state',
        'district',
        'taluka',
        'pincode',
        'category_name',
        'subcategory',
        'lead_type',
        'email_1',
        'email_2',
        'lead_stage',
        'lead_source_id',
        'description',
        'product_requirements',
        'assign_to',
        'next_followup_date',
        'generated_by',
        'status',
    ];

    // const STAGE_NEW = 1;
    // const STAGE_CONTACTED = 2;
    // const STAGE_QUOTATION_SEND = 3;
    // const STAGE_INTERESTED = 4;
    // const STAGE_VISIT = 5;
    // const STAGE_UNDER_REVIEW = 6;
    // const STAGE_CONVERT = 7;
    // const STAGE_UNQUALIFIED = 8;

    // public static function stageLabels(): array
    // {
    //     return [
    //         self::STAGE_NEW => 'New',
    //         self::STAGE_CONTACTED => 'Contacted',
    //         self::STAGE_QUOTATION_SEND => 'Quotation Send',
    //         self::STAGE_INTERESTED => 'Interested',
    //         self::STAGE_VISIT => 'Visit',
    //         self::STAGE_UNDER_REVIEW => 'Under Review',
    //         self::STAGE_CONVERT => 'Convert',
    //         self::STAGE_UNQUALIFIED => 'Unqualified',
    //     ];
    // }
    public static function stageLabels(): array
        {
            return DB::table('leads_stage')
                ->orderBy('sequence', 'asc')
                ->pluck('name', 'id')
                ->toArray();
        }
        public static function stageColors(): array
        {
            return DB::table('leads_stage')
                ->orderBy('sequence')
                ->pluck('color', 'id')
                ->toArray();
        }

        public static function stageIcons(): array
        {
            return DB::table('leads_stage')
                ->orderBy('sequence')
                ->pluck('icon', 'id')
                ->toArray();
        }

    public function firm()
    {
        return $this->belongsTo(FirmMaster::class, 'firm_id');
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    public function generatedByUser()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function contacts()
    {
        return $this->hasMany(LeadContact::class, 'lead_id');
    }

    public function calls()
    {
        return $this->hasMany(LeadCall::class, 'lead_id')->orderByDesc('call_date');
    }
    public function meetings()
    {
        return $this->hasMany(\App\Models\Backend\LeadMeeting::class, 'lead_id');
    }
    public function isAccessibleByCurrentUser(): bool
{
    $user = auth()->user();

    if (!$user) {
        return false;
    }

    if ($user->isAdmin()) {
        return true;
    }

    return $this->assign_to === $user->id || $this->generated_by === $user->id;
}
}