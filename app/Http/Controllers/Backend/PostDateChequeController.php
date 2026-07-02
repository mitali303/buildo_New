<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Backend\PostDatedCheque;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\Flat_details;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Booking_Customer;
use Illuminate\Support\Facades\DB;

class PostDateChequeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = PostDatedCheque::with(['customers','accountNo', 'schemes','flats'])
                ->when($request->from_date, function ($q) use ($request) {
                    $q->whereDate('Date', '>=', $request->from_date);
                })
                ->when($request->to_date, function ($q) use ($request) {
                    $q->whereDate('Date', '<=', $request->to_date);
                })
                ->select(
                    'ID',
                    'Date',
                    'Booking_ID',
                    'schemeID',
                    'FlatID',
                    'cheque_no',
                    'account_no',
                    'amt_pay'
                );

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('date', function ($row) {
                    return date('d-m-Y', strtotime($row->Date));
                })

                ->addColumn('customer_name', function ($row) {
                    return optional($row->customers)->CutomerName;
                })

                ->addColumn('schemes_name', function ($row) {
                    return $row->schemes->Name ?? '';
                })

                ->addColumn('flat_no', function ($row) {
                    return $row->flats->FlatNo ?? '';
                })
                ->addColumn('accounts_name', function ($row) {
                    return $row->accountNo->Name ?? '';
                })

              
                ->addColumn('actions', function ($row) {

                    $actions = '';

                    if (hasPermission('edit_post_dated_cheque')) {
                        $actions .= '<a href="' . route('post_dated_cheque.edit', $row->ID) . '" class="me-2 text-primary">
                                        <i data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_post_dated_cheque')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="delete-form-' . $row->ID . '">
                                        <i data-feather="trash"></i>
                                    </a>
                                    <form id="delete-form-' . $row->ID . '" action="' . route('post_dated_cheque.delete', $row->ID) . '" method="POST" class="d-none">
                                        ' . csrf_field() . method_field('DELETE') . '
                                    </form>';
                    }

                    return $actions;
                })

               


                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.post_dated_cheque.index');
    }
     public function create()
    {   
        $clientId = session('selected_scheme_id');
        $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $schemes = SchemeDetail::where('ID', $clientId)->get();
        $booking_cust = Booking_Customer::first();
        $db_record = new PostDatedCheque();
        
        return view('backend.post_dated_cheque.create', compact('banks','schemes','booking_cust','db_record'));
    }
    public function store(Request $request)
    {
        
    
        // dd($request);
        $account=$request->validate([
            'scheme'      => 'required|string',
            'typesrch'   => 'required|string',
            'FlatID'   => 'required|string',
            'CustomerID'   => 'string',
           
            'Pay_type'  => 'required|string',
            'amount_pay'       => 'required|numeric|min:0.01',
            
            'cheque_no'       => 'nullable|string',
            'account_no'       => 'nullable|string',
            'narration'       => 'nullable|string',
        ]);
        //dd($account);

        PostDatedCheque::create([
            'ID'       => uniqid(),
            'ClientID'       => session('selected_scheme_id'),
            'schemeID'   => $request->scheme,
            'Wing'     => $request->typesrch,
            'FlatID'     => $request->FlatID,
            'Booking_ID'     => $request->CustomerID,
            'account_no'     => $request->account_no,
            'narration'     => $request->narration,
            
            'Date'           => $request->date,
            'amt_pay'        => $request->amount_pay,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'flag' => 0,
            'reconciliation' => 0,
            'userID'         => auth()->id(),
        ]);

        return redirect()
            ->route('post_dated_cheque')
            ->with('success', 'Record added successfully!');
    }
    
    public function edit($id)
    {
        $clientId = session('selected_scheme_id');
       $postdated = PostDatedCheque::where('ClientID', $clientId)
        ->where('id', $id)
        ->firstOrFail();

        $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $schemes = SchemeDetail::where('ID', $clientId)->get();
        $db_record = new PostDatedCheque();
       $booking_cust = Booking_Customer::first();
        return view('backend.post_dated_cheque.create', compact(
            'postdated','banks','schemes','db_record','booking_cust'
        ));
    }
    public function update(Request $request)
    {
        // Validate request
        $request->validate([
            'scheme'      => 'required|string',
            'typesrch'   => 'required|string',
            'FlatID'   => 'required|string',
            'CustomerID'   => 'string',
           
            'Pay_type'  => 'required|string',
            'amount_pay'       => 'required|numeric|min:0.01',
            
            'cheque_no'       => 'nullable|string',
            'account_no'       => 'nullable|string',
            'narration'       => 'nullable|string',
        ]);

        // Find record using correct key
        $accountTransfer = PostDatedCheque::findOrFail($request->ID);

        // Update record
        $accountTransfer->update([
            'ClientID'       => session('selected_scheme_id'),
            'schemeID'      => $request->scheme,
            'Wing'          => $request->typesrch,
            'FlatID'        => $request->FlatID,
            'Booking_ID'     => $request->CustomerID,
            'account_no'     => $request->account_no,
            'narration'     => $request->narration,
            
            'Date'           => $request->date,
            'amt_pay'        => $request->amount_pay,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'flag' => 0,
            'reconciliation' => $request->reconciliation ?? 0,
            'userID'         => Auth::id(),
        ]);

        return redirect()
            ->route('post_dated_cheque')
            ->with('success', 'Record updated successfully!');
    }



    public function getWingNoPosted(Request $request)
    {
        $schemeId = $request->input('schmid');

        // Fetch unique wings for the scheme
        $wingDetails = Flat_details::where('scheme_ID', $schemeId)
            ->groupBy('Wing')
            ->get(['Wing']);

        // Return HTML for the <select> dropdown
        
        $html = '<select name="typesrch" id="typesrch" class="input-sm form-control chosen-select " data-placeholder="Select" onchange="wing();">';
        $html .= '<option value="">Select Type</option>';

        foreach ($wingDetails as $wing) {
            $html .= '<option value="' . $wing->Wing . '">' . $wing->Wing . '</option>';
        }

        $html .= '</select>';
        $html .= '<input type="hidden" id="schemeH" name="schemeH" value="' . $schemeId . '">';

        // Include JS for Chosen plugin initialization
        $html .= '<script>
            $(function() {
                $(".chosen-select").chosen();
                $(".chosen-select-deselect").chosen({ allow_single_deselect: true });
            });
        </script>';

        return $html;
    }
    public function getFlatsPost(Request $request)
    {
        $schemeId = $request->schmid;
        $wing = $request->wing;

        // Get IDs of cancelled flats (if needed)
        // $cancelledFlats = DB::table('booking_cancel')
        //     ->where('SchemID', $schemeId)
        //     ->pluck('flatID')->toArray();
        // if (empty($cancelledFlats)) $cancelledFlats = [0];

        // Get IDs of already booked flats
        $bookedFlats = DB::table('booking_customer')
            ->where('Scheme', $schemeId)
            ->where('cancel_flag', 0)
            // ->whereNotIn('FlatNo', $cancelledFlats)
            ->pluck('FlatNo')
            ->toArray();

        if (empty($bookedFlats)) {
            $bookedFlats = [0];
        }

        // Get available flats for the scheme & wing
        $flats = DB::table('flats_details')
        ->where('scheme_ID', $schemeId)
        ->where('Wing', $wing)
        ->get();
            

        // Return HTML for the dropdown
        $html = '<select name="FlatID" id="FlatID" class="input-sm form-control chosen-select required" data-placeholder="Select" onchange="getCustomerDetails();">';
        $html .= '<option value="">Select</option>';

        foreach ($flats as $flat) {
            $selected = (isset($postdated) && $postdated->FlatID == $flat->ID) ? 'selected' : '';
            $html .= "<option value='{$flat->ID}' {$selected}>{$flat->FlatNo}</option>";
        }

        $html .= '</select>';
        $html .= '<input type="hidden" id="schemeH" name="schemeH" value="'.$schemeId.'">';
        $html .= '<script>$(".chosen-select").chosen();</script>';

        return $html;
    }
    public function getCustomerPost(Request $request)
    {
        $flatID = $request->flatID;
        //echo $flatID;

        $flat = DB::table('booking_customer')
            ->where('FlatNo', $flatID)
            ->where('ClientID', session('selected_scheme_id'))
            ->where('cancel_flag', 0)
            ->first();
       // dd($flat);
        if ($flat) {
            return response()->json([
                'customer_name' => $flat->CutomerName,
                'customer_id'   => $flat->ID,
                'pending_amount'=> $flat->TotalFlatAmt,
            ]);
        }

        return response()->json([
            'customer_name' => '',
            'customer_id'   => '',
            'pending_amount'=> '',
        ]);
    }
    public function accountByPaymentPost(Request $request)
    {
        $payMethod = $request->pay_method;

        if ($payMethod === 'cash') {
            $accounts = Bank_Acc::where('name', 'Cash In Hand')
                ->where('ClientID', session('selected_scheme_id'))
                ->orderBy('name')
                ->get();
            $selectOption = '';
        } else {
            $accounts = Bank_Acc::where('name', '!=', 'Cash In Hand')
                ->where('ClientID', session('selected_scheme_id'))
                ->orderBy('name')
                ->get();
            $selectOption = '<option value="">Select</option>';
        }

        $html = '<select id="account_no" name="account_no"
                    class="form-control chosen-select required"
                    onchange="getBalance();">';

        $html .= $selectOption;

        foreach ($accounts as $account) {
            $html .= '<option value="'.$account->id.'">'
                . $account->name.' ('.substr($account->acno, -3).')'
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
    public function getBookingPendingPost(Request $request)
    {
        $flatID   = $request->flatID;
        $payID    = $request->PayID;
        $clientID = session('selected_scheme_id');

        // booking_customer
        $bookingCustomer = DB::table('booking_customer')
            ->where('FlatNo', $flatID)
            ->where('ClientID', $clientID)
            ->where('cancel_flag', '0')
            ->first();

        if (!$bookingCustomer) {
            return 0;
        }

        $postedcheque = DB::table('post_dated_cheque')
            ->where('ClientID', $clientID)
            ->where('ID', '!=', $payID)
            ->sum('amt_pay');

        // Sum of booking_payment
        $bookPay = DB::table('booking_payment')
            ->where('Booking_ID', $bookingCustomer->ID)
            ->where('ClientID', $clientID)
            ->where('ID', '!=', $payID)
            ->sum('amt_pay');

        // Sum of customer_refund
        $refundPay = DB::table('customer_refund')
            ->where('bookingcustomer', $bookingCustomer->ID)
            ->where('ClientID', $clientID)
            ->sum('amt_pay');

        // Sum of additional bills
        $billPay = DB::table('add_bill')
            ->where('FlatNo', $flatID)
            ->where('ClientID', $clientID)
            ->sum('Amount');

        $paidAmt = $bookPay - $refundPay;

        $pendingAmount = (($bookingCustomer->TotalFlatAmt - $paidAmt) + $billPay)-$postedcheque;

        return response()->json($pendingAmount);
    }
    public function destroy($id)
    {
        $bankform = PostDatedCheque::findOrFail($id);
        $bankform->delete();

        return redirect()
            ->route('post_dated_cheque')
            ->with('success', 'Record has been deleted successfully!');
    }
    
 
}
