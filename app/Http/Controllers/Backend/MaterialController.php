<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Material;
use App\Models\Backend\Scheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 


class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Material::select(['ID', 'Name', 'Type', 'Unit','Created']);
            return DataTables::of($query)
                ->addIndexColumn()
               
                ->addColumn('actions', function ($row) {
                     $editUrl   = route('Material.edit',   $row->ID);
                    $deleteUrl = route('Material.delete', $row->ID);
                    $formId = 'delete-form-' . $row->ID;
                    $actions = '';
                
                    if (hasPermission('edit_Material')) {
                        $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                     </a>';
                    }
                
                    if (hasPermission('delete_Material')) {
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

        return view('backend.Material.index');
    }

    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {
        $permissions    = Permission::all();
        $units          = Material::query()->distinct()->pluck('Unit');
        $material       = null;                  // ← no material in create
        $materialRows   = collect([[]]);         // ← at least one empty row
        $material_name  = '';                    // ← blank by default

        return view('backend.Material.create', compact('permissions', 'units', 'material', 'materialRows', 'material_name'));
    }


    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'material_name' => ['required','string', 'max:255','regex:/^[\pL\pN\s]+$/u', 'unique:material,Name'],
        'type'                => ['required','array','min:1'],   
        'type.*'              => ['required','string','max:255'],
        'unit'                => ['required','array'],
        'unit.*'              => ['required','string','max:50'],
    ],[
        'material_name.unique' => 'This material name already exists.',
        'type.required' => 'Add at least one Type / Unit row.',
        'unit.*.required' => 'Unit field is required.',
    ]);

    $inserted = false;                
    $rows     = count($validated['type']);

    for ($i = 0; $i < $rows; $i++) {
        $mat                = new Material();
        $mat->ID = uniqid();   
        $mat->Created       = now();
        $mat->LastEdited    = now();
        $mat->userID        = Auth::id();
    $mat->ClientID       = session('selected_scheme_id');

        $mat->Name          = $validated['material_name'];
        $mat->Type          = $validated['type'][$i];
        $mat->Unit          = $validated['unit'][$i];

        if ($mat->save()) {               
            $inserted = true;
        }
    }

    return redirect()
        ->route('Material')
               ->with('success', 'Record has been deleted successfully!');

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
    $materialRows = Material::where('Name', function ($q) use ($id) {
        $q->select('Name')->from('material')->where('ID', $id)->limit(1);
    })->get();

    if ($materialRows->isEmpty()) {
        abort(404, 'Material not found.');
    }

    $material      = $materialRows->first(); // for ID, etc.
    $material_name = $material->Name;
    $units         = Material::query()->distinct()->pluck('Unit');

    return view('backend.Material.create', compact('material', 'materialRows', 'material_name', 'units'));
}


public function update(Request $request)
{
    $validated = $request->validate([
       'material_name' => ['required','string', 'max:255','regex:/^[\pL\pN\s]+$/u', 'unique:material,Name'],
        'type'                => ['required','array','min:1'],   
        'type.*'              => ['required','string','max:255'],
        'unit'                => ['required','array'],
        'unit.*'              => ['required','string','max:50'],
    ],[
        'material_name.unique' => 'This material name already exists.',
        'type.required'       => 'Add at least one Type / Unit row.',
        'unit.*.required'     => 'Unit field is required.',
    ]);

    // 1️⃣ Delete all old entries with same material name
    Material::where('Name', $validated['material_name'])->delete();

    // 2️⃣ Insert updated rows
    $rows = count($validated['type']);
    $inserted = false;

    for ($i = 0; $i < $rows; $i++) {
        $mat             = new Material();
        $mat->ID         = uniqid();
        $mat->Created    = now();
        $mat->LastEdited = now();
        $mat->userID     = Auth::id();
        $mat->ClientID   = session('selected_scheme_id');
        $mat->Name       = $validated['material_name'];
        $mat->Type       = $validated['type'][$i];
        $mat->Unit       = $validated['unit'][$i];
        $mat->save();

        $inserted = true;
    }

    return redirect()
        ->route('Material')
        ->with('success', 'Material record has been updated successfully!');
}

    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
    $mat = Material::findOrFail($id);

    if (!canDeleteRecord('po_product', 'Material', $id)) {
            return redirect()
                ->route('Material')
                ->with('error', 'Cannot delete its used.');
        }
        
    activity_log(
                'delete',
                'Payment Slab deleted: ' . $mat->Name,
                $mat
            );
    $mat->delete();

    return redirect()
        ->route('Material')
        ->with('success', 'Record has been deleted successfully!');
}

}