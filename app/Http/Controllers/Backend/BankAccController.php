<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Bank_Acc;
use App\Models\Backend\Scheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 


class BankAccController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
               $clientId = session('selected_scheme_id');

        $query = Bank_Acc::where('ClientID', $clientId)
            ->select([
                'ID',
                'Name',
                'ACNo',
                'Branch',
                'IFSC',
                'ClientID',
                'Created'
            ]);
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('scheme', function ($row) {
                    return $row->scheme->Name ?? '-';
                })
                ->addColumn('actions', function ($row) {
                     $editUrl   = route('BankAcc.edit',   $row->ID);
                    $deleteUrl = route('BankAcc.delete', $row->ID);
                    $formId = 'delete-form-' . $row->ID;
                    $actions = '';
                
                    if (hasPermission('edit_Bank_Account')) {
                        $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                     </a>';
                    }
                
                    if (hasPermission('delete_Bank_Account')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                                        <i class="align-middle" data-feather="trash"></i>
                                     </a>
                                     <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                     </form>';
                    } 
                
                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.BankAcc.index');
    }

    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {   
        $permissions = Permission::all();
        return view('backend.BankAcc.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    /* ───────── 1. VALIDATE ───────── */
    $validated = $request->validate([
            'bank_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z\s]+$/',
                Rule::unique('accounts', 'Name') // or bank_acc table name
            ],
        'account_no'      => ['required', 'regex:/^[0-9]{9,18}$/', 'unique:accounts,ACNo'],
        'branch'          => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'ifsc'            => ['required', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
        'opening_balance' => ['required', 'numeric'],
    ], [
            'bank_name.unique' => 'This bank name already exists. Duplicate entries are not allowed.',
    'account_no.regex' => 'Account number must be between 9 and 18 digits.',
    'ifsc.regex' => 'Please enter a valid IFSC code (e.g., SBIN0001234).',
    ]);

    /* ───────── 2. INSERT MAIN ACCOUNT ───────── */
    $acc               = new Bank_Acc();           // ↳ model maps to `accounts`
    $acc->ID           = uniqid();
    $acc->ClientID     = session('selected_scheme_id');
    $acc->Name         = $validated['bank_name'];
    $acc->ACNo         = $validated['account_no'];
    $acc->Branch       = $validated['branch'];
    $acc->IFSC         = $validated['ifsc'];
    $acc->OBalance     = $validated['opening_balance'];
    $acc->userID       = Auth::id();
    $acc->Created      = now();
    $acc->LastEdited   = now();
    $acc->save();

    /* ───────── 3. ENSURE “Cash In Hand” ACCOUNT ───────── */
    $clientId = session('selected_scheme_id');
    $exists   = Bank_Acc::where('ClientID', $clientId)
                        ->where('ID', 'Cash In Hand')
                        ->exists();

    if (!$exists) {
        Bank_Acc::create([
            'ID'         => 'Cash In Hand',
            'ClientID'   => $clientId,
            'Name'       => 'Cash In Hand',
            'ACNo'       => 'Cash In Hand',
            'OBalance'   => 0,
            'userID'     => Auth::id(),
            'Created'    => now(),
            'LastEdited' => now(),
        ]);
    }

    /* ───────── 4. REDIRECT ───────── */
    return redirect()
           ->route('BankAcc')          // your index route
           ->with('success', 'Bank account added successfully!');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit($id)
{
    $bankacc = Bank_Acc::where('ClientID', session('selected_scheme_id'))
                ->findOrFail($id);

    return view('backend.BankAcc.create', compact('bankacc'));
}

public function update(Request $request)
{
    /* 1️⃣ VALIDATE -------------------------------------------------- */
    $validated = $request->validate([
        'id' => [
    'required',
    Rule::exists('accounts', 'ID')
        ->where(function ($query) {
            $query->where('ClientID', session('selected_scheme_id'));
        })
], // hidden input
        'bank_name'       => ['required', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'account_no'      => ['required', 'regex:/^[0-9]{9,18}$/',],
        'branch'          => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'ifsc'            => ['required', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
        'opening_balance' => ['required', 'numeric'],
    ], [
    'account_no.regex' => 'Account number must be between 9 and 18 digits.',
    'ifsc.regex' => 'Please enter a valid IFSC code (e.g., SBIN0001234).',
    ]);

    /* 2️⃣ UPDATE THE CHOSEN ACCOUNT -------------------------------- */
    $acc = Bank_Acc::where('ClientID', session('selected_scheme_id'))
        ->findOrFail($validated['id']);
    $acc->LastEdited  = now();
    $acc->Name        = $validated['bank_name'];       // ← column = Name
    $acc->ACNo        = $validated['account_no'];      // ← column = ACNo
    $acc->Branch      = $validated['branch'];
    $acc->IFSC        = $validated['ifsc'];
    $acc->OBalance    = $validated['opening_balance']; // ← column = OBalance
    $acc->userID      = Auth::id();
    $acc->save();

    /* 3️⃣ ENSURE “Cash In Hand” EXISTS ----------------------------- */
    $clientId = session('selected_scheme_id');         // or $_SESSION['Client_Id']
    $cashAcc  = Bank_Acc::where('ID', 'Cash In Hand')
                        ->where('ClientID', $clientId)
                        ->first();

    if (!$cashAcc) {
        Bank_Acc::create([
            'ID'         => 'Cash In Hand',
            'ClientID'   => $clientId,
            'ACNo'       => 'Cash In Hand',
            'Created'    => now(),
            'LastEdited' => now(),
            'userID'     => Auth::id(),
        ]);
    }

    /* 4️⃣ BACK TO INDEX ------------------------------------------- */
    return redirect()
           ->route('BankAcc')
           ->with('success', 'Record has been updated successfully!');
}


    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
      $bankacc = Bank_Acc::where('ClientID', session('selected_scheme_id'))
                ->findOrFail($id);

    if (!canDeleteRecord('workorder_payment', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('owner_payment', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('labour_payment', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('inv_payment', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('partners_loan', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('site_expences', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('tds_payment', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('account_transfer', 'account_to', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('account_transfer', 'account_from', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('customer_refund', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('land', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('stampotherexpenses', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('booking_payment', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('employee_advance_payments', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('employee_advance', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('salarymaster', 'account_no', $id)) {
            return redirect()
                ->route('BankAcc')
                ->with('error', 'Cannot delete its used.');
        }

    // 🔹 Log before delete
            activity_log(
                'delete',
                'Bank Account deleted: ' . $bankacc->Name,
                $bankacc
            );

    $bankacc->delete();

    return redirect()
        ->route('BankAcc')
        ->with('success', 'Record has been deleted successfully!');
}

}