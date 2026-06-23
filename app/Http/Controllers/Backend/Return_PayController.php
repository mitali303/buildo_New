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
use App\Models\Backend\Income_Payment;
use App\Models\Backend\Inv_Detail;
use App\Models\Backend\Inv_Product;
use App\Models\Backend\Workorder_details;
use App\Models\Backend\Workorder_material;
use App\Models\Backend\Workorder_payment;
use App\Models\Backend\Material;
use App\Models\Backend\Site_expences;
use App\Models\Backend\Daily_trans;
use App\Models\Backend\Partners_Investor_Loan;
use App\Models\Backend\Scope_payment_detail;
use App\Models\Backend\Rejected_Material;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Return_PayController extends Controller
{

public function index(Request $request)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');

        try {
            $query = Income_Payment::with(['scheme'])
                ->select([
                    'ID',
                    'Date',
                    'payment_method',
                    'amt_pay', 
                    'Type_Payment',
                    'paydetail',
                    'schemeID',
                    'narration'
                ])
                ->where('ClientID', $clientId)
                ->orderBy('Date', 'DESC');

                // ✅ DATE FILTER
                if ($request->from_date && $request->to_date) {
                    $query->whereBetween('Date', [
                        $request->from_date,
                        $request->to_date
                    ]);
                } elseif ($request->from_date) {
                    $query->where('Date', '>=', $request->from_date);
                } elseif ($request->to_date) {
                    $query->where('Date', '<=', $request->to_date);
                }
                
            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('Date', fn ($row) =>
                    Carbon::parse($row->Date)->format('d-m-Y')
                )

                ->addColumn('schemeID', fn ($row) =>
                    optional($row->scheme)->Name ?? '-'
                )

                ->addColumn('actions', function ($row) {

                    $editUrl = route('Return_pay.edit', [
                        'id' => $row->ID
                    ]);

                    $deleteUrl = route('Return_pay.delete', [
                        'id' => $row->ID
                    ]);

                    $formId = 'delete-form-' . $row->ID;

                    $actions = '';

                    if (hasPermission('edit_return_payment')) {
                        $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_return_payment')) {
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

    return view('backend.Return_pay.index');
}

public function getEmployeeSalary(Request $request)
{
    $employee = Staff::where('ID', $request->employee_id)->first();

    if (!$employee) {
        return response()->json(['error' => true], 404);
    }

    return response()->json([
        'salary_type'  => $employee->SalaryType,
        'salary_amount' => $employee->DailyWage,
    ]);
}
 public function getContractorInvoices(Request $request)
    {
        $contractorId = $request->contractor_id;
        
        $invoices = Workorder_details::where('ContractorID', $contractorId)
            ->select('ID', 'WorkorderNo')
            ->orderBy('WorkorderNo', 'desc')
            ->get();

        return response()->json($invoices);
    }

 public function getSupplierOrder(Request $request)
    {
        $supplierId  = $request->supplier_id;
        // dd($supplierId);
        
       $orders = Rejected_Material::where('purchasefrom', $supplierId)
            ->select(
                'Invno',
                DB::raw('MAX(ID) as ID'), 
                DB::raw('MAX(srno) as srno')
            )
            ->groupBy('Invno')
            ->orderByDesc('srno')
            ->get();

        return response()->json($orders->values());
    }

public function getConInvoicesPending(Request $request)
{
    $invoiceId = $request->invoice_id;
    $paymentId = $request->payment_id; // 👈 current edit ID (nullable)

    // Total workorder amount
    $totalAmount = Workorder_payment::where('WorkorderID', $invoiceId)
        ->sum('amt_pay');

    // Paid amount (exclude current record if edit)
    $paidQuery = Income_Payment::where('WorkorderID', $invoiceId);

    if (!empty($paymentId)) {
        $paidQuery->where('ID', '!=', $paymentId);
    }

    $paidamt = $paidQuery->sum('amt_pay');

    $pendingAmount = $totalAmount - $paidamt;

    return response()->json([
        'pending' => max(0, $pendingAmount)
    ]);
}

public function getSupOrderPending(Request $request)
{
     $orderId = $request->order_id;

    // Sum of paid amount for that workorder
    $totalAmount = Rejected_Material::where('Invno', $orderId)
        ->sum('Total');

    $paidamt = Income_Payment::where('Invno', $orderId)
        ->sum('amt_pay');

    $pendingAmount = $totalAmount - $paidamt;

    return response()->json([
        'pending' => $pendingAmount
    ]);
}
public function getOrderDetails(Request $request)
{
    $orderId = $request->order_id;
    $paymentId = $request->payment_id;

   $totalAmount = Rejected_Material::where('Invno', $orderId)
        ->sum('Total');

    // 2️⃣ Paid Amount (exclude current payment)
    $paidQuery = Income_Payment::where('Invno', $orderId);

    if (!empty($paymentId)) {
        $paidQuery->where('ID', '!=', $paymentId);
    }

    $paidamt = $paidQuery->sum('amt_pay');

    // 3️⃣ Pending
    $pendingAmount = $totalAmount - $paidamt;

    // Order items with material details
    $items = Rejected_Material::from('rejected_matrial_detail as r')
        ->join('material as m', 'm.ID', '=', 'r.Material')
        ->where('r.Invno', $orderId)
        ->get([
            'm.Name as Name',
            'm.Type as Type',
            'm.Unit',
            'r.Qty',
            'r.rejected_qty',
            'r.Rate',
            'r.Disc',
            'r.CGST',
            'r.SGST',
            'r.IGST',
            'r.Total'
        ]);

    return response()->json([
        'pending' => $pendingAmount,
        'items'   => $items
    ]);
}

public function getMaterialTransferBalance(Request $request)
{
    $schemeId = $request->scheme_id;
    $clientId = session('selected_scheme_id');

    // 1️⃣ Total transferred amount to this scheme
    $transferAmount = DB::table('transfer_material')
        ->where('To_site', $schemeId)
        // ->where('ClientID', $clientId)
        ->sum('Amount');

    $paidQuery = DB::table('income_payment')
    ->where('schemeID', $schemeId)
    ->where('IncomeType', 'Matrial Transfer')
    ->where('ClientID', $clientId);

    if ($request->payment_id) {
        $paidQuery->where('ID', '!=', $request->payment_id);
    }

    $paidAmount = $paidQuery->sum('amt_pay');

    // 3️⃣ Pending amount
    $pending = round($transferAmount - $paidAmount, 2);

    return response()->json([
        'pending' => $pending
    ]);
}

public function create()
{
    $clientId = Session::get('selected_scheme_id');

   $contractors = Supplier_contractor::where('Type', 'CONTRACTOR')
        ->where('ClientID', $clientId)
        ->get();

   $suppliers = Supplier_contractor::where('Type', 'VENDOR')
        ->where('ClientID', $clientId)
        ->get();

    $schemes = SchemeDetail::where('ID', $clientId)
        ->where('completFalg', 0)
        ->get();

    $employes = Staff::get();

    $expences = Income_Payment::select('IncomeType')->distinct()->get();
    return view('backend.Return_pay.create', [
        'schemes'   => $schemes,
        'expences'   => $expences,
        'employes'   => $employes,
        'contractors'   => $contractors,
        'suppliers'   => $suppliers,
    ]);
}


public function store(Request $request)
{
    $clientId = session('selected_scheme_id');
    $userId   = Auth::id();
    
    /* -------------------------------------------------
     | 1️⃣ BASE VALIDATION (COMMON FOR ALL)
     -------------------------------------------------*/
    $rules = [
        'Date'        => ['required', 'date_format:d-m-Y'],
        'Destination'=> ['required'], // scheme
        'Exp_type'    => ['required'],
        'Pay_type'    => ['required'],
        'account_no'  => ['required'],
        'amount_pay'  => ['required', 'numeric', 'gt:0'],
        'narration'   => ['nullable', 'string'],
        'Description'=> ['nullable', 'string'],
    ];

    /* -------------------------------------------------
     | 2️⃣ CONDITIONAL VALIDATIONS
     -------------------------------------------------*/

     // Payment method based validation
    if ($request->Pay_type === 'epay') {
        $rules['cheque_no'] = ['required'];
    }

    if ($request->Pay_type === 'cheque') {
        $rules['cheque_no'] = ['required'];
    }

    // Contractor
    if ($request->Exp_type === 'Contractor') {
        $rules['contractor'] = ['required'];
        $rules['invoice_id'] = ['required'];
    }

    // Return / Rejected Material
    if ($request->Exp_type === 'Return/Rejected Matrial') {
        $rules['supplier'] = ['required'];
        $rules['order_id'] = ['required'];
    }

    // Salary / Advance Salary
    if (in_array($request->Exp_type, ['Salary', 'Advanced Salary'])) {
        $rules['employee'] = ['required'];

        // Monthly salary
        if ($request->month) {
            $rules['month'] = ['required'];
            $rules['year']  = ['required'];
        } else {
            // Daily salary
            $rules['FDate'] = ['required', 'date_format:d-m-Y'];
            $rules['TDate'] = ['required', 'date_format:d-m-Y'];
        }
    }

    // Material Transfer → amount must be <= pending
    if ($request->Exp_type === 'Matrial Transfer') {
        $rules['pending'] = ['required', 'numeric'];
        $rules['amount_pay'][] = 'lte:pending';
    }

    $validated = $request->validate($rules);

    /* -------------------------------------------------
     | 3️⃣ PREPARE DATA (CLEAN & SAFE)
     -------------------------------------------------*/
    $data = [
        'ID'             => uniqid(),
        'ClientID'       => $clientId,
        'userID'         => $userId,
        'Date'           => \Carbon\Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),

        'IncomeType'     => $request->Exp_type,
        'amt_pay'        => $request->amount_pay,
        'payment_method' => $request->Pay_type,
        'cheque_no'      => $request->cheque_no,
        'account_no'     => $request->account_no,
        'narration'      => $request->narration,
        'Description'    => $request->Description,

        // 🔑 IMPORTANT: scheme is always Destination (NO if/else)
        'schemeID'       => $request->Destination,

        // Optional mappings
        'vendor'         => $request->supplier ?? $request->contractor,
        'Invno'          => $request->order_id,
        'workorderID'    => $request->invoice_id,
        'conID'          => $request->contractor,
        'empID'          => $request->employee,

        'month'          => $request->month,
        'year'           => $request->year,

        'fromdate'       => $request->FDate
                                ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->FDate)->format('Y-m-d')
                                : null,

        'todate'         => $request->TDate
                                ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->TDate)->format('Y-m-d')
                                : null,

        'paydetail'      => $request->paydetail,
    ];

    
    if (!empty($request->account_no)) {

        Bank_Acc::where('ID', $request->account_no)
            ->decrement('OBalance', (float)$request->amount_pay);
    }

    
    /* -------------------------------------------------
     | 4️⃣ INSERT
     -------------------------------------------------*/
    DB::table('income_payment')->insert($data);

    /* -------------------------------------------------
     | 5️⃣ REDIRECT
     -------------------------------------------------*/
    return redirect()
        ->route('Return_pay')
        ->with('success', 'Payment has been Added Successfully!');
}

public function edit($id)
{
    $clientId = session('selected_scheme_id');

    /* ---------------------------------------------
     | 1️⃣ FETCH PAYMENT RECORD
     ---------------------------------------------*/
    $payment = Income_Payment::where('ClientID', $clientId)
        ->where('ID', $id)
        ->first();

    if (!$payment) {
        abort(404, 'Payment not found');
    }

    /* ---------------------------------------------
     | 2️⃣ LOAD MASTER DATA (SAME AS CREATE)
     ---------------------------------------------*/
    $contractors = Supplier_contractor::where('Type', 'CONTRACTOR')
        ->where('ClientID', $clientId)
        ->get();

    $suppliers = Supplier_contractor::where('Type', 'VENDOR')
        ->where('ClientID', $clientId)
        ->get();

    $schemes = SchemeDetail::where('ID', $clientId)
        ->where('completFalg', 0)
        ->get();

    $employes = Staff::get();

    $expences = Income_Payment::select('IncomeType')
    ->distinct()
    ->get();

    /* ---------------------------------------------
     | 3️⃣ RETURN SAME CREATE VIEW
     ---------------------------------------------*/
    return view('backend.Return_pay.create', [
        'payment'     => $payment,   // 🔑 edit data
        'schemes'     => $schemes,
        'expences'    => $expences,
        'employes'    => $employes,
        'contractors' => $contractors,
        'suppliers'   => $suppliers,
        'isEdit'      => true         // 🔥 flag used by JS
    ]);
}

public function update(Request $request, $id)
{
    
    $clientId = session('selected_scheme_id');
    $userId   = Auth::id();

    /* ---------------------------------------------
     | 1️⃣ VALIDATION (SAME AS STORE)
     ---------------------------------------------*/
    $rules = [
        'Date'        => ['required', 'date_format:d-m-Y'],
        'Destination'=> ['required'],
        'Exp_type'    => ['required'],
        'Pay_type'    => ['required'],
        'account_no'  => ['required'],
        'amount_pay'  => ['required', 'numeric', 'gt:0'],
        'narration'   => ['nullable', 'string'],
        'Description'=> ['nullable', 'string'],
    ];

    // Payment method based validation
    if ($request->Pay_type === 'epay') {
        $rules['cheque_no'] = ['required'];
    }

    if ($request->Pay_type === 'cheque') {
        $rules['cheque_no'] = ['required'];
    }

    if ($request->Exp_type === 'Contractor') {
        $rules['contractor'] = ['required'];
        $rules['invoice_id'] = ['required'];
    }

    if ($request->Exp_type === 'Return/Rejected Matrial') {
        $rules['supplier'] = ['required'];
        $rules['order_id'] = ['required'];
    }

    if (in_array($request->Exp_type, ['Salary', 'Advanced Salary'])) {
        $rules['employee'] = ['required'];

        if ($request->month) {
            $rules['month'] = ['required'];
            $rules['year']  = ['required'];
        } else {
            $rules['FDate'] = ['required', 'date_format:d-m-Y'];
            $rules['TDate'] = ['required', 'date_format:d-m-Y'];
        }
    }

    if ($request->Exp_type === 'Matrial Transfer') {
        $rules['pending'] = ['required', 'numeric'];
        $rules['amount_pay'][] = 'lte:pending';
    }

    $validated = $request->validate($rules);

    /* ---------------------------------------------
     | 2️⃣ UPDATE RECORD
     ---------------------------------------------*/
    $updated = Income_Payment::where('ClientID', $clientId)
        ->where('ID', $id)
        ->update([

            'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'IncomeType'     => $request->Exp_type,
            'amt_pay'        => $request->amount_pay,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,
            'narration'      => $request->narration,
            'Description'    => $request->Description,

            // 🔑 Scheme ALWAYS Destination
            'schemeID'       => $request->Destination,

            // Conditional mappings
            'vendor'         => $request->supplier ?? $request->contractor,
            'Invno'          => $request->order_id,
            'workorderID'    => $request->invoice_id,
            'conID'          => $request->contractor,
            'empID'          => $request->employee,

            'month'          => $request->month,
            'year'           => $request->year,

            'fromdate'       => $request->FDate
                                    ? Carbon::createFromFormat('d-m-Y', $request->FDate)->format('Y-m-d')
                                    : null,

            'todate'         => $request->TDate
                                    ? Carbon::createFromFormat('d-m-Y', $request->TDate)->format('Y-m-d')
                                    : null,

            'paydetail'      => $request->paydetail,
            'userID'         => $userId,
        ]);

    if (!$updated) {
        return back()->with('error', 'Record not found or not updated.');
    }

    return redirect()
        ->route('Return_pay')
        ->with('success', 'Payment has been updated successfully!');
}


public function destroy($id)
{
    $clientId = session('selected_scheme_id');

    $deleted = Income_Payment::where('ClientID', $clientId)
        ->where('ID', $id)
        ->delete();

    if ($deleted) {
        return redirect()
            ->route('Return_pay')
            ->with('success', 'Payment has been deleted successfully!');
    }

    return redirect()
        ->route('Return_pay')
        ->with('error', 'Record not found or already deleted.');
}

}
