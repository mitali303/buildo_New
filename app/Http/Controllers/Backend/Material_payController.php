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
use App\Models\Backend\Inv_Payment;
use App\Models\Backend\Inv_Payment_Detail;
use App\Models\Backend\Scope_payment_detail;
use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class Material_payController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');

        // 🔹 Subquery: invoice total per vendor
        $invoiceTotalSub = DB::table('inv_detail')
            ->select(
                'purchasefrom',
                'ClientID',
                DB::raw('SUM(GTotal) as total_invoice')
            )
            ->where('ClientID', $clientId)
            ->groupBy('purchasefrom', 'ClientID');

        $query = Inv_Payment::query()
            ->select([
                'inv_payment.PurchaseFrom',
                'inv_payment.schemeID',

                DB::raw('MAX(inv_payment.payment_date) as payment_date'),
                DB::raw('SUM(inv_payment_detail.amt_pay) as total_paid'),
                DB::raw('COALESCE(inv_tot.total_invoice, 0) as total_invoice'),
            ])
            ->leftJoin(
                'inv_payment_detail',
                'inv_payment_detail.Payment_ID',
                '=',
                'inv_payment.ID'
            )
            ->leftJoinSub($invoiceTotalSub, 'inv_tot', function ($join) {
                $join->on('inv_tot.purchasefrom', '=', 'inv_payment.PurchaseFrom')
                     ->on('inv_tot.ClientID', '=', 'inv_payment.ClientID');
            })
            ->where('inv_payment.ClientID', $clientId);

            // ✅ ADD THIS BLOCK
            if ($request->from_date && $request->to_date) {
                $query->whereBetween('inv_payment.payment_date', [
                    $request->from_date,
                    $request->to_date
                ]);
            } elseif ($request->from_date) {
                $query->where('inv_payment.payment_date', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $query->where('inv_payment.payment_date', '<=', $request->to_date);
            }

            $query->groupBy(
                'inv_payment.PurchaseFrom',
                'inv_payment.schemeID',
                'inv_tot.total_invoice'   // ✅ REQUIRED
            )
            ->with(['vendor', 'scheme']);

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('Date', fn ($row) =>
                Carbon::parse($row->payment_date)->format('d-m-Y')
            )

            ->addColumn('PurchaseFrom', fn ($row) =>
                optional($row->vendor)->Name ?? '-'
            )

            ->addColumn('scheme', fn ($row) =>
                optional($row->scheme)->Name ?? '-'
            )

            ->addColumn('invoice_total', fn ($row) =>
                number_format($row->total_invoice, 2)
            )

            ->addColumn('amt_pay', fn ($row) =>
                number_format($row->total_paid, 2)
            )

->addColumn('actions', function ($row) {

    $url = route('Material_pay.invoicewise', $row->PurchaseFrom);

    return "
        <a href='{$url}' class='text-info' title='Invoice-wise Payment'>
            <i data-feather='file-text'></i>
        </a>
    ";
})

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.Material_pay.index');
}

