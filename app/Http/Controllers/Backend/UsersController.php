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

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
{
    $users   = User::select('ID', 'Name', 'UserID', 'Role', 'scheme', 'access_type')->get();
    $roles   = Role::pluck('name', 'id');       // [id => name]
    $schemes = Scheme::pluck('Name', 'ID');     // [ID => Name]

    // Map role/scheme names into the users collection
          foreach ($users as $user) {
    $user->role_name = $roles[$user->Role] ?? 'N/A';

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

        return view('backend.users.create', compact('roles','permissions','schemes'));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'username' => 'required|alpha_num|string|max:255|unique:user,UserID',
            'password' => [
                'nullable',
                'string',
                'min:5',
                'max:16',
                'regex:/^(?=.*\d)(?=.*[@$!%*#?&]).{5,16}$/'
            ],
        'role_id'     => 'required',
        'schemes' => 'required|array',
        'schemes.*' => 'exists:scheme_step1,ID', // optional: ensure each selected scheme exists
        'access_type' => 'required|in:web,mobile,both',  // ✅ Add this
    ],[
            'password.regex' => 'Password must contain at least one uppercase letter and one number.',
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

        
        return view('backend.users.create', compact('roles','user', 'permissions','schemes'));
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
            'password' => [
                'nullable',
                'string',
                'min:5',
                'max:16',
                'regex:/^(?=.*\d)(?=.*[@$!%*#?&]).{5,16}$/'
            ],
            'access_type' => 'required|in:web,mobile,both'  // ✅ Add this
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