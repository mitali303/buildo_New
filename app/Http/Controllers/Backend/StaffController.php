<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Staff;
use App\Models\Backend\Scheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Staff::select(['ID', 'Name', 'Address', 'ContactNo', 'Department','Designation','Status','Created','SalaryType','DailyWage']);
            return DataTables::of($query)
                ->addIndexColumn()
               
                ->addColumn('actions', function ($row) {
                     $editUrl   = route('Staff.edit',   $row->ID);
                    $deleteUrl = route('Staff.delete', $row->ID);
                    $formId = 'delete-form-' . $row->ID;
                    $actions = '';
                
                    if (hasPermission('edit_staff')) {
                        $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                     </a>';
                    }
                
                    if (hasPermission('delete_staff')) {
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

        return view('backend.Staff.index');
    }

    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {   
        $permissions = Permission::all();
         $departments  = Staff::query()->distinct()->pluck('Department');   // ["HR","Site","…"]
    $designations = Staff::query()->distinct()->pluck('Designation');  // ["Manager","Mason",…]
    $schemes      = Scheme::select('ID','Name')->get();
        return view('backend.Staff.create', compact('permissions','departments','designations','schemes'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    /* ───────────────────────────
       1️⃣  VALIDATE INPUT
    ─────────────────────────── */
    $validated = $request->validate([
        'name'        => ['required','string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'contact_no'  => ['required','regex:/^[0-9]{10}$/'],
        'email'       => ['nullable','email','max:255'],
        'department'  => ['required'],
        'department_other' => ['nullable','string','max:255'],
        'designation' => ['required'],
        'designation_other' => ['nullable','string','max:255'],
        'salary_type' => ['required','in:Monthly,Weekly,Daily'],
        'salary'      => ['required','numeric','min:0'],
        'username'    => ['required','string','max:255','unique:staff,username'],
        'password' => ['required','string','min:8','max:16','regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,16}$/'],
        'scheme'      => ['required','exists:scheme_step1,ID'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        'address'     => ['nullable','string','max:255'],
    ],[
        'required' => 'This field is required.',
        'unique'   => 'Username already taken.',
        'exists'   => 'Selected scheme does not exist.',
        'password.regex' => 'Password must be 8–16 characters long and include at least one uppercase letter, one number, one special character and no spaces.',
    ]);

    $department  = $validated['department']  === 'OTHER'
        ? $validated['department_other']  // new value from hidden input
        : $validated['department'];

    $designation = $validated['designation'] === 'OTHER'
        ? $validated['designation_other']
        : $validated['designation'];

    $staff                 = new Staff();
    $staff->Name           = $validated['name'];
    $staff->ContactNo      = $validated['contact_no'];
    $staff->Email          = $validated['email']        ?? null;
    $staff->Department     = $department;
    $staff->Designation    = $designation;
    $staff->SalaryType     = $validated['salary_type'];
    $staff->DailyWage         = $validated['salary'];
    $staff->username       = $validated['username'];
    $staff->Password       = Hash::make($validated['password']);   // encrypt
    $staff->scheme       = $validated['scheme'];
    $staff->Address        = $validated['address']      ?? null;

    // extra bookkeeping (if these columns exist in your table)
    $staff->ClientID       = session('selected_scheme_id');
        $staff->Status = $validated['status'];
    $staff->userID         = Auth::id();
    $staff->Created        = now();
    $staff->LastEdited     = now();

    $staff->save();
    return redirect()
           ->route('Staff')
           ->with('success', 'Staff record added successfully!');
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
    $staff         = Staff::findOrFail($id);

    // dropdown values (distinct, alphabetic)
    $departments   = Staff::query()->orderBy('Department')
                                   ->whereNotNull('Department')
                                   ->distinct()->pluck('Department');

    $designations  = Staff::query()->orderBy('Designation')
                                   ->whereNotNull('Designation')
                                   ->distinct()->pluck('Designation');

    $schemes       = Scheme::select('ID','Name')->orderBy('Name')->get();

    return view('backend.Staff.create',
                compact('staff','departments','designations','schemes'));
}

public function update(Request $request)
{
    /* 1️⃣  VALIDATION */
    $validated = $request->validate([
        'id'           => ['required','exists:staff,ID'],
        'name'         => ['required','max:255', 'regex:/^[A-Za-z\s]+$/'],
        'contact_no'   => ['required','regex:/^[0-9]{10}$/'],
        'email'        => ['nullable','email','max:255'],
        'department'   => ['required'],
        'department_other'  => ['nullable','string','max:255'],
        'designation'  => ['required'],
        'designation_other' => ['nullable','string','max:255'],
        'salary_type'  => ['required','in:Monthly,Weekly,Daily'],
        'salary'       => ['required','numeric','min:0'],
        // ignore uniqueness against the current record
        'username'     => ['required','string','max:255',
                           'unique:staff,UserName,'.$request->id.',ID'],
        'scheme'       => ['required','exists:scheme_step1,ID'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        'address'      => ['nullable','string','max:255'],
         'password' => ['nullable','string','min:8','max:16','regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,16}$/'],
    ],[
       'password.regex' => 'Password must be 8–16 characters long and include at least one uppercase letter, one number, one special character and no spaces.',
    ]);

    /* 2️⃣  RESOLVE “ADD NEW” OPTIONS */
    $department  = $validated['department']  === 'OTHER'
                 ? $validated['department_other']
                 : $validated['department'];

    $designation = $validated['designation'] === 'OTHER'
                 ? $validated['designation_other']
                 : $validated['designation'];

    /* 3️⃣  UPDATE RECORD */
    $staff                 = Staff::findOrFail($validated['id']);
    $staff->Name           = $validated['name'];
    $staff->ContactNo      = $validated['contact_no'];
    $staff->Email          = $validated['email']        ?? null;
    $staff->Department     = $department;
    $staff->Designation    = $designation;
    $staff->SalaryType     = $validated['salary_type'];
    $staff->DailyWage         = $validated['salary'];
    $staff->username       = $validated['username'];
    if (!empty($validated['password'])) {                // change only if sent
        $staff->Password   = Hash::make($validated['password']);
    }
    $staff->scheme       = $validated['scheme'];
    $staff->Address        = $validated['address']      ?? null;
        $staff->Status = $validated['status'];
    $staff->ClientID       = session('selected_scheme_id');
    $staff->LastEdited     = now();
    $staff->userID         = Auth::id();

    $staff->save();

    return redirect()
           ->route('Staff')
           ->with('success', 'Staff record updated successfully!');
}

    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
    $staff = Staff::findOrFail($id);

     if (!canDeleteRecord('attendancemaster', 'emp_id', $id)) {
            return redirect()
                ->route('Staff')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('employee_advance', 'emp_id', $id)) {
            return redirect()
                ->route('Staff')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('emp_avance_pay', 'emp_id', $id)) {
            return redirect()
                ->route('Staff')
                ->with('error', 'Cannot delete its used.');
        }

    // 🔹 Log before delete
            activity_log(
                'delete',
                'Staff deleted: ' . $staff->Name,
                $staff
            );

    $staff->delete();

    return redirect()
        ->route('Staff')
        ->with('success', 'Record has been deleted successfully!');
}

}