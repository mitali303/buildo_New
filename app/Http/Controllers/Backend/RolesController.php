<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;


class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Role::select(['id', 'name', 'slug', 'permissions', 'status', 'created_at']);
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('roles.edit', $row->id);
                    $deleteUrl = route('roles.delete', $row->id);
                    $formId = 'delete-form-' . $row->id;
                    $actions = '';
                
                    if (hasPermission('edit_role')) {
                        $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                     </a>';
                    }
                
                    if (hasPermission('delete_role')) {
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
                ->rawColumns(['status','actions'])
                ->make(true);
        }

        return view('backend.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        $permissions = Permission::all();
        return view('backend.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'name'      => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-]+$/',
            'status' => 'required|in:0,1',
        ]);


        $data = new Role;   
        $data->name = $validated['name'];
        $data->slug = str_replace(' ', '-',  strtolower($validated['name']));
        $data->permissions = json_encode($request->permissions ?? []);
        $data->status = $validated['status'];
        $data->save(); // ✅ Save the user
        return redirect()->route('roles')->with('success', 'Role added successfully!');
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
        $role = Role::find($id);
        $permissions = Permission::all();
        $rolePermissions = json_decode($role->permissions, true);
        return view('backend.roles.create', compact('permissions','role','rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // ✅ Validate input
        $validated = $request->validate([
            'name'      => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-]+$/',
            'status' => 'required|in:0,1',
        ]);

        $id = $request->id;
        $data = Role::find($id);   
        $data->name = $validated['name'];
        $data->slug = str_replace(' ', '-',  strtolower($validated['name']));
        $data->permissions = json_encode($request->permissions ?? []);
        $data->status = $validated['status'];
        $data->save(); // ✅ Save the user

        return redirect()->route('roles')->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Role::findOrFail($id);
        if($user->id == 1){
            return redirect()->route('roles')->with('error', 'Admin can not be deleted');
        }
        
        if($user->id == 2){
        return redirect()->route('roles')->with('error', 'Supervisor role can not be deleted');
    }
        $user->delete();
        // Toastr::success('User Deleted successfully!', 'Success');
        return redirect()->route('roles')->with('success', 'Role deleted successfully!');
    }
}