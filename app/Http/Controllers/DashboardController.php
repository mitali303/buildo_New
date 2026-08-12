<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Backend\Labour_Work;
use App\Models\Backend\Transfer_material;
use App\Models\Backend\Material_Consumption;
use App\Models\Backend\DailyWorkEntry;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $clientId = session('selected_scheme_id');

        // Default Date Logic (Like Old PHP)
        $fromDate = $request->from_date 
            ? Carbon::parse($request->from_date)->format('Y-m-d')
            : Carbon::now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        $dateFilter = function ($query, $column = 'Created') use ($fromDate, $toDate) {
            $query->whereDate($column, '>=', $fromDate)
                ->whereDate($column, '<=', $toDate);
        };

        // Common reconciliation condition
        $recon = function ($query) {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('payment_method', 'cheque')
                        ->where('reconciliation', '1');
                })->orWhere('payment_method', '!=', 'cheque');
            });
        };

        /*
        |--------------------------------------------------------------------------
        | TOTAL INCOME (Same as your UNION query)
        |--------------------------------------------------------------------------
        */

        $totreceive = 0;

        $totreceive += DB::table('loan')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Received')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        $totreceive += DB::table('partners_loan')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Received')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        $totreceive += DB::table('rent_property_payment')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Received')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        $totreceive += DB::table('project_payment')
            ->where('ClientID', $clientId)
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        $totreceive += DB::table('booking_payment')
            ->where('ClientID', $clientId)
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        $totreceive += DB::table('income_payment')
            ->where('ClientID', $clientId)
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        $totreceive += DB::table('owner_payment')
            ->where('ClientID', $clientId)
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');


        /*
        |--------------------------------------------------------------------------
        | TOTAL EXPENSE (Same as your UNION query)
        |--------------------------------------------------------------------------
        */

        $totPaid = 0;

        $tables = [
            'inv_payment',
            'booking_cancel'
        ];

        foreach ($tables as $table) {
            $totPaid += DB::table($table)
                ->where('ClientID', $clientId)
                ->where($recon)
                ->where($dateFilter)
                ->sum('amt_pay');
        }

        $totPaid += DB::table('daily_trans')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $totPaid += DB::table('workorder_payment')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $totPaid += DB::table('stampotherexpenses')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $totPaid += DB::table('customer_refund')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $totPaid += DB::table('project_payment_bill')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $totPaid += DB::table('site_expences')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');
                
        $totPaid += DB::table('land')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        // Loan Paid
        $totPaid += DB::table('loan')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Paid')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        // Partners Loan Paid
        $totPaid += DB::table('partners_loan')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Paid')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        // Rent Paid
        $totPaid += DB::table('rent_property_payment')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Paid')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        // Deduction
        $totPaid += DB::table('project_payment_bill')
            ->where('ClientID', $clientId)
            ->where('paydetail', 'Deduction')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        /*
        |--------------------------------------------------------------------------
        | SCHEME DATA
        |--------------------------------------------------------------------------
        */

        $scheme = DB::table('scheme_step1')
            ->where('ID', $clientId)
            ->first();

        $builtupArea = $scheme->BuiltupArea ?? 0;
        $projectArea = $scheme->Area ?? 0;

        /*
        |--------------------------------------------------------------------------
        | BOOKING AREA LOGIC (Exact Same As Old)
        |--------------------------------------------------------------------------
        */

        // Step 1: Get Cancelled Flat IDs
            $cancelFlatIds = DB::table('booking_cancel')
                ->where('SchemID', $clientId)
                ->pluck('flatID')
                ->toArray();


            // Step 2: Get Booked Flats (Exclude Cancelled)
            $bookingQuery = DB::table('booking_customer')
                ->where('Scheme', $clientId);

            $bookingQuery->whereDate('Created', '>=', $fromDate)
                ->whereDate('Created', '<=', $toDate);

            if (!empty($cancelFlatIds)) {
                $bookingQuery->whereNotIn('FlatNo', $cancelFlatIds);
            }


            // Step 3: Get All Flat Numbers (same as $bookidall)
            $bookedFlatIdsAll = $bookingQuery
                ->pluck('FlatNo')
                ->toArray();


            // Step 4: Get Sold Area
            $flatssold_Area = 0;

            if (!empty($bookedFlatIdsAll)) {
                $flatssold_Area = DB::table('flats_details')
                    ->where('scheme_ID', $clientId)
                    ->whereIn('ID', $bookedFlatIdsAll)
                    ->sum('Area');
            }


            // Step 5: Balance Area
            $balanceArea = $projectArea - $flatssold_Area;

    
            /*
            |--------------------------------------------------------------------------
            | ACCOUNTS LIST (For Balance Dropdown)
            |--------------------------------------------------------------------------
            */

            $accounts = Bank_Acc::where('ACNo', '!=', 'Cash In Hand')
                ->where('ClientID', $clientId)
                ->orderBy('Name')
                ->get();

            // Optional: Get Cash In Hand account
            $cashAccount = Bank_Acc::where('ACNo', 'Cash In Hand')
                ->where('ClientID', $clientId)
                ->first();

            /*
        |--------------------------------------------------------------------------
        | Construction Expense
        |--------------------------------------------------------------------------
        */

    $constexp = 0;

        $tables = [
            'inv_payment',
            'tds_payment',
            'booking_cancel'
        ];

        foreach ($tables as $table) {
            $constexp += DB::table($table)
                ->where('ClientID', $clientId)
                ->where($recon)
                ->where($dateFilter)
                ->sum('amt_pay');
        }

        $constexp += DB::table('daily_trans')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $constexp += DB::table('workorder_payment')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $constexp += DB::table('project_payment_bill')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $constexp += DB::table('stampotherexpenses')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        $constexp += DB::table('site_expences')
                ->where('ClientID', $clientId)
                ->where($recon)
                ->whereDate('Date', '>=', $fromDate)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

        // Loan Paid
        $constexp += DB::table('loan')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Paid')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        // Rent Paid
        $constexp += DB::table('rent_property_payment')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Paid')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        // Deduction
        $constexp += DB::table('project_payment_bill')
            ->where('ClientID', $clientId)
            ->where('paydetail', 'Deduction')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');


            /*
        |--------------------------------------------------------------------------
        | COST PER SQFT
        |--------------------------------------------------------------------------
        */

        $costPerSqft = $builtupArea > 0
            ? round($constexp / $builtupArea, 2)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | OWNER PAYMENT (No Site / No Type Filter)
        |--------------------------------------------------------------------------
        */

        // Total Owner Payment (owner table uses Created)
        $owner_total = DB::table('owner')
            ->where('ClientID', $clientId)
            ->whereDate('Created', '>=', $fromDate)
            ->whereDate('Created', '<=', $toDate)
            ->sum('amt');

        // Owner Received Amount (owner_payment uses Date)
        $owner_received = DB::table('owner_payment')
            ->where('ClientID', $clientId)
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        // Pending
        $owner_pending = $owner_total - $owner_received;


        /*
        |--------------------------------------------------------------------------
        | MATERIAL PAYMENT (No Site / No Type Filter)
        |--------------------------------------------------------------------------
        */

        // Total Material Bill (inv_detail uses Created)
        // $material_total = DB::table('inv_detail')
        //     ->where('ClientID', $clientId)
        //     ->whereDate('Created', '>=', $fromDate)
        //     ->whereDate('Created', '<=', $toDate)
        //     ->sum('gtotal');
            $material_total = DB::table('inv_detail')
                ->where('ClientID', $clientId)
                ->whereDate('Created', '>=', $fromDate)
                ->whereDate('Created', '<=', $toDate)
                ->sum('GTotal');
        // Material Paid (inv_payment uses Created)
        // $material_received = DB::table('inv_payment')
        //     ->where('ClientID', $clientId)
        //     ->where($recon)
        //     ->whereDate('Created', '>=', $fromDate)
        //     ->whereDate('Created', '<=', $toDate)
        //     ->sum('amt_pay');
            $material_received = DB::table('inv_payment_detail')
                ->join('inv_payment', 'inv_payment.ID', '=', 'inv_payment_detail.Payment_ID')
                ->where('inv_payment.ClientID', $clientId)
                ->whereDate('inv_payment.Created', '>=', $fromDate)
                ->whereDate('inv_payment.Created', '<=', $toDate)
                ->sum('inv_payment_detail.amt_pay');
        // Pending
        // $material_pending = $material_total - $material_received;
            $material_pending = $material_total - $material_received;

            if ($material_pending < 0) {
                $material_pending = 0;
            }
        /*
        |--------------------------------------------------------------------------
        | SITE EXPENSES (No Site / No Type Filter)
        |--------------------------------------------------------------------------
        */

        // Investment Or Loan (loan paid)
        $inv_exp = DB::table('loan')
            ->where('ClientID', $clientId)
            ->where('paytype', 'Paid')
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        // Office Expense (site_expences table)
        $off_exp = DB::table('site_expences')
            ->where('ClientID', $clientId)
            ->where($recon)
            ->whereDate('Date', '>=', $fromDate)
            ->whereDate('Date', '<=', $toDate)
            ->sum('amt_pay');

        /*
        |--------------------------------------------------------------------------
        | LABOUR & CONTRACTOR
        |--------------------------------------------------------------------------
        */

        // Total Workorder Material (NO date filter in old PHP)
        $lbr_total = DB::table('workorder_material')
            ->where('ClientID', $clientId)
            ->sum('Amount');

        // Paid Amount (uses Created column)
        $lbr_paid = DB::table('workorder_payment')
            ->where('ClientID', $clientId)
            ->whereDate('Created', '>=', $fromDate)
            ->whereDate('Created', '<=', $toDate)
            ->sum('amt_pay');

        // Pending
        $lbr_pending = $lbr_total - $lbr_paid;

        // Labour Work Count
        $labourWorkCount = Labour_Work::where('schemeID', $clientId)->count();

        //purchase invoice
        $materialInwardCount = DB::table('inv_detail')
        ->where('ClientID', $clientId)
        ->count();

            //material transfer
        $materialTransferCount = Transfer_material::where('from_site', $clientId)
        ->orWhere('To_site', $clientId)
        ->count();
        //materieal consumption
        $materialConsumptionCount = Material_Consumption::where('ClientID', $clientId)->count();

        //DailyWorkEntry
       $dailyWorkCount = DailyWorkEntry::count();

        return view('backend.dashboard', compact(
            'totreceive',
            'totPaid',
            'constexp',
            'builtupArea',
            'projectArea',
            'flatssold_Area',
            'balanceArea',
            'costPerSqft',
            'accounts',
            'cashAccount',
            'lbr_total',
            'lbr_paid',
            'lbr_pending',
            'owner_total',
            'owner_received',
            'owner_pending',
            'material_total',
            'material_received',
            'material_pending',
            'inv_exp',
            'off_exp',
            'fromDate',
            'toDate',
            'labourWorkCount',
            'materialInwardCount',
            'materialTransferCount',
            'materialConsumptionCount',
            'dailyWorkCount'
        ));
    }
}