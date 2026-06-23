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
use App\Models\Backend\Inv_Payment;
use App\Models\Backend\Workorder_details;
use App\Models\Backend\Reports;
use App\Models\Backend\Workorder_material;
use App\Models\Backend\Income_Payment;
use App\Models\Backend\Workorder_payment;
use App\Models\Backend\Consumption_Details;
use App\Models\Backend\Scope_payment_detail;
use App\Models\Backend\Transfer_detail;
use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Backend\CustomerRefund;
use App\Models\Backend\StampOtherExpenses;
use App\Models\Backend\Loan;
use App\Models\Backend\LandExpenses;
use App\Models\Backend\CustomerPayment;
use App\Models\Backend\Booking_Customer;
use App\Models\Backend\Labour_Work;


class Report_Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function site_lbr_pay(Request $request)
{
    $clientId = session('selected_scheme_id');

    $query = Workorder_details::with(['Contractor', 'Scheme'])
        ->where('ClientID', $clientId)
        ->orderBy('Date', 'DESC');

    // 🔹 Date filter
    if ($request->fdate && $request->tdate) {
        $from = Carbon::createFromFormat('d-m-Y', $request->fdate)->format('Y-m-d');
        $to   = Carbon::createFromFormat('d-m-Y', $request->tdate)->format('Y-m-d');
        $query->whereBetween('Date', [$from, $to]);
    }

