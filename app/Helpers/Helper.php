<?php
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Backend\Role;
use App\Models\Backend\CompanySetting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Backend\ActivityLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;


// Activity Logs store
if (!function_exists('activity_log')) {
    function activity_log($action, $description = null, $model = null)
    {
        try {

            $ip = Request::ip() === '::1' ? '127.0.0.1' : Request::ip();

            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => $action,
                'description' => $description,
                'model_type'  => is_object($model) ? class_basename($model) : null,
                'model_id'    => is_object($model) ? $model->id : null,
                'ip_address'  => $ip,
                'created_by'  => Auth::id(),
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to log activity: " . $e->getMessage());
        }
    }
}
// if(!function_exists('hasPermission')){
//     function hasPermission($permission=null){
//         $user_id = Auth::id();
//         $userdata = User::where('id',$user_id)->first();
//         $roleid = $userdata->role_id;
//         $rolesdata = Role::find($roleid);
//         $permissinsarr = $rolesdata->permissions;

//         // if(in_array($permission,Auth::user()->permissions)){
//         if(in_array($permission,$permissinsarr)){
//             return true;
//         }
//         return false;
//     }
// }
if(!function_exists('hasPermission')){
    function hasPermission($permission = null){
        $user_id = Auth::id();
        $userdata = User::where('ID', $user_id)->first();
        $roleid = $userdata->Role;
        $rolesdata = Role::find($roleid);

        // Convert permissions to array
        $permissinsarr = json_decode($rolesdata->permissions, true); // or explode(',', ...) if it's a CSV

        if(is_array($permissinsarr) && in_array($permission, $permissinsarr)){
            return true;
        }
        return false;
    }
}

//Company Settings
if (!function_exists('company_setting')) {
    function company_setting($key = null)
    {
        $setting = CompanySetting::first();

        if (!$setting) {
            return null;
        }

        // Return full object if no key passed
        if (is_null($key)) {
            return $setting;
        }

        // Return specific field
        return $setting->$key ?? null;
    }
}