public function invoiceWise(Request $request, $supplierId)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');

       $query = DB::table('inv_payment_detail')
    ->select([
        'inv_payment_detail.Invoice_No',

        // invoice info
        'inv_detail.Invno as invoice_number',
        'inv_detail.GTotal as invoice_total',

        // sums
        DB::raw('SUM(inv_payment_detail.amt_pay) as total_paid'),
        DB::raw('SUM(inv_payment_detail.extra) as total_extra'),

        // ✅ NEW: debit used
        DB::raw('SUM(inv_payment.debit_amount) as debit_used'),

        DB::raw('MAX(inv_payment.Created) as last_payment_date'),
    ])
    ->join(
        'inv_payment',
        'inv_payment.ID',
        '=',
        'inv_payment_detail.Payment_ID'
    )
    ->join(
        'inv_detail',
        'inv_detail.ID',
        '=',
        'inv_payment_detail.Invoice_No'
    )
    ->where('inv_payment.ClientID', $clientId)
    ->where('inv_payment.PurchaseFrom', $supplierId)
    ->groupBy(
        'inv_payment_detail.Invoice_No',
        'inv_detail.Invno',
        'inv_detail.GTotal'
    );


        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('InvoiceNo', fn ($row) => $row->invoice_number)

            ->addColumn('InvoiceTotal', fn ($row) =>
                number_format($row->invoice_total, 2)
            )

            ->addColumn('PaidAmount', fn ($row) =>
                number_format($row->total_paid, 2)
            )

            ->addColumn('Extra', fn ($row) =>
                number_format($row->total_extra, 2)
            )

            ->addColumn('LastPaymentDate', fn ($row) =>
                Carbon::parse($row->last_payment_date)->format('d-m-Y')
            )

            ->addColumn('DebitUsed', fn ($row) =>
                number_format($row->debit_used ?? 0, 2)
            )

            ->addColumn('TotalPaid', function ($row) {
                $paid  = (float) ($row->total_paid ?? 0);
                $extra = (float) ($row->total_extra ?? 0);

                return number_format($paid + $extra, 2);
            })
            ->addColumn('actions', function ($row) {

                $url = route('Material_pay.mainpay', [
                    'invoice' => $row->Invoice_No
                ]);

                return "
                    <a href='{$url}' class='text-info' title='View Payments'>
                        <i data-feather='eye'></i>
                    </a>
                ";
            })
            ->rawColumns(['actions'])

            ->make(true);
    }

    return view('backend.Material_pay.invoicewise');
}

