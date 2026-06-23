<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Partners_Investor_Loan;
use Illuminate\Support\Facades\Auth;


class PartnerLoanInvestorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    if ($request->ajax()) {

        $query = Partners_Investor_Loan::select([
            'ID','Name','ContactNo','Address','Email','Type','CFlag'
        ]);

        return DataTables::of($query)
            ->addIndexColumn()

            // ➜ checkbox column
            ->addColumn('CFlag', function ($row) {
                $checked = $row->CFlag ? 'checked' : '';
                return "<input type='checkbox' class='flag-toggle' data-id='$row->ID' $checked>";
            })
            ->rawColumns(['CFlag','actions'])


            // existing actions column
           ->addColumn('actions', function ($row) {
            $editUrl   = route('PartnerLoanInvestor.edit', $row->ID);
            $deleteUrl = route('PartnerLoanInvestor.delete', $row->ID);
            $formId    = 'delete-form-' . $row->ID;
            $actions   = '';

            if (hasPermission('edit_partner_loan_investor')) {
                $actions .= '
                    <a href="' . $editUrl . '" class="me-2 text-primary">
                        <i data-feather="edit-2"></i>
                    </a>';
            }

            if (hasPermission('delete_partner_loan_investor')) {
                $actions .= '
                    <a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                        <i data-feather="trash"></i>
                    </a>
                    <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                    </form>';
            }

            return $actions;
        })

            ->rawColumns(['CFlag','actions'])   // tell DataTables these contain HTML
            ->make(true);
    }

    return view('backend.Partners_Investor_Loan.index');
}

// PartnerLoanInvestorController.php
public function toggleStatus(Request $request)
{
    $request->validate([
        'id'   => 'required|exists:partners,ID',
        'flag' => 'required|boolean'
    ]);

    $partner        = Partners_Investor_Loan::findOrFail($request->id);
    $partner->CFlag = $request->flag;
    $partner->save();

    return response()->json(['success' => true]);
}


    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {   
        $permissions = Permission::all();
        return view('backend.Partners_Investor_Loan.create', compact('permissions'));
    }

        public function store(Request $request)
    {
        $validated = $request->validate([
            'Name'       => ['required', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'Type'       => ['required', 'in:PARTNER,INVESTOR,LOAN'],
            'ContactNo'  => ['required', 'regex:/^[0-9]{10}$/'],
            'Email'      => ['nullable', 'email', 'max:255', 'regex:/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/'],
            'Address'    => ['nullable', 'string', 'max:255'],
        ], [
            'required' => 'This field is required.',
            'in'       => 'Type must be PARTNER, INVESTOR, or LOAN.',
        ]);

        $partner                = new Partners_Investor_Loan();          // table = partners
        $partner->ClientID      = session('selected_scheme_id');
        $partner->Name          = $validated['Name'];
        $partner->Type          = $validated['Type'];
        $partner->Address       = $validated['Address']   ?? null;
        $partner->ContactNo     = $validated['ContactNo'];
        $partner->Email         = $validated['Email']     ?? null;
        $partner->userID        = Auth::id();             // logged‑in user
        $partner->Created       = now();
        $partner->LastEdited    = now();

        $partner->save();

        return redirect()
            ->route('PartnerLoanInvestor')
            ->with('success', 'Record has been added successfully!');
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
    $partners = Partners_Investor_Loan::findOrFail($id);  
    return view('backend.Partners_Investor_Loan.create', compact('partners'));
}


    /**
     * Update the specified resource in storage.
     */
 public function update(Request $request)
{
    $validated = $request->validate([
        'id'         => ['required', 'exists:partners,ID'],      // hidden input
        'Name'       => ['required', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'Type'       => ['required', 'in:PARTNER,INVESTOR,LOAN'],
        'ContactNo'  => ['required', 'regex:/^[0-9]{10}$/'],
        'Email'      => ['nullable', 'email', 'max:255', 'regex:/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/'],
        'Address'    => ['nullable', 'string', 'max:255'],
    ], [
        'required'   => 'This field is required.',
        'in'         => 'Type must be PARTNER, INVESTOR, or LOAN.',
        'exists'     => 'Record not found.',
    ]);

    $partner                = Partners_Investor_Loan::findOrFail($validated['id']);
    $partner->Name          = $validated['Name'];
    $partner->Type          = $validated['Type'];
    $partner->ContactNo     = $validated['ContactNo'];
    $partner->Email         = $validated['Email']   ?? null;
    $partner->Address       = $validated['Address'] ?? null;

    $partner->ClientID      = session('selected_scheme_id');
    $partner->userID        = Auth::id();
    $partner->LastEdited    = now();

    $partner->save();

    return redirect()
        ->route('PartnerLoanInvestor')
        ->with('success', 'Record has been updated successfully!');
}

    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
    $partner = Partners_Investor_Loan::findOrFail($id);

     if (!canDeleteRecord('partners_loan', 'partners', $id)) {
            return redirect()
                ->route('PartnerLoanInvestor')
                ->with('error', 'Cannot delete its used.');
        }

    // 🔹 Log before delete
            activity_log(
                'delete',
                'Partner deleted: ' . $partner->Name,
                $partner
            );
    $partner->delete();

    return redirect()
        ->route('PartnerLoanInvestor')
        ->with('success', 'Record has been deleted successfully!');
}

}
