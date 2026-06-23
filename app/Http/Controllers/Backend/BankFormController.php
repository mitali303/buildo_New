<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Bank_Form;
use App\Models\Backend\Scheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 


class BankFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Bank_Form::select(['ID', 'bank_name', 'to_name', 'city','Created']);
            return DataTables::of($query)
                ->addIndexColumn()
               
                ->addColumn('actions', function ($row) {
                     $editUrl   = route('BankForm.edit',   $row->ID);
                    $deleteUrl = route('BankForm.delete', $row->ID);
                    $formId = 'delete-form-' . $row->ID;
                    $actions = '';
                
                    if (hasPermission('edit_bank_loan_request')) { 
                        $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                     </a>';
                    }
                
                    if (hasPermission('delete_bank_loan_request')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                                        <i class="align-middle" data-feather="trash"></i>
                                     </a>
                                     <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                     </form>';
                                     
                                     $printUrl = route('BankForm.print', $row->ID);

                        $actions .= '<a href="' . $printUrl . '" target="_blank" class="text-success">
                            <i class="align-middle" data-feather="printer"></i>
                        </a>';
                    } 
                
                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('backend.bank_Form.index');
    }

    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {   
        $permissions = Permission::all();
        return view('backend.bank_Form.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    /* ───────────── 1. VALIDATE ───────────── */
    $validated = $request->validate([
        'bank_name'    => ['required','max:255', 'regex:/^[A-Za-z\s]+$/'],
        'to_name'      => ['required','max:255', 'regex:/^[A-Za-z\s]+$/'],
        'bank_address' => ['required','string','max:255'],
        'city'         => ['required','max:255', 'regex:/^[^0-9]*$/'],
        'pincode'      => ['required','regex:/^[1-9][0-9]{5}$/'],
        'subject'      => ['required','string','max:255'],
        'loan_details' => ['required','string'],
    ]);

    /* ───────────── 2. INSERT ───────────── */
    $bankform                 = new Bank_Form();          // table = bank_loan_form
    $bankform->ID             = uniqid();
    $bankform->ClientID       = session('selected_scheme_id'); // or $_SESSION['Client_Id']

    // columns = form-field names (adjust if your DB uses different casing)
    $bankform->bank_name       = $validated['bank_name'];
    $bankform->to_name         = $validated['to_name'];
    $bankform->address    = $validated['bank_address'];
    $bankform->city           = $validated['city'];
    $bankform->pincode        = $validated['pincode'];
    $bankform->subject        = $validated['subject'];
    $bankform->loan_request    = $validated['loan_details'];

    // audit
    $bankform->Created        = now();
    $bankform->LastEdited     = now();

    $bankform->save();

    /* ───────────── 3. REDIRECT ───────────── */
    return redirect()
            ->route('BankForm')          // index route
            ->with('success', 'Bank‑loan form added successfully!');
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
    $bankform = Bank_Form::findOrFail($id);  
    return view('backend.bank_Form.create', compact('bankform'));
}

public function update(Request $request)
{
    /* 1️⃣ VALIDATE -------------------------------------------------- */
    $validated = $request->validate([
        'id'            => ['required', 'exists:bank_loan_form,ID'],   // hidden <input name="id">
        'bank_name'     => ['required', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'to_name'       => ['required', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'bank_address'  => ['required', 'string', 'max:255'],
        'city'          => ['required', 'max:255', 'regex:/^[^0-9]*$/'],
        'pincode'       => ['required', 'regex:/^[1-9][0-9]{5}$/'],
        'subject'       => ['required', 'string', 'max:255'],
        'loan_details'  => ['required', 'string'],
    ]);

    /* 2️⃣ FETCH & UPDATE ------------------------------------------- */
    $bankform = Bank_Form::findOrFail($validated['id']);

    $bankform->bank_name    = $validated['bank_name'];
    $bankform->to_name      = $validated['to_name'];
    $bankform->address      = $validated['bank_address'];   // column = address
    $bankform->city         = $validated['city'];
    $bankform->pincode      = $validated['pincode'];
    $bankform->subject      = $validated['subject'];
    $bankform->loan_request = $validated['loan_details'];   // column = loan_request

    // bookkeeping / audit fields
    $bankform->ClientID   = session('selected_scheme_id');
    $bankform->LastEdited = now();

    $bankform->save();


    /* 3️⃣ REDIRECT -------------------------------------------------- */
    return redirect()
        ->route('BankForm')                       // your index route
        ->with('success', 'Bank‑loan form updated successfully!');
}


    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
    $bankform = Bank_Form::findOrFail($id);

    // 🔹 Log before delete
            activity_log(
                'delete',
                'Bank Load-Request deleted',
                $bankform
            );
    $bankform->delete();

    return redirect()
        ->route('BankForm')
        ->with('success', 'Record has been deleted successfully!');
}

public function print($id)
    {
        $bankform = Bank_Form::findOrFail($id);

        return view('backend.bank_Form.print', compact('bankform'));
    }
    

}
