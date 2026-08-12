<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBooking extends Model
{
    use HasFactory;
    protected $table = 'booking_customer';
    protected $primaryKey = 'ID';
    protected $keyType = 'string';

     public $timestamps = false;
      protected $fillable = [
        'ID',
        'ClientID',
        'Created',
        'LastEdited',
        'CutomerName',
        'Address',
        'village',
        'taluka',
        'district',
        'pincode',
        'Contact',
        'Email',
        'BookingDate',
        'Idproof',
        'scanimg',
        'Scheme',
        'FlatNo',
        'Wing',
        'RateSqft',
        'BspAmount',
        'agreementAmt',
        'lightChrg',
        'parkingChrg',
        'maintances',
        'othersTotal',
        'docChrg',
        'roundUp',
        'sitedevCharges',
        'taxrate',
        'stamp_amt',
        'regiChrg',
        'vatAmt',
        'servicetax',
        'TaxamtTotal',
        'TotalFlatAmt',
        'regiChrgPer',
        'vatAmtPer',
        'servicetaxPer',
        'rate_per_sqft_type',
        'userID',
        'Ptype',
        'cancel_flag',
        'loan_sanction_amt',
        'bankname',
        'pilnth',
        'slab',
        'bricks',
        'plaster',
        'floaring',
        'plumbing',
        'project',
        'slab_total',
        'agreement_complete',
        'agreement_no',
        'reg_date',
    ];

    public function schemes()
    {
        return $this->belongsTo(SchemeDetail::class, 'Scheme', 'ID');
    }
    public function flatsdetail()
    {
        return $this->belongsTo(Flat_details::class, 'FlatNo', 'ID');
    }
    public function bookingpay()
    {
        return $this->belongsTo(CustomerPayment::class, 'Booking_ID', 'ID');
    }
}
