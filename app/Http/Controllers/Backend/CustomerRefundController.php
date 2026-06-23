<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Booking_Customer;
use App\Models\Backend\CustomerRefund;
use Illuminate\Support\Facades\DB;

class CustomerRefundController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = CustomerRefund::with(['customers','accountNo', 'schemes'])
    ->when($request->from_date, function ($q) use ($request) {
        $q->whereDate('customer_refund.Date', '>=', $request->from_date);
    })
    ->when($request->to_date, function ($q) use ($request) {
        $q->whereDate('customer_refund.Date', '<=', $request->to_date);
    })
    ->select(
        'customer_refund.ID',
        'customer_refund.Date',
        'customer_refund.bookingcustomer',
        'customer_refund.schemeID',
        'customer_refund.amt_pay'
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

                ->addColumn('accounts_name', function ($row) {
                    return $row->accountNo->Name ?? '';
                })

              
                ->addColumn('actions', function ($row) {

                    $actions = '';

                    if (hasPermission('edit_customer_refund')) {
                        $actions .= '<a href="' . route('customer_refund.edit', $row->ID) . '" class="me-2 text-primary">
                                        <i data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_customer_refund')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="delete-form-' . $row->ID . '">
                                        <i data-feather="trash"></i>
                                    </a>
                                    <form id="delete-form-' . $row->ID . '" action="' . route('customer_refund.delete', $row->ID) . '" method="POST" class="d-none">
                                        ' . csrf_field() . method_field('DELETE') . '
                                    </form>';
                    }

                    return $actions;
                })

               


                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.customer_refund.index');
    }
    
    public function create()
    {   
        $clientId = session('selected_scheme_id');
        $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $schemes = SchemeDetail::where('completFalg', 0)
        ->where('ID', $clientId)
        ->get();

        $booking_cust = Booking_Customer::where('ClientID', $clientId)->get();
        $db_record = new CustomerRefund();

        
        
        return view('backend.customer_refund.create', compact('banks','schemes','booking_cust','db_record'));
    }
    public function getBookingCustomer(Request $request)
    {
        $schemeId = $request->scheme_id;
        $clientId = session('selected_scheme_id');

        $count = DB::table('booking_customer')
            ->where('SchemeID', $schemeId)
            ->where('ClientID', $clientId)
            ->count();

        return response()->json($count);
    }
    public function getTotalPaid(Request $request)
    {
        $bookingID = $request->bookingID;
        $uid       = $request->Uid ?? '000';
        $clientId  = session('selected_scheme_id');

        $bookingPay = DB::table('booking_payment')
            ->where('Booking_ID', $bookingID)
            ->where('ClientID', $clientId)
            ->sum('amt_pay');

        $refundPay = DB::table('customer_refund')
            ->where('bookingcustomer', $bookingID)
            ->where('ClientID', $clientId)
            ->where('ID', '!=', $uid)
            ->sum('amt_pay');

        $totalPaid = $bookingPay - $refundPay;

        return response()->json([
            'totalPaid' => $totalPaid
        ]);
    }

    public function getAccountOptions(Request $request)
    {
       $payMethod = $request->pay_method;

        if ($payMethod === 'cash') {
            $accounts = Bank_Acc::where('Name', 'Cash In Hand')
                ->where('ClientID', session('selected_scheme_id'))
               
                ->get();
            $selectOption = '';
        } else {
            $accounts = Bank_Acc::where('Name', '!=', 'Cash In Hand')
                ->where('ClientID', session('selected_scheme_id'))
                ->orderBy('Name')
                ->get();
                //print_r($accounts);
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
    public function getCustomerRefundBalance(Request $request)
    {
        $matId = $request->input('Matid');
        $payId = $request->input('PayID');
        $opening = $request->input('opening', 'no');
        $date = $request->input('Date', now()->format('Y-m-d'));

        // Adjust date if opening is yes
        if ($opening === 'yes') {
            $date = now()->subDay()->format('Y-m-d');
        }

       
        $balance = getBalanceTransferRefund($matId, $payId, $date);

        return response()->json(['balance' => $balance]);
    }

    public function store(Request $request)
    {
        // dd($request);
        $account=$request->validate([
            'schem'      => 'required|string',
            'customer'   => 'required|string',
            'Pay_type'  => 'required|string',
            'amount_pay'       => 'required|numeric|min:0.01',
            'pincode' => 'nullable|numeric',
            'contactno' => 'nullable|numeric',
            'email' => 'nullable|email',
            'cheque_no'       => 'nullable|string',
            
            'account_no'       => 'nullable|string',
            'bnk_charge'       => 'nullable|numeric',
            'narration'       => 'nullable|string',
        ]);
        //dd($account);

        CustomerRefund::create([
            'ID'       => uniqid(),
            'ClientID'       => session('selected_scheme_id'),
            'schemeID'   => $request->schem,
            'bookingcustomer'     => $request->customer,
            'account_no'     => $request->account_no,
            'narration'     => $request->narration,
            
            'Date'           => $request->date,
            'amt_pay'        => $request->amount_pay,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            
            'bankcharge'      => $request->bnk_charge,
            'reconciliation' => 0,
            'paytype' =>'Refund',
            'userID'         => auth()->id(),
        ]);

        return redirect()
            ->route('customer_refund')
            ->with('success', 'Record added successfully!');
    }

    public function edit($id)
    {
        $clientId = session('selected_scheme_id');
       $postdated = CustomerRefund::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

        $banks = Bank_Acc::where('ClientID', $clientId)->get();
        $schemes = SchemeDetail::where('ID', $clientId)->get();
        $db_record = new CustomerRefund();
        $booking_cust = Booking_Customer::where('ClientID', $clientId)->get();

        return view('backend.customer_refund.create', compact(
            'postdated','banks','schemes','db_record','booking_cust'
        ));
    }

     public function update(Request $request)
    {
        // Validate request
        $request->validate([
            'schem'      => 'required|string',
            'customer'   => 'required|string',
            'Pay_type'  => 'required|string',
            'amount_pay'       => 'required|numeric|min:0.01',
            'pincode' => 'nullable|numeric',
            'contactno' => 'nullable|numeric',
            'email' => 'nullable|email',
            'cheque_no'       => 'nullable|string',
            
            'account_no'       => 'nullable|string',
            'bnk_charge'       => 'nullable|numeric',
            'narration'       => 'nullable|string',
        ]);

        // Find record using correct key
        $customerTransfer = CustomerRefund::findOrFail($request->ID);

        // Update record
        $customerTransfer->update([
            'ClientID'       => session('selected_scheme_id'),
            'schemeID'   => $request->schem,
            'bookingcustomer'     => $request->customer,
            'account_no'     => $request->account_no,
            'narration'     => $request->narration,
            
            'Date'           => $request->date,
            'amt_pay'        => $request->amount_pay,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            
            'bankcharge'      => $request->bnk_charge,
            'reconciliation' => 0,
            'paytype' =>'Refund',
            'userID'         => Auth::id(),
        ]);

        return redirect()
            ->route('customer_refund')
            ->with('success', 'Record updated successfully!');
    }

    public function destroy($id)
    {
        $bankform = CustomerRefund::findOrFail($id);
        $bankform->delete();

        return redirect()
            ->route('customer_refund')
            ->with('success', 'Record has been deleted successfully!');
    }
    
    

}
