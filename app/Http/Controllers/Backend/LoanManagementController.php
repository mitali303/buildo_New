<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Loan;
use App\Models\Backend\Partners_Investor_Loan;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\Scheme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;


class LoanManagementController extends Controller
{
    public function index(Request $request)
    {
      $fromDate = $request->FromDate
        ? Carbon::parse($request->FromDate)->format('Y-m-d')
        : session('FromDate', now()->startOfMonth()->format('Y-m-d'));

    $toDate = $request->ToDate
        ? Carbon::parse($request->ToDate)->format('Y-m-d')
        : session('ToDate', now()->endOfMonth()->format('Y-m-d'));

    session([
        'FromDate' => $fromDate,
        'ToDate' => $toDate
    ]);
     $clientId = session('selected_scheme_id');

        // Get unique customers with loan records
        $loans = Loan::select('customer', DB::raw('MAX(Date) as latest_date'))
        ->whereBetween('Date', [$fromDate, $toDate])
        ->where('ClientID', $clientId)
        ->groupBy('customer')
        ->with('partner')
        ->get();


        // Calculate totals per customer
        $loanData = $loans->map(function($loan) use ($fromDate, $toDate) {
            $customer = $loan->partner;

            $loanAmtGive = Loan::whereBetween('Date', [$fromDate, $toDate])
                ->where('customer', $customer->ID)
                ->where('paytype', 'Paid')
                ->where('transType', 0)
                ->sum('amt_pay');

            $loanAmtTaken = Loan::whereBetween('Date', [$fromDate, $toDate])
                ->where('customer', $customer->ID)
                ->where('paytype', 'Received')
                ->where('transType', 0)
                ->sum('amt_pay');

            $interestRec = Loan::whereBetween('Date', [$fromDate, $toDate])
                ->where('customer', $customer->ID)
                ->where('paytype', 'Received')
                ->where('transType', 1)
                ->sum('amt_pay');

            $interestPaid = Loan::whereBetween('Date', [$fromDate, $toDate])
                ->where('customer', $customer->ID)
                ->where('paytype', 'Paid')
                ->where('transType', 1)
                ->sum('amt_pay');

            $credit = $loanAmtTaken - $loanAmtGive;

            return [
                'customer' => $customer,
                'loanAmtGive' => $loanAmtGive,
                'loanAmtTaken' => $loanAmtTaken,
                'interestRec' => $interestRec,
                'interestPaid' => $interestPaid,
                'balance' => $credit,
            ];
        });

        return view('backend.loanmanagement.index', compact('loanData', 'fromDate', 'toDate'));
    }
    public function detail($customer)
    {
        $loans = Loan::where('customer', $customer)
            ->with('partner')
            ->orderBy('Date', 'asc')
            ->get();

        return view('backend.loanmanagement.detail', compact('loans'));
    }

