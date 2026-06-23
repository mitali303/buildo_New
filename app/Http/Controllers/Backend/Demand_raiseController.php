<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Demand_raise;
use App\Models\Backend\Flat_details;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Scheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class Demand_raiseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {

        $query = Demand_raise::with(['Scheme', 'flatno'])
            ->where('ClientID', session('selected_scheme_id'));

        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('Date', function ($row) {
                return \Carbon\Carbon::parse($row->Date)->format('d-m-Y');
            })

            ->addColumn('scheme_name', function ($row) {
                return $row->Scheme->Name ?? '-';
            })

            ->addColumn('flat_no', function ($row) {
                return $row->flatno->FlatNo ?? '-';
            })

            ->editColumn('Due_date', function ($row) {
                return \Carbon\Carbon::parse($row->Due_date)->format('d-m-Y');
            })

            ->addColumn('actions', function ($row) {

                $editUrl   = route('demand_raise.edit', $row->ID);
                $deleteUrl = route('demand_raise.delete', $row->ID);

                $actions = '';

                if (hasPermission('edit_customer_demand_raise')) {
                    $actions .= '<a href="'.$editUrl.'" class="text-primary me-2">
                                    <i class="align-middle" data-feather="edit"></i>
                                </a>';
                }

                if (hasPermission('delete_customer_demand_raise')) {
                    $actions .= '<form action="'.$deleteUrl.'" method="POST" style="display:inline">
                                    '.csrf_field().'
                                    '.method_field("DELETE").'
                                    <button type="submit" class="btn btn-link text-danger p-0"
                                        onclick="return confirm(\'Are you sure?\')">
                                        <i class="align-middle" data-feather="trash"></i>
                                    </button>
                                </form>';
                }

                return $actions;
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('backend.Demand_raise.index');
}

    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {   
        $clientId = Session::get('selected_scheme_id');
        $permissions = Permission::all();

        $allschemes = SchemeDetail::where('completFalg', 0)->where('ID', $clientId)
        ->get();

        $wings = Flat_details::where('scheme_ID', $clientId)
        ->distinct()
        ->pluck('Wing');

        return view('backend.Demand_raise.create', compact('permissions','allschemes','wings'));
    }

public function getFlatNosByWing(Request $request)
{
    $clientId = session('selected_scheme_id');
    $wing = $request->wing;

    $flats = Flat_details::where('scheme_ID', $clientId)
        ->where('Wing', $wing)
        ->select('ID', 'FlatNo')
        ->get();

    return response()->json($flats);
}

public function getFlatDetails(Request $request)
{
    $clientId = session('selected_scheme_id');
    $flatId = $request->flat_id;

    $flat = Flat_details::where('ID', $flatId)
        ->where('scheme_ID', $clientId)
        ->first();

    $customer = DB::table('booking_customer')
        ->where('FlatNo', $flatId ?? null)
        ->where('ClientID', $clientId)
        ->where('cancel_flag', 0)
        ->first();


    return response()->json([
        'customer_name' => $customer->CutomerName ?? '',
        'flat_type'     => $flat->FlatType ?? ''
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    
        /* ==========================
           VALIDATION
        ========================== */
        $request->validate([
            'Date'         => 'required|date',
            'Destination'  => 'required',
            'Wing'         => 'required|string',
            'FlatID'       => 'required',
            'FlatNo'       => 'required',
            'CustomerName'       => 'required',
            'FlatType'       => 'required',
            'Demand_amt'   => 'required|numeric|min:1',
            'InterestRate' => 'required|numeric|min:0',
            'Due_date'     => 'required|date|after_or_equal:Date',
        ]);

        $clientId = session('selected_scheme_id');

        if (!$clientId) {
            throw new \Exception('Client not selected');
        }

        /* ==========================
           INSERT DEMAND RAISE
        ========================== */
        DB::table('demand_rase')->insert([
            'ID'           => uniqid(),
            'ClientID'     => $clientId,
            'Date'         => Carbon::parse($request->Date)->format('Y-m-d'),
            'scheme'       => $request->Destination,
            'Wing'         => $request->Wing,
            'FlatNo'       => $request->FlatID,
            'InterestRate' => $request->InterestRate,
            'Demand_amt'   => $request->Demand_amt,
            'Due_date'     => Carbon::parse($request->Due_date)->format('Y-m-d'),
            'customer'     => $request->CustomerName,
            'userID'       => auth()->id(),
        ]);

        return redirect()
            ->route('demand_raise')
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
    $clientId = session('selected_scheme_id');

    $demandraise = Demand_raise::where('ID', $id)
        ->where('ClientID', $clientId)
        ->firstOrFail();

    $allschemes = SchemeDetail::where('completFalg', 0)->get();

    $wings = Flat_details::where('scheme_ID', $clientId)
        ->distinct()
        ->pluck('Wing');

    return view('backend.Demand_raise.create', compact(
        'demandraise',
        'allschemes',
        'wings'
    ));
}

public function update(Request $request)
{
    $request->validate([
        'id'           => 'required',
        'Date'         => 'required|date',
        'Destination'  => 'required',
        'Wing'         => 'required',
        'FlatID'       => 'required',
        'FlatNo'       => 'required',
        'CustomerName'       => 'required',
        'FlatType'       => 'required',
        'Demand_amt'   => 'required|numeric|min:1',
        'InterestRate' => 'required|numeric|min:0',
        'Due_date'     => 'required|date'
    ]);

    $demand = Demand_raise::where('ID', $request->id)->firstOrFail();

    $demand->update([
        'Date'         => Carbon::parse($request->Date)->format('Y-m-d'),
        'scheme'       => $request->Destination,
        'Wing'         => $request->Wing,
        'FlatNo'       => $request->FlatID,
        'Demand_amt'   => $request->Demand_amt,
        'InterestRate' => $request->InterestRate,
        'Due_date'     => Carbon::parse($request->Due_date)->format('Y-m-d'),
        'customer'     => $request->CustomerName,
        'userID'       => auth()->id(),
    ]);

    return redirect()
        ->route('demand_raise')
        ->with('success', 'Record updated successfully!');
}

    /**
     * Remove the specified resource from storage.
     */
public function destroy($id)
{
    Demand_raise::where('ID', $id)->delete();

    return redirect()
        ->route('demand_raise')
        ->with('success', 'Record deleted successfully!');
}

}
