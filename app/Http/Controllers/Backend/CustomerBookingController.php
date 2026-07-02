<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\SchemeDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Backend\CustomerBooking;
use App\Models\Backend\CustomerPayment;
use App\Models\Backend\Flat_details;
use Illuminate\Support\Facades\File;

use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerBookingController extends Controller
{
     public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = CustomerBooking::with(['schemes','flatsdetail','bookingpay'])
                ->select(
                    'ID',
                    'CutomerName',
                    'Address',
                    'Scheme',
                    'Contact',
                    'FlatNo',
                    'TotalFlatAmt',
                    

                )
                ->orderBy('Created', 'DESC');

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('date', function ($row) {
                    return date('d-m-Y', strtotime($row->Date));
                })

                ->addColumn('schemes_name', function ($row) {
                    return $row->schemes->Name ?? '';
                })
                ->addColumn('flat_no', function ($row) {
                    return ($row->flatsdetail->Wing ?? '') . '-' . ($row->flatsdetail->FlatNo ?? '');
                })
                // ->addColumn('booking', function ($row) {
                //     return $row->bookingpay->amt_pay ?? '';
                // })

                ->addColumn('actions', function ($row) {

                    $actions = '';

                    if (hasPermission('edit_customer_booking')) {
                        $actions .= '<a href="' . route('customer_booking.edit', $row->ID) . '" class="me-2 text-primary">
                                        <i data-feather="edit-2"></i>
                                    </a>';

                     $actions .= '<a href="' . route('customer_booking.reminder', $row->ID) . '" 
                        class="btn btn-sm btn-success me-2" title="Reminder Letter">
                        Reminder Letter
                    </a>';
                    }

                    if (hasPermission('delete_customer_booking')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="delete-form-' . $row->ID . '">
                                        <i data-feather="trash"></i>
                                    </a>
                                    <form id="delete-form-' . $row->ID . '" action="' . route('customer_booking.delete', $row->ID) . '" method="POST" class="d-none">
                                        ' . csrf_field() . method_field('DELETE') . '
                                    </form>';
                    }
                    

                    return $actions;
                })
                ->addColumn('slabs', function ($row) {

                    // You can change route if needed
                    $url = route('customer_booking.payment_schedule', $row->ID);

                    return '<a href="'.$url.'" class="btn btn-sm btn-info" title="slab">
                                <i class="fa fa-retweet"></i>
                            </a>';
                })



                ->rawColumns(['actions','slabs'])
                ->make(true);

                
        }

        return view('backend.Customer_booking.index');
    }

    public function reminder($id)
    {
        $booking = CustomerBooking::findOrFail($id);

        return view('backend.customer_booking.customerbooking_reminder', compact('booking'));
    }

   

    public function show(Request $request)
    {
        $booking = DB::table('booking_customer')
            ->where('ID', $request->id)
            ->first();

        $scheme = DB::table('scheme_step1')
            ->where('ID', $booking->ClientID)
            ->first();

        $flat = DB::table('flats_details')
            ->where('ID', $booking->FlatNo)
            ->first();

        $payamount = DB::table('booking_payment')
            ->where('Booking_ID', $booking->ID)
            ->where('payement_by', 'bank')
            ->sum('amt_pay');

        $selfmoney = $booking->TotalFlatAmt - $booking->loan_sanction_amt;

        $todayamount = DB::table('booking_payment')
            ->where('Booking_ID', $booking->ID)
            ->where('payement_by', '!=', 'bank')
            ->sum('amt_pay');

        $todayamountwithdownpayment = DB::table('booking_payment')
            ->where('Booking_ID', $booking->ID)
            ->where('payement_by', 'self')
            ->where('type', 'Downpayment')
            ->sum('amt_pay');

        $today_paid_amt = $todayamount + $todayamountwithdownpayment;

        $fdate = Carbon::createFromFormat('d-m-Y', $request->fdate);
        $tdate = Carbon::createFromFormat('d-m-Y', $request->tdate);

        return view('backend.Customer_booking.reminder_letter', compact(
            'booking',
            'scheme',
            'flat',
            'selfmoney',
            'today_paid_amt',
            'fdate',
            'tdate',
            'request'
        ));
    }

    public function create()
    {   
        $clientId = session('selected_scheme_id');
       
        $schemes = SchemeDetail::where('completFalg', 0)
        ->where('ID', $clientId)
        ->get();

        //$booking_cust = Booking_Customer::where('ClientID', $clientId)->get();
       $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $slabs = DB::table('slabs')
        ->where('ClientID', $clientId)
        ->first();

        if (!$slabs) {
            $slabs = (object)[
                'pilnth' => 0,
                'slab' => 0,
                'bricks' => 0,
                'plaster' => 0,
                'floaring' => 0,
                'plumbing' => 0,
                'project' => 0,
            ];
        }

       $bookcancelFlatIDs = DB::table('booking_cancel')
            ->where('SchemID', $clientId)
            ->pluck('flatID')
            ->toArray();

        if (empty($bookcancelFlatIDs)) {
            $bookcancelFlatIDs = [0];
        }

        /* 2️⃣ Get already booked flat IDs (excluding current user) */
        $bookingFlatIDs = DB::table('booking_customer')
            ->where('Scheme', $clientId)
            ->where('ID', '!=', request('Uid'))
            ->whereNotIn('FlatNo', $bookcancelFlatIDs)
            ->pluck('FlatNo')
            ->toArray();

        if (empty($bookingFlatIDs)) {
            $bookingFlatIDs = [0];
        }

        /* 3️⃣ Get available flats */
        $flatsDetails = DB::table('flats_details')
            ->where('scheme_ID', $clientId)
            ->whereNotIn('ID', $bookingFlatIDs)
            ->get();
     
        
        return view('backend.Customer_booking.create', compact('schemes','flatsDetails','slabs','banks','clientId'));
    }
    public function getFlatsNo(Request $request)
    {
        $sid       = $request->schmid;
        $wing      = $request->wing;
        $uid       = $request->Uid;
        $type_bill = $request->type_bill;
        $clientId  = session('selected_scheme_id');

        /** Booked flat IDs */
        $bookingFlatIds = DB::table('booking_customer')
            ->where('Scheme', $sid)
            ->where('ClientID', $clientId)
            ->where('cancel_flag', 0)
            ->where('ID', '!=', $uid)
            ->pluck('FlatNo')
            ->toArray();

        if (empty($bookingFlatIds)) {
            $bookingFlatIds = [0];
        }

        /** Flats query */
        $flatsQuery = DB::table('flats_details')
            ->where('Wing', $wing)
            ->where('ClientID', $clientId);

        if (!in_array($type_bill, ['add bill', 'd_Raise'])) {
            $flatsQuery->whereNotIn('ID', $bookingFlatIds);
        }

        $flats = $flatsQuery->get();

        /** Build SELECT HTML */
        $html  = '<select name="fno" id="fno" 
                    class="input-sm form-control chosen-select required"
                    data-placeholder="Select"
                    onchange="getFlatInfo();">';

        $html .= '<option value="">Select</option>';

        foreach ($flats as $flat) {

            $nm  = '';
            $cid = '';

            if ($type_bill === 'add bill') {
                $cust = DB::table('booking_customer')
                    ->where('Scheme', $sid)
                    ->where('FlatNo', $flat->ID)
                    ->where('ClientID', $clientId)
                    ->where('cancel_flag', 0)
                    ->first();

                if ($cust) {
                    $nm  = $cust->CutomerName;
                    $cid = '#'.$cust->ID;
                }
            }

            $html .= '<option value="'.$flat->ID.$cid.'">'
                .  $flat->FlatNo.' '.$nm
                .  '</option>';
        }

        $html .= '</select>';

        /** Re-initialize chosen */
        $html .= '<script>
                    $(".chosen-select").chosen();
                </script>';

        return response($html);
    }
    public function getFlatInfo(Request $request)
    {
        $flat = Flat_details::where('ID', $request->ftno)
            ->where('ClientID', session('selected_scheme_id'))
            ->first();

        if (!$flat) {
            return response()->json([]);
        }

        return response()->json([
            'FlatAttribute' => $flat->FlatAttribute,
            'TotalSqFt'     => $flat->TotalSqFt,
            'Other2'        => $flat->Other2,
            'Other1'        => $flat->Other1,
            'Terrace'       => $flat->Terrace,
            'Area'          => $flat->Area,
            'Floor'         => $flat->Floor,
            'Wing'          => $flat->Wing,
            'FlatType'      => ucfirst($flat->FlatType),
        ]);
    }
    public function accountByBookingPayment(Request $request)
    {
        $payMethod = $request->pay_method;

        if ($payMethod === 'cash') {
            $accounts = Bank_Acc::where('Name', 'Cash In Hand')
                ->where('ClientID', session('selected_scheme_id'))
                ->orderBy('Name')
                ->get();
            $selectOption = '';
        } else {
            $accounts = Bank_Acc::where('Name', '!=', 'Cash In Hand')
                ->where('ClientID', session('selected_scheme_id'))
                ->orderBy('Name')
                ->get();
            $selectOption = '<option value="">Select</option>';
        }

        $html = '<select id="account_no" name="account_no"
                    class="form-control chosen-select required"
                    onchange="getBalance();">';

        $html .= $selectOption;

        foreach ($accounts as $account) {
            $html .= '<option value="'.$account->ID.'">'
                . $account->Name.' ('.substr($account->ACNo, -3).')'
                . '</option>';
        }

        $html .= '</select>';

        $html .= '
            <script>
                $(".chosen-select").chosen();
            </script>
        ';

        return response($html);
    }

    public function store(Request $request)
    {
         $filesArr = [];

        if ($request->has('uploadfile')) {
            foreach ($request->uploadfile as $file) {
               
                $filesArr[] = $file;
            }
        }

        $files = implode(',', $filesArr);
        $bookingId = uniqid();

        $account = $request->validate([
            'name'      => 'required|string',
            'address'    => 'required|string',
            'village'      => 'nullable|string',
            'taluka'      => 'nullable|string',
            'district'    => 'nullable|string',
            'pincode' => 'nullable|string',
            'contactno'    => 'nullable|numeric',
            'email'  => 'nullable|string',
            'scheme'  => 'required|string',
            'idproof'  => 'required|string',
            'typesrch'  => 'required|string',
            'agreement_complete'   => 'nullable|numeric',
            
            'agreement_no'  => 'nullable|string',
            'fno'  => 'required|string',
            'rateamt'  => 'nullable|numeric',
            'RateSqft'   => 'nullable|numeric',
            'costBSP'   => 'nullable|numeric',
            'cost'   => 'nullable|numeric',
            'diff'   => 'nullable|numeric',
            'light'   => 'nullable|numeric',
            'parking'   => 'nullable|numeric',
            'maintance'   => 'nullable|numeric',
            'documentation'   => 'nullable|numeric',
            'sitedevcharges'   => 'nullable|numeric',
            'totalOthers'   => 'nullable|numeric',
            'stamp_amt'   => 'nullable|numeric',
            'vat_per'   => 'nullable|numeric',
            'vat'   => 'nullable|numeric',
            'servicetax_per'   => 'nullable|numeric',
            'servicetax'   => 'nullable|numeric',
            'regist_per'   => 'nullable|numeric',
            'registration'   => 'nullable|numeric',
            'TotalTaxAmt'   => 'nullable|numeric',
            'roundUp'   => 'nullable|numeric',
            'TotalFlatAmt'   => 'nullable|numeric',
            'Downpayment'   => 'nullable|numeric',
            'loan_sanction_amt'   => 'nullable|numeric',
            'bankname'   => 'nullable|string',
            'pilnth'   => 'nullable|numeric',
            'slab'   => 'nullable|numeric',
            'bricks'   => 'nullable|numeric',
            'plaster'   => 'nullable|numeric',
            'floaring'   => 'nullable|numeric',
            'plumbing'   => 'nullable|numeric',
            'project'   => 'nullable|numeric',
            'Pay_type'   => 'required|string',
            'receipt_no'   => 'nullable|string',
            'account_no'   => 'nullable|string',
            'cheque_no'   => 'nullable|string',
            'amount_pay'   => 'nullable|numeric',
            'narration'   => 'nullable|string',
        ]);

        $bookingCustomer = CustomerBooking::create([
            'ID'                => $bookingId,
            'ClientID'          => session('selected_scheme_id'),
            'Created'           =>date("Y-m-d H:i:s"),
            'LastEdited'        => date("Y-m-d H:i:s"),
            'CutomerName'       => $request->name,
            'Address'           => $request->address,
            'village'           => $request->village,
            'taluka'            => $request->taluka,
            'district'          => $request->district,
            'pincode' => $request->filled('pincode') ? (int)$request->pincode: null,
            'Contact'           => $request->contactno,
            'Email'             => $request->email,
            'BookingDate'       => date('Y-m-d', strtotime($request->bdate)),
            'Idproof'           => $request->idproof,
            'scanimg'           => $files,
            'Scheme'            => $request->scheme,
            'FlatNo'            => $request->fno,
            'RateSqft'          => $request->RateSqft,
            'vatAmt'            => $request->vat,
            'BspAmount' => $request->filled('costBSP') ? (float) $request->costBSP : null,

            'agreementAmt'      => $request->cost,
            'lightChrg'         => $request->light,
            'parkingChrg'       => $request->parking,
            'maintances'        => $request->maintance,
            'othersTotal'       => $request->totalOthers,
            'docChrg'           => $request->documentation,
            'sitedevcharges'    => $request->sitedevcharges,
            'regiChrgPer'       => $request->regist_per,
            'vatAmtPer'         => $request->vat_per,
            'servicetaxPer'     => $request->servicetax_per,
            'taxrate'           => $request->taxrate,
            'regiChrg'          => $request->registration,
            'servicetax'        => $request->servicetax,
            'TaxamtTotal'       => $request->TotalTaxAmt,
            'rate_per_sqft_type'=> $request->rateamt,
            'roundUp' => $request->filled('roundUp') ? $request->roundUp : null,

            'TotalFlatAmt'      => $request->TotalFlatAmt,
            'Wing'              => $request->typesrch,
            'pilnth'            => $request->pilnth,
            'slab'              => $request->slab,
            'bricks'            => $request->bricks,
            'plaster'           => $request->plaster,
            'floaring'          => $request->floaring,
            'plumbing'          => $request->plumbing,
            'project'           => $request->project,
            'cancel_flag'           => 0,
            'stamp_amt' => $request->filled('stamp_amt') ? $request->stamp_amt : null,

            'Ptype'             => $request->rateamt,
            'loan_sanction_amt' => $request->loan_sanction_amt,
            'bankname'          => $request->bankname,
            'agreement_complete'=> $request->agreement_complete,
            'agreement_no'      => $request->agreement_no,
            'reg_date'          => date('Y-m-d', strtotime($request->reg_date)),
            'userID'        => auth()->id(),
        ]);

        // =========================
        // Insert booking_payment
        // =========================
        CustomerPayment::create([
            'ID'             => uniqid(),
            'ClientID'       => session('selected_scheme_id'),
            'Date'           => date('Y-m-d', strtotime($request->bdate)),
            'amt_pay'        => $request->amount_pay,
            'receipt_no'     => $request->receipt_no,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,
            'narration'      => $request->narration,
            'schemeID'       => $request->scheme,
            'FlatID'         => $request->fno,
            'Wing'             => $request->typesrch,
            'Booking_ID'     => $bookingId,
            'type'           => 'Downpayment',
            'userID'        => auth()->id(),
        ]);

        return redirect()->route('customer_booking')->with('success', 'Record added successfully!');
    }

    public function edit($id)
    {
        $clientId = session('selected_scheme_id');
       
        $schemes = SchemeDetail::where('completFalg', 0)
        ->where('ID', $clientId)
        ->get();

        $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $postdated = CustomerBooking::findOrFail($id);
        
      $booking_pay = DB::table('booking_payment')
        ->where('Booking_ID', $postdated['ID'])
        ->where('type', 'Downpayment')
        ->first();
       //dd($booking_pay);
        $slabs = DB::table('slabs')
            ->where('ClientID', $clientId)
            ->first();

       $bookcancelFlatIDs = DB::table('booking_cancel')
            ->where('SchemID', $postdated['Scheme'])
            ->pluck('flatID')
            ->toArray();

        if (empty($bookcancelFlatIDs)) {
            $bookcancelFlatIDs = [0];
        }

        /* 2️⃣ Get already booked flat IDs (excluding current user) */
        $bookingFlatIDs = DB::table('booking_customer')
            ->where('Scheme', $postdated['Scheme'])
            ->where('ID', '!=', $postdated->ID) // current booking exclude
            ->where('ID', '!=', request('Uid'))
            ->whereNotIn('FlatNo', $bookcancelFlatIDs)
            ->pluck('FlatNo')
            ->toArray();

        if (empty($bookingFlatIDs)) {
            $bookingFlatIDs = [0];
        }

        /* 3️⃣ Get available flats */
       $flatsDetails = DB::table('flats_details')
            ->where('scheme_ID', $postdated->Scheme)
            ->where('Wing', $postdated->Wing)
            ->whereNotIn('ID', $bookingFlatIDs)
            ->get();
            
        $flat_detail = DB::table('flats_details')
            ->where('ID', $postdated['FlatNo'])
            ->where('scheme_ID', $postdated['Scheme'])
            ->get();

        return view('backend.Customer_booking.create', compact(
            'schemes','postdated','flatsDetails','slabs','banks','flat_detail','booking_pay'
        ));
    }
    public function update(Request $request)
    {
        // Validate request
        $request->validate([
            'name'      => 'required|string',
            'address'    => 'required|string',
            'village'      => 'nullable|string',
            'taluka'      => 'nullable|string',
            'district'    => 'nullable|string',
            'pincode' => 'nullable|string',
            'contactno'    => 'nullable|numeric',
            'email'  => 'nullable|string',
            'scheme'  => 'required|string',
            'idproof'  => 'required|string',
            'typesrch'  => 'required|string',
            'agreement_complete'   => 'nullable|numeric',
            'agreement_no'  => 'nullable|string',
            'fno'  => 'required|string',
            'rateamt'  => 'nullable|numeric',
            'RateSqft'   => 'nullable|numeric',
            'costBSP'   => 'nullable|numeric',
            'cost'   => 'nullable|numeric',
            'diff'   => 'nullable|numeric',
            'light'   => 'nullable|numeric',
            'parking'   => 'nullable|numeric',
            'maintance'   => 'nullable|numeric',
            'documentation'   => 'nullable|numeric',
            'sitedevcharges'   => 'nullable|numeric',
            'totalOthers'   => 'nullable|numeric',
            'stamp_amt'   => 'nullable|numeric',
            'vat_per'   => 'nullable|numeric',
            'vat'   => 'nullable|numeric',
            'servicetax_per'   => 'nullable|numeric',
            'servicetax'   => 'nullable|numeric',
            'regist_per'   => 'nullable|numeric',
            'registration'   => 'nullable|numeric',
            'TotalTaxAmt'   => 'nullable|numeric',
            'roundUp'   => 'nullable|numeric',
            'TotalFlatAmt'   => 'nullable|numeric',
            'Downpayment'   => 'nullable|numeric',
            'loan_sanction_amt'   => 'nullable|numeric',
            'bankname'   => 'nullable|string',
            'pilnth'   => 'nullable|numeric',
            'slab'   => 'nullable|numeric',
            'bricks'   => 'nullable|numeric',
            'plaster'   => 'nullable|numeric',
            'floaring'   => 'nullable|numeric',
            'plumbing'   => 'nullable|numeric',
            'project'   => 'nullable|numeric',
            'Pay_type'   => 'required|string',
            'receipt_no'   => 'nullable|string',
            'account_no'   => 'nullable|string',
            'cheque_no'   => 'nullable|string',
            'amount_pay'   => 'nullable|numeric',
            'narration'   => 'nullable|string',
        ]);

        // Find record using correct key
        $customerTransfer = CustomerBooking::findOrFail($request->ID);

         $booking = CustomerBooking::where('ID', $customerTransfer->ID)
        ->where('ClientID', session('selected_scheme_id'))
        ->firstOrFail();

        /* 3️⃣ Handle Images */
        $newImages = $request->uploadfile ?? []; // array from form
        $oldImages = $booking->scanimg ? explode(',', $booking->scanimg) : [];

        $uploadPath = public_path('uploads/');

         // Delete removed images
        foreach ($oldImages as $img) {
            if (!in_array($img, $newImages)) {
                $filePath = $uploadPath . str_replace('..', '.', $img);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
        }

        $files = implode(',', $newImages);

        // Update record
        $customerTransfer->update([
          'ClientID'          => session('selected_scheme_id'),
            'Created'           =>date("Y-m-d H:i:s"),
            'LastEdited'        => date("Y-m-d H:i:s"),
            'CutomerName'       => $request->name,
            'Address'           => $request->address,
            'village'           => $request->village,
            'taluka'            => $request->taluka,
            'district'          => $request->district,
            'pincode' => $request->filled('pincode') ? (int)$request->pincode : null,
            'Contact'           => $request->contactno,
            'Email'             => $request->email,
            'BookingDate'       => date('Y-m-d', strtotime($request->bdate)),
            'Idproof'           => $request->idproof,
            'scanimg'           => $files,
            'Scheme'            => $request->scheme,
            'FlatNo'            => $request->fno,
            'RateSqft'          => $request->RateSqft,
            'vatAmt'            => $request->vat,
            'BspAmount'      => $request->filled('costBSP') ? (float)$request->costBSP : null,
            'agreementAmt'      => $request->cost,
            'lightChrg'         => $request->light,
            'parkingChrg'       => $request->parking,
            'maintances'        => $request->maintance,
            'othersTotal'       => $request->totalOthers,
            'docChrg'           => $request->documentation,
            'sitedevcharges' => $request->filled('sitedevcharges') ? (float)$request->sitedevcharges : null,
            'regiChrgPer'       => $request->regist_per,
            'vatAmtPer'         => $request->vat_per,
            'servicetaxPer'     => $request->servicetax_per,
            'taxrate'           => $request->taxrate,
            'regiChrg'          => $request->registration,
            'servicetax'        => $request->servicetax,
            'TaxamtTotal'       => $request->TotalTaxAmt,
            'rate_per_sqft_type'=> $request->rateamt,
            'roundUp'        => $request->filled('roundUp') ? (float)$request->roundUp : null,
            'TotalFlatAmt'      => $request->TotalFlatAmt,
            'Wing'              => $request->typesrch,
            'pilnth'            => $request->pilnth,
            'slab'              => $request->slab,
            'bricks'            => $request->bricks,
            'plaster'           => $request->plaster,
            'floaring'          => $request->floaring,
            'plumbing'          => $request->plumbing,
            'project'           => $request->project,
            'userID'            => session('userID'),
            'stamp_amt'      => $request->filled('stamp_amt') ? (float)$request->stamp_amt : null,
            'Ptype'             => $request->rateamt,
            'loan_sanction_amt' => $request->loan_sanction_amt,
            'bankname'          => $request->bankname,
            'cancel_flag'           => 0,
            'agreement_complete'=> $request->agreement_complete,
            'agreement_no'      => $request->agreement_no,
            'reg_date'          => date('Y-m-d', strtotime($request->reg_date)),
            'userID'         => Auth::id(),
        ]);
         CustomerPayment::where('Booking_ID', $customerTransfer->ID)->delete();

           /* 5️⃣ Insert new Downpayment */
        CustomerPayment::create([
            'ID'             => uniqid(),
            'ClientID'       => session('selected_scheme_id'),
            'Date'           => date('Y-m-d', strtotime($request->bdate)),
            'amt_pay'        => $request->amount_pay,
            'receipt_no'     => $request->receipt_no,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,
            'narration'      => $request->narration,
            'schemeID'       => $request->scheme,
            'FlatID'         => $request->fno,
            'Wing'             => $request->typesrch,
            'Booking_ID'     => $customerTransfer->ID,
            'type'           => 'Downpayment',
            'userID'         => Auth::id(),
        ]);

        return redirect()
            ->route('customer_booking')
            ->with('success', 'Record updated successfully!');
    }
    public function destroy($id)
    {
        $bankform = CustomerBooking::findOrFail($id);
        CustomerPayment::where('Booking_ID', $bankform->ID)->delete();
        $bankform->delete();

        return redirect()
            ->route('customer_booking')
            ->with('success', 'Record has been deleted successfully!');
    }
    public function paymentSchedule($id)
    {
        $booking = CustomerBooking::with('flatsdetail')->findOrFail($id);

        // slabs table (assuming one row config like old code)
        $slab = DB::table('slabs')->first();

        $total = $booking->TotalFlatAmt;

        // calculate slab amounts
        $calc = [
            'plinth'   => $total * $slab->pilnth / 100,
            'slab'     => $total * $slab->slab / 100,
            'bricks'   => $total * $slab->bricks / 100,
            'plaster'  => $total * $slab->plaster / 100,
            'flooring' => $total * $slab->floaring / 100,
            'plumbing' => $total * $slab->plumbing / 100,
            'project'  => $total * $slab->project / 100,
            'total'    => $total * $slab->total / 100,
        ];

        return view(
            'backend.Customer_booking.payment_schedule',
            compact('booking', 'slab', 'calc')
        );
    }





}