function getBalanceTransferRefund($matid, $payid, $date)
{
  $_REQUEST['Matid'] = $matid;
  //echo $matid;
   // $_REQUEST['paymentId'] = $paymentId;
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
     //   echo "hii";

        $mname_record = DB::table('accounts')
            ->where('ClientID', $clientId)
            ->sum('OBalance');

        $condition = "((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";

        $cond1 = "1";
        $cond2 = "1";

    } elseif ($_REQUEST['Matid'] != 'All') {
       // echo "bye";

        $condition = "account_no='" . $_REQUEST['Matid'] . "' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";
        $cond1     = "account_from='" . $_REQUEST['Matid'] . "' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";
        $cond2     = "account_to='" . $_REQUEST['Matid'] . "' AND ((payment_method='cheque' AND reconciliation='1') OR (payment_method!='cheque'))";

    } else {

     //   echo "hello";

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
    $getExp_LandExp   = DB::table('land')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' AND (PaymentId IS NULL OR PaymentId!='$pid') $condition5")->sum('amt_pay');
    $getIncome_Expense = DB::table('booking_cancel')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getExp_Stamp     = DB::table('stampotherexpenses')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' AND (PaymentId IS NULL OR PaymentId!='$pid') $condition5")->sum('amt_pay');
    $getExp_CustRefund = DB::table('customer_refund')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');

    $getExp_lbrpay = DB::table('labour_payment')
    ->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")
    ->sum('amt_pay');

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
    ->sum('net_salary');

    // Income
    $getIncome_loan = DB::table('loan')->whereRaw("$condition AND ClientID='$cid' AND paytype='Received' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getIncome_loanPay = DB::table('loan_payment')->whereRaw("$condition AND ClientID='$cid' AND paytype='Received' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getIncome_partners = DB::table('partners_loan')->whereRaw("$condition AND ClientID='$cid' AND paytype='Received' AND ID!='$pid' $condition5")->sum('amt_pay');
    $getIncome_Project = DB::table('project_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('Payable');
    $getIncome_booking = DB::table('booking_payment')->whereRaw("$condition AND ClientID='$cid' AND amt_pay>0 AND ID!='$pid' $condition5")->sum('amt_pay');
    $getIncome_payment = DB::table('income_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');

    $getOwner_payment = DB::table('owner_payment')->whereRaw("$condition AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');

    $acto = DB::table('account_transfer')->whereRaw("$cond2 AND ClientID='$cid' AND ID!='$pid' $condition5")->sum('amt_pay');

    //payroll income
   $emp_adv_pay = DB::table('employee_advance_payments')
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
    ->sum('advance');

    /** -------------------------
     *  FINAL CALCULATION
     *  ------------------------- */
    $oldPayment = DB::table('workorder_payment')
    ->where('ID', $pid)
    ->where('ClientID', $clientId)
    ->first();

    $oldAmt = $oldPayment->amt_pay ?? 0;


    $incometotal = $getIncome_loan + $getIncome_loanPay + $getIncome_partners +
                   $getIncome_Project + $getIncome_booking + $getIncome_payment + $acto + $getOwner_payment
                   + $emp_adv_pay;

    $exptotal = $getExp_WorkOrder + $getExp_invoice + $getExp_partners +
                $getExp_loan + $getExp_loanPayment + $getExp_salary +
                $getExp_Expences + $getExp_SiteExp + $getExp_LandExp +
                $acfrom + $getIncome_Expense + $getExp_Stamp + $getExp_CustRefund + $getExp_lbrpay
                + $emp_adv + $emp_salary;
               

    $totalbalance = ($mname_record + $incometotal) - $exptotal;

    if ($_REQUEST['PayID'] != '0000') {
        $totalbalance += $oldAmt;
    }
 
    return round($totalbalance, 2);
}
class CustomHelper
{
public static function numberToWords($number)

{
    // Handle negative numbers (if needed)
    if ($number < 0) {
        return 'Minus ' . self::convertNumberToWords(abs($number));
    }

    // Handle zero
    if ($number == 0) {
        return 'Zero';
    }

    return self::convertNumberToWords($number);
}

private static function convertNumberToWords($number)
{
    // Words for numbers
    $words = [
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven',
        12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen',
        17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    ];

    // Handle numbers less than 20
    if ($number < 20) {
        return $words[$number];
    }

    // Handle numbers less than 100
    if ($number < 100) {
        $tens = floor($number / 10) * 10;
        $ones = $number % 10;
        return $words[$tens] . ($ones ? ' ' . $words[$ones] : '');
    }

    // Handle numbers less than 1000
    if ($number < 1000) {
        $hundreds = floor($number / 100);
        $remainder = $number % 100;
        return $words[$hundreds] . ' Hundred' . ($remainder ? ' and ' . self::convertNumberToWords($remainder) : '');
    }

    // Handle numbers in the thousands range
    if ($number < 100000) {
        $thousands = floor($number / 1000);
        $remainder = $number % 1000;
        return self::convertNumberToWords($thousands) . ' Thousand' . ($remainder ? ' ' . self::convertNumberToWords($remainder) : '');
    }

    // Handle numbers in the Lakh range
    if ($number < 10000000) {
        $lakhs = floor($number / 100000);
        $remainder = $number % 100000;
        return self::convertNumberToWords($lakhs) . ' Lakh' . ($remainder ? ' ' . self::convertNumberToWords($remainder) : '');
    }

    // Handle numbers in the Crore range
    if ($number < 1000000000) {
        $crores = floor($number / 10000000);
        $remainder = $number % 10000000;
        return self::convertNumberToWords($crores) . ' Crore' . ($remainder ? ' ' . self::convertNumberToWords($remainder) : '');
    }

    // If the number is larger than a Crore, we can keep extending this logic for more units (like Ten Crores, etc.)
    return $number . ' Rupees Only'; // As a fallback for larger numbers
}
}


if (!function_exists('canDeleteRecord')) {

    /**
     * Check if a record is used in another table
     *
     * @param string $table   Table name (e.g. labour_works)
     * @param string $column  Column name (e.g. Agency_ID)
     * @param mixed  $value   ID to check
     * @return bool           true = safe to delete
     */
    function canDeleteRecord($table, $column, $value)
    {
        // dd($value." ".$column.' '.$table);

        return !DB::table($table)
            ->where($column, $value)
            ->exists();
    }
}


if (!function_exists('checkRecordUsage')) {

    /**
     * Check multiple tables at once
     *
     * @param array $checks
     * @return bool true = used somewhere (BLOCK DELETE)
     */
    function checkRecordUsage(array $checks)
    {
        foreach ($checks as $check) {
            if (DB::table($check['table'])
                ->where($check['column'], $check['value'])
                ->exists()) {
                return true;
            }
        }
        return false;
    }
}