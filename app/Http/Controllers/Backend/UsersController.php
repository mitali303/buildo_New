<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Backend\Role;
use App\Models\Backend\Scheme;
use App\Models\Backend\Permission;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $users   = User::select('ID', 'Name', 'UserID', 'Role', 'scheme', 'access_type',  'status')->get();
    $roles   = Role::pluck('name', 'id');       // [id => name]
    $schemes = Scheme::pluck('Name', 'ID');     // [ID => Name]

    // Map role/scheme names into the users collection
          foreach ($users as $user) {
    $user->role_name = $roles[$user->Role] ?? 'N/A';
    $user->status_name = $user->status == 1 ? 'Active' : 'Inactive';
    // decode JSON if possible
    $scheme_ids = json_decode($user->scheme, true);

    if (is_array($scheme_ids) && count($scheme_ids) > 0) {
        // new JSON format
        $scheme_ids = array_map('strval', $scheme_ids);
        $user->scheme_name = Scheme::whereIn('ID', $scheme_ids)
                                   ->pluck('Name')
                                   ->implode(', ');
    } elseif (!empty($user->scheme)) {
        // old single-scheme format
        $user->scheme_name = Scheme::where('ID', $user->scheme)
                                   ->pluck('Name')
                                   ->first() ?? 'N/A';
    } else {
        $user->scheme_name = 'N/A';
    }

    $user->access_type_name = $user->access_type ?? 'N/A';
}

    return view('backend.users.index', compact('users'));
}



    public function create()
    {
        $roles = Role::where('status',1)->get();
        $schemes = Scheme::all();
        $permissions = Permission::all();

         $designations = DB::table('user')
        ->whereNotNull('designation')
        ->where('designation', '!=', '')
        ->select('designation')
        ->distinct()
        ->orderBy('designation')
        ->get();

    return view('backend.users.create', compact(
        'roles',
        'permissions',
        'schemes',
        'designations'
    ));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
       
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'username' => 'required|alpha_num|string|max:255|unique:user,UserID',
            'password' => ['nullable','confirmed','string','min:5','max:16','regex:/^(?=.*\d)(?=.*[@$!%*#?&]).{5,16}$/'],
        'role_id'     => 'required',
        'schemes' => 'required|array',
        'schemes.*' => 'exists:scheme_step1,ID', // optional: ensure each selected scheme exists
        'access_type' => 'required|in:web,mobile,both',  // ✅ Add this
        'total_salary' => 'nullable|numeric',
    'perhour_salary' => 'nullable|numeric',
    'overtime_salary_perhour' => 'nullable|numeric',
    'designation' => 'nullable|string|max:255',
    'account_no' => 'nullable|string|max:100',
    'IFSC' => 'nullable|string|max:20',
    'bank_name' => 'nullable|string|max:255',
    'PF' => 'nullable',
    'PF_No' => 'nullable|string|max:100',
    'ESI' => 'nullable',
    'ESI_No' => 'nullable|string|max:100',
    'allowance_amount' => 'nullable|numeric',
    'pf_amount' => 'nullable|numeric',
    'hra_allowance_amount' => 'nullable|numeric',
    'emp_id' => 'nullable|string|max:100',
    'status' => 'required|in:0,1',
    ],[
            'password.regex' => 'Password must contain at least one number and one special character.',
    ]);
        $user = new User();
        $user->Name = $request->input('name');
        $user->UserID = $request->input('username');

        if ($request->filled('password')) {
            $user->Password = Hash::make($validated['password']);
        }

        $user->Role = $validated['role_id'];
        $user->scheme = json_encode($validated['schemes']); // store as JSON
        $user->access_type = $validated['access_type'];
        $user->Created = now();
        $user->LastEdited = now();
        $user->ClientID = Session::get('selected_scheme_id');
        $user->access_type = $request->input('access_type', 'web'); // <- add this
        $user->device_token = '';
        $user->total_salary = $request->total_salary;
        $user->perhour_salary = $request->perhour_salary;
        $user->overtime_salary_perhour = $request->overtime_salary_perhour;
        $user->designation = $request->designation;
        $user->account_no = $request->account_no;
        $user->IFSC = $request->IFSC;
        $user->bank_name = $request->bank_name;
        $user->PF = $request->PF;
        $user->PF_No = $request->PF_No;
        $user->ESI = $request->ESI;
        $user->ESI_No = $request->ESI_No;
        $user->allowance_amount = $request->allowance_amount;
        $user->pf_amount = $request->pf_amount;
        $user->hra_allowance_amount = $request->hra_allowance_amount;
        $user->emp_id = $request->emp_id;
        $user->status = $request->status;
        $user->Password = Hash::make($request->password);
        $user->save();
    return redirect()->route('users')->with('success', 'User created successfully!');
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
    public function edit(string $id)
    {
        $user = User::find($id);
          $roles = Role::where('status',1)->get();
        $schemes = Scheme::all();
        $permissions = Permission::all();

        
        $designations = DB::table('user')
        ->whereNotNull('designation')
        ->where('designation', '!=', '')
        ->select('designation')
        ->distinct()
        ->orderBy('designation')
        ->get();

    return view('backend.users.create', compact(
        'roles',
        'user',
        'permissions',
        'schemes',
        'designations'
    ));
}
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $user = User::find($id); 
       $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'username' => 'required|alpha_num|string|max:255',
            'role_id' => 'required',
            'schemes' => 'required|array',        
            'schemes.*' => 'exists:scheme_step1,ID',  
            'password' => [ 'nullable', 'confirmed','string','min:5','max:16','regex:/^(?=.*\d)(?=.*[@$!%*#?&]).{5,16}$/'
 ],
            'access_type' => 'required|in:web,mobile,both' , // ✅ Add this
            'total_salary' => 'nullable|numeric',
            'perhour_salary' => 'nullable|numeric',
            'overtime_salary_perhour' => 'nullable|numeric',
            'designation' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:100',
            'IFSC' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'PF' => 'nullable',
            'PF_No' => 'nullable|string|max:100',
            'ESI' => 'nullable',
            'ESI_No' => 'nullable|string|max:100',
            'allowance_amount' => 'nullable|numeric',
            'pf_amount' => 'nullable|numeric',
            'hra_allowance_amount' => 'nullable|numeric',
            'shift_assign' => 'nullable|string|max:100',
            'emp_id' => 'nullable|string|max:100',
            'status' => 'required|in:0,1',
        ], [
            'password.regex' => 'Password must contain at least one number and one special character.',
        ]);
            $user->Name     = $request->input('name');
            $user->UserID   = $request->input('username');
            if ($request->filled('password')) {
                $user->Password = Hash::make($validated['password']);
            }    
            $user->Role     = $validated['role_id'];
            $user->scheme = json_encode($request->schemes);
            $user->access_type = $validated['access_type'];
            $user->Created  = now();
            $user->LastEdited  = now();
            $user->ClientID = Session::get('selected_scheme_id');     
            $user->access_type = $request->input('access_type', ''); // default empty if not sent
            $user->device_token = '';
            $user->total_salary = $request->total_salary;
            $user->perhour_salary = $request->perhour_salary;
            $user->overtime_salary_perhour = $request->overtime_salary_perhour;
            $user->designation = $request->designation;
            $user->account_no = $request->account_no;
            $user->IFSC = $request->IFSC;
            $user->bank_name = $request->bank_name;
            $user->PF = $request->PF;
            $user->PF_No = $request->PF_No;
            $user->ESI = $request->ESI;
            $user->ESI_No = $request->ESI_No;
            $user->allowance_amount = $request->allowance_amount;
            $user->pf_amount = $request->pf_amount;
            $user->hra_allowance_amount = $request->hra_allowance_amount;
            $user->shift_assign = $request->shift_assign;
            $user->emp_id = $request->emp_id;
            $user->status = $request->status;
            
            $user->save();

            return redirect()->route('users')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users')->with('success', 'User deleted successfully!');
    }
}