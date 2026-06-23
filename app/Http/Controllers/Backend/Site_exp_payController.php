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
use App\Models\Backend\Site_expences;
use App\Models\Backend\Daily_trans;
use App\Models\Backend\Partners_Investor_Loan;
use App\Models\Backend\Scope_payment_detail;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Site_exp_payController extends Controller
{

public function index(Request $request)
{
    if ($request->ajax()) {

        $clientId = session('selected_scheme_id');

        try {
            $query = Site_expences::with(['scheme'])
                ->select([
                    'ID',
                    'Date',
                    'payment_method',
                    'amt_pay', 
                    'bankcharge',
                    'Exp_type',
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

                    $editUrl = route('Site_exp_pay.edit', [
                        'id' => $row->ID
                    ]);

                    $deleteUrl = route('Site_exp_pay.delete', [
                        'id' => $row->ID
                    ]);

                    $formId = 'delete-form-' . $row->ID;

                    $actions = '';

                    if (hasPermission('edit_site_expenses')) {
                        $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_site_expenses')) {
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

    return view('backend.Site_exp_pay.index');
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


public function create()
{
    $clientId = Session::get('selected_scheme_id');

    $schemes = SchemeDetail::where('ID', $clientId)
        ->where('completFalg', 0)
        ->get();

    $employes = Staff::get();

    $expences = Site_expences::select('Exp_type')->distinct()->get();
    $titles = Site_expences::select('title')->distinct()->get();
    return view('backend.Site_exp_pay.create', [
        'schemes'   => $schemes,
        'expences'   => $expences,
        'employes'   => $employes,
        'titles'   => $titles,
    ]);
}

public function store(Request $request)
{

$rules = [
    'Date'        => 'required|date_format:d-m-Y',
    'Destination' => 'required',
    'Exp_type'    => 'required|string',
    'title'       => 'required|string',
    'Pay_type'    => 'required',
    'amount_pay'  => 'required|numeric|min:0',
    'narration'   => 'nullable|string',
];

// ADD NEW Expense Type
if ($request->Exp_type === 'OTHER') {
    $rules['Exp_type'] = 'required|string|max:255';
}

// ADD NEW Title
if ($request->title === 'OTHER') {
    $rules['title'] = 'required|string|max:255';
}

// Salary / Advanced Salary base rules
if (in_array($request->Exp_type, ['Salary', 'Advanced Salary'])) {
    $rules['employee'] = 'required';
}

// Payment type rules
if ($request->Pay_type !== 'cash') {
    $rules['account_no'] = 'required';
}

if (in_array($request->Pay_type, ['cheque', 'e-Payment'])) {
    $rules['cheque_no'] = 'required';
}

if ($request->Pay_type === 'e-Payment') {
    $rules['bnk_charge'] = 'required|numeric|min:0';
}

/**
 * ✅ SINGLE VALIDATOR
 */
$validator = Validator::make($request->all(), $rules);

/**
 * ✅ AFTER VALIDATION (employee-dependent logic)
 */
$validator->after(function ($validator) use ($request) {

    // 🔒 IMPORTANT FIX 3: ONLY touch employee for Salary types
    if (!in_array($request->Exp_type, ['Salary', 'Advanced Salary'])) {
        return;
    }

    if (!$request->employee) {
        $validator->errors()->add('employee', 'Employee is required');
        return;
    }

    $employee = Staff::find($request->employee);

    if (!$employee) {
        $validator->errors()->add('employee', 'Invalid employee selected');
        return;
    }

    // Monthly salary
    if ($employee->SalaryType === 'Monthly') {

        if (!$request->month) {
            $validator->errors()->add('month', 'Month is required');
        }

        if (!$request->year) {
            $validator->errors()->add('year', 'Year is required');
        }

    } else {
        // Daily / Other salary
        if (!$request->FDate) {
            $validator->errors()->add('FDate', 'From Date is required');
        }

        if (!$request->TDate) {
            $validator->errors()->add('TDate', 'To Date is required');
        }
    }
});

// 🔥 THIS is the ONLY validation call
$validated = $validator->validate();


    // ===============================
    // 7️⃣ PREPARE DATA (CORE LOGIC)
    // ===============================
    $id = uniqid();
    $isEPayment  = $request->Pay_type === 'e-Payment';

    $data = [
        'ID'             => $id,
        'ClientID'       => session('selected_scheme_id'),
        'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
        'amt_pay'        => $request->amount_pay,
        'bankcharge'     => ($isEPayment)
                                ? (float) $request->bnk_charge
                                : 0,
        'schemeID'       => $request->Destination,
        'payment_method' => $request->Pay_type,
        'cheque_no'      => $request->cheque_no,
        'account_no'     => $request->account_no,
        'narration'      => $request->narration,
        'title'          => $request->title,
        'Exp_type'       => $request->Exp_type,
        'empID'          => $request->employee ?? null,
        'month'          => $request->month ?? null,
        'year'           => $request->year ?? null,
        'fromdate'       => $request->FDate
                                ? Carbon::createFromFormat('d-m-Y', $request->FDate)->format('Y-m-d')
                                : null,
        'todate'         => $request->TDate
                                ? Carbon::createFromFormat('d-m-Y', $request->TDate)->format('Y-m-d')
                                : null,
        'bFlag'          => 0,
         'userID'      => Auth::id(),                       
    ];

    // ===============================
    // 8️⃣ INSERT MAIN RECORD
    // ===============================
    DB::table('site_expences')->insert($data);

    // ===============================
    // 9️⃣ BANK CHARGE ENTRY (e-Payment)
    // ===============================
    if ($request->Pay_type === 'e-Payment' && $request->bnk_charge > 0) {

        DB::table('site_expences')->insert([
            'ID'             => uniqid(),
            'ClientID'       => session('selected_scheme_id'),
            'Date'           => $data['Date'],
            'amt_pay'        => $request->bnk_charge,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,
            'bankcharge'     => ($isEPayment)
                                ? (float) $request->bnk_charge
                                : 0,
            'narration'      => $request->narration,
            'title'          => 'Site Expenses Payment Bank Charges',
            'schemeID'       => $request->Destination,
            'Exp_type'       => 'E-Payment Bank Charges',
            'PaymentId'      => $id,
            'Type_Payment'   => 'Site Expense (Bank Charges)',
            'bFlag'          => 1,
            'userID'      => Auth::id(),
        ]);
    }

    if (!empty($request->account_no)) {

    Bank_Acc::where('ID', $request->account_no)
        ->decrement('OBalance', (float)$request->amount_pay);
}
    // ===============================
    // 🔟 REDIRECT
    // ===============================
    return redirect()
        ->route('Site_exp_pay')
        ->with('success', 'Record has been added successfully!');
}


public function edit($id)
{
    $clientId = session('selected_scheme_id');

    // ===============================
    // 1️⃣ LOAD MAIN EXPENSE RECORD
    // ===============================
    $payment = DB::table('site_expences')
        ->where('ClientID', $clientId)
        ->where('ID', $id)
        ->first();

    if (!$payment) {
        abort(404, 'Record not found');
    }

    // ===============================
    // 2️⃣ LOAD BANK CHARGE (IF EXISTS)
    // ===============================
    $bankCharge = DB::table('site_expences')
        ->where('ClientID', $clientId)
        ->where('PaymentId', $id)
        ->where('Exp_type', 'E-Payment Bank Charges')
        ->first();

    // ===============================
    // 3️⃣ LOAD MASTER DATA (SAME AS CREATE)
    // ===============================
    $schemes = SchemeDetail::where('ID', $clientId)
        ->where('completFalg', 0)
        ->get();

    $employes = Staff::get();

    $expences = Site_expences::select('Exp_type')
        ->distinct()
        ->whereNotNull('Exp_type')
        ->get();

    $titles = Site_expences::select('title')
        ->distinct()
        ->whereNotNull('title')
        ->get();

    // ===============================
    // 4️⃣ RETURN SAME CREATE VIEW
    // ===============================
    return view('backend.Site_exp_pay.create', [
        'payment'    => $payment,     // edit data
        'bankCharge' => $bankCharge,  // e-payment charge (nullable)
        'schemes'    => $schemes,
        'expences'   => $expences,
        'titles'     => $titles,
        'employes'   => $employes,
        'isEdit'     => true,         // 🔥 useful flag
    ]);
}



public function update(Request $request, $uid)
{

$rules = [
    'Date'        => 'required|date_format:d-m-Y',
    'Destination' => 'required',
    'Exp_type'    => 'required|string',
    'title'       => 'required|string',
    'Pay_type'    => 'required',
    'amount_pay'  => 'required|numeric|min:0',
    'narration'   => 'nullable|string',
];

// ADD NEW Expense Type
if ($request->Exp_type === 'OTHER') {
    $rules['Exp_type'] = 'required|string|max:255';
}

// ADD NEW Title
if ($request->title === 'OTHER') {
    $rules['title'] = 'required|string|max:255';
}

// Salary / Advanced Salary base rules
if (in_array($request->Exp_type, ['Salary', 'Advanced Salary'])) {
    $rules['employee'] = 'required';
}

// Payment type rules
if ($request->Pay_type !== 'cash') {
    $rules['account_no'] = 'required';
}

if (in_array($request->Pay_type, ['cheque', 'e-Payment'])) {
    $rules['cheque_no'] = 'required';
}

if ($request->Pay_type === 'e-Payment') {
    $rules['bnk_charge'] = 'required|numeric|min:0';
}

/**
 * ✅ SINGLE VALIDATOR
 */
$validator = Validator::make($request->all(), $rules);

/**
 * ✅ AFTER VALIDATION (employee-dependent logic)
 */
$validator->after(function ($validator) use ($request) {

    // 🔒 IMPORTANT FIX 3: ONLY touch employee for Salary types
    if (!in_array($request->Exp_type, ['Salary', 'Advanced Salary'])) {
        return;
    }

    if (!$request->employee) {
        $validator->errors()->add('employee', 'Employee is required');
        return;
    }

    $employee = Staff::find($request->employee);

    if (!$employee) {
        $validator->errors()->add('employee', 'Invalid employee selected');
        return;
    }

    // Monthly salary
    if ($employee->SalaryType === 'Monthly') {

        if (!$request->month) {
            $validator->errors()->add('month', 'Month is required');
        }

        if (!$request->year) {
            $validator->errors()->add('year', 'Year is required');
        }

    } else {
        // Daily / Other salary
        if (!$request->FDate) {
            $validator->errors()->add('FDate', 'From Date is required');
        }

        if (!$request->TDate) {
            $validator->errors()->add('TDate', 'To Date is required');
        }
    }
});

// 🔥 THIS is the ONLY validation call
$validated = $validator->validate();


    // ===============================
    // 2️⃣ DELETE OLD RECORDS (CORE LOGIC)
    // ===============================
    DB::beginTransaction();

    try {

        $clientId = session('selected_scheme_id');

        // delete bank charge entries
        DB::table('site_expences')
            ->where('ClientID', $clientId)
            ->where('PaymentId', $uid)
            ->delete();

        // delete main record
        DB::table('site_expences')
            ->where('ClientID', $clientId)
            ->where('ID', $uid)
            ->delete();

        // ===============================
        // 3️⃣ INSERT NEW MAIN RECORD
        // ===============================
        $newId = uniqid();
        $isEPayment = $request->Pay_type === 'e-Payment';

        DB::table('site_expences')->insert([
            'ID'             => $newId,
            'ClientID'       => $clientId,
            'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'amt_pay'        => $request->amount_pay,
            'bankcharge'     => $isEPayment ? $request->bnk_charge : 0,
            'schemeID'       => $request->Destination,
            'payment_method' => $request->Pay_type,
            'cheque_no'      => $request->cheque_no,
            'account_no'     => $request->account_no,
            'narration'      => $request->narration,
            'title'          => $request->title,
            'Exp_type'       => $request->Exp_type,
            'empID'          => $request->employee ?? null,
            'month'          => $request->month ?? null,
            'year'           => $request->year ?? null,
            'fromdate'       => $request->FDate
                                    ? Carbon::createFromFormat('d-m-Y', $request->FDate)->format('Y-m-d')
                                    : null,
            'todate'         => $request->TDate
                                    ? Carbon::createFromFormat('d-m-Y', $request->TDate)->format('Y-m-d')
                                    : null,
            'bFlag'          => 0,
            'userID'         => Auth::id(),
        ]);

        // ===============================
        // 4️⃣ INSERT BANK CHARGE (IF e-PAYMENT)
        // ===============================
        if ($isEPayment && $request->bnk_charge > 0) {
            DB::table('site_expences')->insert([
                'ID'             => uniqid(),
                'ClientID'       => $clientId,
                'Date'           => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'amt_pay'        => $request->bnk_charge,
                'bankcharge'     => ($isEPayment)
                                ? (float) $request->bnk_charge
                                : 0,
                'payment_method' => $request->Pay_type,
                'cheque_no'      => $request->cheque_no,
                'account_no'     => $request->account_no,
                'narration'      => $request->narration,
                'title'          => 'Site Expenses Payment Bank Charges',
                'schemeID'       => $request->Destination,
                'Exp_type'       => 'E-Payment Bank Charges',
                'PaymentId'      => $newId,
                'Type_Payment'   => 'Site Expense (Bank Charges)',
                'bFlag'          => 1,
                'userID'         => Auth::id(),
            ]);
        }

        DB::commit();

        return redirect()
            ->route('Site_exp_pay')
            ->with('success', 'Record has been updated successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}


public function destroy($id)
{
    $clientId = session('selected_scheme_id');

    DB::beginTransaction();

    try {

        // ===============================
        // 1️⃣ DELETE BANK CHARGE / CHILD ENTRIES
        // ===============================
        DB::table('site_expences')
            ->where('ClientID', $clientId)
            ->where('PaymentId', $id)
            ->delete();

        // ===============================
        // 2️⃣ DELETE MAIN RECORD
        // ===============================
        $deleted = DB::table('site_expences')
            ->where('ClientID', $clientId)
            ->where('ID', $id)
            ->delete();

        DB::commit();

        if ($deleted) {
            return redirect()
                ->route('Site_exp_pay')
                ->with('success', 'Record has been deleted successfully!');
        }

        return redirect()
            ->route('Site_exp_pay')
            ->with('error', 'Record not found or already deleted.');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->route('Site_exp_pay')
            ->with('error', 'Failed to delete record.');
    }
}


}
