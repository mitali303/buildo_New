<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Add_bill;
use App\Models\Backend\Add_bill_details;
use App\Models\Backend\Flat_details;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Scheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class Add_BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {

        $query = Add_bill::with(['Scheme', 'flatno'])
            ->where('ClientID', session('selected_scheme_id'));

        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('Date', function ($row) {
                return \Carbon\Carbon::parse($row->Date)->format('d-m-Y');
            })

            ->addColumn('scheme_name', function ($row) {
                return $row->Scheme->Name ?? '-';
            })

            ->addColumn('CustomerName', function ($row) {
            return $row->Customer ?? '-';
        })

        ->addColumn('FlatNo', function ($row) {
                return $row->flatno->FlatNo ?? '-';
            })

            ->addColumn('actions', function ($row) {

                $editUrl   = route('Add_bill.edit', $row->ID);
                $deleteUrl = route('Add_bill.delete', $row->ID);

                $actions = '';

                if (hasPermission('edit_add_bill')) {
                    $actions .= '<a href="'.$editUrl.'" class="text-primary me-2">
                                    <i class="align-middle" data-feather="edit"></i>
                                </a>';
                }

                if (hasPermission('delete_add_bill')) {
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

    return view('backend.Add_bill.index');
}

public function uploadImage(Request $request)
{
    if ($request->hasFile('file')) {

        $filename = time() . '_' . $request->file('file')->getClientOriginalName();

        session()->push('temp_bill_uploads', $filename);
        $request->file('file')->move(public_path('Uploads/addbill'), $filename);

        return response()->json([
            'success' => true,
            'filename' => $filename
        ]);
    }

    return response()->json(['success' => false], 400);
}

public function deleteImage(Request $request)
{
    $filename = $request->name;
    $path = public_path('Uploads/addbill/' . $filename);

    if (file_exists($path)) {
        unlink($path);
    }

    return response()->json(['success' => true]);
}
    /**
     * Show the form for creating a new resource. 
     */
    public function create()
    {   
        $clientId = Session::get('selected_scheme_id');
        $permissions = Permission::all();

        $allschemes = SchemeDetail::where('completFalg', 0)
        ->get();

        $wings = Flat_details::where('scheme_ID', $clientId)
        ->distinct()
        ->pluck('Wing');

        return view('backend.Add_bill.create', compact('permissions','allschemes','wings'));
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
    // ✅ Validation
    $validator = Validator::make($request->all(), [
        'Date'       => 'required|date_format:d-m-Y',
        'Destination'    => 'required',
        'Wing'   => 'required|string|max:50',
        'FlatNo'        => 'required|string', // FlatID#Customer
        'tp'         => 'required|string|max:50',

        // Detail fields (SINGLE)
        'billno'     => 'nullable|string|max:255',
        'Amount'     => 'required|numeric|min:1',
        'uploadfile' => 'nullable|array',
        'uploadfile.0' => 'string'
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    DB::beginTransaction();

    try {

        // 🔹 Generate main ID
        $billId = uniqid();

        // ✅ Insert MAIN bill
        DB::table('add_bill')->insert([
            'ID'         => $billId,
            'ClientID'   => session('selected_scheme_id'),
            'Created'    => now(),
            'Lastedited' => now(),
            'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'Project_ID' => $request->Destination,
            'Amount'     => $request->Amount,
            'Wing'       => $request->Wing,
            'FlatNo'     => $request->FlatNo,
            'Flatcat'     => $request->FlatType,
            'Customer'   => $request->CustomerName,
            'scheme'     => $request->Destination,
            'type'       => $request->tp,
            'userID'         => Auth::id(),
        ]);

        // ✅ Handle SINGLE image
        // ✅ Handle uploaded images (array)
        $photos = [];

        if ($request->has('uploadfile')) {
            $photos = $request->uploadfile;   // already array
        }


        // ✅ Insert SINGLE bill detail
        DB::table('add_bill_detail')->insert([
            'ID'         => uniqid(),
            'ClientID'   => session('selected_scheme_id'),
            'Created'    => now(),
            'Lastedited' => now(),
            'type'       => $request->tp,
            'Bid'        => $billId,
            'billNo'     => $request->billNo,
            'Amount'     => $request->Amount,
            'Photo' => implode(',', $photos),
            'userID'         => Auth::id(),
        ]);

        DB::commit();

        session()->forget('temp_bill_uploads');
        return redirect()
            ->route('Add_bill')
            ->with('success', 'Record has been added successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()->back()
            ->with('error', $e->getMessage())
            ->withInput();
    }

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

    $addbill = Add_bill::where('ID', $id)
        ->where('ClientID', $clientId)
        ->firstOrFail();

    $billdet = Add_bill_details::where('Bid', $id)
    ->firstOrFail();

    $allschemes = SchemeDetail::where('completFalg', 0)->get();

    $wings = Flat_details::where('scheme_ID', $clientId)
        ->distinct()
        ->pluck('Wing');

        $existingImages = [];

    if (!empty($billdet->Photo)) {
        // stored as comma-separated list
        $existingImages = explode(',', $billdet->Photo);
    }

    return view('backend.Add_bill.create', compact(
        'addbill',
        'allschemes',
        'billdet',
        'existingImages',
        'wings'
    ));
}

public function update(Request $request)
{
    // ✅ Validation
    $request->validate([
        'id'           => 'required',
        'Date'         => 'required|date_format:d-m-Y',
        'Destination'  => 'required',
        'Wing'         => 'required|string|max:50',
        'FlatNo'       => 'required|string',
        'Amount'       => 'required|numeric|min:1',
        'billNo'       => 'nullable|string|max:255',
        'uploadfile'   => 'nullable|array',
        'uploadfile.*' => 'string',
    ]);

    DB::beginTransaction();

    try {
        $clientId = session('selected_scheme_id');

        // 🔹 MAIN BILL
        $bill = Add_bill::where('ID', $request->id)
            ->where('ClientID', $clientId)
            ->firstOrFail();

        $bill->update([
            'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'Project_ID' => $request->Destination,
            'scheme'     => $request->Destination,
            'Wing'       => $request->Wing,
            'FlatNo'     => $request->FlatNo,
            'Flatcat'    => $request->FlatType,
            'Customer'   => $request->CustomerName,
            'Amount'     => $request->Amount,
            'Lastedited' => now(),
            'userID'     => Auth::id(),
        ]);

        // 🔹 BILL DETAIL
        $billDetail = Add_bill_details::where('Bid', $request->id)
            ->where('ClientID', $clientId)
            ->firstOrFail();

        // ✅ Merge existing + new images
        $photos = [];

        if (!empty($billDetail->Photo)) {
            $photos = explode(',', $billDetail->Photo);
        }

        if ($request->filled('uploadfile')) {
            $photos = array_merge($photos, $request->uploadfile);
        }

        $photos = array_unique($photos); // avoid duplicates

        $billDetail->update([
            'billNo'     => $request->billNo,
            'Amount'     => $request->Amount,
            'Photo'      => implode(',', $photos),
            'Lastedited' => now(),
            'userID'     => Auth::id(),
        ]);

        DB::commit();

        // 🔥 Clear temp uploads after success
        session()->forget('temp_bill_uploads');

        return redirect()
            ->route('Add_bill')
            ->with('success', 'Record updated successfully!');

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->back()
            ->with('error', $e->getMessage())
            ->withInput();
    }
}

    /**
     * Remove the specified resource from storage.
     */
public function destroy($id)
{
    DB::beginTransaction();

    try {
        $clientId = session('selected_scheme_id');

        // 🔹 Get bill
        $bill = Add_bill::where('ID', $id)
            ->where('ClientID', $clientId)
            ->firstOrFail();

        // 🔹 Get bill details
        $billDetail = Add_bill_details::where('Bid', $id)
            ->where('ClientID', $clientId)
            ->first();

        // 🔹 Delete files from disk
        if ($billDetail && !empty($billDetail->Photo)) {
            $photos = explode(',', $billDetail->Photo);

            foreach ($photos as $photo) {
                $path = public_path('Uploads/addbill/' . $photo);
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        // 🔹 Delete bill detail
        Add_bill_details::where('Bid', $id)
            ->where('ClientID', $clientId)
            ->delete();

        // 🔹 Delete bill
        $bill->delete();

        DB::commit();

        return redirect()
            ->route('Add_bill')
            ->with('success', 'Record deleted successfully!');

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()
            ->route('Add_bill')
            ->with('error', 'Unable to delete record. ' . $e->getMessage());
    }
}

}
