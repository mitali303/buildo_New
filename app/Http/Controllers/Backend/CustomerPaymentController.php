<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Backend\CustomerPayment;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\Flat_details;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Booking_Customer;
use Illuminate\Support\Facades\DB;

class CustomerPaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
// dd(session('selected_scheme_id'));
           $payments = CustomerPayment::query()
                        ->when($request->from_date, function ($q) use ($request) {
                        $q->whereDate('Date', '>=', $request->from_date);
                    })
                    ->when($request->to_date, function ($q) use ($request) {
                        $q->whereDate('Date', '<=', $request->to_date);
                    })
                ->selectRaw('
                    Booking_ID,
                    MAX(ID) as ID,
                    MAX(schemeID) as schemeID,
                    MAX(FlatID) as FlatID,
                    MAX(payement_by) as payement_by
                ')
                ->where('ClientID', session('selected_scheme_id'))
                ->where('type', 'Downpayment')
                ->groupBy('Booking_ID');

            
            return DataTables::of($payments)
                ->addIndexColumn()

                ->addColumn('customer_name', function ($row) {
                    return $row->booking_cust->CutomerName ?? '';
                })

                ->addColumn('flat_no', function ($row) {
                    return $row->booking_cust->flat->FlatNo ?? '';
                })

                ->addColumn('wing', function ($row) {
                    return $row->booking_cust->flat->Wing ?? '';
                })

                ->addColumn('total_cost', function ($row) {
                    return $row->booking_cust->TotalFlatAmt;
                })

                ->addColumn('extra_work', function ($row) {
                    return DB::table('add_bill')
                        ->where('Project_ID', $row->schemeID)
                        ->where('FlatNo', $row->FlatID)
                        ->sum('Amount');
                })

                ->addColumn('refund', function ($row) {
                    return DB::table('customer_refund')
                        ->where('schemeID', $row->schemeID)
                        ->where('bookingcustomer', $row->Booking_ID)
                        ->sum('amt_pay');
                })

                ->addColumn('grand_total', function ($row) {
                    return $row->booking_cust->TotalFlatAmt
                        + DB::table('add_bill')->where('FlatNo',$row->FlatID)->sum('Amount');
                })

                ->addColumn('bank_sanction', fn($r) => $r->booking_cust->loan_sanction_amt)

                ->addColumn('bank_paid', function ($row) {
                    return CustomerPayment::where('Booking_ID',$row->Booking_ID)
                        ->where('payement_by','bank')
                        ->sum('amt_pay');
                })

                ->addColumn('bank_pending', function ($row) {
                    $paid = CustomerPayment::where('Booking_ID',$row->Booking_ID)
                        ->where('payement_by','bank')
                        ->sum('amt_pay');
                    return $row->booking_cust->loan_sanction_amt - $paid;
                })

                ->addColumn('self_payment', function ($row) {
                    return $row->booking_cust->TotalFlatAmt - $row->booking_cust->loan_sanction_amt;
                })

                ->addColumn('self_paid', function ($row) {
                    return CustomerPayment::where('Booking_ID',$row->Booking_ID)
                        ->where('payement_by','self')
                        ->sum('amt_pay');
                })

                ->addColumn('self_pending', function ($row) {
                    $self = $row->booking_cust->TotalFlatAmt - $row->booking_cust->loan_sanction_amt;
                    $paid = CustomerPayment::where('Booking_ID',$row->Booking_ID)
                        ->where('payement_by','self')
                        ->sum('amt_pay');
                    return $self - $paid;
                })

                ->addColumn('total_paid', function ($row) {
                    return CustomerPayment::where('Booking_ID',$row->Booking_ID)->sum('amt_pay');
                })

                ->addColumn('total_pending', function ($row) {
                    $grand = $row->booking_cust->TotalFlatAmt;
                    $paid  = CustomerPayment::where('Booking_ID',$row->Booking_ID)->sum('amt_pay');
                    return $grand - $paid;
                })

                ->addColumn('payment_by', fn($r) => ucfirst($r->payement_by))

                ->addColumn('actions', function ($row) {
                    return '
                    <a href="'.route('customer_payment.show',$row->ID).'" class="text-primary me-2">
                        <i data-feather="eye"></i>
                    </a>';
                })

                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.customer_payment.index');
    }

    public function create()
    {   
        $clientId = session('selected_scheme_id');
        $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $schemes = SchemeDetail::where('ID', $clientId)->get();
        $booking_cust = Booking_Customer::first();

        $bookcancel_flats = DB::table('booking_cancel')
            ->where('SchemID', $clientId)
            ->pluck('flatID');

        $booking_flats = DB::table('booking_customer')
            ->where('Scheme', $clientId)
            ->whereNotIn('FlatNo', $bookcancel_flats)
            ->pluck('FlatNo');

        $flats = DB::table('flats_details')
            ->where('scheme_ID', $clientId)
            ->whereIn('ID', $booking_flats)
            ->get();
        
        return view('backend.customer_payment.create', compact('banks','schemes','booking_cust','clientId','flats'));
    }
    public function store(Request $request)
    {
        $account = $request->validate([
            'scheme'      => 'required|string',
            'typesrch'    => 'required|string',
            'FlatID'      => 'required|string',
            'CustomerID'  => 'required|string',
            'bill_pay'    => 'nullable|string',
            'payement_by' => 'nullable|string',
            'Pay_type'    => 'required|string',
            'amount_pay'  => 'required|numeric|min:0.01',
            'receipt_no'  => 'nullable|numeric',
            'cheque_no'   => 'nullable|string',
            'account_no'  => 'required|string',
            'bnk_charge'  => 'nullable|numeric',
            'narration'   => 'nullable|string',
        ]);

        CustomerPayment::create([
            'ID'            => uniqid(),
            'ClientID'      => session('selected_scheme_id'),
            'schemeID'      => $request->scheme,
            'Wing'          => $request->typesrch,
            'FlatID'        => $request->FlatID,
            'Booking_ID'    => $request->CustomerID,
            'bill_payment'  => $request->bill_pay,
            'payement_by'   => $request->payement_by,
            'payment_method'=> $request->Pay_type,
            'account_no'    => $request->account_no,
            'narration'     => $request->narration,
            'receipt_no'    => $request->receipt_no,
            'Date'          => $request->date,
            'amt_pay'       => $request->amount_pay,
            'cheque_no'     => $request->cheque_no,
            'reconciliation'=> 0,
            'type'          => 'Flat Payment',
            'userID'        => auth()->id(),
        ]);

        return redirect()->route('customer_payment')->with('success', 'Record added successfully!');
    }

    public function edit($id)
    {
         $clientId = session('selected_scheme_id');
        $customerpayment = CustomerPayment::findOrFail($id);
        $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $schemes = SchemeDetail::all();
         $booking_cust = Booking_Customer::first();

        // Get booked flats for the selected scheme and wing
        $bookcancel_flats = DB::table('booking_cancel')
            ->where('SchemID', $customerpayment->schemeID)
            ->pluck('flatID');

        $booking_flats = DB::table('booking_customer')
            ->where('Scheme', $customerpayment->schemeID)
            ->whereNotIn('FlatNo', $bookcancel_flats)
            ->pluck('FlatNo');

        $flats = DB::table('flats_details')
            ->where('scheme_ID', $customerpayment->schemeID)
            ->where('Wing', $customerpayment->Wing)
            ->whereIn('ID', $booking_flats)
            ->get();

        return view('backend.customer_payment.create', compact(
            'customerpayment', 'banks', 'schemes', 'flats','booking_cust'
        ));
    }

    public function update(Request $request)
    {
        // Validate request
        $request->validate([
            'scheme'      => 'required|string',
            'typesrch'    => 'required|string',
            'FlatID'      => 'required|string',
            'CustomerID'  => 'required|string',
            'bill_pay'    => 'nullable|string',
            'payement_by' => 'nullable|string',
            'Pay_type'    => 'required|string',
            'amount_pay'  => 'required|numeric|min:0.01',
            'receipt_no'  => 'nullable|numeric',
            'cheque_no'   => 'nullable|string',
            'account_no'  => 'nullable|string',
            'bnk_charge'  => 'nullable|numeric',
            'narration'   => 'nullable|string',
        ]);

        // Find record using correct key
        $customerTransfer = CustomerPayment::findOrFail($request->ID);

        // Update record
        $customerTransfer->update([
           'ClientID'      => session('selected_scheme_id'),
            'schemeID'      => $request->scheme,
            'Wing'          => $request->typesrch,
            'FlatID'        => $request->FlatID,
            'Booking_ID'    => $request->CustomerID,
            'bill_payment'  => $request->bill_pay,
            'payement_by'   => $request->payement_by,
            'payment_method'=> $request->Pay_type,
            'account_no'    => $request->account_no,
            'narration'     => $request->narration,
            'receipt_no'    => $request->receipt_no,
            'Date'          => $request->date,
            'amt_pay'       => $request->amount_pay,
            'cheque_no'     => $request->cheque_no,
            'reconciliation'=> 0,
            'type'          => 'Flat Payment',
            'userID'         => Auth::id(),
        ]);

        return redirect()
            ->route('customer_payment')
            ->with('success', 'Record updated successfully!');
    }


 
    public function getWingNo(Request $request)
    {
        $schemeId = $request->input('schmid');

        // Fetch unique wings for the scheme
        $wingDetails = Flat_details::where('scheme_ID', $schemeId)
            ->groupBy('Wing')
            ->get(['Wing']);

        // Return HTML for the <select> dropdown
        
        $html = '<select name="typesrch" id="typesrch" class="input-sm form-control chosen-select " data-placeholder="Select" onchange="wing();" >';
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
    public function getFlats(Request $request)
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
            ->whereIn('ID', $bookedFlats)
            ->get();

        // Return HTML for the dropdown
        $html = '<select name="FlatID" id="FlatID" class="input-sm form-control chosen-select required" data-placeholder="Select" onchange="getCustomerDetails();">';
        $html .= '<option value="">Select</option>';

        foreach ($flats as $flat) {
            $selected = (isset($db_record) && $db_record->FlatID == $flat->ID) ? 'selected' : '';
            $html .= "<option value='{$flat->ID}' {$selected}>{$flat->FlatNo}</option>";
        }

        $html .= '</select>';
        $html .= '<input type="hidden" id="schemeH" name="schemeH" value="'.$schemeId.'">';
        $html .= '<script>$(".chosen-select").chosen();</script>';

        return $html;
    }
    public function getCustomer(Request $request)
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
    public function accountByPayment(Request $request)
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
   public function getBookingPending(Request $request)
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

        $pendingAmount = ($bookingCustomer->TotalFlatAmt - $paidAmt) + $billPay;

        return response()->json($pendingAmount);
    }
    public function show($id)
    {
        /* -------------------------------------------------
        MAIN PAYMENT & BOOKING
        ------------------------------------------------- */

        $payment = CustomerPayment::with(['schemes', 'booking_cust'])
            ->where('ID', $id)
            ->firstOrFail();

        $booking = $payment->booking_cust;

        /* -------------------------------------------------
        EXTRA BILL
        ------------------------------------------------- */

        $extraWork = DB::table('add_bill')
            ->where('Project_ID', $booking->Scheme)
            ->where('FlatNo', $booking->FlatNo)
            ->sum('Amount');

        /* -------------------------------------------------
        REFUND
        ------------------------------------------------- */

        $refund = DB::table('customer_refund')
            ->where('schemeID', $payment->schemeID)
            ->where('bookingcustomer', $payment->Booking_ID)
            ->sum('amt_pay');

        /* -------------------------------------------------
        DOWN PAYMENT
        ------------------------------------------------- */

        $downPayment = CustomerPayment::where('Booking_ID', $payment->Booking_ID)
            ->where('type', 'Downpayment')
            ->sum('amt_pay');

        /* -------------------------------------------------
        BANK & SELF PAID
        ------------------------------------------------- */

        $bankPaid = CustomerPayment::where('Booking_ID', $payment->Booking_ID)
            ->where('payement_by', 'bank')
            ->sum('amt_pay');

        $selfPaid = CustomerPayment::where('Booking_ID', $payment->Booking_ID)
            ->where(function ($q) {
                $q->where('payement_by', 'self')
                ->orWhere('type', 'Downpayment');
            })
            ->sum('amt_pay');

        /* -------------------------------------------------
        BUILD FULL PAYMENT TABLE (AS OLD PHP)
        ------------------------------------------------- */

        $rows = collect();

        /** BOOKING PAYMENTS **/
        $bookingPayments = CustomerPayment::with('accountNo')
            ->where('Booking_ID', $payment->Booking_ID)
            ->get();

        foreach ($bookingPayments as $p) {
            $rows->push([
                'id' => $p->ID,
                'date' => $p->type === 'Downpayment'
                    ? $booking->BookingDate
                    : $p->Date,
                'receipt_no' => $p->receipt_no,
                'payment_method' => $p->payment_method,
                'account' => $p->account->Name ?? '-',
                'amount' => $p->amt_pay . ' - ' . $p->narration,
                'type' => $p->type,
                'pay_by' => $p->payement_by,
                'action' => !in_array($p->type, ['Downpayment']),
            ]);
        }

        /** REFUNDS **/
        $refunds = DB::table('customer_refund')
            ->where('schemeID', $payment->schemeID)
            ->where('bookingcustomer', $payment->Booking_ID)
            ->get();

        foreach ($refunds as $r) {
            $account = DB::table('accounts')->where('ID', $r->account_no)->first();

            $rows->push([
                'id' => $r->ID,
                'date' => $r->Date,
                'receipt_no' => $r->ReceiptNo ?? '-',
                'payment_method' => $r->payment_method,
                'account' => $account->Name ?? '-',
                'amount' => $r->amt_pay . ' - ' . $r->narration,
                'type' => 'Refund',
                'pay_by' => '-',
                'action' => false,
            ]);
        }

        /** EXTRA BILLS **/
        $extras = DB::table('add_bill')
            ->where('Project_ID', $booking->Scheme)
            ->where('FlatNo', $booking->FlatNo)
            ->get();
            // dd($extras);

        foreach ($extras as $e) {
            // $account = DB::table('accounts')->where('ID', $e->account_no)->first();
            $bill = DB::table('add_bill_detail')->where('Bid', $e->ID)->first();

            $rows->push([
                'id' => $e->ID,
                'date' => $e->Date,
                'receipt_no' => $e->receipt_no ?? '-',
                'payment_method' => $e->type,
                'account' => $account->Name ?? '-',
                'amount' => $e->Amount . ' - ' . ($bill->billNo ?? ''),
                'type' => 'Extra',
                'pay_by' => '-',
                'action' => false,
            ]);
        }

        /** SORT BY DATE **/
        $payments = $rows->sortBy('date')->values();

        /* -------------------------------------------------
        TOTAL CALCULATIONS (MATCH OLD PHP)
        ------------------------------------------------- */

        $totalBookingPaid = CustomerPayment::where('Booking_ID', $payment->Booking_ID)
            ->sum('amt_pay');

        $totalRefund = DB::table('customer_refund')
            ->where('schemeID', $payment->schemeID)
            ->where('bookingcustomer', $payment->Booking_ID)
            ->sum('amt_pay');

        $totalPaid = $totalBookingPaid - $totalRefund;

        $grandTotal = $booking->TotalFlatAmt + $extraWork;

        $pendingAmount = $grandTotal - $totalPaid;

        return view('backend.customer_payment.show', compact(
            'payment',
            'booking',
            'extraWork',
            'refund',
            'downPayment',
            'bankPaid',
            'selfPaid',
            'payments',
            'totalPaid',
            'grandTotal',
            'pendingAmount'
        ));
    }
    public function receipt($id)
    {
        // Get payment record
        $payment = CustomerPayment::findOrFail($id);

        // Get booking/customer info
        $booking = Booking_Customer::findOrFail($payment->Booking_ID);

        $grandTotal=$payment->amt_pay;

        $client = DB::table('Client')
        ->where('ClientID', '')
        ->orWhereRaw('1 = 1')
        ->first();
        $totalInWords = \CustomHelper::numberToWords(round($grandTotal));

        return view('backend.customer_payment.receipt_print', compact('payment', 'booking', 'client','totalInWords'));
    }
    

    

}