    public function create(Request $request)
    {
        // Task = add
        //$task = 'add';

        $date = date('Y-m-d');
        $PayStyle = "display:none;";
        $display = "display:none;";
        $Paytype1 = "";
        $PayType = "";
        $checked = $checked1 = "";

        if ($request->payTp == 'Paid') {
            $checked = 'checked';
            $checked1 = '';
            $Paytype1 = 'Paid';
            $PayType = 'Amount Paid';
        }

        if ($request->payTp == 'Received') {
            $checked1 = 'checked';
            $checked = '';
            $Paytype1 = 'Received';
            $PayType = 'Amount Received';
            $display = "display:block;";
        }
        $clientId = session('selected_scheme_id');
       // $loan = Loan::first(); 
        $partners_loan = Partners_Investor_Loan::all();
       // $schemes = Scheme::all();
        $schemes = Scheme::where('ID', $clientId)
        ->get();
        
         $banks = Bank_Acc::where('ClientID', $clientId)->get();
         $loan = new Loan();

        return view('backend.loanmanagement.create', compact('loan',
            'date','PayStyle','display','Paytype1','PayType',
            'checked','checked1','partners_loan','schemes','banks'
        ));
    }
    public function store(Request $request)
    {
        // dd($request);
        $account=$request->validate([
            'scheme'      => 'required|string',
            'paytype'      => 'required|string',
            'customer'      => 'required|string',
            'transType'      => 'nullable|string',
            'Pay_type'  => 'required|string',
            'amount_pay'       => 'required|numeric|min:0.01',
            'cheque_no'       => 'nullable|string',
            'account_no'       => 'nullable|string',
            'bnk_charge'       => 'nullable|numeric',
            'PaymentId'       => 'nullable|numeric',
            'bFlag'       => 'nullable|numeric',
            'narration'       => 'nullable|string',
            'title'       => 'nullable|string',
            'exptype'       => 'nullable|string',
            'paydetail'       => 'nullable|string',
        ]);
       

         $id = uniqid();

    $expense = Loan::create([
        'ID'             => $id,
        'ClientID'       => session('selected_scheme_id'),
        'Date'           => $request->date,
        'customer'        =>$request->customer,
        'scheme'        =>$request->scheme,
        'paytype'        =>$request->paytype,
        'transType'        =>$request->transType,
        'amt_pay'        => $request->amount_pay,
        'bankcharge'     => $request->bnk_charge,
        'payment_method' => $request->Pay_type,
        'cheque_no'      => $request->cheque_no,
        'paydetail'      => $request->paydetail,
        'interest'      => $request->interest,
        'account_no'     => $request->account_no,
        'narration'      => $request->narration,
        'title'          => $request->title,
        'Exp_type'       => $request->exptype,
        'empID'          => $request->empName,
        'month'          => $request->mth,
        'year'           => $request->year,
        'fromdate'       => Carbon::parse($request->fromDate)->format('Y-m-d'),
        'todate'         => Carbon::parse($request->toDate)->format('Y-m-d'),
         'userID'         => auth()->id(),
    ]);

        return redirect()
            ->route('loan_management')
            ->with('success', 'Record added successfully!');
    }

