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
use App\Models\Backend\Agency;
use App\Models\Backend\Labour_pay;
use App\Models\Backend\Labour_Work;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Labour_payController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
    {
        if ($request->ajax()) {

        $clientId = session('selected_scheme_id');

            $query = Labour_Work::select(
                'Agency_ID',
                'schemeID',

                DB::raw('SUM(gtotal) as total_gtotal'),

                DB::raw('GROUP_CONCAT(ID) as work_ids'),

                DB::raw('MAX(Date) as latest_date'),

                // ✅ NEW
                DB::raw('(
        SELECT COALESCE(SUM(lpd.amt_pay),0)
        FROM labour_payment_detail lpd
        WHERE FIND_IN_SET(lpd.labourworkID, GROUP_CONCAT(labour_work.ID))
    ) as paid_amount')

            )
    ->with(['agency','scheme'])
    ->where('ClientID',$clientId)
    ->groupBy('Agency_ID','schemeID')
    ->orderByDesc(DB::raw('MAX(Date)'));

// if ($request->from_date && $request->to_date) {
//     $query->whereBetween('Date', [
//         $request->from_date,
//         $request->to_date
//     ]);
// }
// elseif ($request->from_date) {
//     $query->where('Date','>=',$request->from_date);
// }
// elseif ($request->to_date) {
//     $query->where('Date','<=',$request->to_date);
// }

if (!empty($request->agency)) {
    $query->where('Agency_ID',$request->agency);
}



            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) {

                    $search = request('search.value');

                    if (!empty($search)) {

                        $query->where(function ($q) use ($search) {

                            $q->where('gtotal', 'like', "%{$search}%")

                            ->orWhereHas('agency', function ($sq) use ($search) {
                                $sq->where('Name', 'like', "%{$search}%");
                            })

                            ->orWhereHas('scheme', function ($sq) use ($search) {
                                $sq->where('Name', 'like', "%{$search}%");
                            });

                        });
                    }

                }, false)
               ->editColumn('latest_date', function ($row) {

                    return \Carbon\Carbon::parse($row->latest_date)
                            ->format('d-m-Y');

                })
                ->addColumn('Agency', function ($row) {
                    return $row->agency->Name ?? '';
                })

                ->addColumn('Scheme', function ($row) {
                    return $row->scheme->Name ?? '';
                })
                // ✅ PAID AMOUNT
                ->addColumn('paid_amount', function ($row) {

                    return '₹ ' . number_format($row->paid_amount, 2);

                })

                // ✅ PENDING AMOUNT
                ->addColumn('pending_amount', function ($row) {

                    $pending = $row->total_gtotal - $row->paid_amount;

                    if ($pending < 0) {
                        $pending = 0;
                    }

                    return '₹ ' . number_format($pending, 2);

                })
                ->addColumn('actions', function ($row) {

                    $makePaymentUrl = route('Labour_work_pay.makePayment',  $row->work_ids);
                    $viewPaymentUrl = route('Labour_work_pay.viewPayment', $row->work_ids);
                    $detailUrl = route('Labour_work_pay.details', $row->work_ids);

                    $actions = '';

                    if (hasPermission('create_labour_work_payment')) {
                        $actions .= '<a href="'.$makePaymentUrl.'" class="me-2 text-success" title="Make Payment">
                                         <span style="font-weight:bold;font-size:20px;">₹</span>
                                    </a>';
                    }

                    if (hasPermission('create_labour_work_payment')) {
                        $actions .= '<a href="'.$viewPaymentUrl.'" class="me-2 text-info" title="View Payments">
                                        <i class="align-middle" data-feather="file-text"></i>
                                    </a>';
                    }

                    $actions .= '<a href="'.$detailUrl.'" class="me-2 text-info" title="View Sub Entries">
                                    <i data-feather="list"></i>
                                </a>';

                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.Labour_payment.index');
    }

public function paymentDetails(Request $request, $ids)
{
    $clientId = session('selected_scheme_id');
    $workIds  = explode(',', $ids);

    $query = DB::table('labour_payment_detail')
        ->join('labour_work','labour_work.ID','=','labour_payment_detail.labourworkID')
        ->where('labour_payment_detail.ClientID',$clientId)
        ->whereIn('labour_payment_detail.labourworkID',$workIds)
        ->select(
            'labour_work.Date as work_date',
            'labour_work.gtotal as total_gtotal',
            'labour_payment_detail.amt_pay as paid_amt'
        );

    if ($request->ajax()) {

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('work_date', function ($row) {
                return Carbon::parse($row->work_date)->format('d-m-Y');
            })
            ->make(true);
    }

    return view('backend.Labour_payment.payment_details', [
        'ids' => $ids
    ]);
}

public function viewPayment($ids)
{
    $workIds = explode(',', $ids);

    $gtotal = Labour_Work::whereIn('ID', $workIds)->sum('gtotal');

    return view('backend.Labour_payment.view_payment', [
        'ids'    => $ids,
        'gtotal' => $gtotal
    ]);
}


public function viewPaymentData(Request $request, $ids)
{
    $clientId = session('selected_scheme_id');

    $workIds = explode(',', $ids);

    // 🔹 Get master payment IDs from detail table
    $paymentIds = DB::table('labour_payment_detail')
        ->where('ClientID', $clientId)
        ->whereIn('labourworkID', $workIds)
        ->pluck('lbr_pay_id')
        ->unique()
        ->toArray();

    // 🔹 Fetch master payments
    $query = DB::table('labour_payment')
        ->where('ClientID', $clientId)
        ->whereIn('ID', $paymentIds)
        ->orderBy('Created', 'desc');

    return DataTables::of($query)
        ->addIndexColumn()

        ->editColumn('Date', function ($row) {
            return Carbon::parse($row->Date)->format('d-m-Y');
        })

        ->addColumn('actions', function ($row) {

            $editUrl   = route('Labour_work_pay.edit', $row->ID);
            $deleteUrl = route('Labour_work_pay.delete', $row->ID);
             $printUrl  = route('Labour_work_pay.print', $row->ID);
            $formId    = 'delete-form-'.$row->ID;

            $btn = '';

            if (hasPermission('edit_labour_work_payment')) {
                $btn .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                            <i data-feather="edit-2"></i>
                         </a>';
            }

            if (hasPermission('delete_labour_work_payment')) {
                $btn .= '<a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
                            <i data-feather="trash"></i>
                         </a>
                         <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                            '.csrf_field().'
                            '.method_field("DELETE").'
                         </form>';
            }

             $btn .= '<a href="'.$printUrl.'" target="_blank" class="me-2 text-success">
                    <i class="align-middle" data-feather="printer"></i>
                 </a>';

            return $btn;
        })

        ->rawColumns(['actions'])
        ->make(true);
}

public function printPayment($id)
{
    $clientId = session('selected_scheme_id');

    $data = DB::table('labour_payment as lp')
        ->leftJoin('agency as a', 'a.ID', '=', 'lp.AgencyID')
        ->select(
            'lp.*',
            'a.Name as ContractorName'
        )
        ->where('lp.ClientID', $clientId)
        ->where('lp.ID', $id)
        ->first();

    if(!$data){
        abort(404,'Payment not found');
    }

    $client = DB::table('company_settings')->first();

    $date = Carbon::parse($data->Date)->format('d/m/Y');

    return view('backend.Labour_payment.print_payment', [
        'data'   => $data,
        'date'   => $date,
        'client' => $client
    ]);
}

    public function makePayment($ids)
{
    $clientId = session('selected_scheme_id');

    $idsArray = explode(',', $ids);

    // 🔹 Load selected works
    $works = Labour_Work::where('ClientID', $clientId)
        ->whereIn('ID', $idsArray)
        ->get();

    // 🔹 Calculate pending per work
    foreach ($works as $work) {

        $paid = DB::table('labour_payment_detail')
            ->where('ClientID', $clientId)
            ->where('labourworkID', $work->ID)
            ->sum('amt_pay');

        $work->pending = max(0, $work->gtotal - $paid);
    }

    $totalPending = $works->sum('pending');

    return view('backend.Labour_payment.create', [
        'works'        => $works,
        'ids'          => $ids,
        'totalPending' => $totalPending
    ]);
}

public function store(Request $request)
{
    // --------------------
    // 1. Validation
    // --------------------
    $request->validate([
        "Date"        => "required|date_format:d-m-Y",
        "Pay_type"    => "required|in:cash,cheque,e-Payment",
        "account_no"  => "required",
        "amount_pay"  => "required|numeric|min:0",
        "payable"     => "required|numeric|min:0",
        "cheque_no"   => "nullable|required_if:Pay_type,cheque,e-Payment",
        "bnk_charge"  => "nullable|numeric|min:0",
        "narration"   => "nullable|string|max:500",
        "payments"    => "required|array"
    ]);

    DB::beginTransaction();

    try {

        // --------------------
        // 2. Basic values
        // --------------------
        $clientId   = session('selected_scheme_id');
        $userId     = Auth::id();
        $masterId   = uniqid();
        $bankCharge = $request->bnk_charge ?? 0;

        $date = Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d');

        // --------------------
        // 3. Insert MASTER
        // --------------------

        // Get next receipt number
        $maxReceipt = DB::table('labour_payment')
            ->max('receipt_no');

        $nextReceiptNo = $maxReceipt ? $maxReceipt + 1 : 1; 

        DB::table('labour_payment')->insert([

            "ID"            => $masterId,
            "ClientID"      => $clientId,
            "Date"          => $date,

            "payment_method"=> $request->Pay_type,
            "account_no"    => $request->account_no,
            "cheque_no"     => $request->cheque_no,

            "amt_pay" => $request->amount_pay,
            "bankcharge"    => $bankCharge,
            "Payable"       => $request->payable,

            "narration"     => $request->narration,
            "paydetail"     => $request->narration ?? '',
            'receipt_no' => $nextReceiptNo, 

            "AgencyID"      => $request->AgencyID,
            "reconciliation"=> 0,

            "Created"       => now(),
            "Lastedited"    => now(),
            "userID"        => $userId
        ]);

        // --------------------
        // 4. Insert DETAILS
        // --------------------
        foreach ($request->payments as $workId => $amount) {

            if ($amount > 0) {

                DB::table('labour_payment_detail')->insert([

                    "ID"            => uniqid(),
                    "ClientID"      => $clientId,
                    "lbr_pay_id"      => $masterId,
                    "Date"          => $date,
                    "labourworkID"  => $workId,
                    "amt_pay"       => $amount,

                    "Created"       => now(),
                    "Lastedited"    => now()
                ]);
            }
        }

        // --------------------
        // 5. Bank Charges Entry (if E-Payment)
        // --------------------
        if ($request->Pay_type === 'e-Payment' && $bankCharge > 0) {

            DB::table('daily_trans')->insert([
                'ID'             => uniqid(),
                'ClientID'       => $clientId,
                'Date'           => $date,
                'amt_pay'        => $bankCharge,
                'bankcharge'     => $bankCharge,
                'payment_method' => $request->Pay_type,
                'cheque_no'      => $request->cheque_no,
                'account_no'     => $request->account_no,
                'narration'      => $request->narration,
                'Description'    => 'Partners Payment Charge',
                'bFlag'          => 1,
                'Exp_type'       => 'E-Payment Bank Charges',
                'PaymentId'      => $masterId,
                'Type_Payment'   => 'Office Expense(Bank Charges)',
                'userID'         => $userId,
            ]);
        }
        // Deduct account balance
        Bank_Acc::where('ID', $request->account_no)
            ->decrement('OBalance', (float)$request->payable);
        DB::commit();

        return redirect()
            ->route("Labour_work_pay")
            ->with("success", "Payment added successfully!");

    } catch (\Exception $e) {

        DB::rollback();
        return back()->withErrors($e->getMessage());
    }
}

public function edit($id)
{
    $clientId = session('selected_scheme_id');

    // 🔹 Master payment (Eloquent)
    $payment = Labour_pay::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

    // 🔹 Detail rows
    $details = DB::table('labour_payment_detail')
        ->where('ClientID', $clientId)
        ->where('lbr_pay_id', $id)
        ->get();

    // 🔹 Get work IDs
    $workIds = $details->pluck('labourworkID')->toArray();

    // 🔹 Load works
    $works = Labour_Work::whereIn('ID', $workIds)->get();

    // 🔹 Calculate pending per work
    foreach ($works as $work) {

        $paid = DB::table('labour_payment_detail')
            ->where('ClientID', $clientId)
            ->where('labourworkID', $work->ID)
            ->where('lbr_pay_id', '!=', $id)
            ->sum('amt_pay');

        $work->pending = max(0, $work->gtotal - $paid);
    }

    return view('backend.Labour_payment.edit', compact(
        'payment',
        'works',
        'details'
    ));
}

public function update(Request $request, $id)
{
    $request->validate([
        "Date"        => "required|date_format:d-m-Y",
        "Pay_type"    => "required|in:cash,cheque,e-Payment",
        "account_no"  => "required",
        "amount_pay"  => "required|numeric|min:0",
        "payable"     => "required|numeric|min:0",
        "cheque_no"   => "nullable|required_if:Pay_type,cheque,e-Payment",
        "bnk_charge"  => "nullable|numeric|min:0",
        "narration"   => "nullable|string|max:500",
        "payments"    => "required|array"
    ]);

    DB::beginTransaction();

    try {

        $clientId   = session('selected_scheme_id');
        $userId     = Auth::id();
        $bankCharge = ($request->bnk_charge === null || $request->bnk_charge === '')
                ? 0
                : $request->bnk_charge;
        $date       = Carbon::createFromFormat('d-m-Y',$request->Date)->format('Y-m-d');

        // 🔹 Update MASTER
        DB::table('labour_payment')
            ->where('ID',$id)
            ->update([

                "Date"          => $date,
                "payment_method"=> $request->Pay_type,
                "account_no"    => $request->account_no,
                "cheque_no"     => $request->cheque_no,

                "amt_pay"       => $request->amount_pay,
                "bankcharge"    => $bankCharge,
                "Payable"       => $request->payable,

                "narration"     => $request->narration,
                "paydetail"     => $request->narration,
                "Lastedited"    => now(),
                "userID"        => $userId
            ]);

        // 🔹 Delete old detail rows
        DB::table('labour_payment_detail')
            ->where('ClientID',$clientId)
            ->where('lbr_pay_id',$id)
            ->delete();

        // 🔹 Reinsert new detail rows
        foreach ($request->payments as $workId => $amount) {

            if ($amount > 0) {

                DB::table('labour_payment_detail')->insert([
                    "ID"           => uniqid(),
                    "ClientID"     => $clientId,
                    "lbr_pay_id"   => $id,
                    "Date"         => $date,
                    "labourworkID" => $workId,
                    "amt_pay"      => $amount,
                    "Created"      => now(),
                    "Lastedited"   => now()
                ]);
            }
        }

        // 🔹 Remove old bank charge
        DB::table('daily_trans')
            ->where('ClientID',$clientId)
            ->where('PaymentId',$id)
            ->where('Exp_type','E-Payment Bank Charges')
            ->delete();

        // 🔹 Reinsert bank charge if needed
        if ($request->Pay_type === 'e-Payment' && $bankCharge > 0) {

            DB::table('daily_trans')->insert([
                'ID'             => uniqid(),
                'ClientID'       => $clientId,
                'Date'           => $date,
                'amt_pay'        => $bankCharge,
                'bankcharge'     => $bankCharge,
                'payment_method' => $request->Pay_type,
                'cheque_no'      => $request->cheque_no,
                'account_no'     => $request->account_no,
                'narration'      => $request->narration,
                'Description'    => 'Partners Payment Charge',
                'bFlag'          => 1,
                'Exp_type'       => 'E-Payment Bank Charges',
                'PaymentId'      => $id,
                'Type_Payment'   => 'Office Expense(Bank Charges)',
                'userID'         => $userId,
            ]);
        }

        // Deduct account balance
        Bank_Acc::where('ID', $request->account_no)
            ->decrement('OBalance', (float)$request->payable);

        DB::commit();

        return redirect()
            ->route('Labour_work_pay')
            ->with('success','Payment updated successfully');

    } catch (\Exception $e) {

        DB::rollback();
        return back()->withErrors($e->getMessage());
    }
}

public function destroy($id)
{
    $clientId = session('selected_scheme_id');

    DB::beginTransaction();

    try {

        // 🔹 Fetch master payment
        $payment = Labour_pay::where('ClientID', $clientId)
            ->where('ID', $id)
            ->firstOrFail();

        // 🔹 Delete related detail rows FIRST
        DB::table('labour_payment_detail')
            ->where('ClientID', $clientId)
            ->where('lbr_pay_id', $id)
            ->delete();

        // 🔹 Delete related bank charge entry
        DB::table('daily_trans')
            ->where('ClientID', $clientId)
            ->where('PaymentId', $id)
            ->where('Exp_type', 'E-Payment Bank Charges')
            ->delete();

        // 🔹 Delete master payment
        $payment->delete();

        DB::commit();

        return redirect()
            ->route('Labour_work_pay')
            ->with('success', 'Payment deleted successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->route('Labour_work_pay')
            ->with('error', 'Failed to delete payment.');
    }
}

}