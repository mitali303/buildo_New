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
use App\Models\Backend\Partner_load;
use App\Models\Backend\Daily_trans;
use App\Models\Backend\Partners_Investor_Loan;
use App\Models\Backend\Scope_payment_detail;
use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Partner_payController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private function resolvePtype(string $type): int
    {
        return $type === 'investor' ? 1 : 0;
    }

public function index(Request $request, string $type)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');
        $ptype    = $this->resolvePtype($type);

        $query = DB::table('partners_loan as pl')
            ->leftJoin('partners as p', 'p.ID', '=', 'pl.partners')
            ->where('pl.ClientID', $clientId)
            ->where('pl.Ptype', $ptype);

            // ✅ DATE FILTER
            // if ($request->from_date && $request->to_date) {
            //     $query->whereBetween('pl.Date', [
            //         $request->from_date,
            //         $request->to_date
            //     ]);
            // } elseif ($request->from_date) {
            //     $query->where('pl.Date', '>=', $request->from_date);
            // } elseif ($request->to_date) {
            //     $query->where('pl.Date', '<=', $request->to_date);
            // }

            $query ->groupBy('pl.partners', 'p.Name')
            ->select(
                'pl.partners',
                'p.Name as partner_name',

                // ✅ CREDIT = Received
                DB::raw("SUM(CASE WHEN pl.paytype = 'Received' THEN pl.amt_pay ELSE 0 END) as credit"),

                // ✅ DEBIT = Paid
                DB::raw("SUM(CASE WHEN pl.paytype = 'Paid' THEN pl.amt_pay ELSE 0 END) as debit"),

                DB::raw('MAX(pl.Date) as last_payment_date')
            );

        return DataTables::of($query)
                ->filter(function ($query) use ($request) {

                    $search = $request->input('search.value');

                    if (!empty($search)) {
                        $query->havingRaw("
            p.Name LIKE ?
            OR SUM(CASE WHEN pl.paytype = 'Received' THEN pl.amt_pay ELSE 0 END) LIKE ?
            OR SUM(CASE WHEN pl.paytype = 'Paid' THEN pl.amt_pay ELSE 0 END) LIKE ?
        ", [
                            "%{$search}%",
                            "%{$search}%",
                            "%{$search}%"
                        ]);
                    }
                })
            ->addIndexColumn()

            ->addColumn('partner', fn ($row) => $row->partner_name)

            ->addColumn('credit', fn ($row) =>
                number_format($row->credit, 2)
            )

            ->addColumn('debit', fn ($row) =>
                number_format($row->debit, 2)
            )

            ->addColumn('balance', function ($row) {
                return number_format($row->credit - $row->debit, 2);
            })

            ->addColumn('last_payment_date', fn ($row) =>
                Carbon::parse($row->last_payment_date)->format('d-m-Y')
            )

            ->addColumn('actions', function ($row) use ($type) {
                return '
                                    <a href="' . route('Partner_pay.mainpay', [
                    'type'    => $type,
                    'partner' => $row->partners
                ]) . '" class="text-primary fw-bold" title="View Details">
                    <i class="fas fa-eye"></i>
                </a>';
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.Partner_pay.index', ['type' => $type]);
}



