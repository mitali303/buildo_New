<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Payment_Slab;
use Illuminate\Support\Facades\Auth;


class PaymentSlabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schemeId = session('selected_scheme_id');

        // 👉 ONLY this needed for button control
        $hasSlab = Payment_Slab::exists();

        if ($request->ajax()) {
            $query = Payment_Slab::select(['ID', 'pilnth', 'slab', 'bricks', 'plaster', 'floaring','plumbing','project','total','Created']);
            return DataTables::of($query)
                ->addIndexColumn()
               
                ->addColumn('actions', function ($row) {
                     $editUrl   = route('PaymentSlab.edit',   $row->ID);
                    $deleteUrl = route('PaymentSlab.delete', $row->ID);
                    $formId = 'delete-form-' . $row->ID;
                    $actions = '';
                
                    if (hasPermission('edit_payment_slab')) {
                        $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                     </a>';
                    }
                
                    if (hasPermission('delete_payment_slab')) {
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

        // 🔥 THIS IS IMPORTANT (you missed passing variable)
        return view('backend.payment_slab.index', compact('hasSlab'));
    }

    /**
     * Show the form for creating a new resource. 
     */
   public function create()
{
    if (Payment_Slab::exists()) {
        return redirect()->route('PaymentSlab');
    }

    $permissions = Permission::all();

    return view('backend.payment_slab.create', compact('permissions'));
}

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    if (Payment_Slab::exists()) {
    return redirect()
        ->route('PaymentSlab')
        ->with('error', 'Only one Payment Slab record is allowed.');
}
    /* ---------- validation ---------- */
    $validated = $request->validate([
        'pilnth'   => ['required', 'numeric', 'min:0'],
        'slab'     => ['required', 'numeric', 'min:0'],
        'bricks'   => ['required', 'numeric', 'min:0'],
        'plaster'  => ['required', 'numeric', 'min:0'],
        'floaring' => ['required', 'numeric', 'min:0'],
        'plumbing' => ['required', 'numeric', 'min:0'],
        'project'  => ['required', 'numeric', 'min:0'],
    ], [
        'required' => 'This field is required.',
        'numeric'  => 'Value must be numeric.',
        'min'      => 'Value cannot be negative.',
    ]);

    $slab               = new Payment_Slab();
    $slab->ID           = uniqid();
    $slab->ClientID     = session('selected_scheme_id');
    $slab->Created      = now();
    $slab->LastEdited   = now();

    $slab->pilnth       = $validated['pilnth'];
    $slab->slab         = $validated['slab'];
    $slab->bricks       = $validated['bricks'];
    $slab->plaster      = $validated['plaster'];
    $slab->floaring     = $validated['floaring'];
    $slab->plumbing     = $validated['plumbing'];
    $slab->project      = $validated['project'];

        $total = $slab->pilnth + $slab->slab + $slab->bricks +
            $slab->plaster + $slab->floaring +
            $slab->plumbing + $slab->project;

        if ($total != 100) {
            return back()->withErrors([
                'total' => "Total must be exactly 100%. Current total is {$total}%"
            ])->withInput();
        }

        $slab->total = $total;
    $slab->save();

    return redirect()->route('PaymentSlab')
                     ->with('success', 'Payment slab added successfully!');
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
    $payslab = Payment_Slab::findOrFail($id);          // Payment_Slab model
    return view('backend.payment_slab.create', compact('payslab'));
}


    /**
     * Update the specified resource in storage.
     */
  public function update(Request $request)
{
    $validated = $request->validate([
        'pilnth'   => 'required|numeric|min:0',
        'slab'     => 'required|numeric|min:0',
        'bricks'   => 'required|numeric|min:0',
        'plaster'  => 'required|numeric|min:0',
        'floaring' => 'required|numeric|min:0',
        'plumbing' => 'required|numeric|min:0',
        'project'  => 'required|numeric|min:0',
    ]);

    $payslab = Payment_Slab::findOrFail($request->id); // hidden <input name="id">

    $payslab->LastEdited = now();
    $payslab->pilnth     = $validated['pilnth'];
    $payslab->slab       = $validated['slab'];
    $payslab->bricks     = $validated['bricks'];
    $payslab->plaster    = $validated['plaster'];
    $payslab->floaring   = $validated['floaring'];
    $payslab->plumbing   = $validated['plumbing'];
    $payslab->project    = $validated['project'];
    $payslab->total      = $validated['pilnth'] + $validated['slab'] + $validated['bricks']
                          + $validated['plaster'] + $validated['floaring']
                          + $validated['plumbing'] + $validated['project'];

            $total = $validated['pilnth']
            + $validated['slab']
            + $validated['bricks']
            + $validated['plaster']
            + $validated['floaring']
            + $validated['plumbing']
            + $validated['project'];

        if ($total != 100) {
            return back()->withErrors([
                'total' => "Total must be exactly 100%. Current total is {$total}%"
            ])->withInput();
        }         

    $payslab->save();

    return redirect()->route('PaymentSlab')
                     ->with('success', 'Payment slab updated successfully!');
}

    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
    $payslab = Payment_Slab::findOrFail($id);

    activity_log(
                'delete',
                'Payment Slab deleted: ' . $payslab->ID,
                $payslab
            );

    $payslab->delete();

    return redirect()
        ->route('PaymentSlab')
        ->with('success', 'Record has been deleted successfully!');
}

}