    /**
     * =========================
     * 🔹 EXPORT TO EXCEL (CSV)
     * =========================
     */
    if ($request->type === 'excel') {

        $data = $query->get();
        $filename = "Site_Work_Order_" . now()->format('d-m-Y') . ".csv";

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date', 'Contractor', 'Scheme',
                'Total', 'TDS', 'Retention', 'Payable Total'
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    Carbon::parse($row->Date)->format('d-m-Y'),
                    optional($row->Contractor)->Name ?? '-',
                    optional($row->Scheme)->Name ?? '-',
                    $row->Total,
                    $row->TDSAmt,
                    $row->retain_amt,
                    $row->gtotal,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * =========================
     * 🔹 PRINT
     * =========================
     */
    if ($request->type === 'print') {

        $records = $query->get();

        return response()->view('backend.Reports.site_lbr_pay', compact('records'))
            ->withHeaders(['X-Print' => 'true']);
    }

    /**
     * =========================
     * 🔹 DATATABLE AJAX
     * =========================
     */
    if ($request->ajax()) {

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('Date', fn($r) => Carbon::parse($r->Date)->format('d-m-Y'))
            ->editColumn('ContractorID', fn($r) => optional($r->Contractor)->Name ?? '-')
            ->editColumn('SiteLocation', fn($r) => optional($r->Scheme)->Name ?? '-')
              // 🔍 Enable search on Contractor name
            ->filterColumn('ContractorID', function ($query, $keyword) {
                $query->whereHas('Contractor', function ($q) use ($keyword) {
                    $q->where('Name', 'like', "%{$keyword}%");
                });
            })
            ->make(true);
    }

    /**
     * =========================
     * 🔹 NORMAL PAGE LOAD
     * =========================
     */
    return view('backend.Reports.site_lbr_pay');
}


public function material_pay(Request $request)
{
    $clientId = session('selected_scheme_id');

    // 🔹 Subquery
    $invoiceTotalSub = DB::table('inv_detail')
        ->select('purchasefrom', 'ClientID', DB::raw('SUM(GTotal) as total_invoice'))
        ->where('ClientID', $clientId)
        ->groupBy('purchasefrom', 'ClientID');

    $query = Inv_Payment::query()
        ->when($request->from_date, function ($q) use ($request) {
            $q->whereDate('inv_payment.payment_date', '>=', $request->from_date);
        })
        ->when($request->to_date, function ($q) use ($request) {
            $q->whereDate('inv_payment.payment_date', '<=', $request->to_date);
        })
        ->select([
            'inv_payment.PurchaseFrom',
            'inv_payment.schemeID',
            DB::raw('MAX(inv_payment.payment_date) as payment_date'),
            DB::raw('SUM(inv_payment_detail.amt_pay) as total_paid'),
            DB::raw('COALESCE(inv_tot.total_invoice, 0) as total_invoice'),
        ])
        ->leftJoin('inv_payment_detail', 'inv_payment_detail.Payment_ID', '=', 'inv_payment.ID')
        ->leftJoinSub($invoiceTotalSub, 'inv_tot', function ($join) {
            $join->on('inv_tot.purchasefrom', '=', 'inv_payment.PurchaseFrom')
                 ->on('inv_tot.ClientID', '=', 'inv_payment.ClientID');
        })
        ->where('inv_payment.ClientID', $clientId)
        ->groupBy('inv_payment.PurchaseFrom', 'inv_payment.schemeID', 'inv_tot.total_invoice')
        ->with(['vendor', 'scheme']);

    /**
     * =========================
     * 🔹 EXPORT TO EXCEL
     * =========================
     */
    if ($request->type === 'excel') {

        $data = $query->get();
        $filename = "Material_Payment_Report_" . now()->format('d-m-Y') . ".csv";

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date', 'Vendor', 'Scheme', 'Invoice Total', 'Amount Paid'
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    Carbon::parse($row->payment_date)->format('d-m-Y'),
                    optional($row->vendor)->Name ?? '-',
                    optional($row->scheme)->Name ?? '-',
                    $row->total_invoice,
                    $row->total_paid,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * =========================
     * 🔹 DATATABLE AJAX
     * =========================
     */
    if ($request->ajax()) {

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('Date', fn ($r) => Carbon::parse($r->payment_date)->format('d-m-Y'))
            ->addColumn('PurchaseFrom', fn ($r) => optional($r->vendor)->Name ?? '-')
            ->addColumn('scheme', fn ($r) => optional($r->scheme)->Name ?? '-')
            ->addColumn('invoice_total', fn ($r) => number_format($r->total_invoice, 2))
            ->addColumn('amt_pay', fn ($r) => number_format($r->total_paid, 2))
           
            ->filterColumn('PurchaseFrom', function ($query, $keyword) {
                $query->whereHas('vendor', function ($q) use ($keyword) {
                    $q->where('Name', 'like', "%{$keyword}%");
                });
            })
            ->make(true);
    }

    return view('backend.Reports.Material_pay');
}

private function resolvePtype(string $type): int
    {
        return $type === 'investor' ? 1 : 0;
    }
public function partner_pay(Request $request, string $type)
{
    $clientId = session('selected_scheme_id');
    $ptype    = $this->resolvePtype($type);

    $query = DB::table('partners_loan as pl')
        ->leftJoin('partners as p', 'p.ID', '=', 'pl.partners')
        ->where('pl.ClientID', $clientId)
        ->where('pl.Ptype', $ptype);

    // 🔹 Date filter
    if (!empty($request->from_date) && !empty($request->to_date)) {
        $query->whereBetween(DB::raw('DATE(pl.Date)'), [
            $request->from_date,
            $request->to_date
        ]);
    }

    $query->groupBy('pl.partners', 'p.Name')
        ->select(
            'pl.partners',
            'p.Name as partner_name',
            DB::raw("SUM(CASE WHEN pl.paytype = 'Received' THEN pl.amt_pay ELSE 0 END) as credit"),
            DB::raw("SUM(CASE WHEN pl.paytype = 'Paid' THEN pl.amt_pay ELSE 0 END) as debit"),
            DB::raw('MAX(pl.Date) as last_payment_date')
        );
        

    /**
     * =========================
     * 📊 EXPORT TO EXCEL
     * =========================
     */
    if ($request->export === 'excel') {

        $data = $query->get();
        $filename = ucfirst($type) . "_Payment_Report_" . now()->format('d-m-Y') . ".csv";

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Partner', 'Credit', 'Debit', 'Balance', 'Last Payment Date'
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->partner_name,
                    $row->credit,
                    $row->debit,
                    $row->credit - $row->debit,
                    Carbon::parse($row->last_payment_date)->format('d-m-Y'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * =========================
     * 🔹 DATATABLE AJAX
     * =========================
     */
    if ($request->ajax()) {

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('partner', fn ($r) => $r->partner_name)
            ->addColumn('credit', fn ($r) => number_format($r->credit, 2))
            ->addColumn('debit', fn ($r) => number_format($r->debit, 2))
            ->addColumn('balance', fn ($r) => number_format($r->credit - $r->debit, 2))
            ->addColumn('last_payment_date', fn ($r) =>
                Carbon::parse($r->last_payment_date)->format('d-m-Y')
            )

            ->filterColumn('partner', function ($query, $keyword) {
                $query->where('p.Name', 'like', "%{$keyword}%");
            })
            ->make(true);
    }

    return view('backend.Reports.partners_pay', ['type' => $type]);
}

    public function material_cunsum(Request $request)
    {
        $clientId = session('selected_scheme_id');

        $query = Consumption_Details::with([
            'Scheme',
            'materials.materialDetail'
        ])
            ->where('ClientID', $clientId);

        // ✅ DATE FILTER
        if (!empty($request->from_date) && !empty($request->to_date)) {
            $query->whereBetween('Date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        /**
         * =========================
         * 📊 EXPORT TO EXCEL
         * =========================
         */
        if ($request->export === 'excel') {

            $data = $query->get();
            $filename = "Material_Consumption_" . now()->format('d-m-Y') . ".csv";

            $headers = [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');

                fputcsv($file, [
                    'Date',
                    'Scheme',
                    'Material',
                    'Type',
                    'Unit',
                    'Quantity'
                ]);

                foreach ($data as $row) {
                    foreach ($row->materials as $m) {
                        fputcsv($file, [
                            \Carbon\Carbon::parse($row->Date)->format('d-m-Y'),
                            optional($row->Scheme)->Name,
                            optional($m->materialDetail)->Name,
                            optional($m->materialDetail)->Type,
                            $m->Unit,
                            $m->Qty,
                        ]);
                    }
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        /**
         * =========================
         * 🔹 DATATABLE AJAX
         * =========================
         */
        if ($request->ajax()) {

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn(
                    'Date',
                    fn($row) =>
                    \Carbon\Carbon::parse($row->Date)->format('d-m-Y')
                )
                ->addColumn(
                    'scheme',
                    fn($row) =>
                    optional($row->Scheme)->Name ?? '-'
                )
                ->addColumn('material', function ($row) {
                    return '<table class="inner-table">' .
                        $row->materials->map(function ($m) {
                            $name = optional($m->materialDetail)->Name;
                            $type = optional($m->materialDetail)->Type;
                            return "<tr><td>{$name} - {$type}</td></tr>";
                        })->implode('') .
                        '</table>';
                })
                ->addColumn(
                    'Unit',
                    fn($row) =>
                    '<table class="inner-table">' .
                    $row->materials->map(
                        fn($m) =>
                        "<tr><td>{$m->Unit}</td></tr>"
                    )->implode('') .
                    '</table>'
                )
                ->addColumn(
                    'Qty',
                    fn($row) =>
                    '<table class="inner-table">' .
                    $row->materials->map(
                        fn($m) =>
                        "<tr><td>{$m->Qty}</td></tr>"
                    )->implode('') .
                    '</table>'
                )
                ->filter(function ($query) use ($request) {

                    $search = $request->input('search.value');

                    if (!empty($search)) {

                        $query->where(function ($q) use ($search) {

                            // Date
                            $q->where('Date', 'like', "%{$search}%")

                                // Scheme
                                ->orWhereHas('Scheme', function ($s) use ($search) {
                                $s->where('Name', 'like', "%{$search}%");
                            })

                                // Material Name + Type
                                ->orWhereHas('materials.materialDetail', function ($m) use ($search) {
                                $m->where('Name', 'like', "%{$search}%")
                                    ->orWhere('Type', 'like', "%{$search}%");
                            })

                                // Unit + Qty
                                ->orWhereHas('materials', function ($m) use ($search) {
                                $m->where('Unit', 'like', "%{$search}%")
                                    ->orWhere('Qty', 'like', "%{$search}%");
                            });

                        });
                    }
                })
                ->rawColumns(['material', 'Unit', 'Qty'])
                ->make(true);
        }

        return view('backend.Reports.material_consumption');
    }

    public function return_pay(Request $request)
{
    $clientId = session('selected_scheme_id');

    $query = Income_Payment::with(['scheme'])
        ->where('ClientID', $clientId)
        ->orderBy('Date', 'DESC');

    // ✅ DATE FILTER
    if (!empty($request->fdate) && !empty($request->tdate)) {
        $from = Carbon::createFromFormat('d-m-Y', $request->fdate)->format('Y-m-d');
        $to   = Carbon::createFromFormat('d-m-Y', $request->tdate)->format('Y-m-d');

        $query->whereBetween('Date', [$from, $to]);
    }

    /**
     * =========================
     * 📊 EXPORT TO EXCEL
     * =========================
     */
    if ($request->export === 'excel') {

        $data = $query->get();
        $filename = "Return_Payment_" . now()->format('d-m-Y') . ".csv";

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date', 'Payment Method', 'Return Type', 'Amount', 'Scheme', 'Narration'
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    Carbon::parse($row->Date)->format('d-m-Y'),
                    $row->payment_method,
                    $row->Type_Payment,
                    $row->amt_pay,
                    optional($row->scheme)->Name,
                    $row->narration,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * =========================
     * 🔹 DATATABLE AJAX
     * =========================
     */
    if ($request->ajax()) {

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('Date', fn ($r) =>
                Carbon::parse($r->Date)->format('d-m-Y')
            )
            ->addColumn('schemeID', fn ($r) =>
                optional($r->scheme)->Name ?? '-'
            )
            ->make(true);
    }

    return view('backend.Reports.return_pay');
}


public function dailyDiary(Request $request)
{
     try {

        // full existing code

    } catch (\Throwable $e) {

        return response()->json([
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');
        $date = $request->date ?? now()->toDateString();

        // OPENING BALANCE (yesterday)
        $openingBalance = $this->getbalance(
            'Allwithcashinhand',
            '0000',
            Carbon::parse($date)->subDay()->toDateString()
        );

        // CLOSING BALANCE (today)
        $closingBalance = $this->getbalance(
            'Allwithcashinhand',
            '0000',
            $date
        );

        // 1️⃣ UNION with source_table
        $query = DB::query()->fromSub(function ($q) use ($clientId, $date) {

            $q->select(
                'ID',
                'Date',
                'amt_pay',
                'Type_Payment',
                'cheque_no',
                DB::raw("'daily_trans' as source")
            )
            ->from('daily_trans')
            ->where('ClientID',$clientId)
            ->whereDate('Date',$date)

            ->unionAll(
                DB::table('partners_loan')
                ->select(
                    'ID','Date','amt_pay','Type_Payment','cheque_no',
                    DB::raw("'partners_loan' as source")
                )
                ->where('ClientID',$clientId)
                ->whereDate('Date',$date)
            )

            ->unionAll(
                DB::table('loan')
                ->select(
                    'ID','Date','amt_pay','Type_Payment','cheque_no',
                    DB::raw("'loan' as source")
                )
                ->where('ClientID',$clientId)
                ->whereDate('Date',$date)
            )

            ->unionAll(
                DB::table('workorder_payment')
                ->select(
                    'ID','Date as Date','amt_pay','Type_Payment','cheque_no',
                    DB::raw("'workorder_payment' as source")
                )
                ->where('ClientID',$clientId)
                ->whereDate('Date',$date)
            )

            ->unionAll(
                DB::table('site_expences')
                ->select(
                    'ID','Date as Date','amt_pay','Exp_type as Type_Payment','cheque_no',
                    DB::raw("'site_expences' as source")
                )
                ->where('ClientID',$clientId)
                ->whereDate('Date',$date)
            );

        }, 't');

        // Execute query (same data as before)
$data = $query->get();

$rows = [];

// 1️⃣ OPENING BALANCE ROW
$rows[] = [
    'drcr' => 'Cr',
    'particulars' => '<b>Opening Balance</b>',
    'payment_details' => '',
    'debit' => '',
    'credit' => number_format($openingBalance, 2),
];

// 2️⃣ TRANSACTION ROWS (from your query)
foreach ($data as $row) {

    // Default values
    $debit = '';
    $credit = '';
    $drcr = 'Dr';

    // Partners / Loan logic (same as your core PHP)
    if (in_array($row->source, ['partners_loan','loan',])) {
        $rec = DB::table($row->source)->find($row->ID);

        if ($rec->paytype === 'Paid') {
            $debit = number_format($row->amt_pay, 2);
        } else {
            $credit = number_format($row->amt_pay, 2);
            $drcr = 'Cr';
        }
    } else {
        // Other tables are expense
        $debit = number_format($row->amt_pay, 2);
    }

    $rows[] = [
        'drcr' => $drcr,
        'particulars' => $row->Type_Payment,
        'payment_details' => $row->cheque_no ?: 'Cash In Hand',
        'debit' => $debit,
        'credit' => $credit,
    ];
}

// 3️⃣ CLOSING BALANCE ROW
$rows[] = [
    'drcr' => 'Cr',
    'particulars' => '<b>Closing Balance</b>',
    'payment_details' => '',
    'debit' => '',
    'credit' => number_format($closingBalance, 2),
];

// 4️⃣ SEND TO DATATABLES
return DataTables::of(collect($rows))
    ->rawColumns(['particulars'])
    ->addIndexColumn() 
    ->make(true);

    }

    return view('backend.Reports.daily_dairy');
}

private function getbalance($matid, $payid, $date)
{
    $clientId = session('selected_scheme_id');

    if ($payid == '') {
        $payid = '0000';
    }

    /* =========================
       OPENING BALANCE
    ========================= */

    if ($matid == 'All') {

        $mname_record = DB::table('accounts')
            ->where('ClientID',$clientId)
            ->where('ID','!=','Cash In Hand')
            ->sum('OBalance');

    } else {

        $mname_record = DB::table('accounts')
            ->where('ClientID',$clientId)
            ->where('ID',$matid)
            ->value('OBalance') ?? 0;
    }

    /* =========================
       CONDITIONS
    ========================= */

    if ($matid == 'Allwithcashinhand') {

        $mname_record = DB::table('accounts')
            ->where('ClientID',$clientId)
            ->sum('OBalance');

        $condition = function ($q) {
            $q->where(function ($x) {
                $x->where('payment_method','!=','cheque')
                ->orWhere(function ($y) {
                    $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
                });
            });
        };

        $cond1 = $cond2 = $condition;

    } elseif ($matid != 'All') {

        $condition = function ($q) use ($matid) {
            $q->where('account_no',$matid)
            ->where(function ($x) {
                $x->where('payment_method','!=','cheque')
                ->orWhere(function ($y) {
                    $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
                });
            });
        };

        $cond1 = fn($q)=>$q->where('account_from',$matid);
        $cond2 = fn($q)=>$q->where('account_to',$matid);

    } else {

        $condition = function ($q) {
            $q->where('account_no','!=','Cash In Hand')
            ->where(function ($x) {
                $x->where('payment_method','!=','cheque')
                ->orWhere(function ($y) {
                    $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
                });
            });
        };

        $cond1 = fn($q)=>$q->where('account_from','!=','Cash In Hand');
        $cond2 = fn($q)=>$q->where('account_to','!=','Cash In Hand');
    }

    /* =========================
       EXPENSES
    ========================= */

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

    $exptotal =
        DB::table('workorder_payment')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('inv_payment')->where('ClientID',$clientId)->where($condition)->whereDate('payment_date','<=',$date)->sum('amt_pay')

        + DB::table('partners_loan')->where('ClientID',$clientId)->where('paytype','Paid')->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('loan')->where('ClientID',$clientId)->where('paytype','Paid')->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('loan_payment')->where('ClientID',$clientId)->where('paytype','Paid')->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('salary')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('daily_trans')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('site_expences')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('labour_payment')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('land')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('booking_cancel')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('stampotherexpenses')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('customer_refund')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('account_transfer')->where('ClientID',$clientId)->where($cond1)->whereDate('Date','<=',$date)->sum('amt_pay')
        
        + DB::table('employee_advance')
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
        ->sum('advance')

        + DB::table('salarymaster')
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


    /* =========================
       INCOME
    ========================= */

    $incometotal =
        DB::table('loan')->where('ClientID',$clientId)->where('paytype','Received')->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('loan_payment')->where('ClientID',$clientId)->where('paytype','Received')->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('partners_loan')->where('ClientID',$clientId)->where('paytype','Received')->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('project_payment')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('Payable')

        + DB::table('booking_payment')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('income_payment')->where('ClientID',$clientId)->where($condition)->whereDate('Date','<=',$date)->sum('amt_pay')

        + DB::table('account_transfer')->where('ClientID',$clientId)->where($cond2)->whereDate('Date','<=',$date)->sum('amt_pay')
        
        + DB::table('emp_avance_pay')
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

    /* =========================
       FINAL BALANCE
    ========================= */

    return round(($mname_record + $incometotal) - $exptotal, 2);
}

public function incomeExpense(Request $request)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');
        $fdate = $request->fdate;
        $tdate = $request->tdate;

        /* ===============================
         | OPENING BALANCE (CORE LOGIC)
         =============================== */
        $openingBalance = $this->getbalance(
            'Allwithcashinhand',
            '00000',
            Carbon::parse($fdate)->subDay()->toDateString()
        );

        /* ===============================
         | INCOME
         =============================== */
        $income = DB::query()->fromSub(function ($q) use ($clientId,$fdate,$tdate) {

        $q->select('ID','Date','account_no','amt_pay','Type_Payment','cheque_no')
        ->from('loan')
        ->where('ClientID',$clientId)
        ->where('paytype','Received')
        ->whereBetween('Date',[$fdate,$tdate])

        ->unionAll(
        DB::table('partners_loan')
        ->select('ID','Date','account_no','amt_pay','Type_Payment','cheque_no')
        ->where('ClientID',$clientId)
        ->where('paytype','Received')
        ->whereBetween('Date',[$fdate,$tdate])
        )

        ->unionAll(
        DB::table('loan_payment')
        ->select('ID','Date','account_no','amt_pay','paytype as Type_Payment','cheque_no')
        ->where('ClientID',$clientId)
        ->where('paytype','Received')
        ->whereBetween('Date',[$fdate,$tdate])
        )

        ->unionAll(
        DB::table('project_payment')
        ->select('ID','Date','account_no','amt_pay','Type_Payment','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        )

        ->unionAll(
        DB::table('income_payment')
        ->select('ID','Date','account_no','amt_pay','Type_Payment','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        )

        ->unionAll(
        DB::table('booking_payment')
        ->select('ID','Date','account_no','amt_pay','type as Type_Payment','cheque_no')
        ->where('ClientID',$clientId)
        ->where('amt_pay','>',0)
        ->whereBetween('Date',[$fdate,$tdate])
        );

        },'i')->get();

        /* ===============================
         | EXPENSE
         =============================== */
        $expense = DB::query()->fromSub(function ($q) use ($clientId,$fdate,$tdate) {

        $q->select('Date','Type_Payment','amt_pay','cheque_no')
        ->from('daily_trans')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })

        ->unionAll(
        DB::table('site_expences')
        ->select('Date','Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        )

        ->unionAll(
        DB::table('land')
        ->select('Date','Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        )

        ->unionAll(
        DB::table('loan')
        ->select('Date','Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->where('paytype','Paid')
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        )

        ->unionAll(
        DB::table('inv_payment')
        ->select('payment_date as Date','Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('payment_date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        )

        ->unionAll(
        DB::table('partners_loan')
        ->select('Date','Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->where('paytype','Paid')
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        )

        ->unionAll(
        DB::table('workorder_payment')
        ->select('Date','Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        )

        ->unionAll(
        DB::table('customer_refund')
        ->select('Date','paytype as Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        )

        ->unionAll(
        DB::table('booking_cancel')
        ->select('Date','Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        )

        ->unionAll(
        DB::table('stampotherexpenses')
        ->select('Date','Type_Payment','amt_pay','cheque_no')
        ->where('ClientID',$clientId)
        ->whereBetween('Date',[$fdate,$tdate])
        ->where(function($x){
            $x->where('payment_method','!=','cheque')
            ->orWhere(function($y){
                $y->where('payment_method','cheque')
                    ->where('reconciliation',1);
            });
        })
        );

        }, 'e')->get();

        /* ===============================
         | BUILD ROWS (LIKE CORE PHP)
         =============================== */
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        $rows[] = [
            'date' => '',
            'particulars' => '<b>Opening Balance</b>',
            'payment_details' => '',
            'debit' => '',
            'credit' => '<b>'.number_format($openingBalance, 2).'</b>',
        ];

        

      

        // 🔹 INCOME ROWS
        foreach ($income as $r) {
            $rows[] = [
                'date' => Carbon::parse($r->Date)->format('d-m-Y'),
                'particulars' => $r->Type_Payment,
                'payment_details' => $r->cheque_no ?: 'Cash In Hand',
                'debit' => '',
                'credit' => number_format($r->amt_pay, 2),
            ];
            $totalCredit += $r->amt_pay;
        }

        // 🔹 EXPENSE ROWS
        foreach ($expense as $r) {
            $rows[] = [
                'date' => Carbon::parse($r->Date)->format('d-m-Y'),
                'particulars' => $r->Type_Payment,
                'payment_details' => $r->cheque_no ?: 'Cash In Hand',
                'debit' => number_format($r->amt_pay, 2),
                'credit' => '',
            ];
            $totalDebit += $r->amt_pay;
        }

        // 🔹 OPENING ROW
        $rows[] = [
            'date' => '',
            'particulars' => 'Total',
            'payment_details' => '',
            'debit' => number_format($totalDebit, 2),
            'credit' => number_format($totalCredit, 2),
        ];

        // 🔹 CLOSING BALANCE
        $closingBalance = $openingBalance + $totalCredit - $totalDebit;

          $rows[] = [
            'date' => '',
            'particulars' => '<b>Closing Balance</b>',
            'payment_details' => '',
            'debit' => '',
            'credit' => '<b>'.number_format($closingBalance, 2).'</b>',
        ];

        return response()->json([
            'data' => $rows
        ]);
    }

    return view('backend.Reports.income_expense');
}

public function tdsReport(Request $request)
{
    if ($request->ajax()) {

        // ✅ SAME CLIENT ID LOGIC AS WORKING INDEX()
        $clientId = session('selected_scheme_id');

        $vendor = $request->vendor;

        // ⛔ Do nothing until vendor selected (same as old PHP)
        if (!$vendor) {
            return response()->json([
                'summary' => [
                    'calculated_tds' => '0.00',
                    'tds_amount'     => '0.00',
                    'paid_tds'       => '0.00',
                    'pending_tds'    => '0.00',
                ],
                'data' => [],
                'grandTotal' => '0.00',
            ]);
        }

        $fdate = Carbon::parse($request->fdate)->toDateString();
        $tdate = Carbon::parse($request->tdate)->toDateString();

        /* =================================================
         | 1️⃣ DETAIL PAYMENTS (BOTTOM TABLE)
         ================================================= */
        $workorderPayments = DB::table('workorder_payment')
            ->where('ClientID', $clientId)
            ->where('Type_Payment', 'Workorder Expense')
            ->where('conID', $vendor)
            ->whereBetween('Date', [$fdate, $tdate])
            ->orderBy('Date')
            ->get();

        /* =================================================
         | 2️⃣ SUMMARY CALCULATIONS (MATCH INDEX LOGIC)
         ================================================= */

        // ✅ Calculated TDS (REFERENCE LOGIC)
        $calculatedTds = DB::table('workorder_detail')
            ->where('ClientID', $clientId)
            ->where('ContractorID', $vendor)
            ->sum('TDSAmt');

        // ✅ TDS Amount (from payments)
        $tdsAmount = $workorderPayments->sum('TDSAmt');

        // ✅ Paid TDS
        $tdsPaid = DB::table('tds_payment')
            ->where('ClientID', $clientId)
            ->where('conID', $vendor)
            ->whereBetween('payment_date', [$fdate, $tdate])
            ->sum('amt_pay');

        // ✅ Pending TDS
        $pending = round($tdsAmount - $tdsPaid, 2);

        /* =================================================
         | 3️⃣ ROW FORMATTING (MATCH OLD PHP)
         ================================================= */
        $rows = [];
        $grandTotal = 0;

        foreach ($workorderPayments as $p) {

            $wk = DB::table('workorder_detail')->find($p->WorkorderID);
            $scheme = DB::table('scheme_step1')->find($wk->SiteLocation ?? null);
            $vendorRec = DB::table('vendor')->find($wk->ContractorID ?? null);

            $rows[] = [
                'particulars' =>
                    'Payment To ' .
                    ($vendorRec->Name ?? '') . ' - ' .
                    ($scheme->Name ?? '') . ' - ' .
                    ($p->Type_Payment ?? ''),
                'payment_details' =>
                    $p->payment_method . ' - ' .
                    ($p->cheque_no ?: 'Cash In Hand'),
                'tds' => number_format($p->TDSAmt, 2),
            ];

            $grandTotal += $p->TDSAmt;
        }

        return response()->json([
            'summary' => [
                'calculated_tds' => number_format($calculatedTds, 2),
                'tds_amount'     => number_format($tdsAmount, 2),
                'paid_tds'       => number_format($tdsPaid, 2),
                'pending_tds'    => number_format($pending, 2),
            ],
            'data' => $rows,
            'grandTotal' => number_format($grandTotal, 2),
        ]);
    }

    /* =================================================
     | VENDOR DROPDOWN (ONLY CONTRACTORS WITH TDS)
     ================================================= */
    $vendors = DB::table('vendor')
        ->where('ClientID', session('selected_scheme_id'))
        ->where('Type', 'CONTRACTOR')
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
              ->from('workorder_detail')
              ->whereColumn('workorder_detail.ContractorID', 'vendor.ID');
        })
        ->orderBy('Name')
        ->get(['ID', 'Name']);

    return view('backend.Reports.tds_report', compact('vendors'));
}

    public function material_transfer_Report(Request $request)
    {
        if ($request->ajax()) {
            $clientId = session('selected_scheme_id');

            try {

                $query = Transfer_detail::with([
                    'fromsite',
                    'Tosite',
                    'materials.material'
                ])
                    ->select(['ID', 'Date', 'Srno', 'gtotal', 'from_site', 'To_site'])
                    ->where('from_site', $clientId);

                if ($request->from_date) {
                    $query->whereDate('Date', '>=', $request->from_date);
                }

                if ($request->to_date) {
                    $query->whereDate('Date', '<=', $request->to_date);
                }

                $query->orderBy('Srno', 'DESC');

                return DataTables::of($query)
                    ->filter(function ($query) use ($request) {

                        $search = $request->input('search.value');

                        if (!empty($search)) {

                            $query->where(function ($q) use ($search) {

                                $q->where('Date', 'like', "%{$search}%")

                                    ->orWhereHas('fromsite', function ($f) use ($search) {
                                        $f->where('Name', 'like', "%{$search}%");
                                    })

                                    ->orWhereHas('Tosite', function ($t) use ($search) {
                                        $t->where('Name', 'like', "%{$search}%");
                                    })

                                    ->orWhereHas('materials.material', function ($m) use ($search) {
                                        $m->where('Name', 'like', "%{$search}%")
                                            ->orWhere('Type', 'like', "%{$search}%");
                                    })

                                    ->orWhereHas('materials', function ($m) use ($search) {
                                        $m->where('qty', 'like', "%{$search}%");
                                    });

                            });
                        }
                    })

                    ->addIndexColumn()
                    ->addColumn('Date', function ($row) {
                        return Carbon::parse($row->Date)->format('d-m-Y');
                    })
                    ->addColumn('from_site', function ($row) {
                        return optional($row->fromsite)->Name ?? '-';
                    })
                    ->addColumn('To_site', function ($row) {
                        return optional($row->Tosite)->Name ?? '-';
                    })
                    ->addColumn('materials', function ($row) {

                        if ($row->materials->isEmpty()) {
                            return '-';
                        }

                        $html = '<table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th>Type</th>
                                        <th class="text-end">Qty</th>
                                    </tr>
                                </thead><tbody>';

                        foreach ($row->materials as $m) {
                            $html .= '
                            <tr>
                                <td>' . ($m->material->Name ?? '-') . '</td>
                                <td>' . ($m->material->Type ?? '-') . '</td>
                                <td class="text-end">' . $m->qty . '</td>
                            </tr>';
                        }

                        $html .= '</tbody></table>';

                        return $html;
                    })

                    ->rawColumns(['materials'])
                    ->make(true);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
            }
        }

        return view('backend.Reports.material_transfer');
    }

public function material_recieve_Report(Request $request)
{
    if ($request->ajax()) {
        $clientId = session('selected_scheme_id');

        try {
            
             $query = Transfer_detail::with([
                'fromsite',
                'Tosite',
                'materials.material'   // 👈 IMPORTANT
            ])
            ->select(['ID', 'Date', 'Srno', 'gtotal', 'from_site', 'To_site'])
            ->where('To_site', $clientId);

            if($request->from_date && $request->to_date){
            $query->whereBetween('Date', [$request->from_date, $request->to_date]);
        }

            $query ->orderBy('Srno', 'DESC');


            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('Date', function ($row) {
                    return Carbon::parse($row->Date)->format('d-m-Y');
                })
                ->addColumn('from_site', function ($row) {
                    return optional($row->fromsite)->Name ?? '-';
                })
                ->addColumn('To_site', function ($row) {
                    return optional($row->Tosite)->Name ?? '-';
                })
                ->addColumn('materials', function ($row) {

                    if ($row->materials->isEmpty()) {
                        return '-';
                    }

                    $html = '<table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th>Type</th>
                                        <th class="text-end">Qty</th>
                                    </tr>
                                </thead><tbody>';

                    foreach ($row->materials as $m) {
                        $html .= '
                            <tr>
                                <td>'.($m->material->Name ?? '-').'</td>
                                <td>'.($m->material->Type ?? '-').'</td>
                                <td class="text-end">'.$m->qty.'</td>
                            </tr>';
                    }

                    $html .= '</tbody></table>';

                    return $html;
                })

                ->rawColumns(['materials'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    return view('backend.Reports.material_receive');
}

public function abstractReport(Request $request)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');
        $fdate = $request->fdate;
        $tdate = $request->tdate;

        /* =========================
           1️⃣ LOAN ABSTRACT
        ========================= */
        $loanRows = [];
        $totalloancredit = 0;
        $totalloandebit  = 0;

        $loanCustomers = DB::table('loan')
            ->whereBetween('Date', [$fdate, $tdate])
            ->where('ClientID', $clientId)
            ->groupBy('customer')
            ->pluck('customer');

        foreach ($loanCustomers as $customerId) {

            $vendor = DB::table('vendor')->where('ID', $customerId)->first();

            $credit = DB::table('loan')
                ->where('customer', $customerId)
                ->whereBetween('Date', [$fdate, $tdate])
                ->where('paytype', 'Received')
                ->sum('amt_pay');

            $debit = DB::table('loan')
                ->where('customer', $customerId)
                ->whereBetween('Date', [$fdate, $tdate])
                ->where('paytype', 'Paid')
                ->sum('amt_pay');

            $creditPrev = DB::table('loan')
                ->where('customer', $customerId)
                ->where('Date', '<', $fdate)
                ->where('paytype', 'Received')
                ->sum('amt_pay');

            $debitPrev = DB::table('loan')
                ->where('customer', $customerId)
                ->where('Date', '<', $fdate)
                ->where('paytype', 'Paid')
                ->sum('amt_pay');

            $openBal = $creditPrev - $debitPrev;
            $balance = ($credit - $debit) + $openBal;

            $totalloancredit += $credit;
            $totalloandebit  += $debit;

            $loanRows[] = compact(
                'vendor', 'openBal', 'credit', 'debit', 'balance'
            );
        }

        /* =========================
           2️⃣ PARTNERS ABSTRACT
        ========================= */
        $partnerRows = [];
        $totalpartnercredit = 0;
        $totalpartnerdebit  = 0;

        $partners = DB::table('partners_loan')
            ->whereBetween('Date', [$fdate, $tdate])
            ->groupBy('partners')
            ->pluck('partners');

        foreach ($partners as $partnerId) {

            $partner = DB::table('partners')
                ->where('ID', $partnerId)
                ->where('CFlag', 0)
                ->first();

            $credit = DB::table('partners_loan')
                ->where('partners', $partnerId)
                ->whereBetween('Date', [$fdate, $tdate])
                ->where('paytype', 'Received')
                ->sum('amt_pay');

            $debit = DB::table('partners_loan')
                ->where('partners', $partnerId)
                ->whereBetween('Date', [$fdate, $tdate])
                ->where('paytype', 'Paid')
                ->sum('amt_pay');

            $creditPrev = DB::table('partners_loan')
                ->where('partners', $partnerId)
                ->where('Date', '<', $fdate)
                ->where('paytype', 'Received')
                ->sum('amt_pay');

            $debitPrev = DB::table('partners_loan')
                ->where('partners', $partnerId)
                ->where('Date', '<', $fdate)
                ->where('paytype', 'Paid')
                ->sum('amt_pay');

            $openBal = $creditPrev - $debitPrev;
            $balance = ($credit - $debit) + $openBal;

            $totalpartnercredit += $credit;
            $totalpartnerdebit  += $debit;

            $partnerRows[] = compact(
                'partner', 'openBal', 'credit', 'debit', 'balance'
            );
        }

        /* =========================
           3️⃣ OFFICE EXPENSE
        ========================= */
        $officeExpense = DB::table('daily_trans')
            ->whereBetween('Date', [$fdate, $tdate])
            ->where('bFlag', 0)
            ->sum('amt_pay');

        $officeExpensePrev = DB::table('daily_trans')
            ->where('Date', '<', $fdate)
            ->where('bFlag', 0)
            ->sum('amt_pay');

        /* =========================
           4️⃣ TDS
        ========================= */
        // $tdsCredit = DB::table('workorder_payment')
        //     ->whereBetween('Date', [$fdate, $tdate])
        //     ->sum('TDSAmt');

        // $tdsDebit = DB::table('tds_payment')
        //     ->whereBetween('payment_date', [$fdate, $tdate])
        //     ->sum('amt_pay');

        // $tdsCreditPrev = DB::table('workorder_payment')
        //     ->where('Date', '<', $fdate)
        //     ->sum('TDSAmt');

        // $tdsDebitPrev = DB::table('tds_payment')
        //     ->where('payment_date', '<', $fdate)
        //     ->sum('amt_pay');
            $tdsCredit = DB::table('workorder_payment')
                ->where('ClientID', $clientId)
                ->whereBetween('Date', [$fdate, $tdate])
                ->sum('TDSAmt');

            $tdsDebit = DB::table('tds_payment')
                ->where('ClientID', $clientId)
                ->whereBetween('payment_date', [$fdate, $tdate])
                ->sum('amt_pay');

            $tdsCreditPrev = DB::table('workorder_payment')
                ->where('ClientID', $clientId)
                ->where('Date', '<', $fdate)
                ->sum('TDSAmt');

            $tdsDebitPrev = DB::table('tds_payment')
                ->where('ClientID', $clientId)
                ->where('payment_date', '<', $fdate)
                ->sum('amt_pay');
        /* =========================
           5️⃣ FINAL SUMMARY
        ========================= */
        $oldBalance =
    $this->getbalance('All', '', $fdate) +
    $this->getbalance('Cash In Hand', '', $fdate);

        $grandCredit =
            $totalloancredit +
            $totalpartnercredit +
            $tdsCredit;

        $grandDebit =
            $totalloandebit +
            $totalpartnerdebit +
            $officeExpense +
            $tdsDebit;

        return response()->json([
            'loan'     => $loanRows,
            'partners' => $partnerRows,
            'office'   => compact('officeExpense', 'officeExpensePrev'),
            'tds'      => compact('tdsCredit','tdsDebit','tdsCreditPrev','tdsDebitPrev'),
            'summary'  => compact('oldBalance','grandCredit','grandDebit')
        ]);
    }

    return view('backend.Reports.abstract_report');
}

/////////////////////////////////////////////////////////////////////////////////

    public function customerRefund(Request $request)
    {
        $customerRefund = CustomerRefund::select('Id')
        ->when($request->from_date, function ($q) use ($request) {
                $q->whereDate('Date','>=',$request->from_date);
            })
            ->when($request->to_date, function ($q) use ($request) {
                $q->whereDate('Date','<=',$request->to_date);
            })
        ->get();
        return view('backend.Reports.customer_refund', compact('customerRefund'));

    }
    public function stampOtherExpense(Request $request)
    {
       
        $query = StampOtherExpenses::query();

        // Filter by date
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('Date', [
                date('Y-m-d', strtotime($request->from_date)),
                date('Y-m-d', strtotime($request->to_date))
            ]);
        }

        // Filter by type
        if ($request->typesrch && $request->typesrch != 'all') {
            $query->where('Exp_type', $request->typesrch);
        }

        // Filter by client (assume logged-in client ID stored in session)
        $query->where('ClientID', session('selected_scheme_id'))->where('bFlag', 0);

        // Order
        $records = $query->orderBy('Date', 'desc')->get();

        // Total sum
        $totalAmount = $records->sum('amt_pay');

        // Fetch schemes and accounts for display
        $schemes = SchemeDetail::all()->keyBy('ID');
        
        $accounts = Bank_Acc::all()->keyBy('ID');

        $types = StampOtherExpenses::where('ClientID', session('selected_scheme_id'))
        ->where('bFlag', 0)
        ->distinct()
        ->pluck('Exp_type');

        return view('backend.Reports.stamp_other_expense', compact(
            'records', 'totalAmount', 'schemes', 'accounts', 'types'
        ));
    }
    public function index()
    {
        $accounts = DB::table('accounts')
            ->where('ClientID', session('selected_scheme_id'))
            ->orderBy('Name')
            ->get();

        return view('backend.Reports.bank_transaction_report', compact('accounts'));
    }

    public function reportData(Request $request)
    {
        $fdate = date('Y-m-d', strtotime($request->fdate));
        $tdate = date('Y-m-d', strtotime($request->tdate));
        $ac    = $request->account_no;

        /** CREDIT QUERY (same as PHP) */
        $credit = DB::select("
            SELECT Date, amt_pay as Amt, Type_Payment as Type, cheque_no
            FROM income_payment
            WHERE ClientID = ?
            AND account_no = ?
            AND Date BETWEEN ? AND ?
        ", [session('selected_scheme_id'), $ac, $fdate, $tdate]);

        /** DEBIT QUERY (same as PHP) */
        $debit = DB::select("
            SELECT Date, amt_pay as Amt, Type_Payment as Type, cheque_no
            FROM daily_trans
            WHERE ClientID = ?
            AND account_no = ?
            AND Date BETWEEN ? AND ?
        ", [session('selected_scheme_id'), $ac, $fdate, $tdate]);

        return view('backend.Reports.bank_transaction_report_table', compact(
            'credit',
            'debit'
        ));
    }
    public function loanreport(Request $request)
    {
        // Optional filter by dates
        $fromDate = $request->input('FromDate', session('FromDate', now()->startOfMonth()->format('Y-m-d')));
        $toDate = $request->input('ToDate', session('ToDate', now()->endOfMonth()->format('Y-m-d')));
        $clientId = session('selected_scheme_id');

        // Save filters in session if needed
        session(['FromDate' => $fromDate, 'ToDate' => $toDate]);

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

        /* 🔹 Excel Download */
        if ($request->type === 'excel') {
            return response()->streamDownload(function () use ($loanData) {
                $handle = fopen('php://output', 'w');

                // Header
                fputcsv($handle, array_keys((array) $loanData->first()));

                // Data
                foreach ($loanData as $row) {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, 'loan_payment_report.csv');
        }

        return view('backend.Reports.loan_report', compact('loanData', 'fromDate', 'toDate'));
    }
    public function loandetail($customer)
    {
        $loans = Loan::where('customer', $customer)
            ->with('partner')
            ->orderBy('Date', 'asc')
            ->get();

        return view('backend.Reports.loan_detail_report', compact('loans'));
    }

    public function StockReport(Request $request)
    {
        $clientId = session('selected_scheme_id');

        // Date filter (same logic as core PHP)
        if ($request->task === 'SetFilter') {
            if (empty($request->from_date) && empty($request->to_date)) {
                $fromDate = Carbon::now()->subDays(50)->format('Y-m-d');
                $toDate   = Carbon::now()->format('Y-m-d');
            } else {
                $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
                $toDate   = Carbon::parse($request->to_date)->format('Y-m-d');
            }
        } else {
            $fromDate = session('FromDate', Carbon::now()->subDays(50)->format('Y-m-d'));
            $toDate   = session('ToDate', Carbon::now()->format('Y-m-d'));
        }

        session([
            'FromDate' => $fromDate,
            'ToDate'   => $toDate
        ]);

        // UNION query converted to Laravel
        $materials = DB::table('inv_product')
            ->select('Material as material_id')
            ->where('ClientID', $clientId)

            ->union(
                DB::table('rejected_matrial_detail')
                    ->select('Material as material_id')
                    ->where('ClientID', $clientId)
            )
            ->union(
                DB::table('transfer_material')
                    ->select('matrialID as material_id')
                    ->where('from_site', $clientId)
            )
            ->union(
                DB::table('transfer_material')
                    ->select('matrialID as material_id')
                    ->where('to_site', $clientId)
            )
            ->get();

        $stockData = [];

        foreach ($materials as $mat) {
            if (!$mat->material_id) {
                continue;
            }

            $material = DB::table('material')
                ->where('ID', $mat->material_id)
                ->first();

            if (!$material) {
                continue;
            }

            $stock = $this->getStock($mat->material_id, $clientId);

            $stockData[] = [
                'name'  => $material->Name,
                'type'  => $material->Type,
                'unit'  => $material->Unit,
                'stock' => $stock
            ];
        }
         /* ---------------- Excel Export ---------------- */
        if ($request->type === 'excel') {
            return response()->streamDownload(function () use ($stockData) {
                $handle = fopen('php://output', 'w');

                // CSV Header
                if (!empty($stockData)) {
                    fputcsv($handle, array_keys($stockData[0]));
                }

                // CSV Rows
                foreach ($stockData as $row) {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, 'stock_report.csv');
        }


        return view('backend.Reports.stock', compact('stockData'));
    }
    public function getStock($materialId, $clientId)
    {
        // 1. Inward quantity
        $inwardQty = DB::table('inv_product')
            ->where('material', $materialId)
            ->where('ClientID', $clientId)
            ->sum('Qty');

        // 2. Outward quantity
        $outwardQty = DB::table('material_outward')
            ->where('material', $materialId)
            ->where('ClientID', $clientId)
            ->sum('quantity');

        // 3. Transfer OUT
        $transferOutQty = DB::table('transfer_material')
            ->where('matrialID', $materialId)
            ->where('from_site', $clientId)
            ->sum('qty');

        // 4. Transfer IN
        $transferInQty = DB::table('transfer_material')
            ->where('matrialID', $materialId)
            ->where('to_site', $clientId)
            ->sum('qty');

        // 5. Get consumption_detail IDs
        $consumptionIds = DB::table('consumption_detail')
            ->where('ClientID', $clientId)
            ->pluck('ID');

        // 6. Consumption quantity (filtered by consumption_detail)
        $consumedQty = 0;
        if ($consumptionIds->isNotEmpty()) {
            $consumedQty = DB::table('material_consumption')
                ->where('material', $materialId)
                ->where('ClientID', $clientId)
                ->whereIn('Cid', $consumptionIds)
                ->sum('qty');
        }

        // Null safety
        $inwardQty      = $inwardQty ?? 0;
        $outwardQty     = $outwardQty ?? 0;
        $transferOutQty = $transferOutQty ?? 0;
        $transferInQty  = $transferInQty ?? 0;
        $consumedQty    = $consumedQty ?? 0;

        // Same formula as legacy code
        $availableStock = ($inwardQty + $transferInQty)
                            - ($transferOutQty + $consumedQty + $outwardQty);

        return $availableStock;
    }
    public function MaterialPurchase(Request $request)
    {
        $fromDate = $request->from_date
        ? date('Y-m-d', strtotime($request->from_date))
        : date('Y-m-01');

        $toDate = $request->to_date
        ? date('Y-m-d', strtotime($request->to_date))
        : date('Y-m-d');

        

        /* ==============================
           Dropdown Data
        ===============================*/
        $clientId = session('selected_scheme_id');

        $materials = DB::table('material')
            ->select('ID as id', 'Name as name')
            ->where('ClientID', $clientId)
            ->get();
        

        $vendors = DB::table('vendor')
            ->where('Type', 'VENDOR')
            ->select('ID as id', 'Name')
            ->where('ClientID', $clientId)
            ->get();

        $schemes = DB::table('scheme_step1')
            ->select('ID as id', 'Name')
            ->where('ID', $clientId)
            ->get();

        /* ==============================
           Main Invoice Query
        ===============================*/

        $invoices = DB::table('inv_detail')
            ->join('inv_product', 'inv_detail.ID', '=', 'inv_product.Invno')
            ->select(
                'inv_detail.ID',
                'inv_detail.Date',
                'inv_detail.Invno',
                'inv_detail.gtotal',
                'inv_detail.destination'
            )
            ->whereBetween(DB::raw('date(inv_detail.Created)'), [$fromDate, $toDate]);
            

        if ($request->material_id) {
            $invoices->where('inv_product.Material', $request->material_id);
        }

        if ($request->vendor_id) {
            $invoices->where('inv_detail.purchasefrom', $request->vendor_id);
        }

        if ($request->scheme_id) {
            $invoices->where('inv_detail.destination', $request->scheme_id);
        }

        $invoices = $invoices
            ->groupBy(
                'inv_detail.ID',
                'inv_detail.Date',
                'inv_detail.Invno',
                'inv_detail.gtotal',
                'inv_detail.destination'
            )
            ->get();

        /* ==============================
           Attach Products (Nested Table)
        ===============================*/

        foreach ($invoices as $invoice) {

            $invoice->products = DB::table('inv_product')
                ->join('material', 'inv_product.Material', '=', 'material.ID')
                ->select(
                    'material.Name',
                    'material.Type',
                    'inv_product.Qty',
                    'inv_product.Rate',
                    'inv_product.Amount',
                    'inv_product.Vat'
                )
                ->where('inv_product.Invno', $invoice->ID)
                ->get();

            $invoice->scheme = DB::table('scheme_step1')
                ->where('ID', $invoice->destination)
                ->value('Name');
        }

        return view('backend.Reports.material_report', compact(
            'invoices',
            'materials',
            'vendors',
            'schemes','fromDate','toDate'
        ));
    }
    public function SiteExpense(Request $request)
    {
        $clientId = session('selected_scheme_id');
       $fromDate = $request->FromDate
        ? date('Y-m-d', strtotime($request->FromDate))
        : date('Y-m-01');

        $toDate = $request->ToDate
        ? date('Y-m-d', strtotime($request->ToDate))
        : date('Y-m-d');
        $typeSearch = $request->input('typesrch', '');
        $schemeId = $request->input('SCHEME', '');
        $rtype = $request->input('rtype', 'fi');

        $query = DB::table('site_expences')
            ->where('ClientID', $clientId)
            ->where('bFlag', 0)
            ->whereBetween('Date', [$fromDate, $toDate]);

        if ($schemeId) {
            $query->where('schemeID', $schemeId);
        }

        if ($typeSearch && $typeSearch != 'all') {
            $query->where('Exp_type', $typeSearch);
        }

        if (!$typeSearch && !$request->has('customersrch')) {
            $db_records = $query->orderBy('Date', 'DESC')->limit(10)->get();
        } else {
            $db_records = $query->orderBy('Date', 'DESC')->get();
        }

        $grandTotal = $db_records->sum('amt_pay');

        $expTypes = DB::table('site_expences')
            ->where('ClientID', $clientId)
            ->groupBy('Exp_type')
            ->pluck('Exp_type');

        $schemeIds = $db_records->pluck('schemeID')->unique()->toArray();
        $accountIds = $db_records->pluck('account_no')->unique()->toArray();

        $schemes = DB::table('scheme_step1')->whereIn('ID', $schemeIds)->get()->keyBy('ID');
        $accounts = DB::table('accounts')->whereIn('ID', $accountIds)->get()->keyBy('ID');

         /* ---------------- Excel Export ---------------- */
        if ($request->type === 'excel') {
            return response()->streamDownload(function () use ($db_records, $schemes, $accounts) {
                $handle = fopen('php://output', 'w');

                // CSV Header
                fputcsv($handle, [
                    'Date',
                    'Paid To',
                    'Amount',
                    'Type',
                    'Payment Method',
                    'Scheme',
                    'Narration'
                ]);

                // CSV Rows
                foreach ($db_records as $rec) {
                    fputcsv($handle, [
                        date('d-m-Y', strtotime($rec->Date)),
                        $rec->title,
                        $rec->amt_pay,
                        $rec->Exp_type,
                        $rec->payment_method == 'cash'
                            ? 'Cash'
                            : (($accounts[$rec->account_no]->Name ?? '') . ' (' . $rec->cheque_no . ')'),
                        $schemes[$rec->schemeID]->Name ?? '',
                        $rec->narration,
                    ]);
                }

                fclose($handle);
            }, 'site_expenses_report.csv');
        }


        return view('backend.Reports.site_expenses', compact(
            'db_records', 'rtype', 'grandTotal', 'expTypes', 'schemes', 'accounts','fromDate','toDate'
        ));
    }
    public function availableFlats(Request $request)
    {
        $tp = $request->Tp ?? 'Part';
        $payTp = $request->payTp ?? '';
        $rtype = $request->rtype ?? '';

        $pcond = $tp == 'Part' ? "Ptype = 0" : "Ptype = 1";
        $pwrd = $tp == 'Part' ? 'Partners' : 'Investors';

        // Payment condition
        $Condin = $payTp != '' ? "paytype = '$payTp'" : '1';

        // Report date condition
        $rpcond = '';
        if ($rtype == 'report') {
            $fromDate = session('FromDate');
            $toDate = session('ToDate');
            $rpcond = "Date >= '$fromDate' AND Date <= '$toDate' AND ";
        }

        // Get canceled bookings
        $bookingCancel = DB::table('booking_cancel')
            ->where('SchemID', session('selected_scheme_id'))
            ->pluck('BookingID')
            ->toArray();

        $cancelIds = count($bookingCancel) ? implode(',', $bookingCancel) : 0;

        // Get flats details excluding booked flats
        $db_record = DB::table('flats_details')
            ->where('ClientID', session('selected_scheme_id'))
            ->whereNotIn('ID', function($query) use ($cancelIds) {
                $query->select('FlatNo')
                    ->from('booking_customer')
                    ->where('Scheme', session('selected_scheme_id'))
                    ->where('ClientID', session('selected_scheme_id'))
                    ->whereNotIn('ID', explode(',', $cancelIds));
            })
            ->get();

        return view('backend.Reports.available_flat_report', compact('db_record', 'pwrd', 'rtype'));
    }
    public function LandExpenseReport(Request $request)
    {
        if ($request->ajax()) {

            $data = LandExpenses::with(['accountNo', 'schemes'])
                ->select(
                    'ID',
                    'Date',
                    'title',
                    'amt_pay',
                    'Exp_type',
                    'payment_method',
                    'schemeID',
                    'narration'
                );

            // ✅ From Date filter
            if ($request->filled('from_date')) {
                $data->whereDate('Date', '>=', $request->from_date);
            }

            // ✅ To Date filter
            if ($request->filled('to_date')) {
                $data->whereDate('Date', '<=', $request->to_date);
            }

            // ✅ Expense Type filter
            if ($request->filled('typesrch') && $request->typesrch !== 'all') {
                $data->where('Exp_type', $request->typesrch);
            }

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('date', function ($row) {
                    return date('d-m-Y', strtotime($row->Date));
                })

                ->addColumn('schemes_name', function ($row) {
                    return $row->schemes->Name ?? '';
                })

                
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.Reports.land_expense_report');
    }
    public function ReraReport(Request $request)
    {
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->format('Y-m-d')
            : Carbon::now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        $clientId = session('selected_scheme_id');

        /* ------------------------------
           CANCELLED BOOKINGS
        ------------------------------ */
        $cancelIds = DB::table('booking_cancel')
            ->where('SchemID', $clientId)
            ->pluck('BookingID')
            ->toArray();

        if (empty($cancelIds)) {
            $cancelIds = [0];
        }

        /* ------------------------------
           SOLD FLATS
        ------------------------------ */
        $soldFlats = DB::table('flats_details as f')
            ->join('booking_customer as b', function ($join) use ($clientId, $cancelIds, $toDate) {
                $join->on('b.FlatNo', '=', 'f.ID')
                    ->where('b.ClientID', $clientId)
                    ->whereNotIn('b.ID', $cancelIds)
                    ->where('b.agreement_complete', 1)
                    ->whereDate('b.reg_date', '<=', $toDate);
            })
            ->leftJoin('booking_payment as p', function ($join) use ($clientId, $cancelIds, $toDate) {
                $join->on('p.Booking_ID', '=', 'b.ID')
                    ->where('p.ClientID', $clientId)
                    ->whereNotIn('p.ID', $cancelIds)
                    ->whereDate('p.Date', '<=', $toDate);
            })
            ->select(
                'f.FlatType',
                'f.FlatNo',
                'f.Area',
                'f.Wing',
                'b.CutomerName',
                'b.agreement_no',
                'b.reg_date',
                'b.agreementAmt',
                'b.ID as booking_id',
                DB::raw('SUM(p.amt_pay) as total_received')
            )
            ->groupBy(
                'f.FlatType',
                'f.FlatNo',
                'f.Area',
                'f.Wing',
                'b.CutomerName',
                'b.agreement_no',
                'b.reg_date',
                'b.agreementAmt',
                'b.ID'
            )
            ->get();

        foreach ($soldFlats as $flat) {
            $flat->balance = $flat->agreementAmt - $flat->total_received;
        }

        /* ------------------------------
           UNSOLD FLATS (WITH scheme_step1)
        ------------------------------ */
        $unsoldFlats = DB::table('flats_details as f')
            ->join('scheme_step1 as s', 's.ID', '=', 'f.scheme_ID')
            ->where('f.ClientID', $clientId)
            ->whereNotIn('f.ID', function ($query) use ($clientId, $cancelIds) {
                $query->select('FlatNo')
                    ->from('booking_customer')
                    ->where('ClientID', $clientId)
                    ->where('agreement_complete', 1)
                    ->whereNotIn('ID', $cancelIds);
            })
            ->select(
                'f.FlatType',
                'f.FlatNo',
                'f.Area',
                's.gov_rate'
            )
            ->get();

        /* ------------------------------
           PASS TO VIEW
        ------------------------------ */
        return view('backend.Reports.sold_flat_report', compact(
            'soldFlats',
            'unsoldFlats',
            'fromDate',
            'toDate'
        ));
    }
    public function GstReport(Request $request)
    {
        
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->format('Y-m-d')
            : Carbon::now()->startOfMonth()->format('Y-m-d');

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');


        $clientId = session('selected_scheme_id');

        $conditionScheme = $request->scheme
            ? ['schemeID' => $request->scheme]
            : [];

        $records = DB::table('booking_payment')
        ->select(
            'Booking_ID',
            DB::raw('MAX(Date) as Date'),
            DB::raw('COUNT(*) as cnt')
        )
        ->where('ClientID', $clientId)
        ->whereBetween('Date', [$fromDate, $toDate])
        ->when($request->scheme, function ($q) use ($request) {
            $q->where('schemeID', $request->scheme);
        })
        ->groupBy('Booking_ID')
        ->orderByDesc('Date')
        ->get();


        $data = [];

        foreach ($records as $row) {

            $booking = DB::table('booking_customer')
                ->where('ID', $row->Booking_ID)
                ->where('ClientID', $clientId)
                ->first();

            if (!$booking) continue;

            $totalGstPer = $booking->servicetaxPer;

            $totalReceived = DB::table('booking_payment')
                ->where('Booking_ID', $row->Booking_ID)
                ->where('ClientID', $clientId)
                ->whereDate('Date', '<=', $toDate)
                ->sum('amt_pay');

            $currentMonthReceived = DB::table('booking_payment')
                ->where('Booking_ID', $row->Booking_ID)
                ->where('ClientID', $clientId)
                ->whereBetween('Date', [$fromDate, $toDate])
                ->sum('amt_pay');

            $gstTotal = ($booking->agreementAmt * $totalGstPer) / 100;
            $gstTillDate = ($totalReceived * $totalGstPer) / 100;
            $currentGst = ($currentMonthReceived * $totalGstPer) / 100;

            $data[] = [
                'date' => $row->Date,
                'customer_name' => $booking->CutomerName,
                'agreement_amt' => $booking->agreementAmt,
                'gst_per' => $totalGstPer,
                'gst_total' => $gstTotal,
                'received_till' => $totalReceived,
                'gst_till' => $gstTillDate,
                'current_received' => $currentMonthReceived,
                'current_gst' => $currentGst,
            ];

           
        }

        return view('backend.Reports.gst_report', compact(
            'data',
            'fromDate',
            'toDate'
        ));
    }
    public function customerpaymentReport(Request $request)
    {
        if ($request->ajax()) {

            $payments = CustomerPayment::query()
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

                ->addColumn('customer_name', fn($row) => $row->booking_cust->CutomerName ?? '')
                ->addColumn('flat_no', fn($row) => $row->booking_cust->flat->FlatNo ?? '')
                ->addColumn('wing', fn($row) => $row->booking_cust->flat->Wing ?? '')
                ->addColumn('total_cost', fn($row) => $row->booking_cust->TotalFlatAmt)

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
                    return $row->booking_cust->TotalFlatAmt +
                        DB::table('add_bill')
                            ->where('FlatNo', $row->FlatID)
                            ->sum('Amount');
                })

                ->addColumn('bank_sanction', fn($r) => $r->booking_cust->loan_sanction_amt)

                ->addColumn('bank_paid', function ($row) {
                    return CustomerPayment::where('Booking_ID', $row->Booking_ID)
                        ->where('payement_by', 'bank')
                        ->sum('amt_pay');
                })

                ->addColumn('bank_pending', function ($row) {
                    $paid = CustomerPayment::where('Booking_ID', $row->Booking_ID)
                        ->where('payement_by', 'bank')
                        ->sum('amt_pay');

                    return $row->booking_cust->loan_sanction_amt - $paid;
                })

                ->addColumn('self_payment', function ($row) {
                    return $row->booking_cust->TotalFlatAmt - $row->booking_cust->loan_sanction_amt;
                })

                ->addColumn('self_paid', function ($row) {
                    return CustomerPayment::where('Booking_ID', $row->Booking_ID)
                        ->where('payement_by', 'self')
                        ->sum('amt_pay');
                })

                ->addColumn('self_pending', function ($row) {
                    $self = $row->booking_cust->TotalFlatAmt - $row->booking_cust->loan_sanction_amt;

                    $paid = CustomerPayment::where('Booking_ID', $row->Booking_ID)
                        ->where('payement_by', 'self')
                        ->sum('amt_pay');

                    return $self - $paid;
                })

                ->addColumn('total_paid', function ($row) {
                    return CustomerPayment::where('Booking_ID', $row->Booking_ID)->sum('amt_pay');
                })

                ->addColumn('total_pending', function ($row) {
                    $grand = $row->booking_cust->TotalFlatAmt;
                    $paid = CustomerPayment::where('Booking_ID', $row->Booking_ID)->sum('amt_pay');
                    return $grand - $paid;
                })

                ->addColumn('payment_by', fn($r) => ucfirst($r->payement_by))

                ->make(true);
        }

        return view('backend.Reports.customer_payment_report');
    }
    public function CustomerbankPayment(Request $request)
    {
        if ($request->ajax()) {

            $clientId = session('selected_scheme_id');
            $type     = $request->get('type', 'bank'); // bank or self
            $schemeId = $request->get('SCHEME');

            $payments = DB::table('booking_payment')
                ->select('Booking_ID', DB::raw('MIN(FlatID) as FlatID'), 'schemeID', 'payement_by', 'ID')
                ->where('ClientID', $clientId)
                ->when($schemeId, fn($q) => $q->where('schemeID', $schemeId))
                ->where('payement_by', 'bank')
                ->groupBy('Booking_ID','schemeID','payement_by','ID')
                ->orderBy('FlatID')
                ->get();

            $data = [];
            $i = 1;

            foreach ($payments as $pay) {

                // Booking Details
                $booking = DB::table('booking_customer')
                    ->where('ID', $pay->Booking_ID)
                    ->where('ClientID', $clientId)
                    ->first();
                if (!$booking) continue;

                // Flat Details
                $flat = DB::table('flats_details')
                    ->where('ID', $booking->FlatNo)
                    ->where('ClientID', $clientId)
                    ->first();

                // Scheme Details
                $scheme = DB::table('scheme_step1')
                    ->where('ID', $pay->schemeID)
                    ->where('completFalg', 0)
                    ->first();

                // Payment Sums
                $bankPaid = DB::table('booking_payment')
                    ->where('Booking_ID', $pay->Booking_ID)
                    ->where('ClientID', $clientId)
                    ->where('payement_by', 'bank')
                    ->sum('amt_pay');

                $selfPaid = DB::table('booking_payment')
                    ->where('Booking_ID', $pay->Booking_ID)
                    ->where('ClientID', $clientId)
                    ->where('payement_by', 'self')
                    ->sum('amt_pay');

                $downPayment = DB::table('booking_payment')
                    ->where('Booking_ID', $pay->Booking_ID)
                    ->where('type', 'Downpayment')
                    ->sum('amt_pay');

                $billAmount = DB::table('add_bill')
                    ->where('Project_ID', $pay->schemeID)
                    ->where('FlatNo', $pay->FlatID)
                    ->where('ClientID', $clientId)
                    ->sum('Amount');

                $refundAmount = DB::table('customer_refund')
                    ->where('schemeID', $pay->schemeID)
                    ->where('bookingcustomer', $pay->Booking_ID)
                    ->where('ClientID', $clientId)
                    ->sum('amt_pay');

                // Total Calculations
                $totalCost   = $booking->TotalFlatAmt;
                $grandTotal  = $totalCost + $billAmount;

                $bankSanction   = $booking->loan_sanction_amt;
                $bankPending    = $bankSanction - $bankPaid;

                $selfPayment    = $grandTotal - $bankSanction;
                $selfPaidTotal  = $selfPaid + $downPayment;
                $selfPending    = $selfPayment - $selfPaidTotal;

                $totalPaid      = $bankPaid + $selfPaidTotal;
                $totalPending   = $grandTotal - $totalPaid;

                // Stage-wise Payment
                $plinth   = ($booking->pilnth * $bankSanction)/100;
                $slab     = ($booking->slab * $bankSanction)/100;
                $bricks   = ($booking->bricks * $bankSanction)/100;
                $plaster  = ($booking->plaster * $bankSanction)/100;
                $floaring = ($booking->floaring * $bankSanction)/100;
                $plumbing = ($booking->plumbing * $bankSanction)/100;
                $project  = ($booking->project * $bankSanction)/100;

                // Cancel Booking
                $cancel = DB::table('booking_cancel')
                    ->where('BookingID', $pay->Booking_ID)
                    ->where('ClientID', $clientId)
                    ->exists();

                // Actions
        

                $data[] = [
                    'DT_RowIndex'   => $i++,
                    'customer_name' => $booking->CutomerName,
                    'flat_no'       => $flat->FlatNo ?? '',
                    'wing'          => $flat->Wing ?? '',
                    'grand_total'   => $grandTotal,
                    'self_payment'  => $selfPayment,
                    'downpayment'   => $downPayment,
                    'self_paid'     => $selfPaidTotal,
                    'loan_sanction' => $bankSanction,
                    'plinth'        => $plinth,
                    'slab'          => $slab,
                    'bricks'        => $bricks,
                    'plaster'       => $plaster,
                    'floaring'      => $floaring,
                    'plumbing'      => $plumbing,
                    'project'       => $project,
                    'total_paid'    => $totalPaid,
                    'total_pending' => round($totalPending,2),
                    'payment_by'    => $pay->payement_by,
                    
                ];
            }

            return DataTables::of($data)->rawColumns(['action'])->make(true);
        }

        
        return view('backend.Reports.customer_paymentbank');
    }
    public function CustomerSelfPayment(Request $request)
    {
            if ($request->ajax()) {

                $clientId = session('selected_scheme_id');
                $type     = $request->get('type', 'bank'); // bank or self
                $schemeId = $request->get('SCHEME');

                $payments = DB::table('booking_payment')
                    ->select('Booking_ID', DB::raw('MIN(FlatID) as FlatID'), 'schemeID', 'payement_by', 'ID')
                    ->where('ClientID', $clientId)
                    ->when($schemeId, fn($q) => $q->where('schemeID', $schemeId))
                    ->where('payement_by', 'self')
                    ->groupBy('Booking_ID','schemeID','payement_by','ID')
                    ->orderBy('FlatID')
                    ->get();

                $data = [];
                $i = 1;

                foreach ($payments as $pay) {

                    // Booking Details
                    $booking = DB::table('booking_customer')
                        ->where('ID', $pay->Booking_ID)
                        ->where('ClientID', $clientId)
                        ->first();
                    if (!$booking) continue;

                    // Flat Details
                    $flat = DB::table('flats_details')
                        ->where('ID', $booking->FlatNo)
                        ->where('ClientID', $clientId)
                        ->first();

                    // Scheme Details
                    $scheme = DB::table('scheme_step1')
                        ->where('ID', $pay->schemeID)
                        ->where('completFalg', 0)
                        ->first();

                    // Payment Sums
                    $bankPaid = DB::table('booking_payment')
                        ->where('Booking_ID', $pay->Booking_ID)
                        ->where('ClientID', $clientId)
                        ->where('payement_by', 'bank')
                        ->sum('amt_pay');

                    $selfPaid = DB::table('booking_payment')
                        ->where('Booking_ID', $pay->Booking_ID)
                        ->where('ClientID', $clientId)
                        ->where('payement_by', 'self')
                        ->sum('amt_pay');

                    $downPayment = DB::table('booking_payment')
                        ->where('Booking_ID', $pay->Booking_ID)
                        ->where('type', 'Downpayment')
                        ->sum('amt_pay');

                    $billAmount = DB::table('add_bill')
                        ->where('Project_ID', $pay->schemeID)
                        ->where('FlatNo', $pay->FlatID)
                        ->where('ClientID', $clientId)
                        ->sum('Amount');

                    $refundAmount = DB::table('customer_refund')
                        ->where('schemeID', $pay->schemeID)
                        ->where('bookingcustomer', $pay->Booking_ID)
                        ->where('ClientID', $clientId)
                        ->sum('amt_pay');

                    // Total Calculations
                    $totalCost   = $booking->TotalFlatAmt;
                    $grandTotal  = $totalCost + $billAmount;

                    $bankSanction   = $booking->loan_sanction_amt;
                    $bankPending    = $bankSanction - $bankPaid;

                    $selfPayment    = $grandTotal - $bankSanction;
                    $selfPaidTotal  = $selfPaid + $downPayment;
                    $selfPending    = $selfPayment - $selfPaidTotal;

                    $totalPaid      = $bankPaid + $selfPaidTotal;
                    $totalPending   = $grandTotal - $totalPaid;

                    // Stage-wise Payment
                    $plinth   = ($booking->pilnth * $bankSanction)/100;
                    $slab     = ($booking->slab * $bankSanction)/100;
                    $bricks   = ($booking->bricks * $bankSanction)/100;
                    $plaster  = ($booking->plaster * $bankSanction)/100;
                    $floaring = ($booking->floaring * $bankSanction)/100;
                    $plumbing = ($booking->plumbing * $bankSanction)/100;
                    $project  = ($booking->project * $bankSanction)/100;

                    // Cancel Booking
                    $cancel = DB::table('booking_cancel')
                        ->where('BookingID', $pay->Booking_ID)
                        ->where('ClientID', $clientId)
                        ->exists();

                    // Actions
            

                    $data[] = [
                        'DT_RowIndex'   => $i++,
                        'customer_name' => $booking->CutomerName,
                        'flat_no'       => $flat->FlatNo ?? '',
                        'wing'          => $flat->Wing ?? '',
                        'grand_total'   => $grandTotal,
                        'self_payment'  => $selfPayment,
                        'downpayment'   => $downPayment,
                        'self_paid'     => $selfPaidTotal,
                        'total_paid'    => $totalPaid,
                        'total_pending' => round($totalPending,2),
                        'payment_by'    => $pay->payement_by,
                        
                    ];
                }

                return DataTables::of($data)->rawColumns(['action'])->make(true);
            }

            
            return view('backend.Reports.customer_paymentSelf');
        }

        public function lbrpay_report(Request $request)
    {
        if ($request->ajax()) {

        $query = Labour_Work::leftJoin(
        'labour_payment_detail as lpd',
        'lpd.labourworkID',
        '=',
        'labour_work.ID'
    )
    ->select(
        'labour_work.Agency_ID',
        'labour_work.schemeID',

        DB::raw('SUM(DISTINCT labour_work.gtotal) as total_gtotal'),
        DB::raw('SUM(IFNULL(lpd.amt_pay,0)) as total_paid'),
        DB::raw('GROUP_CONCAT(DISTINCT labour_work.ID) as work_ids'),
        DB::raw('MAX(labour_work.Date) as latest_date')
    )
    ->with(['agency','scheme'])
    ->groupBy('labour_work.Agency_ID','labour_work.schemeID');

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('labour_work.Date', [
                $request->from_date,
                $request->to_date
            ]);
        }
        elseif ($request->from_date) {
            $query->where('Date','>=',$request->from_date);
        }
        elseif ($request->to_date) {
            $query->where('Date','<=',$request->to_date);
        }

        if (!empty($request->agency)) {
            $query->where('Agency_ID',$request->agency);
        }
        

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {

                    $search = $request->input('search.value');

                    if (!empty($search)) {

                        // ❌ REMOVE havingRaw
                        // ✅ ADD simple where (safe fix)
    
                        $query->where(function ($q) use ($search) {

                            $q->whereHas('agency', function ($a) use ($search) {
                                $a->where('Name', 'like', "%{$search}%");
                            })
                                ->orWhereHas('scheme', function ($s) use ($search) {
                                    $s->where('Name', 'like', "%{$search}%");
                                })
                                ->orWhere('labour_work.Date', 'like', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()

                ->editColumn('latest_date', function ($row) {
    return \Carbon\Carbon::parse($row->latest_date)->format('d-m-Y');
})
                ->addColumn('Agency', function ($row) {
                    return $row->agency->Name ?? '';
                })

                ->addColumn('Scheme', function ($row) {
                    return $row->scheme->Name ?? '';
                })
                ->addColumn('pending', function ($row) {
                    return $row->total_gtotal - $row->total_paid;
                })

                ->make(true);
        }

        return view('backend.Reports.lbrpay_report');
    }
    
    
    
    public function customerReport()
    {
        $clientId = session('selected_scheme_id');

        $customers = \App\Models\Backend\Booking_Customer::where('ClientID', $clientId)
            ->get();

        return view('backend.Reports.cust_pay', compact('customers'));
    }

    public function customerReportData(Request $request)
    {
        $customerId = $request->customer_id;

        $booking = \App\Models\Backend\Booking_Customer::find($customerId);

        if (!$booking) {
            return response()->json([]);
        }

        $rows = collect();

        // ✅ 1. CUSTOMER PAYMENTS
        $payments = CustomerPayment::where('Booking_ID', $customerId)->get();

        foreach ($payments as $p) {
            $rows->push([
                'date' => $p->Date,
                'receipt_no' => $p->receipt_no,
                'payment_method' => $p->payment_method,
                'payment_by' => ucfirst($p->payement_by),
                'amount' => $p->amt_pay,
                'narration' => $p->narration,
                'type' => $p->type,
            ]);
        }

        // ✅ 2. EXTRA WORK (IMPORTANT)
        $extras = DB::table('add_bill')
            ->where('Project_ID', $booking->Scheme)
            ->where('FlatNo', $booking->FlatNo)
            ->get();

        foreach ($extras as $e) {
            $bill = DB::table('add_bill_detail')->where('Bid', $e->ID)->first();

            $rows->push([
                'date' => $e->Date,
                'receipt_no' => $e->receipt_no ?? '-',
                'payment_method' => 'Extra Work',
                'payment_by' => '-',
                'amount' => $e->Amount,
                'narration' => $bill->billNo ?? '',
                'type' => 'Extra',
            ]);
        }

        // ✅ 3. REFUND (OPTIONAL BUT RECOMMENDED)
        $refunds = DB::table('customer_refund')
            ->where('bookingcustomer', $customerId)
            ->get();

        foreach ($refunds as $r) {
            $rows->push([
                'date' => $r->Date,
                'receipt_no' => $r->ReceiptNo ?? '-',
                'payment_method' => $r->payment_method,
                'payment_by' => '-',
                'amount' => $r->amt_pay,
                'narration' => $r->narration,
                'type' => 'Refund',
            ]);
        }

        // ✅ SORT BY DATE
        $final = $rows->sortBy('date')->values();

        return response()->json($final);
    }

}