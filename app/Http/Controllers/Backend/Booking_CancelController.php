<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Demand_raise;
use App\Models\Backend\Flat_details;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\Booking_Cancel;
use App\Models\Backend\Booking_Customer;
use App\Models\Backend\Booking_Payment;
use App\Models\Backend\Scheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class Booking_CancelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {

        $query = Booking_Cancel::where('ClientID', session('selected_scheme_id'));

        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('Date', function ($row) {
                return \Carbon\Carbon::parse($row->Date)->format('d-m-Y');
            })

            ->addColumn('actions', function ($row) {

                $editUrl   = route('booking_cancel.edit', $row->ID);
                $deleteUrl = route('booking_cancel.delete', $row->ID);

                $actions = '';

                if (hasPermission('edit_cancel_booking')) {
                    $actions .= '<a href="'.$editUrl.'" class="text-primary me-2">
                                    <i class="align-middle" data-feather="edit"></i>
                                </a>';
                }

                if (hasPermission('delete_cancel_booking')) {
                    $actions .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline">
                                    '.csrf_field().'
                                    '.method_field("DELETE").'
                                    <button type="submit" class="btn btn-link text-danger p-0"
                                        onclick="return confirm(\'Are you sure?\')">
                                        <i class="align-middle" data-feather="trash"></i>
                                    </button>
                                </form>';
                }

                return $actions;
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.booking_cancel.index');
}

    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {   
        $clientId = Session::get('selected_scheme_id');
        $permissions = Permission::all();

        $allschemes = SchemeDetail::where('completFalg', 0)
        ->where('ID', $clientId)
        ->get();

       $customers = Booking_Customer::where('cancel_flag', 0)->where('Scheme',$clientId)->get();

        return view('backend.booking_cancel.create', compact('permissions','allschemes','customers'));
    }


public function getBookingDate(Request $request)
{
    $customerName = $request->cust_name;

    $booking = Booking_Customer::where('CutomerName', $customerName)
        ->where('ClientID', Session::get('selected_scheme_id'))
        ->first();

    if (!$booking) {
        return response()->json([]);
    }

    return response()->json([
        'minDate' => Carbon::parse($booking->BookingDate)->format('d-m-Y')
    ]);
}

    // ✅ Get Booking Detail (huge HTML part)
    public function getBookingDetail(Request $request)
{
    $schemeId = $request->schmid;
    $cust     = $request->cust;
    $cancelId = $request->cancelid;

    // 1️⃣ Booking customer
    $booking = Booking_Customer::where([
        'CutomerName' => $cust,
        'Scheme'      => $schemeId,
        'ClientID'    => Session::get('selected_scheme_id')   // ❗ FIXED
    ])->first();

    if (!$booking) {
        return '';
    }

    // 2️⃣ Total paid
    $bookingPay = Booking_Payment::where('Booking_ID', $booking->ID)
        ->where('ClientID', Session::get('selected_scheme_id'))
        ->sum('amt_pay');

    // 3️⃣ Flat details
    $flat = Flat_details::where('ID', $booking->FlatNo)
        ->where('ClientID', Session::get('selected_scheme_id'))
        ->first();

    // 4️⃣ Booking cancel (THIS WAS MISSING)
    $bc_record = null;
    if (!empty($cancelId)) {
        $bc_record = Booking_Cancel::where('ID', $cancelId)
            ->where('ClientID', Session::get('selected_scheme_id'))
            ->first();
    }

    // 5️⃣ Pending amount (same as old logic)
    $pendingAmt = $bookingPay - ($bc_record->FineAmt ?? 0);
// dd($bc_record->account_no);
    // 6️⃣ Accounts list
    $accounts = Bank_Acc::where('ClientID', Session::get('selected_scheme_id'))
        ->orderBy('Name')
        ->get();

    return view(
        'backend.booking_cancel.booking_detail',
        compact(
            'booking',
            'bookingPay',
            'flat',
            'bc_record',
            'pendingAmt',
            'accounts',
            'cancelId'
        )
    );
}


    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    // 🔹 VALIDATION RULES
    $request->validate([
        'Destination'   => 'required|exists:scheme_step1,ID',
        'CustomerID'    => 'required|string|max:255',
        'Date'          => 'required|date_format:d-m-Y',
        'Bookid'        => 'required',
        'flatID'        => 'required',

        'fineamt'       => 'nullable|numeric|min:0',
        'dueamt'        => 'required|numeric|min:0',

        'Pay_type'      => 'required|in:cash,cheque,e-Payment,RTGS',
        'account_no'    => 'nullable|exists:accounts,ID',

        'amount_pay'    => 'required|numeric|min:0',
        'bnk_charge'    => 'nullable|numeric|min:0',

        'cheque_no'     => 'nullable|string|max:100',
        'narration'     => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();

    try {
$clientId = session('selected_scheme_id');
        // 🔹 UNIQUE ID (matches old uniqid())
        $cancelId = uniqid();
// dd($cancelId);
        // 🔹 INSERT INTO booking_cancel
        DB::table('booking_cancel')->insert([
            'ID'             => $cancelId,
            'ClientID'       => $clientId,
            'Created'        => now(),
            'LastEdited'     => now(),

            'SchemID'        => $request->Destination,
            'CustomerID'     => $request->CustomerID,
            'BookingID'      => $request->Bookid,
            'flatID'         => $request->flatID,

            'FineAmt'        => $request->fineamt ?? 0,
            'amt_pay'        => $request->amount_pay,
            'bankcharge'     => $request->bnk_charge ?? 0,

            'payment_method'=> $request->Pay_type,
            'cheque_no'      => $request->cheque_no,

            'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),

            'account_no'     => $request->account_no,
            'narration'      => $request->narration,

            'userID'         => Auth::id(),
        ]);

        // 🔹 UPDATE booking_customer (cancel_flag = 1)
        DB::table('booking_customer')
            ->where('ID', $request->Bookid)
            ->where('ClientID', $clientId)
            ->update([
                'cancel_flag' => 1,
                'LastEdited'  => now(),
            ]);

        /*
        // 🔹 OPTIONAL: RTGS BANK CHARGE ENTRY (same as your commented code)
        if ($request->Pay_type === 'RTGS' && ($request->bnk_charge ?? 0) > 0) {

            DB::table('daily_trans')->insert([
                'ID'             => uniqid(),
                'ClientID'       => $clientId,
                'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),

                'amt_pay'        => $request->bnk_charge,
                'payment_method'=> 'RTGS',
                'cheque_no'      => $request->cheque_no,
                'account_no'     => $request->account_no,

                'narration'      => $request->narration,
                'Description'    => 'Cancel Booking Payment Charge',
                'Exp_type'       => 'RTGS',
                'PaymentId'      => $cancelId,
            ]);
        }
        */

        DB::commit();

        return redirect()
            ->route('booking_cancel')
            ->with('success', 'Record has been added successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', 'Something went wrong: ' . $e->getMessage());
    }
}
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit($id)
{
    $clientId = session('selected_scheme_id');

    // Booking cancel record
    $cancelbook = Booking_Cancel::where('ID', $id)
        ->where('ClientID', $clientId)
        ->firstOrFail();

    // 🔥 Get actual booking customer
    $bookingCustomer = Booking_Customer::where('ID', $cancelbook->BookingID)
        ->where('ClientID', $clientId)
        ->first();

    // Schemes
    $allschemes = SchemeDetail::where('completFalg', 0)
        ->where('ID', $clientId)
        ->get();

    // Customers
    $customers = Booking_Customer::where('Scheme', $clientId)->get();



    return view('backend.booking_cancel.create', compact(
        'cancelbook',
        'bookingCustomer',
        'allschemes',
        'customers'
    ));
}



public function update(Request $request)
{
    $clientId = session('selected_scheme_id');

    // 🔹 VALIDATION (same as store)
    $request->validate([
        'id'           => 'required',
        'Destination'  => 'required|exists:scheme_step1,ID',
        'CustomerID'   => 'required|string|max:255',
        'Date'         => 'required|date_format:d-m-Y',
        'Bookid'       => 'required',
        'flatID'       => 'required',

        'fineamt'      => 'nullable|numeric|min:0',
        'dueamt'       => 'required|numeric|min:0',

        'Pay_type'     => 'required|in:cash,cheque,e-Payment,RTGS',
        'account_no'   => 'nullable|exists:accounts,ID',

        'amount_pay'   => 'required|numeric|min:0',
        'bnk_charge'   => 'nullable|numeric|min:0',

        'cheque_no'    => 'nullable|string|max:100',
        'narration'    => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();

    try {

        // 🔹 Existing cancel record
        $cancel = Booking_Cancel::where('ID', $request->id)
            ->where('ClientID', $clientId)
            ->firstOrFail();

        // 🔹 UPDATE booking_cancel
        $cancel->update([
            'LastEdited'     => now(),
            'SchemID'        => $request->Destination,
            'CustomerID'     => $request->CustomerID,
            'BookingID'      => $request->Bookid,
            'flatID'         => $request->flatID,

            'FineAmt'        => $request->fineamt ?? 0,
            'amt_pay'        => $request->amount_pay,
            'bankcharge'     => $request->bnk_charge ?? 0,

            'payment_method'=> $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,

            'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)
                                ->format('Y-m-d'),

            'narration'      => $request->narration,
            'userID'         => Auth::id(),
        ]);

        // 🔹 ENSURE booking is marked cancelled
        DB::table('booking_customer')
            ->where('ID', $request->Bookid)
            ->where('ClientID', $clientId)
            ->update([
                'cancel_flag' => 1,
                'LastEdited'  => now(),
            ]);

        DB::commit();

        return redirect()
            ->route('booking_cancel')
            ->with('success', 'Record updated successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->withErrors(['error' => $e->getMessage()]);
    }
}


    /**
     * Remove the specified resource from storage.
     */
public function destroy($id)
{
    $clientId = session('selected_scheme_id');

    DB::beginTransaction();

    try {

        // 1️⃣ Get booking cancel record
        $cancel = Booking_Cancel::where('ID', $id)
            ->where('ClientID', $clientId)
            ->firstOrFail();

        $bookingId = $cancel->BookingID;

        // 2️⃣ Delete booking_cancel entry
        $cancel->delete();

        // 3️⃣ Revert booking_customer cancel flag
        DB::table('booking_customer')
            ->where('ID', $bookingId)
            ->where('ClientID', $clientId)
            ->update([
                'cancel_flag' => 0,
                'LastEdited'  => now(),
            ]);

        DB::commit();

        return redirect()
            ->route('booking_cancel')
            ->with('success', 'Booking cancellation deleted successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->route('booking_cancel')
            ->with('error', 'Delete failed: ' . $e->getMessage());
    }
}

}