public function mainpay(Request $request, string $type, $partner)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');
        $ptype    = $this->resolvePtype($type);

        try {
            $query = Partner_load::with(['Partner'])
                ->select([
                    'ID',
                    'Date',
                    'payment_method',
                    'paytype', 
                    'amt_pay',
                    'partners',
                    'narration'
                ])
                ->where('ClientID', $clientId)
                ->where('Ptype', $ptype)
                ->where('partners', $partner); // ✅ FILTER HERE

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('Date', fn ($row) =>
                    Carbon::parse($row->Date)->format('d-m-Y')
                )

                ->addColumn('partners', fn ($row) =>
                    optional($row->Partner)->Name ?? '-'
                )

                ->addColumn('credit', function ($row) {
                    return $row->paytype === 'Received'
                        ? number_format($row->amt_pay, 2)
                        : '0.00';
                })

                ->addColumn('debit', function ($row) {
                    return $row->paytype === 'Paid'
                        ? number_format($row->amt_pay, 2)
                        : '0.00';
                })
                
                ->filterColumn('partners', function ($query, $keyword) {
                    $query->whereHas('Partner', function ($q) use ($keyword) {
                        $q->where('Name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('actions', function ($row) use ($type) {

                    $editUrl = route('Partner_pay.edit', [
                        'type' => $type,
                        'id'   => $row->ID
                    ]);

                    $deleteUrl = route('Partner_pay.delete', [
                        'type' => $type,
                        'id'   => $row->ID
                    ]);

                    $formId = 'delete-form-' . $row->ID;

                    $actions = '';

                    if (hasPermission('edit_partners_payment')) {
                        $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_partners_payment')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
                                        <i class="align-middle" data-feather="trash"></i>
                                    </a>
                                    <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                                        '.csrf_field().'
                                        '.method_field("DELETE").'
                                    </form>';
                    }

                    return $actions;
                })

                ->rawColumns(['actions'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    return view('backend.Partner_pay.mainpay', [
        'type'    => $type,
        'partner' => $partner
    ]);
}


public function create(string $type)
{
    $clientId = Session::get('selected_scheme_id');
    $ptype = $this->resolvePtype($type);

    $partners = DB::table('partners')
        ->where('Type', $ptype === 0 ? 'PARTNER' : 'INVESTOR')
        ->get();

    return view('backend.Partner_pay.create', [
        'partners'   => $partners,
        'type'     => $type,
        'payment'  => null 
    ]);
}

public function store(Request $request, string $type)
{
    
   $validator = Validator::make($request->all(), [
    'Date'         => 'required|date_format:d-m-Y',
    'paymenttype'  => 'required|in:Paid,Received',
    'Pay_type'     => 'required|in:cash,cheque,e-Payment',
    'partner'      => 'required',
    'amount_pay'   => 'required|numeric|min:1',

    'account_no'   => 'required_if:paymenttype,Paid|required_unless:Pay_type,cash',
        "cheque_no"     => "required_if:Pay_type,cheque,e-Payment|nullable|string|max:150",

    // ✅ only basic rule here
    'bnk_charge'   => 'nullable|numeric|min:0',

    'narration'    => 'nullable|string',
]);

$validator->after(function ($validator) use ($request) {

    // ✅ enforce AND condition
    if ($request->paymenttype === 'paid' && $request->Pay_type === 'e-Payment') {

        if ($request->bnk_charge === null || $request->bnk_charge === '') {
            $validator->errors()->add(
                'bnk_charge',
                'Bank charge is required for Paid e-Payment'
            );
        }
    }
});


    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    DB::beginTransaction();

    try {

        $clientId = session('selected_scheme_id');
        $id       = uniqid();

        $isPaid      = $request->paymenttype === 'Paid';
        $isEPayment  = $request->Pay_type === 'e-Payment';
        
        $ptype = $this->resolvePtype($type);

        /* ---------------------------------
         | 1️⃣ PARTNERS LOAN
         ---------------------------------*/
        $paymentDate = Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d');

        $exists = DB::table('partners_loan')
            ->where('ClientID', $clientId)
            ->where('Date', $paymentDate)
            ->where('partners', $request->partner)
            ->where('amt_pay', $request->amount_pay)
            ->where('paytype', $request->paymenttype)
            ->where('payment_method', $request->Pay_type)
            ->exists();

        if ($exists) {
            return redirect()
                ->route('Partner_pay', ['type' => $type])
                ->with('error', 'Duplicate transaction detected.');
        }

        DB::table('partners_loan')->insert([
            'ID'             => $id,
            'ClientID'       => $clientId,
            'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'amt_pay'        => $request->amount_pay,

            // ✅ bank charge only if PAID + e-Payment
            'bankcharge'     => ($isPaid && $isEPayment)
                                ? (float) $request->bnk_charge
                                : 0,

            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,
            'narration'      => $request->narration,

            'paytype'        => $request->paymenttype, // paid / recieve
            'partners'       => $request->partner,

            'Ptype'          => $ptype,
            'paydetail'      => $request->paydetail ?? null,
            'type'           => $request->payment_type,
            'userID'      => Auth::id(),
        ]);

        
        /* ---------------------------------
         | 2️⃣ BANK CHARGES (ONLY PAID + e-Payment)
         ---------------------------------*/
         
        if ($isPaid && $isEPayment) {
            DB::table('daily_trans')->insert([
                'ID'             => uniqid(),
                'ClientID'       => $clientId,
                'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'amt_pay'        => $request->bnk_charge,
                'bankcharge'        => $request->bnk_charge,
                'payment_method' => $request->Pay_type,
                'cheque_no'      => $request->cheque_no,
                'account_no'     => $request->account_no,
                'narration'      => $request->narration,
                'Description'    => 'Partners Payment Charge',
                'bFlag'          => 1,
                'Exp_type'       => 'E-Payment Bank Charges',
                'PaymentId'      => $id,
                'Type_Payment'   => 'Office Expense(Bank Charges)',
                'userID'      => Auth::id(),
            ]);
        }
        Bank_Acc::where('ID', $request->account_no)
            ->decrement('OBalance', (float)$request->amount_pay);
        DB::commit();

        return redirect()
            ->route('Partner_pay',['type' => $type])
            ->with('success', 'Record Added Successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}

public function edit(string $type, $id)
{
    $clientId = session('selected_scheme_id');
    $ptype    = $this->resolvePtype($type);

    // 🔹 Load partner payment
    $payment = DB::table('partners_loan')
    ->where('ClientID', $clientId)
    ->where('Ptype', $ptype)
    ->where('ID', $id)
    ->first();

    if (!$payment) {
        abort(404, 'Record not found');
    }


    // 🔹 Load bank charge entry (if exists)
    $bankCharge = DB::table('daily_trans')
        ->where('ClientID', $clientId)
        ->where('PaymentId', $id)
        ->where('Exp_type', 'E-Payment Bank Charges')
        ->first();

    // 🔹 Load partners for dropdown
    $ptype = $this->resolvePtype($type);

    $partners = DB::table('partners')
        ->where('Type', $ptype === 0 ? 'PARTNER' : 'INVESTOR')
        ->get();
        
    // 🔹 Load bank accounts
    $accounts = Bank_Acc::where('ClientID', $clientId)->get();

    return view('backend.Partner_pay.create', [
        'payment'    => $payment,
        'partners'   => $partners,
        'accounts'   => $accounts,
        'bankCharge' => $bankCharge, // may be null
        'type'     => $type,
    ]);
}


public function update(Request $request, string $type, $id)
{
    // ✅ VALIDATION (same logic as store)
    $validator = Validator::make($request->all(), [
        'Date'         => 'required|date_format:d-m-Y',
        'paymenttype'  => 'required|in:Paid,recieve',
        'Pay_type'     => 'required|in:cash,cheque,e-Payment',
        'partner'      => 'required',
        'amount_pay'   => 'required|numeric|min:1',

        'account_no'   => 'required_if:paymenttype,Paid|required_unless:Pay_type,cash',
                "cheque_no"     => "required_if:Pay_type,cheque,e-Payment|nullable|string|max:150",


        'bnk_charge'   => 'nullable|numeric|min:0',
        'narration'    => 'nullable|string',
    ]);

    $validator->after(function ($validator) use ($request) {
        if ($request->paymenttype === 'Paid' && $request->Pay_type === 'e-Payment') {
            if ($request->bnk_charge === null || $request->bnk_charge === '') {
                $validator->errors()->add(
                    'bnk_charge',
                    'Bank charge is required for Paid e-Payment'
                );
            }
        }
    });

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    DB::beginTransaction();

    try {

        $clientId = session('selected_scheme_id');
        $userId   = Auth::id();

        $isPaid     = $request->paymenttype === 'Paid';
        $isEPayment = $request->Pay_type === 'e-Payment';

        $ptype = $this->resolvePtype($type);

        /* ---------------------------------
         | 1️⃣ UPDATE partners_loan
         ---------------------------------*/

        DB::table('partners_loan')
            ->where('ClientID', $clientId)
            ->where('ID', $id)
            ->update([
                'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'amt_pay'        => $request->amount_pay,

                'bankcharge'     => ($isPaid && $isEPayment)
                                    ? (float) $request->bnk_charge
                                    : 0,

                'payment_method' => $request->Pay_type,
                'cheque_no'      => $request->cheque_no,
                'account_no'     => $request->account_no,
                'narration'      => $request->narration,

                'paytype'        => $request->paymenttype,
                'partners'       => $request->partner,

                'Ptype'          => $ptype,
                'paydetail'      => $request->paydetail ?? null,
                'type'           => $request->payment_type,

                'userID'         => $userId,
            ]);

        /* ---------------------------------
         | 2️⃣ REMOVE OLD BANK CHARGE ENTRY
         ---------------------------------*/
        DB::table('daily_trans')
            ->where('ClientID', $clientId)
            ->where('PaymentId', $id)
            ->where('Exp_type', 'E-Payment Bank Charges')
            ->delete();

        /* ---------------------------------
         | 3️⃣ INSERT BANK CHARGE AGAIN (IF NEEDED)
         ---------------------------------*/
        if ($isPaid && $isEPayment) {
            DB::table('daily_trans')->insert([
                'ID'             => uniqid(),
                'ClientID'       => $clientId,
                'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'amt_pay'        => $request->bnk_charge,
                'bankcharge'     => $request->bnk_charge,
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

        DB::commit();

        return redirect()
            ->route('Partner_pay',['type' => $type])
            ->with('success', 'Record Updated Successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}


public function destroy(string $type, $id)
{
    $clientId = session('selected_scheme_id');
    $ptype    = $this->resolvePtype($type);

    DB::beginTransaction();

    try {

        // 🔹 Delete bank charge entry (if exists)
        DB::table('daily_trans')
            ->where('ClientID', $clientId)
            ->where('PaymentId', $id)
            ->where('Exp_type', 'E-Payment Bank Charges')
            ->delete();

        // 🔹 Delete partner payment
        DB::table('partners_loan')
            ->where('ClientID', $clientId)
            ->where('Ptype', $ptype)
            ->where('ID', $id)
            ->delete();

        DB::commit();

        return redirect()
            ->route('Partner_pay', ['type' => $type])
            ->with('success', 'Partner payment deleted successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->route('Partner_pay', ['type' => $type])
            ->with('error', 'Failed to delete partner payment.');
    }
}

}