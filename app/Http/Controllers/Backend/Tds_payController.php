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
use App\Models\Backend\tds_payment;
use App\Models\Backend\Material;
use App\Models\Backend\Scope_payment_detail;
use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Tds_payController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $clientId = session('selected_scheme_id');

            // 1️⃣ Total TDS from workorder_payment (per contractor)
            $wpSub = DB::table('workorder_payment')
                ->select(
                    'conID',
                    DB::raw('SUM(TDSAmt) as total_tds')
                )
                ->where('ClientID', $clientId)
                ->groupBy('conID');

            // 2️⃣ Paid TDS from tds_payment (per contractor)
            $tpSub = DB::table('tds_payment')
                ->select(
                    'conID',
                    DB::raw('SUM(amt_pay) as paid_tds')
                )
                ->where('ClientID', $clientId)
                ->groupBy('conID');

            // 3️⃣ Calculated TDS from workorder_detail (per contractor)
            $woSub = DB::table('workorder_detail')
                ->select(
                    'ContractorID as conID',
                    DB::raw('SUM(TDSAmt) as calculated_tds')
                )
                ->where('ClientID', $clientId)
                ->groupBy('ContractorID');

            // 4️⃣ Final query
            $query = DB::table('vendor as v')
                ->where('v.Type', 'CONTRACTOR')
                ->where('v.ClientID', $clientId)

                ->leftJoinSub($wpSub, 'wp', 'wp.conID', '=', 'v.ID')
                ->leftJoinSub($tpSub, 'tp', 'tp.conID', '=', 'v.ID')
                ->leftJoinSub($woSub, 'wo', 'wo.conID', '=', 'v.ID')

                ->where(function ($q) {
                    $q->where('wp.total_tds', '>', 0)
                        ->orWhere('wo.calculated_tds', '>', 0);
                })

                ->select([
                    'v.ID as contractor_id',
                    'v.Name as contractor_name',
                    DB::raw('IFNULL(wo.calculated_tds,0) as calculated_tds'),
                    DB::raw('IFNULL(wp.total_tds,0) as total_tds'),
                    DB::raw('IFNULL(tp.paid_tds,0) as paid_tds'),
                    DB::raw('(IFNULL(wp.total_tds,0) - IFNULL(tp.paid_tds,0)) as pending_tds'),
                ]);

            return datatables()->of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value']) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->where('v.Name', 'like', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {

                    $makePaymentUrl = route('Tds_pay.makePayment', $row->contractor_id);
                    $viewPaymentUrl = route('Tds_pay.viewPayment', $row->contractor_id);

                    $actions = '';

                    if (hasPermission('create_tds_payment')) {
                        $actions .= '<a href="' . $makePaymentUrl . '" class="me-2 text-success">
                                    <i class="align-middle" data-feather="dollar-sign"></i>
                                </a>';
                    }

                    if (hasPermission('create_tds_payment')) {
                        $actions .= '<a href="' . $viewPaymentUrl . '" class="me-2 text-info">
                                    <i class="align-middle" data-feather="file-text"></i>
                                </a>';
                    }

                    return $actions;
                })

                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.Tds_pay.index');
    }

    public function viewPayment($id)
    {
        return view('backend.Tds_pay.view_payment', compact('id'));
    }

    public function viewPaymentData(Request $request, $id)
    {

        $query = tds_payment::where('conID', $id);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('Date', function ($row) {
                return Carbon::parse($row->Date)->format('d-m-Y');
            })

            ->addColumn('actions', function ($row) {

                $editUrl = route('Tds_pay.edit', $row->ID);
                $deleteUrl = route('Tds_pay.delete', $row->ID);
                $formId = 'delete-form-' . $row->ID;

                $actions = '';

                if (hasPermission('edit_tds_payment')) {
                    $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                <i class="align-middle" data-feather="edit-2"></i>
                            </a>';
                }

                if (hasPermission('delete_tds_payment')) {
                    $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                                <i class="align-middle" data-feather="trash"></i>
                            </a>
                            <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                ' . csrf_field() . '
                                ' . method_field("DELETE") . '
                            </form>';
                }

                return $actions;
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    public function makePayment($contractorId)
    {
        $clientId = session('selected_scheme_id');

        // 1️⃣ Contractor
        $contractor = Supplier_contractor::where('ID', $contractorId)
            ->where('Type', 'CONTRACTOR')
            ->firstOrFail();

        // 2️⃣ Total TDS (from workorder_payment)
        $totalTds = DB::table('workorder_payment')
            ->where('ClientID', $clientId)
            ->where('conID', $contractorId)
            ->sum('TDSAmt');

        // 3️⃣ Paid TDS (from tds_payment)
        $paidTds = DB::table('tds_payment')
            ->where('ClientID', $clientId)
            ->where('conID', $contractorId)
            ->sum('amt_pay');

        // 4️⃣ Pending TDS
        $pendingTds = round($totalTds - $paidTds, 2);
        if ($pendingTds < 0)
            $pendingTds = 0;

        // 5️⃣ Dropdown data (accounts etc.)
        $schemes = SchemeDetail::where('ID', $clientId)
            ->where('completFalg', 0)
            ->get();

        return view('backend.Tds_pay.create', [
            'contractor' => $contractor,
            'pendingTds' => $pendingTds,
            'totalTds' => $totalTds,
            'paidTds' => $paidTds,
            'schemes' => $schemes,
        ]);
    }

    public function store(Request $request)
    {
        $clientId = session('selected_scheme_id');
        $userId = auth()->id() ?? session('userID');

        // ✅ VALIDATION (your rule kept)
        $request->validate([
            'conID' => 'required',
            'amount_pay' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attr, $value, $fail) use ($request, $clientId) {

                    $pendingTds =
                        DB::table('workorder_payment')
                            ->where('ClientID', $clientId)
                            ->where('conID', $request->conID)
                            ->sum('TDSAmt')
                        - DB::table('tds_payment')
                            ->where('ClientID', $clientId)
                            ->where('conID', $request->conID)
                            ->sum('amt_pay');

                    if ($value > $pendingTds) {
                        $fail('Amount Paid cannot be greater than Payable TDS');
                    }
                }
            ]
        ]);

        // 🔑 IDs
        $paymentId = uniqid();
        $isEPayment = $request->Pay_type === 'e-Payment';

        // 1️⃣ INSERT INTO tds_payment (MAIN RECORD)
        DB::table('tds_payment')->insert([
            'ID' => $paymentId,
            'ClientID' => $clientId,
            'Created' => now(),
            'LastEdited' => now(),
            'payment_date' => date('Y-m-d', strtotime($request->Date)),
            'bankcharge' => ($isEPayment)
                ? (float) $request->bnk_charge
                : 0,

            'amt_pay' => $request->amount_pay,

            'payment_method' => $request->Pay_type,
            'cheque_no' => $request->cheque_no,
            'account_no' => $request->account_no,
            'narration' => $request->narration,

            'conID' => $request->conID,
            'debit_amount' => $request->amount_pay,
            'userID' => $userId,
        ]);
        if (!empty($request->account_no)) {

    Bank_Acc::where('ID', $request->account_no)
        ->decrement('OBalance', (float)$request->amount_pay);

    if ($request->Pay_type === 'e-Payment' && !empty($request->bnk_charge)) {
        Bank_Acc::where('ID', $request->account_no)
            ->decrement('OBalance', (float)$request->bnk_charge);
    }
}

        // 2️⃣ IF e-Payment → INSERT BANK CHARGES INTO daily_trans
        if ($request->Pay_type === 'e-Payment' && !empty($request->bnk_charge)) {

            DB::table('daily_trans')->insert([
                'ID' => uniqid(),
                'ClientID' => $clientId,
                'Date' => date('Y-m-d', strtotime($request->Date)),
                'amt_pay' => $request->bnk_charge,
                'bankcharge' => $request->bnk_charge,

                'payment_method' => $request->Pay_type,
                'cheque_no' => $request->cheque_no,
                'account_no' => $request->account_no,
                'narration' => $request->narration,

                'Description' => 'TDS Payment Charge',
                'bFlag' => 1,
                'Exp_type' => 'E-Payment Bank Charges',
                'PaymentId' => $paymentId,
                'Type_Payment' => 'Office Expence(Bank Charges)',

                'userID' => $userId,
            ]);
        }

        // 3️⃣ REDIRECT
        return redirect()
            ->route('Tds_pay')
            ->with('success', 'TDS Payment has been added successfully!');
    }

    public function edit($paymentId)
    {
        $clientId = session('selected_scheme_id');

        // 1️⃣ Load existing TDS payment
        $payment = DB::table('tds_payment')
            ->where('ClientID', $clientId)
            ->where('ID', $paymentId)
            ->first();

        // 2️⃣ Contractor
        $contractor = Supplier_contractor::where('ID', $payment->conID)
            ->where('Type', 'CONTRACTOR')
            ->firstOrFail();

        // 3️⃣ Total TDS (from workorder_payment)
        $totalTds = DB::table('workorder_payment')
            ->where('ClientID', $clientId)
            ->where('conID', $payment->conID)
            ->sum('TDSAmt');

        // 4️⃣ Paid TDS EXCLUDING current payment
        $paidTds = DB::table('tds_payment')
            ->where('ClientID', $clientId)
            ->where('conID', $payment->conID)
            ->where('ID', '!=', $paymentId)
            ->sum('amt_pay');

        // 5️⃣ Pending TDS (add back current payment)
        $pendingTds = round($totalTds - $paidTds, 2);
        if ($pendingTds < 0)
            $pendingTds = 0;

        // 6️⃣ Dropdown data
        $schemes = SchemeDetail::where('ID', $clientId)
            ->where('completFalg', 0)
            ->get();

        return view('backend.Tds_pay.create', [
            // shared with makePayment()
            'contractor' => $contractor,
            'pendingTds' => $pendingTds,
            'totalTds' => $totalTds,
            'paidTds' => $paidTds,

            // edit-specific
            'payment' => $payment,
            'isEdit' => true,
            'schemes' => $schemes,
        ]);
    }

    public function update(Request $request, $paymentId)
    {
        $clientId = session('selected_scheme_id');
        $userId = auth()->id() ?? session('userID');

        // 🔐 VALIDATION
        $request->validate([
            'Date' => 'required|date_format:d-m-Y',
            'conID' => 'required',
            'Pay_type' => 'required|in:cash,cheque,e-Payment',
            'account_no' => 'required',
            'amount_pay' => 'required|numeric|min:0.01',
            'bnk_charge' => 'nullable|numeric|min:0',
            'cheque_no' => 'nullable|string|max:150',
            'narration' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {

            /* -------------------------------------------------
             * 1️⃣ DELETE OLD ENTRIES (LEGACY BEHAVIOR)
             * ------------------------------------------------- */

            // delete bank charge entry
            DB::table('daily_trans')
                ->where('ClientID', $clientId)
                ->where('PaymentId', $paymentId)
                ->delete();

            // delete old TDS payment
            DB::table('tds_payment')
                ->where('ClientID', $clientId)
                ->where('ID', $paymentId)
                ->delete();

            /* -------------------------------------------------
             * 2️⃣ INSERT NEW TDS PAYMENT (NEW ID)
             * ------------------------------------------------- */

            $newPaymentId = uniqid();
            $isEPayment = $request->Pay_type === 'e-Payment';

            DB::table('tds_payment')->insert([
                'ID' => $newPaymentId,
                'ClientID' => $clientId,
                'Created' => now(),
                'LastEdited' => now(),
                'payment_date' => date('Y-m-d', strtotime($request->Date)),

                'amt_pay' => $request->amount_pay,
                'bankcharge' => $isEPayment ? (float) $request->bnk_charge : 0,

                'payment_method' => $request->Pay_type,
                'account_no' => $request->account_no,
                'cheque_no' => $request->cheque_no,
                'narration' => $request->narration,

                'conID' => $request->conID,
                'debit_amount' => $request->amount_pay,
                'userID' => $userId,
            ]);

            /* -------------------------------------------------
             * 3️⃣ INSERT BANK CHARGES (IF e-Payment)
             * ------------------------------------------------- */

            if ($isEPayment && !empty($request->bnk_charge)) {
                DB::table('daily_trans')->insert([
                    'ID' => uniqid(),
                    'ClientID' => $clientId,
                    'Date' => date('Y-m-d', strtotime($request->Date)),
                    'amt_pay' => $request->bnk_charge,
                    'bankcharge' => $request->bnk_charge,

                    'payment_method' => $request->Pay_type,
                    'account_no' => $request->account_no,
                    'cheque_no' => $request->cheque_no,
                    'narration' => $request->narration,

                    'Description' => 'TDS Payment Charge',
                    'bFlag' => 1,
                    'Exp_type' => 'E-Payment Bank Charges',
                    'PaymentId' => $newPaymentId,
                    'Type_Payment' => 'Office Expence(Bank Charges)',

                    'userID' => $userId,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('Tds_pay')
                ->with('success', 'TDS Payment updated successfully!');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->route('Tds_pay')
                ->with('error', 'Failed to update TDS payment.');
        }
    }

    public function destroy($id)
    {
        $clientId = session('selected_scheme_id');

        try {

            DB::table('tds_payment')
                ->where('ClientID', $clientId)
                ->where('ID', $id)
                ->delete();

            return redirect()
                ->route('Tds_pay')
                ->with('success', 'TDS Payment deleted successfully!');

        } catch (\Exception $e) {

            return redirect()
                ->route('Tds_pay')
                ->with('error', 'Failed to delete TDS payment.');
        }
    }

}