<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Supplier_contractor;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Inv_Detail;
use App\Models\Backend\Inv_Product;
use App\Models\Backend\Workorder_details;
use App\Models\Backend\Workorder_material;
use App\Models\Backend\Workorder_payment;
use App\Models\Backend\Material;
use App\Models\Backend\Scope_payment_detail;
use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Site_work_payController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');

        $query = Workorder_details::with(['Contractor', 'Scheme'])
            ->select([
                'ID','Date','gtotal','TDSAmt','Total',
                'retain_amt','ContractorID','SiteLocation'
            ])
            ->where('ClientID', $clientId)
            ->orderBy('Date', 'DESC');

        // ✅ Date Filters
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('Date', [
                $request->from_date,
                $request->to_date
            ]);
        } elseif ($request->from_date) {
            $query->whereDate('Date', '>=', $request->from_date);
        } elseif ($request->to_date) {
            $query->whereDate('Date', '<=', $request->to_date);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('amount_pay', function ($row) use ($clientId) {

                return \App\Models\Backend\Workorder_payment::where('ClientID', $clientId)
                    ->where('WorkorderID', $row->ID)
                    ->sum('Payable');   
            })
                // ->addColumn('pending payable', function ($row) {

                //     $clientId = session('selected_scheme_id');

                //     $items = \App\Models\Backend\Workorder_material::where('ClientID', $clientId)
                //         ->where('workorderID', $row->ID)
                //         ->get();

                //     $pending = 0;

                //     foreach ($items as $item) {

                //         $total = $item->Amount ?? 0;

                //         $paid = \App\Models\Backend\Scope_payment_detail::where('ClientID', $clientId)
                //             ->where('WorkorderID', $row->ID)
                //             ->where('Scope', $item->ID)
                //             ->sum('amt_pay');

                //         $pending += ($total - $paid);
                //     }

                //     return number_format($pending, 2);
                // })
                ->addColumn('pending payable', function ($row) use ($clientId) {

    $paidAmount = \App\Models\Backend\Workorder_payment::where('ClientID', $clientId)
        ->where('WorkorderID', $row->ID)
        ->sum('Payable');

    $pending = ($row->gtotal ?? 0) - $paidAmount;

    return number_format($pending, 2);
})
            ->editColumn('Date', function ($row) {
                return Carbon::parse($row->Date)->format('d-m-Y');
            })

            ->addColumn('ContractorID', function ($row) {
                return optional($row->Contractor)->Name ?? '-';
            })

            ->addColumn('SiteLocation', function ($row) {
                return optional($row->Scheme)->Name ?? '-';
            })

            ->addColumn('actions', function ($row) {

                $makePaymentUrl = route('Site_work_pay.makePayment', $row->ID);
                $viewPaymentUrl = route('Site_work_pay.viewPayment', $row->ID);

                $actions = '';

                if (hasPermission('create_site_work_order_payment')) {
                    $actions .= '<a href="'.$makePaymentUrl.'" class="me-2 text-success">
                                    <span style="font-weight:bold;font-size:20px;">₹</span>
                                </a>';
                }

                if (hasPermission('create_site_work_order_payment')) {
                    $actions .= '<a href="'.$viewPaymentUrl.'" class="me-2 text-info">
                                    <i data-feather="file-text"></i>
                                </a>';
                }

                return $actions;
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.Site_work_pay.index');
}


public function viewPayment($id)
{
    return view('backend.Site_work_pay.view_payment', compact('id'));
}

public function viewPaymentData(Request $request, $id)
{
    
    $query = Workorder_payment::where('WorkorderID', $id);

    return DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('Date', function ($row) {
            return Carbon::parse($row->Date)->format('d-m-Y');
        })
        
        ->addColumn('actions', function ($row) {

            $editUrl   = route('Site_work_pay.edit', $row->ID);
            $deleteUrl = route('Site_work_pay.delete', $row->ID);
            $printUrl  = route('Site_work_pay.print', $row->ID);
            $formId    = 'delete-form-' . $row->ID;

            $actions = '';

            if (hasPermission('edit_site_work_order_payment')) {
                $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                <i class="align-middle" data-feather="edit-2"></i>
                            </a>';
            }

            if (hasPermission('delete_site_work_order_payment')) {
                $actions .= '<a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
                                <i class="align-middle" data-feather="trash"></i>
                            </a>
                            <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                            </form>';
            }
            
             $actions .= '<a href="'.$printUrl.'" target="_blank" class="me-2 text-success">
                    <i class="align-middle" data-feather="printer"></i>
                 </a>';

            return $actions;
        })

        ->rawColumns(['actions'])
        ->make(true);
}

public function printPayment($id)
{
    $clientId = session('selected_scheme_id');

    $data = DB::table('workorder_payment as wp')
        ->leftJoin('vendor as sc', 'sc.ID', '=', 'wp.conID')
        ->leftJoin('workorder_detail as wd', 'wd.ID', '=', 'wp.WorkorderID')
            ->leftJoin('scheme_step1 as sm', 'sm.ID', '=', 'wd.SiteLocation') // ✅ ADD THIS
            ->select(
                'wp.*',
                'sc.Name as ContractorName',
                'wd.WorkorderNo',
                'sm.Name as SiteName'   // ✅ THIS IS YOUR SITE NAME
            )
        
        ->where('wp.ClientID', $clientId)
        ->where('wp.ID', $id)
        ->first();

    if(!$data){
        abort(404,'Payment not found');
    }

    $client = DB::table('company_settings')
        ->first();

    $date = Carbon::parse($data->Date)->format('d/m/Y');

    return view('backend.Site_work_pay.print_payment', [
        'data'   => $data,
        'date'   => $date,
        'client' => $client
    ]);
}

public function getAccountList(Request $request)
{
    $payMethod = $request->pay_method;
    $selected  = $request->selected_id ?? null; // may be ID or ACNo text
    $clientId = session('selected_scheme_id');
    // Cash → Only "Cash In Hand"
    if ($payMethod === 'cash') {
        $accounts = Bank_Acc::where('ACNo', 'Cash In Hand')->where('ClientID', $clientId)->orderBy('Name')->get();
    }
    // Other payment methods → Except Cash In Hand
    else {
        $accounts = Bank_Acc::where('ACNo', '!=', 'Cash In Hand')->where('ClientID', $clientId)->orderBy('Name')->get();
    }
    // Build the HTML to return
    $html = '<select id="account_no" name="account_no" class="form-control" onchange="getBalance()">';

    $html .= '<option value="">Select</option>';

    // foreach ($accounts as $acc) {
    //     $html .= '<option value="' . $acc->ID . '">' . 
    //                  $acc->ACNo . ' - ' . $acc->Name . 
    //              '</option>';
    // }

    // $html .= '</select>';
    foreach ($accounts as $acc) {
        // select if ID matches OR ACNo text matches (covers both DB styles)
        $sel = '';
        if ($selected !== null) {
            if ((string)$acc->ID === (string)$selected || (string)$acc->ACNo === (string)$selected) {
                $sel = ' selected';
            }
        }
        $html .= '<option value="'.e($acc->ID).'"'.$sel.'>'.e($acc->ACNo.' - '.$acc->Name).'</option>';
    }

    $html .= '</select>';

    return response()->json(['html' => $html]);
}

public function ajaxGetBalance(Request $request)
{
    $matid = $request->matid;
    $payid = $request->payid ?? '';
    $paymentId = $request->paymentId ?? '';
    $date  = $request->date ?? date('Y-m-d');

    $balance = $this->getbalance($matid, $payid, $date, $paymentId);

    return response()->json([
        'balance' => $balance
    ]);
}

public function getbalance($matid, $payid, $date, $paymentId)
{
    $_REQUEST['Matid'] = $matid;
    $_REQUEST['paymentId'] = $paymentId;
    $_REQUEST['PayID'] = $payid;
    $_REQUEST['Date']  = $date;
    // dd($paymentId);

    
    $clientId = session()->get('selected_scheme_id');
    $ac_id    = $_REQUEST['Matid'];

    if ($_REQUEST['PayID'] == '') {
        $_REQUEST['PayID'] = '0000';
    }

    /** -------------------------
     *   GET OPENING BALANCE
     *  ------------------------- */
    if ($_REQUEST['Matid'] == 'All') {

        $mname_record = DB::table('accounts')
            ->where('ClientID', $clientId)
            ->where('ID', '!=', 'Cash In Hand')
            ->sum('OBalance');

    } else {

        $accbal = DB::table('accounts')
            ->where('ID', $ac_id)
            ->where('ClientID', $clientId)
            ->first();

        $mname_record = $accbal->OBalance ?? 0;
    }

    /** -------------------------
     *  CONDITIONS LIKE CORE PHP
     *  ------------------------- */
    if ($_REQUEST['Matid'] == 'Allwithcashinhand') {

        $mname_record = DB::table('accounts')
            ->where('ClientID', $clientId)
            ->sum('OBalance');

        $condition = "((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";

        $cond1 = "1";
        $cond2 = "1";

    } elseif ($_REQUEST['Matid'] != 'All') {

        $condition = "account_no='" . $_REQUEST['Matid'] . "' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";
        $cond1     = "account_from='" . $_REQUEST['Matid'] . "' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";
        $cond2     = "account_to='" . $_REQUEST['Matid'] . "' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";            


    } else {

        $condition = "account_no!='Cash In Hand' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";
        $cond1     = "account_from!='Cash In Hand' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";
        $cond2     = "account_to!='Cash In Hand' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";


    }

    /** -------------------------
     *   DATE FORMAT HANDLING
     *  ------------------------- */
    $date = $_REQUEST['Date'] != ''
        ? date("Y-m-d", strtotime($_REQUEST['Date']))
        : date("Y-m-d");

    $condition4 = "AND payment_date <= '" . $date . "'";
    $condition5 = "AND Date <= '" . $date . "'";

    /** -------------------------
     *  BUILD ALL SUM QUERIES (DB RAW)
     *  ------------------------- */

    function sumQuery($table, $where)
    {
        return DB::table($table)
            ->whereRaw($where)
            ->sum('amt_pay');
    }

    $cid = $clientId;
    $pid = $_REQUEST['PayID'];

    // Expenses
    $getExp_WorkOrder = DB::table('workorder_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getExp_invoice   = DB::table('inv_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition4")->sum('amt_pay');
    
    $getExp_partners  = DB::table('partners_loan')->whereRaw("$condition AND ClientID='$cid' AND paytype='Paid' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getExp_loan      = DB::table('loan')->whereRaw("$condition AND ClientID='$cid' AND paytype='Paid' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getExp_loanPayment = DB::table('loan_payment')->whereRaw("$condition AND ClientID='$cid' AND paytype='Paid' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getExp_salary    = DB::table('salary')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getExp_Expences  = DB::table('daily_trans')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' AND (PaymentId IS NULL OR PaymentId!='$pid') $condition5")->sum('amt_pay');
    $getExp_SiteExp = DB::table('site_expences')
    ->where('account_no', $_REQUEST['Matid'])
    ->where(function ($q) {
        $q->where(function ($q2) {
            $q2->where('payment_method', 'cheque')
               ->where('reconciliation', '1');
        })
        ->orWhere('payment_method', '!=', 'cheque');
    })
    ->whereDate('Date', '<=', $date)
    ->where('ClientID', $cid)
    ->where('ID', '!=', $pid)
    ->where(function ($q) use ($pid){
        $q->whereNull('PaymentId')
          ->orWhere('PaymentId', '!=', $pid);
    })
    ->sum('amt_pay');


    //labourpay
    $getExp_lbrpay = DB::table('labour_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');
    //endlbrpay

    $getExp_LandExp   = DB::table('land')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' AND (PaymentId IS NULL OR PaymentId!='$pid') $condition5")->sum('amt_pay');
    $getIncome_Expense = DB::table('booking_cancel')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getExp_Stamp     = DB::table('stampotherexpenses')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' AND (PaymentId IS NULL OR PaymentId!='$pid') $condition5")->sum('amt_pay');
    $getExp_CustRefund = DB::table('customer_refund')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');

    // Account Transfers
    $acfrom = DB::table('account_transfer')->whereRaw("$cond1 AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');

    //payroll empadvance and salary
    if(isset($_REQUEST['iid'])){
        $iid = $_REQUEST['iid'];
    }
    else{
        $iid = 000;
    }

    if(isset($_REQUEST['Matid'])){
        $accno = $_REQUEST['Matid'];
    }
    else{
        $accno = 000;
    }
   $emp_adv = DB::table('employee_advance')
    ->where('account_no', $accno)
    ->where('ClientID', $clientId)
    ->where('id', '!=', $iid) // ✅ exclude current record
    ->where(function ($q) {
        $q->where(function ($q2) {
            $q2->where('payment_method', 'cheque')
               ->where('reconciliation', '1');
        })
        ->orWhere('payment_method', '!=', 'cheque');
    })
    ->whereDate('Date', '<=', '2026-04-22')
    ->sum('advance');

    $emp_salary = DB::table('salarymaster')
    ->where('account_no', $accno)
    ->where('ClientID', $clientId)
    ->where('id', '!=', $iid) // ✅ exclude current record
    ->where(function ($q) {
        $q->where(function ($q2) {
            $q2->where('payment_method', 'cheque')
               ->where('reconciliation', '1');
        })
        ->orWhere('payment_method', '!=', 'cheque');
    })
    ->whereDate('Date', '<=', '2026-04-22')
    ->sum('amount');

    // Income
    $getIncome_loan = DB::table('loan')->whereRaw("$condition AND ClientID='$cid' AND paytype='Received' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getIncome_loanPay = DB::table('loan_payment')->whereRaw("$condition AND ClientID='$cid' AND paytype='Received' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getIncome_partners = DB::table('partners_loan')->whereRaw("$condition AND ClientID='$cid' AND paytype='Received' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getIncome_Project = DB::table('project_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('Payable');
    $getIncome_booking = DB::table('booking_payment')->whereRaw("$condition AND ClientID='$cid' AND amt_pay>0 AND ID!='$pid' $condition5")->sum('amt_pay');
    // $getIncome_payment = DB::table('income_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');
$getIncome_payment = DB::table('income_payment')
    ->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")
    ->whereNotIn('IncomeType', [
        'Hotel Expenses',
        'Salary',
        'Advanced Salary',
        'Contractor',
        'Return/Rejected Matrial',
        'Matrial Transfer'
    ])
    ->sum('amt_pay');
    $getExp_ReturnPayment = DB::table('income_payment')
    ->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")
    ->whereIn('IncomeType', [
        'Hotel Expenses',
        'Salary',
        'Advanced Salary',
        'Contractor',
        'Return/Rejected Matrial',
        'Matrial Transfer'
    ])
    ->sum('amt_pay');


    $getOwner_payment = DB::table('owner_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');
    
    $acto = DB::table('account_transfer')->whereRaw("$cond2 AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');
    
    //payroll income
   $emp_adv_pay = DB::table('emp_avance_pay')
    ->where('account_no', $accno)
    ->where('ClientID', $clientId)
    ->where(function ($q) {
        $q->where(function ($q2) {
            $q2->where('payment_method', 'cheque')
               ->where('reconciliation', '1');
        })
        ->orWhere('payment_method', '!=', 'cheque');
    })
    ->whereDate('Date', '<=', '2026-04-22')
    ->sum('amt_pay');

    /** -------------------------
     *  FINAL CALCULATION
     *  ------------------------- */
    $oldPayment = DB::table('workorder_payment')
    ->where('ID', $_REQUEST['paymentId'])
    ->where('ClientID', $clientId)
    ->first();

    $oldAmt = $oldPayment->amt_pay ?? 0;


    $incometotal = $getIncome_loan + $getIncome_loanPay + $getIncome_partners +
                   $getIncome_Project + $getIncome_booking + $getIncome_payment + $acto + $getOwner_payment
                   +$emp_adv_pay;

    $exptotal = $getExp_WorkOrder + $getExp_invoice + $getExp_partners +
                $getExp_loan + $getExp_loanPayment + $getExp_salary +
                $getExp_Expences + $getExp_SiteExp + $getExp_LandExp +
                $acfrom + $getIncome_Expense + $getExp_Stamp + $getExp_CustRefund + $getExp_lbrpay
                +$emp_adv + $emp_salary + $getExp_ReturnPayment;
               

    $totalbalance = ($mname_record + $incometotal) - $exptotal;

    if ($_REQUEST['PayID'] != '0000') {
        $totalbalance += $oldAmt;
    }
 
    return round($totalbalance, 2);
}



    public function makePayment($id)
    {
    $clientId = session('selected_scheme_id');

    // Load header
    $invoice = Workorder_details::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

    // Load work order items
    $items = Workorder_material::where('ClientID', $clientId)
        ->where('workorderID', $id)
        ->get();

    // Dropdown Data
    $schemes = SchemeDetail::where('ID', $clientId)
                ->where('completFalg', 0)
                ->get();

    $vendors = Supplier_contractor::where('Type', 'CONTRACTOR')
                ->where('ClientID', $clientId)
                ->get();

    $materials = Material::select('Name')
                ->groupBy('Name')
                ->orderBy('Name')
                ->get();

    $worktypes = DB::table('workorder_detail')
                ->select('worktype')
                ->groupBy('worktype')
                ->get();

    $units = DB::table('workorder_material')
                ->select('Unit')
                ->groupBy('Unit')
                ->get();

    $materialNameToTypes = Material::orderBy('Name')->get()->groupBy('Name');

    $existingImages = [];

    if (!empty($invoice->scanimg)) {
        // stored as comma-separated list
        $existingImages = explode(',', $invoice->scanimg);
    }

    $rows = [];

    foreach ($items as $item) {

        // total payable for this scope
        $total = $item->Amount;

        // total paid earlier for this scope (all payments)
        $alreadyPaid = DB::table('scope_payment_detail')
            ->where('ClientID', $clientId)
            ->where('workorderID', $invoice->ID)
            ->where('Scope', $item->ID)
            ->sum('amt_pay');

        // remaining amount
        $remaining = $total - $alreadyPaid;
        if ($remaining < 0) $remaining = 0;

        // now send this to Blade row
        $rows[] = [
            'scope'      => $item->ID,
            'scope_text' => $item->scope,
            'payamount'  => $remaining,  // 👈 FIXED VALUE
            'amt'        => 0,
            'balance'    => $remaining,
            'checked'    => false,
        ];
    }

    $usedTax = DB::table('workorder_payment')
    ->where('ClientID', $clientId)
    ->where('WorkorderID', $invoice->ID)
    ->sum('TaxAmt');

    $usedTds = DB::table('workorder_payment')
        ->where('ClientID', $clientId)
        ->where('WorkorderID', $invoice->ID)
        ->sum('TDSAmt');

    $remainingTax = max(0, ($invoice->TaxAmt ?? 0) - $usedTax);
    $remainingTds = max(0, ($invoice->TDSAmt ?? 0) - $usedTds);


    return view('backend.Site_work_pay.create', [
        'invoice'   => $invoice,
        'rows'   => $rows,
        'items'     => $items,
        'schemes'   => $schemes,
        'remainingTax'   => $remainingTax,
        'remainingTds'   => $remainingTds,
        'vendors'   => $vendors,
        'materials' => $materials,
        'worktypes' => $worktypes,
        'units'     => $units,
        'nextInvno' => $invoice->WorkorderNo,
        'existingImages' => $existingImages,
        'materialNameToTypes' => $materialNameToTypes
    ]);
    }



public function store(Request $request)
{
    // 1) Validation
    $request->validate([
        "Date"          => "required|date_format:d-m-Y",
        "purchasefrom"  => "required",
        "worktype"      => "required",
        "Destination"   => "required",

        "Pay_type"      => "required|in:cash,cheque,e-Payment",
        "account_no"    => "required",
        "amount_pay"    => "required|numeric|min:0",
        "payable" => [
            "required",
            "numeric",
            "min:0",
            function ($attribute, $value, $fail) use ($request) {
                $balance = floatval($request->balanceamt ?? 0);
                if ($value > $balance) {
                    $fail("Payable amount cannot be greater than available balance.");
                }
            }
        ],

        "tax"           => "nullable|numeric|min:0",
        "tds"           => "nullable|numeric|min:0",
        "bnk_charge"    => "nullable|numeric|min:0",

        "cheque_no"     => "required_if:Pay_type,cheque,e-Payment|nullable|string|max:150",
        "narration"     => "nullable|string|max:500",

        // arrays coming from rows
        "scope"         => "required|array",
        "scope.*"       => "required|string",   // can be numeric string (ID) or scope text
        "payamount"     => "required|array",
        "payamount.*"   => "nullable|numeric",
        "amt"           => "required|array",
        "amt.*"         => "nullable|numeric",
        "balance"       => "required|array",
        "balance.*"     => "nullable|numeric",

        "checkids"      => "required|string",
        "count"         => "required|integer|min:1",
    ]);

    // 2) Prepare basics
    $clientId = session()->get('selected_scheme_id');
    $userId   = Auth::id();
    $id       = uniqid();

    // Workorder ID and type
    $wtype = $request->worktype;
    $workorderId = $request->Pid; // ensure Pid hidden exists in form

    if (!$workorderId) {
        return back()->withErrors(["Pid" => "Workorder ID is missing."])->withInput();
    }

    // 3) Arrays from form
    $allScopesArr = $request->input('scope', []);        // ordered array of scope values (IDs or text)
    $amountsArr   = $request->input('amt', []);          // same indices as scope[]
    $payamountArr = $request->input('payamount', []);    // same indices as scope[]
    $balanceArr   = $request->input('balance', []);
    $selectedScopeVals = array_filter(array_map('trim', explode(',', $request->checkids)));

    // 4) Build main Scope string (comma separated scope text)
    $scopeStringParts = [];

    foreach ($selectedScopeVals as $selVal) {
        // find index in allScopesArr to use amounts etc.
        $index = array_search($selVal, $allScopesArr);
        // if not found by direct match, try numeric cast (e.g. "10" vs 10)
        if ($index === false) {
            $index = array_search((string) $selVal, $allScopesArr);
        }

        // Determine workorder_material ID and scope text
        $materialId = null;
        $scopeText  = null;

        if (is_numeric($selVal)) {
            // user submitted an ID directly
            $materialId = $selVal;
            $row = DB::table('workorder_material')
                ->where('ClientID', $clientId)
                ->where('ID', $materialId)
                ->first();
            if ($row) {
                $scopeText = $row->scope;
            }
        } else {
            // selVal is scope text -> try to lookup ID
            $row = DB::table('workorder_material')
                ->where('ClientID', $clientId)
                ->where('scope', $selVal)
                ->first();
            if ($row) {
                $materialId = $row->ID;
                $scopeText = $row->scope;
            } else {
                // as last fallback, treat selVal as text that user entered and leave materialId null
                $scopeText = $selVal;
            }
        }

        if ($scopeText) {
            $scopeStringParts[] = $scopeText;
        }
    }

    $scopeString = implode(',', $scopeStringParts);

    $bankcharge = $request->bnk_charge;

    if ($bankcharge === null || $bankcharge === '') {
        $bankcharge = 0;
    }
    
    // Get next receipt number
    $maxReceipt = DB::table('workorder_payment')
        ->max('receipt_no');
    $nextReceiptNo = $maxReceipt ? $maxReceipt + 1 : 1;

    // 5) Insert into workorder_payment
    DB::table("workorder_payment")->insert([
        "ID"            => $id,
        "ClientID"      => $clientId,
        "Created"       => now(),
        "Lastedited"    => now(),
        "Date"          => date("Y-m-d", strtotime($request->Date)),
        "WorkorderID"   => $workorderId,
        "WorkorderType" => $wtype,

        "amt_pay"       => $request->payable,
        "Payable"       => $request->amount_pay,

        "TaxAMt"        => $request->tax,
        "TDSAmt"        => $request->tds,
        "bankcharge"    =>  $bankcharge,

        "payment_method"=> $request->Pay_type,
        "conID"         => $request->purchasefrom,
        "cheque_no"     => $request->cheque_no,
        "account_no"    => $request->account_no,
        "narration"     => $request->narration,
        'receipt_no' => $nextReceiptNo, // 👈 added

        "Scope"         => $scopeString,
        "paydetail"     => $request->narration ?? '',

        "userID"        => $userId
    ]);

    // 6) Insert scope_payment_detail for each selected scope (use index to match amt[])
    foreach ($selectedScopeVals as $selVal) {
        // find index
        $index = array_search($selVal, $allScopesArr);
        if ($index === false) {
            $index = array_search((string)$selVal, $allScopesArr);
        }
        if ($index === false) {
            // can't find matching row in posted arrays; skip safely
            continue;
        }

        // determine material ID (if possible)
        $materialId = null;
        if (is_numeric($selVal)) {
            $materialId = $selVal;
        } else {
            $row = DB::table('workorder_material')
                ->where('ClientID', $clientId)
                ->where('scope', $selVal)
                ->first();
            $materialId = $row->ID ?? null;
        }

        // amount for this selected row
        $amt_for_row = $amountsArr[$index] ?? 0;

        DB::table("scope_payment_detail")->insert([
            "ID"          => uniqid(),
            "ClientID"    => $clientId,
            "Created"     => now(),
            "Lastedited"  => now(),
            "WorkorderID" => $workorderId,
            "Payment_ID"  => $id,
            "Scope"       => $selVal, // if no ID, store whatever user provided
            "amt_pay"     => $amt_for_row,
            "userID"      => $userId
        ]);
    }

    // 7) Redirect
    return redirect()
        ->route("Site_work_pay")
        ->with("success", "Payment has been added successfully!");
}

public function edit($id)
{
    $clientId = session('selected_scheme_id');

    // Load payment header
    $payment = Workorder_payment::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

    // Load scope_payment_detail for this payment
    $scopeDetails = Scope_payment_detail::where('ClientID', $clientId)
        ->where('Payment_ID', $id)
        ->get()
        ->keyBy('Scope');   // key by scope value (scope text or ID)

    // Load workorder details
    $invoice = Workorder_details::where('ClientID', $clientId)
        ->where('ID', $payment->WorkorderID)
        ->firstOrFail();

    // Load all scopes of this work order
    $items = Workorder_material::where('ClientID', $clientId)
        ->where('workorderID', $payment->WorkorderID)
        ->get();

    // ---- BUILD MERGED ARRAY FOR BLADE ----
    $rows = [];

    foreach ($items as $item) {

        $scopeKey = $item->ID;

        // amount paid in THIS payment
        $currentPaid = $scopeDetails->get($scopeKey)->amt_pay ?? 0;

        // amount paid in OTHER payments
        $alreadyPaid = DB::table('scope_payment_detail')
            ->where('ClientID', $clientId)
            ->where('workorderID', $payment->WorkorderID)
            ->where('Scope', $scopeKey)
           ->where('Payment_ID', '!=', $payment->ID) // 🔑 exclude current
            ->sum('amt_pay');

        // remaining payable
        $remaining = $item->Amount - $alreadyPaid;
        if ($remaining < 0) $remaining = 0;

        $rows[] = [
            'scope'      => $scopeKey,
            'scope_text' => $item->scope,

            // ✅ FIXED VALUES
            'payamount'  => $remaining,
            'amt'        => $currentPaid,
            'balance'    => $remaining - $currentPaid,

            'checked'    => $currentPaid > 0,
        ];
    }


    // dropdowns
    $schemes = SchemeDetail::where('ID', $clientId)->get();
    $vendors = Supplier_contractor::where('Type','CONTRACTOR')->where('ClientID',$clientId)->get();
    $worktypes = DB::table('workorder_detail')->select('worktype')->groupBy('worktype')->get();
    $materials = Material::select('Name')->groupBy('Name')->orderBy('Name')->get();
    $units = DB::table('workorder_material')->select('Unit')->groupBy('Unit')->get();


    $usedTax = DB::table('workorder_payment')
    ->where('ClientID', $clientId)
    ->where('WorkorderID', $invoice->ID)
    ->where('ID', '!=', $payment->ID)   // 🔑 exclude current payment
    ->sum('TaxAmt');

$usedTds = DB::table('workorder_payment')
    ->where('ClientID', $clientId)
    ->where('WorkorderID', $invoice->ID)
    ->where('ID', '!=', $payment->ID)   // 🔑 exclude current payment
    ->sum('TDSAmt');

$remainingTax = max(0, ($invoice->TaxAmt ?? 0) - $usedTax);
$remainingTds = max(0, ($invoice->TDSAmt ?? 0) - $usedTds);

    return view('backend.Site_work_pay.edit', [
        'payment'       => $payment,
        'invoice'       => $invoice,
        'rows'          => $rows,     // THIS GOES TO BLADE
        'schemes'       => $schemes,
        'vendors'       => $vendors,
        'materials'     => $materials,
        'worktypes'     => $worktypes,
        'units'         => $units,

        'remainingTax'  => $remainingTax,
        'remainingTds'  => $remainingTds
    ]);
}

public function update(Request $request, $id)
{
    // 1) Validation (same rules as store)
    $request->validate([
        "Date"          => "required|date_format:d-m-Y",
        "purchasefrom"  => "required",
        "worktype"      => "required",
        "Destination"   => "required",

        "Pay_type"      => "required|in:cash,cheque,e-Payment",
        "account_no"    => "required",
        "amount_pay"    => "required|numeric|min:0",

        "payable" => [
            "required",
            "numeric",
            "min:0",
            function ($attribute, $value, $fail) use ($request) {
                $balance = floatval($request->balanceamt ?? 0);
                if ($value > $balance) {
                    $fail("Payable amount cannot be greater than available balance.");
                }
            }
        ],

        "tax"           => "nullable|numeric|min:0",
        "tds"           => "nullable|numeric|min:0",
        "bnk_charge"    => "nullable|numeric|min:0",
        "cheque_no"     => "required_if:Pay_type,cheque,e-Payment|nullable|string|max:150",
        "narration"     => "nullable|string|max:500",

        "scope"         => "required|array",
        "scope.*"       => "required|string",
        "payamount"     => "required|array",
        "payamount.*"   => "nullable|numeric",
        "amt"           => "required|array",
        "amt.*"         => "nullable|numeric",
        "balance"       => "required|array",
        "balance.*"     => "nullable|numeric",

        "checkids"      => "required|string",
        "count"         => "required|integer|min:1",
    ]);

    // 2) Base data
    $clientId = session()->get('selected_scheme_id');
    $userId   = Auth::id();
    $paymentId = $id;

    // Load existing payment
    $payment = Workorder_payment::where('ClientID', $clientId)
        ->where('ID', $paymentId)
        ->firstOrFail();

    $workorderId = $payment->WorkorderID;

    // 3) Arrays from form
    $allScopesArr  = $request->scope;
    $amountsArr    = $request->amt;
    $payamountArr  = $request->payamount;
    $balanceArr    = $request->balance;

    $selectedScopeVals = array_filter(array_map('trim', explode(',', $request->checkids)));

    // 4) Build Scope string from selected scopes
    $scopeStringParts = [];

    foreach ($selectedScopeVals as $selVal) {

        // get scope text from DB
        $row = DB::table('workorder_material')
            ->where('ClientID', $clientId)
            ->where(function ($q) use ($selVal) {
                $q->where('ID', $selVal)
                  ->orWhere('scope', $selVal);
            })
            ->first();

        $scopeText = $row->ID ?? $selVal;

        $scopeStringParts[] = $scopeText;
    }

    $scopeString = implode(',', $scopeStringParts);

    DB::table('workorder_payment')
    ->where('ClientID', $clientId)
    ->where('ID', $paymentId)
    ->delete();

    $newPaymentId = uniqid();

    $bankcharge = $request->bnk_charge;

    if ($bankcharge === null || $bankcharge === '') {
        $bankcharge = 0;
    }
    // 5) UPDATE workorder_payment
    DB::table('workorder_payment')->insert([
        "ID"            => $newPaymentId,
        "ClientID"      => $clientId,
        "Created"       => now(),
        "Lastedited"    => now(),

        "Date"          => date("Y-m-d", strtotime($request->Date)),
        "WorkorderID"   => $workorderId,
        "WorkorderType" => $request->worktype,

        "amt_pay"       => $request->payable,
        "Payable"       => $request->amount_pay,

        "TaxAMt"        => $request->tax,
        "TDSAmt"        => $request->tds,
        "bankcharge"    => $bankcharge,

        "payment_method"=> $request->Pay_type,
        "conID"         => $request->purchasefrom,
        "cheque_no"     => $request->cheque_no,
        "account_no"    => $request->account_no,
        "narration"     => $request->narration,

        "Scope"         => $scopeString,
        "paydetail"     => $request->narration ?? '',

        "userID"        => $userId,
    ]);

    // 6) REMOVE old scope payments
    DB::table('scope_payment_detail')
        ->where('ClientID', $clientId)
        ->where('Payment_ID', $paymentId)
        ->delete();

    // 7) INSERT updated scope_payment_detail
    foreach ($selectedScopeVals as $selVal) {

        $index = array_search($selVal, $allScopesArr);
        if ($index === false) $index = array_search((string)$selVal, $allScopesArr);
        if ($index === false) continue;

        DB::table("scope_payment_detail")->insert([
            "ID"          => uniqid(),
            "ClientID"    => $clientId,
            "Created"     => now(),
            "Lastedited"  => now(),
            "WorkorderID" => $workorderId,
            "Payment_ID"  => $newPaymentId,
            "Scope"       => $selVal,
            "amt_pay"     => $amountsArr[$index] ?? 0,
            "userID"      => $userId
        ]);
    }

    // 8) Done
    return redirect()
        ->route("Site_work_pay")
        ->with("success", "Payment updated successfully!");
}


public function destroy($id)
{
    $clientId = session('selected_scheme_id');

    DB::beginTransaction();

    try {

        // 1) Fetch payment (safety check)
        $payment = Workorder_payment::where('ClientID', $clientId)
            ->where('ID', $id)
            ->firstOrFail();

        // 2) Delete scope payment details
        DB::table('scope_payment_detail')
            ->where('ClientID', $clientId)
            ->where('Payment_ID', $id)
            ->delete();

        // 3) Delete payment
        $payment->delete();

        DB::commit();

        return redirect()
            ->route('Site_work_pay')
            ->with('success', 'Payment deleted successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->route('Site_work_pay')
            ->with('error', 'Failed to delete payment.');
    }
}

}