public function mainpayment(Request $request)
{
    if ($request->ajax()) {

        $clientId  = session('selected_scheme_id');
        $invoiceId = $request->invoice; // 🔥 coming from invoiceWise

        try {

            $query = Inv_Payment::with(['vendor', 'scheme'])
                ->select([
                    'inv_payment.ID',
                    'inv_payment.payment_date',
                    'inv_payment.amt_pay',
                    'inv_payment.PurchaseFrom',
                    'inv_payment.schemeID'
                ])
                ->where('inv_payment.ClientID', $clientId)

                // 🔥 FILTER ONLY PAYMENTS THAT CONTAIN THIS INVOICE
                ->whereExists(function ($q) use ($invoiceId) {
                    $q->select(DB::raw(1))
                        ->from('inv_payment_detail')
                        ->whereColumn(
                            'inv_payment_detail.Payment_ID',
                            'inv_payment.ID'
                        )
                        ->where('inv_payment_detail.Invoice_No', $invoiceId);
                });

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('Date', fn ($row) =>
                    Carbon::parse($row->payment_date)->format('d-m-Y')
                )

                ->addColumn('PurchaseFrom', fn ($row) =>
                    optional($row->vendor)->Name ?? '-'
                )

                ->addColumn('scheme', fn ($row) =>
                    optional($row->scheme)->Name ?? '-'
                )

                ->addColumn('actions', function ($row) {

                    $editUrl   = route('Material_pay.edit', $row->ID);
                    $deleteUrl = route('Material_pay.delete', $row->ID);
                    $formId    = 'delete-form-' . $row->ID;

                    $actions = '';

                    if (hasPermission('edit_material_payment')) {
                        $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_material_payment')) {
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
                'error'   => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    return view('backend.Material_pay.mainpay');
}



public function create()
{
    $clientId = Session::get('selected_scheme_id');

    $schemes = SchemeDetail::where('ID', $clientId)
        ->where('completFalg', 0)
        ->get();

    $vendors = Supplier_contractor::where('Type', 'VENDOR')
        ->where('ClientID', $clientId)
        ->get();

    // 🔥 LOAD UNPAID / ALL INVOICES (adjust condition if needed)
    $invoices = Inv_Detail::where('ClientID', $clientId)
        ->select('ID', 'Invno', 'GTotal')
        ->orderBy('ID', 'DESC')
        ->get();

    return view('backend.Material_pay.create', [
        'schemes'   => $schemes,
        'vendors'   => $vendors,
        'invoices'  => $invoices,
    ]);
}
public function getInvoicesBySupplier(Request $request)
{
    $clientId   = Session::get('selected_scheme_id');
    $supplierId = $request->supplier_id;

    $oldInvoiceIds = $request->old_invoice_id ?? [];
    $checkedInvoiceIds = $request->checked_invoice ?? [];
$oldAmt        = $request->old_amt ?? [];
$oldExtra      = $request->old_extra ?? [];

    if (!$supplierId) {
        return response()->json(['html' => '']);
    }

    $invoices = Inv_Detail::where('ClientID', $clientId)
        ->where('purchasefrom', $supplierId) // 🔥 FILTER HERE
        ->select('ID', 'Invno', 'GTotal')
        ->orderBy('ID', 'DESC')
        ->get();

    $html = '';

    foreach ($invoices as $inv) {

         // 🔥 TOTAL ALREADY PAID
        $paid = DB::table('inv_payment_detail')
            ->where('Invoice_No', $inv->ID)
            ->sum('amt_pay');

        // 🔥 TOTAL EXTRA
        $extra = DB::table('inv_payment_detail')
            ->where('Invoice_No', $inv->ID)
            ->sum('extra');

        // 🔥 REMAINING BALANCE
        $remaining = $inv->GTotal - $paid;
        
        if ($remaining <= 0) {
            $remaining = 0;
        }

                 $editUrl = route('PurchaseInvoice.edit', $inv->ID);



        // find index only for values
        $index = array_search($inv->ID, $checkedInvoiceIds);

      $checked = in_array($inv->ID, $checkedInvoiceIds);

$amtVal   = $checked ? ($oldAmt[$inv->ID]   ?? '') : '';
$extraVal = $checked ? ($oldExtra[$inv->ID] ?? '') : '';



        $html .= "
        <tr class='invoice-row'>
         
            <td>
                <input type='checkbox'
                    class='select-row' name='checked_invoice[]' value='{$inv->ID}'
                    onchange='toggleRow(this)' 
                    ".($checked ? 'checked' : '')."
                    ".($remaining == 0 ? 'disabled' : '').">

            </td>

            <td class='text-center'>
                <input type='hidden' name='invoice_id[]' value='{$inv->ID}'>

                <a href='{$editUrl}'
                   class='btn btn-sm btn-primary'
                   target='_blank'>
                    View / Edit
                </a>

                <div class='text-muted small mt-1'>
                    {$inv->Invno}
                </div>
            </td>

            <td>
                <input type='text' class='form-control payamount'
                       value='{$remaining}' readonly>
            </td>

            <td>
                <input type='text' name='amt[{$inv->ID}]'
                    class='form-control amt'
                    value='{$amtVal}'
                    ".(!$checked ? 'disabled' : '')."
                    oninput='calculateBalance(this)'>

            </td>

            <td>
                <input type='text' name='extra[{$inv->ID}]'
                    class='form-control extra'
                    value='{$extraVal}'
                    ".(!$checked ? 'disabled' : '')."
                    oninput='updateTotal()'>

            </td>

            <td>
                <input type='text' class='form-control balance' readonly>
            </td>
        </tr>";
    }

    return response()->json(['html' => $html]);
}


public function getRetain(Request $request)
{
    $vendor = DB::table('vendor')
        ->where('ID', $request->vendor_id)
        ->select('retain_per')
        ->first();

    return response()->json([
        'retain_per' => $vendor->retain_per ?? 0
    ]);
}

public function store(Request $request)
{

    // ✅ 1. VALIDATION
    $validator = Validator::make($request->all(), [
    'Date'         => 'required|date_format:d-m-Y',
    'PurchaseFrom' => 'required',
    'Destination'  => 'required',
    'pay_type'     => 'required',
    'Pay_type' => 'required_if:pay_type,payment',
    'account_no' => 'required_if:pay_type,payment',
    "cheque_no"     => "required_if:Pay_type,cheque,e-Payment|nullable|string|max:150",

    // arrays must exist
    'checked_invoice' => 'required|array|min:1',
    'invoice_id'   => 'required|array',
    'amt'          => 'required|array',
    'extra'        => 'nullable|array',

    // numeric rules
    'amt.*'        => 'nullable|numeric|min:0',
    'extra.*'      => 'nullable|numeric|min:0',
]);
$validator->after(function ($validator) use ($request) {

    foreach ($request->checked_invoice ?? [] as $invoiceId) {

        $amount = $request->amt[$invoiceId] ?? null;

        if ($amount === null || $amount === '' || $amount <= 0) {
            $validator->errors()->add(
                "amt.$invoiceId",
                "Amount is required and must be greater than 0"
            );
        }

        if (!is_numeric($amount)) {
            $validator->errors()->add(
                "amt.$invoiceId",
                "Amount must be a number"
            );
        }
    }
});


if ($validator->fails()) {
    return back()
        ->withErrors($validator)
        ->withInput()
        ->with('reload_invoices', true);
}


    DB::beginTransaction();

    try {
        $clientId = Session::get('selected_scheme_id');
        $paymentId = uniqid();

        // dd($request->amount_pay);
        /* -------------------------------------------------
         | 2. INSERT INTO inv_payment (MAIN PAYMENT)
         -------------------------------------------------*/
        DB::table('inv_payment')->insert([
            'ID'             => $paymentId,
            'ClientID'       => $clientId,
            'Created'        => now(),
            'LastEdited'     => now(),
            'payment_date'   => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'amt_pay'        => $request->amount_pay,
            'bankcharge' => ($request->filled('bnk_charge') && $request->bnk_charge !== '')
                ? (float) $request->bnk_charge
                : 0,            
            'payment_method'=> $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'debit_amount'    => (float) ($request->amtuse ?: 0),
            'account_no'     => $request->account_no,
            'narration'      => $request->narration,
            'PurchaseFrom'   => $request->PurchaseFrom,
            'schemeID'       => $request->Destination,
            'Type'           => $request->pay_type ?? 'payment',
            'userID'      => Auth::id(),
        ]);

        /* -------------------------------------------------
         | 3. INSERT INTO inv_payment_detail (INVOICE WISE)
         -------------------------------------------------*/
        $invoiceIds = $request->invoice_id ?? [];
        $amounts    = $request->amt ?? [];
        $extras     = $request->extra ?? [];
        $checkedIds = $request->checked_invoice ?? [];

        foreach ($request->checked_invoice as $invoiceId) {

    $amount = $request->amt[$invoiceId] ?? 0;
    $extra  = $request->extra[$invoiceId] ?? 0;

    if ($amount <= 0 && $extra <= 0) {
        continue;
    }

    DB::table('inv_payment_detail')->insert([
        'ID'         => uniqid(),
        'ClientID'   => $clientId,
        'Invoice_No' => $invoiceId,
        'Payment_ID'=> $paymentId,
        'amt_pay'    => $amount,
        'extra'      => $extra,
        'Type'       => $request->pay_type ?? 'payment',
        'userID'     => Auth::id(),
        'Created'    => now(),
        'LastEdited' => now(),
    ]);
}


        /* -------------------------------------------------
         | 4. INSERT BANK CHARGES (ONLY e-Payment)
         -------------------------------------------------*/
        if ($request->Pay_type === 'e-Payment') {

            DB::table('site_expences')->insert([
                'ID'             => uniqid(),
                'ClientID'       => $clientId,
                'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'amt_pay'        => $request->bnk_charge,
                'payment_method'=> 'e-Payment',
                'bankcharge' => ($request->filled('bnk_charge') && $request->bnk_charge !== '')
                ? (float) $request->bnk_charge
                : 0, 
                'cheque_no'      => $request->cheque_no,
                'account_no'     => $request->account_no,
                'narration'      => $request->narration,
                'title'          => 'Invoice Payment Bank Charges',
                'schemeID'       => $request->Destination,
                'Exp_type'       => 'E-Payment Bank Charges',
                'PaymentId'      => $paymentId,
                'Type_Payment'   => 'Site Expense(Bank Charges)',
                'bFlag'          => 1,
                'userID'      => Auth::id(),
            ]);
        }

        DB::commit();

        return redirect()
            ->route('Material_pay')
            ->with('success', 'Record has been added successfully');

    } catch (\Exception $e) {
        DB::rollBack();

        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}


public function getDebitBySupplier(Request $request)
{
    $supplierId = $request->supplier_id;
    $paymentId  = $request->payment_id; // optional (edit case)

    if (!$supplierId) {
        return response()->json(['debit' => 0]);
    }

    /* -----------------------------------------
     | 1️⃣ TOTAL REJECTED MATERIAL VALUE
     -----------------------------------------*/
    $finalTotal = DB::table('rejected_matrial_detail')
        ->where('purchasefrom', $supplierId)
        ->select(DB::raw('SUM(rejected_qty * Rate) as total'))
        ->value('total') ?? 0;

    /* -----------------------------------------
     | 2️⃣ INVOICE RETURN (Income_Payment)
     -----------------------------------------*/
    $invoiceReturn = DB::table('income_payment')
        ->where('vendor', $supplierId)
        ->sum('amt_pay');

    /* -----------------------------------------
     | 3️⃣ ALREADY USED DEBIT (EXCLUDE CURRENT PAYMENT)
     -----------------------------------------*/
    $usedDebit = DB::table('inv_payment')
        ->where('PurchaseFrom', $supplierId)
        ->when($paymentId, function ($q) use ($paymentId) {
            $q->where('ID', '!=', $paymentId);
        })
        ->sum('debit_amount');

    /* -----------------------------------------
     | 4️⃣ EXTRA PAYMENTS
     -----------------------------------------*/
    $extra = DB::table('inv_payment_detail')
        ->join('inv_detail', 'inv_payment_detail.Invoice_no', '=', 'inv_detail.ID')
        ->where('inv_detail.purchasefrom', $supplierId)
        ->when($paymentId, function ($q) use ($paymentId) {
            $q->where('inv_payment_detail.Payment_ID', '!=', $paymentId);
        })
        ->sum('inv_payment_detail.extra');

    /* -----------------------------------------
     | 5️⃣ FINAL DEBIT AVAILABLE
     -----------------------------------------*/
    $debitAvailable = ($finalTotal - ($invoiceReturn + $usedDebit)) + $extra;

    if ($debitAvailable < 0) {
        $debitAvailable = 0;
    }

    return response()->json([
        'debit' => round($debitAvailable, 2)
    ]);
}


public function edit($id)
{
    $clientId = session('selected_scheme_id');

    /* -----------------------------------------
     | 1️⃣ MAIN PAYMENT
     -----------------------------------------*/
    $payment = Inv_Payment::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

    /* -----------------------------------------
     | 2️⃣ PAYMENT DETAILS (INVOICE WISE)
     -----------------------------------------*/
    $paymentDetails = Inv_Payment_Detail::where('ClientID', $clientId)
        ->where('Payment_ID', $id)
        ->get()
        ->keyBy('Invoice_No');

    /* -----------------------------------------
     | 3️⃣ ALL INVOICES OF THIS SUPPLIER
     -----------------------------------------*/
    $invoices = Inv_Detail::where('ClientID', $clientId)
        ->where('purchasefrom', $payment->PurchaseFrom)
        ->select('ID', 'Invno', 'GTotal')
        ->orderBy('ID', 'DESC')
        ->get();

    /* -----------------------------------------
     | 4️⃣ BUILD ROWS FOR BLADE
     -----------------------------------------*/
    $rows = [];

    foreach ($invoices as $inv) {

        $detail = $paymentDetails->get($inv->ID);

        // amount paid in THIS payment
        $currentPaid  = $detail->amt_pay ?? 0;
        $currentExtra = $detail->extra ?? 0;

        // amount paid in OTHER payments
        $alreadyPaid = DB::table('inv_payment_detail')
            ->where('Invoice_No', $inv->ID)
            ->where('Payment_ID', '!=', $payment->ID) // 🔑 exclude current
            ->sum('amt_pay');

        // remaining payable
        $remaining = $inv->GTotal - $alreadyPaid;
        if ($remaining < 0) $remaining = 0;

        $rows[] = [
            'invoice_id' => $inv->ID,
            'invno'      => $inv->Invno,

            // ✅ FIXED VALUES
            'payamount'  => $remaining,
            'amt'        => $currentPaid,
            'extra'      => $currentExtra,
            'balance'    => $remaining - $currentPaid,
            'checked'    => $detail ? true : false,
        ];
    }

    /* -----------------------------------------
     | 5️⃣ DROPDOWNS
     -----------------------------------------*/
    $schemes = SchemeDetail::where('ID', $clientId)
        ->where('completFalg', 0)
        ->get();

    $vendors = Supplier_contractor::where('Type', 'VENDOR')
        ->where('ClientID', $clientId)
        ->get();

    /* -----------------------------------------
     | 6️⃣ RETURN SAME CREATE VIEW
     -----------------------------------------*/
    return view('backend.Material_pay.create', [
        'invoice'  => $payment,   // 🔥 important (used everywhere)
        'rows'     => $rows,
        'schemes'  => $schemes,
        'vendors'  => $vendors,
    ]);
}


public function update(Request $request, $id)
{
    // ✅ 1. VALIDATION (same spirit as store)
    $validator = Validator::make($request->all(), [
        'Date'         => 'required|date_format:d-m-Y',
        'PurchaseFrom' => 'required',
        'Destination'  => 'required',
        'pay_type'     => 'required',
        'Pay_type' => 'required_if:pay_type,payment',
        'account_no' => 'required_if:pay_type,payment',
        "cheque_no"     => "required_if:Pay_type,cheque,e-Payment|nullable|string|max:150",

        'checked_invoice' => 'required|array|min:1',
        'invoice_id'      => 'required|array',
        'amt'             => 'required|array',
        'extra'           => 'nullable|array',

        'amt.*'   => 'nullable|numeric|min:0',
        'extra.*' => 'nullable|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return back()
            ->withErrors($validator)
            ->withInput()
            ->with('reload_invoices', true);
    }

    DB::beginTransaction();

    try {
        $clientId = Session::get('selected_scheme_id');
        $userId   = Auth::id() ?? session('userID');

        $oldPaymentId = $id;

        /* -----------------------------------------
         | 1️⃣ DELETE OLD RECORDS (LEGACY)
         -----------------------------------------*/

        // site_expences
        DB::table('site_expences')
            ->where('ClientID', $clientId)
            ->where('PaymentId', $oldPaymentId)
            ->delete();

        // inv_payment_detail
        DB::table('inv_payment_detail')
            ->where('ClientID', $clientId)
            ->where('Payment_ID', $oldPaymentId)
            ->delete();

        // inv_payment
        DB::table('inv_payment')
            ->where('ClientID', $clientId)
            ->where('ID', $oldPaymentId)
            ->delete();

        /* -----------------------------------------
         | 2️⃣ INSERT NEW PAYMENT (NEW ID)
         -----------------------------------------*/

        $newPaymentId = uniqid();

        DB::table('inv_payment')->insert([
            'ID'            => $newPaymentId,
            'ClientID'      => $clientId,
            'Created'       => now(),
            'LastEdited'    => now(),
            'payment_date' => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'amt_pay'       => $request->amount_pay,
            'bankcharge'    => $request->filled('bnk_charge') ? (float)$request->bnk_charge : 0,
            'payment_method'=> $request->Pay_type,
            'cheque_no'     => $request->cheque_no,
            'account_no'    => $request->account_no,
            'narration'     => $request->narration,
            'PurchaseFrom'  => $request->PurchaseFrom,
            'schemeID'      => $request->Destination,
            'debit_amount'  => (float)($request->amtuse ?? 0),
            'Type'          => $request->Adustment,
            'userID'        => $userId,
        ]);

        /* -----------------------------------------
         | 3️⃣ INSERT NEW INVOICE DETAILS
         -----------------------------------------*/

        foreach ($request->checked_invoice as $invoiceId) {

            $amount = (float) ($request->amt[$invoiceId] ?? 0);
            $extra  = (float) ($request->extra[$invoiceId] ?? 0);

            if ($amount <= 0 && $extra <= 0) {
                continue;
            }

            DB::table('inv_payment_detail')->insert([
                'ID'          => uniqid(),
                'ClientID'    => $clientId,
                'Created'     => now(),
                'LastEdited'  => now(),
                'Invoice_No'  => $invoiceId,
                'Payment_ID'  => $newPaymentId,
                'amt_pay'     => $amount,
                'extra'       => $extra,
                'Type'        => $request->Adustment ?? 'payment',
                'userID'      => $userId,
            ]);
        }

        /* -----------------------------------------
         | 4️⃣ BANK CHARGES (e-Payment)
         -----------------------------------------*/

        if ($request->Pay_type === 'e-Payment') {

            DB::table('site_expences')->insert([
                'ID'            => uniqid(),
                'ClientID'      => $clientId,
                'Date'          => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'amt_pay'       => $request->bnk_charge,
                'payment_method'=> 'e-Payment',
                'cheque_no'     => $request->cheque_no,
                'account_no'    => $request->account_no,
                'narration'     => $request->narration,
                'bankcharge'    => $request->filled('bnk_charge') ? (float)$request->bnk_charge : 0,
                'title'         => 'Invoice Payment Bank Charges',
                'schemeID'      => $request->Destination,
                'Exp_type'      => 'E-Payment Bank Charges',
                'PaymentId'     => $newPaymentId,
                'Type_Payment'  => 'Site Expense(Bank Charges)',
                'bFlag'         => 1,
                'userID'        => $userId,
            ]);
        }

        DB::commit();

        return redirect()
            ->route('Material_pay')
            ->with('success', 'Record has been updated successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}


public function destroy($id)
{
    $clientId = Session::get('selected_scheme_id');

    DB::beginTransaction();

    try {

        /* -----------------------------------------
         | 1️⃣ FETCH PAYMENT (SAFETY CHECK)
         -----------------------------------------*/
        $payment = Inv_Payment::where('ClientID', $clientId)
            ->where('ID', $id)
            ->firstOrFail();

        /* -----------------------------------------
         | 2️⃣ DELETE INVOICE PAYMENT DETAILS
         -----------------------------------------*/
        DB::table('inv_payment_detail')
            ->where('ClientID', $clientId)
            ->where('Payment_ID', $id)
            ->delete();

        /* -----------------------------------------
         | 3️⃣ DELETE BANK CHARGES (IF ANY)
         -----------------------------------------*/
        DB::table('site_expences')
            ->where('PaymentId', $id)
            ->where('ClientID', $clientId)
            ->delete();

        /* -----------------------------------------
         | 4️⃣ DELETE MAIN PAYMENT
         -----------------------------------------*/
        $payment->delete();

        DB::commit();

        return redirect()
            ->route('Material_pay')
            ->with('success', 'Material payment deleted successfully');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->route('Material_pay')
            ->with('error', 'Failed to delete material payment');
    }
}


}
