<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Bank_Acc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;

class SChemeDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    if ($request->ajax()) {
        $query = SchemeDetail::select(['ID','Name','contactperson','Location','cnumber','Created']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('actions', function ($row) {
                $editUrl   = route('Scheme.edit', $row->ID);
                $deleteUrl = route('Scheme.delete', $row->ID);
                $formId    = 'delete-form-' . $row->ID;
                $actions   = '';

                if (hasPermission('edit_scheme_detail')) {
                    $actions .= '
                        <a href="' . $editUrl . '" class="me-2 text-primary">
                            <i data-feather="edit-2"></i>
                        </a>';
                }

                if (hasPermission('delete_scheme_detail')) {
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

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.SchemeDetail.index');
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
    $owners = SchemeDetail::select('cperson')
                ->groupBy('cperson')
                ->get();

    return view('backend.SchemeDetail.create', [
        'scheme' => null,
        'cashBalance' => '',
        'owners' => $owners,
    ]);
}

   public function store(Request $request)
{
    $validated = $request->validate([
   'SiteName' => [
        'required',
        'string',
        'max:255',
        Rule::unique('scheme_step1', 'Name'),
    ],      
        'Address'       => ['nullable', 'string', 'max:255'],
        'Location'      => ['required', 'string', 'max:255'],
            'ContactPerson' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\s\.]+$/u'
            ],
        'ContactNo'     => ['required', 'digits:10'],
        'Email' => ['nullable', 'email', 'max:255', 'regex:/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/'],
        'cperson'       => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'], // used for infyconst
        'CashInHand'    => ['nullable', 'numeric'],
    ]);

    $id = uniqid();

    // Decide cperson logic
    $clientId = session('selected_scheme_id');
    $cperson = $clientId === 'infyconst' ? $request->input('cperson') : $validated['ContactPerson'];

    $scheme = new SchemeDetail();
    $scheme->ID            = $id;
    $scheme->ClientID      = ''; // initially blank, updated in session later
    $scheme->Created       = now();
    $scheme->LastEdited    = now();
    $scheme->Name          = $validated['SiteName'];
    $scheme->Address       = $validated['Address'] ?? null;
    $scheme->cperson       = $cperson;
    $scheme->cnumber       = $validated['ContactNo'];
    $scheme->contactperson = $validated['ContactPerson'];
    $scheme->Location      = $validated['Location'] ?? null;
    $scheme->userID        = Auth::id();
    $scheme->Email         = $validated['Email'] ?? null;
    $scheme->save();

    // Insert into accounts table using Bank_Acc model

    $cashInHand = $request->filled('CashInHand')
    ? (float) $request->CashInHand
    : 0;

    Bank_Acc::create([
        'ID'         => 'Cash In Hand',
        'Name'         => 'Cash In Hand',
        'Created'    => now(),
        'ClientID'   => $id,
        'LastEdited' => now(),
        'ACNo'       => 'Cash In Hand',
        'userID'     => Auth::id(),
        'OBalance'   => $cashInHand,
    ]);

    session()->forget('selected_scheme_id');

    // Set session values
    session([
        'selected_scheme_id' => $id,
        'site'      => $scheme->Name,
    ]);

    return redirect()
        ->route('Scheme')
        ->with('success', 'Record has been added successfully!');
}
   
public function edit($id)
{
    $scheme = SchemeDetail::findOrFail($id);

    $owners = SchemeDetail::select('cperson')
        ->groupBy('cperson')
        ->get();

    // Get cash balance from accounts
    $cashAccount = Bank_Acc::where('ClientID', $id)
                    ->where('ID', 'Cash In Hand')
                    ->first();

    return view('backend.SchemeDetail.create', [
        'scheme'      => $scheme,
        'cashBalance' => $cashAccount->OBalance ?? '',
        'owners'      => $owners,
    ]);
}

public function update(Request $request)
{
    $validated = $request->validate([
        'id'            => ['required', 'exists:scheme_step1,ID'],
        'SiteName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('scheme_step1', 'Name')->ignore($request->id, 'ID'),
            ],  
        'Address'       => ['nullable', 'string', 'max:255'],
        'Location'      => ['required', 'string', 'max:255'],
            'ContactPerson' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\s\.]+$/u'
            ],
        'ContactNo'     => ['required', 'digits:10'],
        'cperson'       => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'Email'         => ['nullable', 'email', 'max:255','regex:/^[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$/'],
        'CashInHand'    => ['nullable', 'numeric'],
    ]);

    $scheme = SchemeDetail::findOrFail($validated['id']);

    $clientId = session('selected_scheme_id');
    $cperson = $clientId === 'infyconst' ? $request->input('cperson') : $validated['ContactPerson'];

    $scheme->LastEdited    = now();
    $scheme->Name          = $validated['SiteName'];
    $scheme->Address       = $validated['Address'] ?? null;
    $scheme->cperson       = $validated['ContactPerson']; 
    $scheme->contactperson = $validated['ContactPerson'];
    $scheme->cnumber       = $validated['ContactNo'];
    $scheme->Location      = $validated['Location'] ?? null;
    $scheme->userID        = Auth::id();

    $scheme->save();

    $cashInHand = $request->filled('CashInHand')
    ? (float) $request->CashInHand
    : 0;

    Bank_Acc::where('ClientID', $scheme->ID)
        ->where('ID', 'Cash In Hand')
        ->update([
            'OBalance'   => $cashInHand,
            'LastEdited' => now(),
        ]);

    return redirect()
        ->route('Scheme')
        ->with('success', 'Record has been updated successfully!');
}


   public function destroy($id)
{
    $scheme = SchemeDetail::findOrFail($id);

    if (!canDeleteRecord('po_detail', 'destination', $id)) {
            return redirect()
                ->route('Scheme')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('inv_detail', 'destination', $id)) {
            return redirect()
                ->route('Scheme')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('labour_work', 'schemeID', $id)) {
            return redirect()
                ->route('Scheme')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('transfer_material', 'from_site', $id)) {
            return redirect()
                ->route('Scheme')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('transfer_material', 'To_site', $id)) {
            return redirect()
                ->route('Scheme')
                ->with('error', 'Cannot delete its used.');
        }

    if (!canDeleteRecord('workorder_detail', 'SiteLocation', $id)) {
            return redirect()
                ->route('Scheme')
                ->with('error', 'Cannot delete its used.');
        }
        
    $scheme->delete();

    return redirect()
        ->route('Scheme')
        ->with('success', 'Record has been deleted successfully!');
}

}