    public function edit($id)
    {
        // Task = update
       // $task = 'update';
        $clientId = session('selected_scheme_id');
        //$loans = Loan::findOrFail($id);
        $loans = Loan::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

        $date = $loans->Date;
        $Paytype1 = $loans->paytype;
        
        if ($Paytype1 == 'Paid') {
            $PayStyle = "";
            $display = "display:block;";
            $checked = 'checked';
            $checked1 = '';
            $PayType = 'Amount Paid';
        } else {
            $PayType = 'Amount Received';
            $PayStyle = "display:none;";
            $checked1 = 'checked';
            $checked = '';
            $display = "display:none;";
        }

        if ($Paytype1 == 'Received') {
            $display = "display:block;";
        }

        $partners_loan = Partners_Investor_Loan::all();
        $schemes = Scheme::all();
        $banks = Bank_Acc::all();
        

        return view('backend.loanmanagement.create', compact(
            'loans','date','PayStyle','display','Paytype1',
            'checked','checked1','PayType','partners_loan','schemes','banks'
        ));
    }

    
    public function getPendingLoan(Request $request)
    {
        $customerId = $request->customer_id;
        $payId = $request->pay_id ?? null;

        if (!$customerId) {
            return response()->json(['error' => 'Customer ID is required'], 422);
        }

        // Sum of "Paid" (Given)
        $loanPaid = Loan::where('customer', $customerId)
                        ->where('paytype', 'Paid')
                        ->where('transType', 0)
                        ->when($payId, function ($q) use ($payId) {
                            return $q->where('id', '!=', $payId);
                        })
                        ->sum('amt_pay');

        // Sum of "Received" (Taken)
        $loanReceived = Loan::where('customer', $customerId)
                            ->where('paytype', 'Received')
                            ->where('transType', 0)
                            ->when($payId, function ($q) use ($payId) {
                                return $q->where('id', '!=', $payId);
                            })
                            ->sum('amt_pay');

        // Calculate Pending
        if ($loanPaid > $loanReceived) {
            $pendingText = "Debit : " . ($loanPaid - $loanReceived);
            $pendingValue = $loanPaid - $loanReceived;
        } else {
            $pendingText = "Credit : " . ($loanReceived - $loanPaid);
            $pendingValue = $loanReceived - $loanPaid;
        }

        return response()->json([
            'pending_text'  => $pendingText,
            'pending_value' => $pendingValue
        ]);
    }
    public function cashMethod(Request $request)
    {
        $payMethod = $request->pay_method;
        $acno      = $request->acno;
        $clientId  = session('Client_Id');

        if ($payMethod === 'cash') {
            $accounts = Bank_Acc::where('ID', 'Cash In Hand')
                ->where('ClientID', $clientId)
                ->orderBy('Name')
                ->get();

            $showSelect = false;
        } else {
            $accounts = Bank_Acc::where('ID', '!=', 'Cash In Hand')
                ->where('ClientID', $clientId)
                ->orderBy('Name')
                ->get();

            $showSelect = true;
        }

        // 🔹 Build HTML here
        $html = '<select id="account_no" name="account_no" 
                    class="form-control chosen-select" 
                    onchange="getBalance()">';

        if ($showSelect) {
            $html .= '<option value="">Select</option>';
        }

        foreach ($accounts as $account) {
            $selected = ($acno == $account->ID) ? 'selected' : '';

            $html .= '<option value="'.$account->ID.'" '.$selected.'>'
                .  $account->Name.' ('.substr($account->ACNo, -3).')'
                .  '</option>';
        }

        $html .= '</select>';

        // 🔹 Re-init chosen after AJAX load
        $html .= '<script>
            $(function () {
                $(".chosen-select").chosen();
            });
        </script>';

        return response($html);
    }
     public function getLoanBalances(Request $request)
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
    public function getPayType(Request $request)
    {
        if ($request->type === 'GetPay_type') {

            // Example: fetch from DB if needed
            $payment_method = 'cash';

            // Decide onchange event
            if ($payment_method === 'cash') {
                $event = 'onchange="checkType(); getBalance();"';
            } else {
                $event = 'onchange="checkType();"';
            }

            // Build HTML select
            $html = '
            <select style="width:100%"
                    class="input-sm form-control chosen-select required"
                    data-rel="chosen"
                    data-placeholder="Select"
                    name="Pay_type"
                    id="Pay_type"
                    '.$event.'>

                <option value="">SELECT</option>
                <option value="cash">Cash</option>
                <option value="cheque">Cheque</option>
                <option value="e-Payment">E-Payment</option>
            </select>

            <script>
                $(function () {
                    $(".chosen-select").chosen();
                });
            </script>';

            return response($html);
        }
    }

    public function getReceivedPaytype(Request $request)
    {
        if ($request->type === 'GetPaytype') {

            $payment_method = 'cash'; // fetch from DB if needed

            $event = ($payment_method === 'cash')
                ? 'onchange="checkType(); getBalance();"'
                : 'onchange="checkType();"';

            $html = '
            <select style="width:100%"
                    class="input-sm form-control chosen-select"
                    name="Pay_type"
                    id="Pay_type" 
                    '.$event.'>

                <option value="">SELECT</option>
                <option value="cash">Cash</option>
                <option value="cheque">Cheque</option>
                <option value="e-Payment">E-Payment</option>

            </select>

            <script>
                $(function () {
                    $(".chosen-select").chosen();
                });
            </script>';

            return response($html);
        }
    }
    public function getAccounts(Request $request)
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
      public function destroy($id)
    {
        $bankform = Loan::findOrFail($id);
        $bankform->delete();

        return redirect()
            ->route('loan_management')
            ->with('success', 'Record has been deleted successfully!');
    }



}
