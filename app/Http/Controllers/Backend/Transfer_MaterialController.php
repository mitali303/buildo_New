<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Backend\Role;
use App\Models\Backend\Permission;
use App\Models\Backend\Supplier_contractor;
use App\Models\Backend\SchemeDetail;
use App\Models\Backend\Inv_Detail;
use App\Models\Backend\Rejected_Material;
use App\Models\Backend\Transfer_detail;
use App\Models\Backend\Transfer_material;
use App\Models\Backend\Reject_Mat;
use App\Models\Backend\Inv_Product;
use App\Models\Backend\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Transfer_MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {
        $clientId = session('selected_scheme_id');

        try {
            
             $query = Transfer_detail::with([
                'fromsite',
                'Tosite',
                'materials.material'   // 👈 IMPORTANT
            ])
            ->select(['ID', 'Date', 'Srno', 'gtotal', 'from_site', 'To_site'])
            ->where(function($q) use ($clientId){
                    $q->where('from_site', $clientId)
                    ->orWhere('To_site', $clientId);
                })
            ->orderBy('Srno', 'DESC');

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) {

                    $search = request('search.value');

                    if (!empty($search)) {

                        $query->where(function ($q) use ($search) {

                            $q->where('Srno', 'like', "%{$search}%")
                            ->orWhere('Date', 'like', "%{$search}%")
                            ->orWhereHas('fromsite', function ($sq) use ($search) {
                                    $sq->where('Name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('Tosite', function ($sq) use ($search) {
                                    $sq->where('Name', 'like', "%{$search}%");
                            });

                        });
                    }

                }, false)
                ->addColumn('Date', function ($row) {
                    return Carbon::parse($row->Date)->format('d-m-Y');
                })
                ->addColumn('from_site', function ($row) {
                    return optional($row->fromsite)->Name ?? '-';
                })
                ->addColumn('To_site', function ($row) {
                    return optional($row->Tosite)->Name ?? '-';
                })
                ->addColumn('materials', function ($row) {

                    if ($row->materials->isEmpty()) {
                        return '-';
                    }

                    $html = '<table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th>Type</th>
                                        <th class="text-end">Qty</th>
                                    </tr>
                                </thead><tbody>';

                    foreach ($row->materials as $m) {
                        $html .= '
                            <tr>
                                <td>'.($m->material->Name ?? '-').'</td>
                                <td>'.($m->material->Type ?? '-').'</td>
                                <td class="text-end">'.$m->qty.'</td>
                            </tr>';
                    }

                    $html .= '</tbody></table>';

                    return $html;
                })

                ->addColumn('actions', function ($row) {

                    $editUrl   = route('Transfer_Material.edit', $row->ID);
                    $deleteUrl = route('Transfer_Material.delete', $row->ID);
                    $formId    = 'delete-form-' . $row->ID;

                    $actions = '';

                    if (hasPermission('edit_material_transfer')) {
                        $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_material_transfer')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="'.$formId.'">
                                        <i class="align-middle" data-feather="trash"></i>
                                    </a>
                                    <form id="'.$formId.'" action="'.$deleteUrl.'" method="POST" class="d-none">
                                        '.csrf_field().'
                                        '.method_field("DELETE").'
                                    </form>';
                    }

                    return $actions;
                })

                ->rawColumns(['actions','materials'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    return view('backend.Transfer_Material.index');
}

public function getMaterialStock(Request $request)
{
    $materialId = $request->matid;      // material ID
    $excludeId  = $request->edit_id;    // current record ID (optional, for edit)
    $clientId   = session('selected_scheme_id'); // same as old PHP

    if (!$materialId || !$clientId) {
        return response()->json(['qty' => 0]);
    }

    /* ---------------------------------------
       1️⃣ INVOICE (INWARD) QTY
    --------------------------------------- */
    $invoiceQty = DB::table('Inv_Product')
        ->where('Material', $materialId)
        ->where('ClientID', $clientId)
        ->sum('Qty');

    /* ---------------------------------------
       2️⃣ OUTWARD QTY
    --------------------------------------- */
    $outwardQty = DB::table('material_outward')
        ->where('material', $materialId)
        ->where('ClientID', $clientId)
        ->sum('quantity');

    /* ---------------------------------------
       3️⃣ TRANSFER OUT (exclude current edit)
    --------------------------------------- */
    $transferOutQuery = DB::table('transfer_material')
        ->where('matrialID', $materialId)
        ->where('from_site', $clientId);

    if (!empty($excludeId)) {
        $transferOutQuery->where('PID', '!=', $excludeId);
    }

    $transferOutQty = $transferOutQuery->sum('qty');

    /* ---------------------------------------
       4️⃣ TRANSFER IN (exclude current edit)
    --------------------------------------- */
    $transferInQuery = DB::table('transfer_material')
        ->where('matrialID', $materialId)
        ->where('To_site', $clientId);

    if (!empty($excludeId)) {
        $transferInQuery->where('PID', '!=', $excludeId);
    }

    $transferInQty = $transferInQuery->sum('qty');

    /* ---------------------------------------
       5️⃣ CONSUMPTION QTY (exclude edit)
    --------------------------------------- */
    $consumptionQuery = DB::table('material_consumption')
        ->where('material', $materialId)
        ->where('ClientID', $clientId);

    if (!empty($excludeId)) {
        $consumptionQuery->where('Cid', '!=', $excludeId);
    }

    $consumptionQty = $consumptionQuery->sum('qty');

    /* ---------------------------------------
       6️⃣ FINAL AVAILABLE STOCK
    --------------------------------------- */
    $availableQty =
        ($invoiceQty + $transferInQty)
        - ($transferOutQty + $consumptionQty + $outwardQty);

    return response()->json([
        'qty' => max(0, $availableQty)
    ]);
}

public function getTypes(Request $request)
{
    // dd('Material requested: ' . $request->material);
    $name = $request->input('material');

    $types = Material::where('Name', $name)
        ->select('Type', 'Unit')
        ->groupBy('Type', 'Unit')
        ->get();

    return response()->json($types);
}
public function addRow(Request $request)
{
    // Fetch all materials (for full data: Name, Type, Unit)
    $materials = Material::orderBy('Name')->get();

    // Group types by material name for JS
    $materialNameToTypes = $materials->groupBy('Name');

    // Extract unique material names for the dropdown
    $uniqueMaterials = $materials->unique('Name')->values();

    return view('backend.Transfer_Material.row', [
        'materials' => $uniqueMaterials,              // ⬅ dropdown will be clean
        'materialNameToTypes' => $materialNameToTypes // ⬅ JS will still have all types
    ])->render();
}

public function uploadImage(Request $request)
{
    if ($request->hasFile('file')) {

        $filename = time() . '_' . $request->file('file')->getClientOriginalName();

        $request->file('file')->move(public_path('Uploads/invattachment'), $filename);

        return response()->json([
            'success' => true,
            'filename' => $filename
        ]);
    }

    return response()->json(['success' => false], 400);
}

public function getInvoice(Request $request)
{
    $clientId     = Session::get('selected_scheme_id');
    $schemeId     = $request->SchemeID;
    $purchaseFrom = $request->purchaseFrom;

    // Step 1: Get all invoice IDs from inv_detail
    $invIds = Inv_Detail::where('destination', $schemeId)
        ->where('purchasefrom', $purchaseFrom)
        ->where('ClientID', $clientId)
        ->pluck('ID')
        ->toArray();

    if (empty($invIds)) {
        $invIds = [0]; // fallback
    }

    // Step 2: Get already-used invoice numbers from rejected_matrial_detail
    $usedInvNos = Rejected_Material::where('purchasefrom', $purchaseFrom)
        ->pluck('Invno')
        ->toArray();

    if (empty($usedInvNos)) {
        $usedInvNos = [0]; // fallback
    }

    // Step 3: Final available invoices
    $availableInvoices = Inv_Detail::whereIn('ID', $invIds)
        ->whereNotIn('ID', $usedInvNos)
        ->orderBy('Invno')
        ->get();

    // Step 4: Return options HTML (same as old core)
    $html = '<option value="">Select</option>';

    foreach ($availableInvoices as $inv) {
        $html .= '<option value="' . $inv->ID . '">' . $inv->Invno . '</option>';
    }

    return response()->json(['html' => $html]);
}

public function getInvoiceDetails(Request $request)
{
   $invID = $request->input('invID');


    if (!$invID) {
        return response()->json(['rows' => []]);
    }

    // 1) Fetch invoice header
    $invoice = Inv_Detail::where('ID', $invID)->first();

    if (!$invoice) {
        return response()->json(['rows' => []]);
    }

    // 2) Fetch invoice products
    $products = Inv_Product::where('Invno', $invoice->ID)->get();

    $rows = [];

    foreach ($products as $p) {

        $mat = Material::where('ID', $p->Material)->first();

        if (!$mat) continue;

        $rows[] = [
    'material_id'   => $mat->ID,
    'material_name' => $mat->Name,
    'type'          => $mat->Type,   // FIXED
    'qty'           => $p->Qty,
    'unit'          => $p->Unit,   // FIXED
    'rate'          => $p->Rate,
    'disc'          => $p->Disc,
    'amount'        => $p->Amount,
    'cgst'          => $p->CGST,
    'sgst'          => $p->SGST,
    'igst'          => $p->IGST,
    'total'         => $p->Total,
];

    }

    return response()->json([
        'rows' => $rows
    ]);
}



    /**
     * Show the form for creating a new resource. 
     */
 public function create()
{
    $clientId = Session::get('selected_scheme_id');

    $maxsrno = Transfer_detail::max('Srno');
    $nextsrno = $maxsrno ? $maxsrno + 1 : 1;

    $expDate = Carbon::now()->format('d-m-Y');

    $schemes = SchemeDetail::where('ID', $clientId)
                ->where('completFalg', 0)
                ->get();

    $allschemes = SchemeDetail::where('completFalg', 0)
                ->get();

    $vendors = Supplier_contractor::where('Type', 'VENDOR')
        ->where('ClientID', $clientId)
        ->get();

        $materials = Material::select('Name')
        ->groupBy('Name')
        ->orderBy('Name')
        ->get();
$materialNameToTypes = \App\Models\Backend\Material::orderBy('Name')->get()->groupBy('Name');

    return view('backend.Transfer_Material.create', [
        'cashBalance' => '',
        'schemes' => $schemes,
        'allschemes' => $allschemes,
        'nextsrno' => $nextsrno,
        'expDate' => $expDate,
        'vendors' => $vendors,
        'materials' => $materials,
        'materialNameToTypes' => $materialNameToTypes,
        'products'   => [],
    ]);
}

public function getAvailableStock(Request $request)
{
    $materialName = $request->material_name;
    $typeName     = $request->type_name; // <-- SIZE

    // find material ID
    $materialId = Material::where("Name", $materialName)->value("ID");

    if (!$materialId || !$typeName) {
        return response()->json(['qty' => 0]);
    }

    $clientId = session('selected_scheme_id');

    // STOCK table must have (material, type)
    $invoiceQty = DB::table('materialinward_data')
        ->where('material', $materialId)
        ->where('type', $typeName)
        ->where('ClientID', $clientId)
        ->sum('quantity');

    $transferIn = DB::table('transfer_material')
        ->where('matrialID', $materialId)
        ->where('type', $typeName)
        ->where('To_site', $clientId)
        ->sum('qty');

    $transferOut = DB::table('transfer_material')
        ->where('matrialID', $materialId)
        ->where('type', $typeName)
        ->where('from_site', $clientId)
        ->sum('qty');

    $consumptionQty = DB::table('material_consumption')
        ->where('material', $materialId)
        ->where('type', $typeName)
        ->where('ClientID', $clientId)
        ->sum('qty');

    $availableQty = ($invoiceQty + $transferIn) - ($transferOut + $consumptionQty);

    return response()->json([
        'qty' => max(0, $availableQty)
    ]);
}
public function getMaterialId(Request $request)
{
    $materialName = $request->material_name;
    $typeName     = $request->type_name;

    $material = Material::where('Name', $materialName)
        ->where('Type', $typeName)
        ->select('ID', 'Unit')
        ->first();

    if (!$material) {
        return response()->json(['error' => true]);
    }

    return response()->json([
        'material_id' => $material->ID,
        'unit'        => $material->Unit
    ]);
}

public function store(Request $request)
{
    $clientId = Session::get('selected_scheme_id');

    /* ------------------------------------
       1️⃣ VALIDATION
    ------------------------------------ */
    $request->validate([
        'Date'        => 'required|date_format:d-m-Y',
        'Invno'        => 'required|numeric|min:1',
        'to_scheme'     => 'required',
        'material_id' => 'required|array|min:1',
        'material_id.*' => 'required',
        'quantity'    => 'required|array|min:1',
        'quantity.*'  => 'required|numeric|min:0.01',
        'rate'        => 'required|array|min:1',
        'rate.*'      => 'required|numeric|min:0',
        'total'       => 'required|array|min:1',
        'total.*'     => 'required|numeric|min:0',
        'loading'     => 'nullable|numeric',
        'transport'   => 'nullable|numeric',
        'round'       => 'nullable|numeric',
        'gtotal'      => 'required|numeric|min:0',
    ]);

    /* ------------------------------------
       2️⃣ CONCURRENCY CHECK (CORE LOGIC)
    ------------------------------------ */
    $maxSrno = DB::table('transfer_detail')
        ->where('ClientID', $clientId)
        ->max('Srno') ?? 0;

    if ($maxSrno >= $request->Invno) {
        return back()
            ->withInput()
            ->with('error', 'Concurrency Error! Record Not Added!');
    }

    /* ------------------------------------
       3️⃣ TRANSACTION START
    ------------------------------------ */
    DB::beginTransaction();

    try {

        /* --------------------------------
           4️⃣ INSERT PARENT (transfer_detail)
        -------------------------------- */
        $parentId = uniqid();

        DB::table('transfer_detail')->insert([
            'ID'        => $parentId,
            'ClientID'  => $clientId,
            'Created'   => now(),
            'Lastedited'=> now(),
            'Date'      => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'Srno'      => $request->Invno,
            'narration' => $request->narration,
            'transport' => $request->transport ?? 0,
            'from_site' => $clientId,
            'To_site'   => $request->to_scheme,
            'loading'   => $request->loading ?? 0,
            'round'     => $request->round ?? 0,
            'gtotal'    => $request->gtotal,
            'userID'    => auth()->id(),
        ]);

        /* --------------------------------
           5️⃣ INSERT CHILD ROWS
        -------------------------------- */
        $count = count($request->material_id);

        for ($i = 0; $i < $count; $i++) {

            DB::table('transfer_material')->insert([
                'ID'        => uniqid(),
                'ClientID'  => $clientId,
                'Created'   => now(),
                'Lastedited'=> now(),
                'PID'       => $parentId,
                'matrialID' => $request->material_id[$i],
                'from_site' => $clientId,
                'To_site'   => $request->to_scheme,
                'qty'       => $request->quantity[$i],
                'rate'      => $request->rate[$i],
                'Amount'    => $request->total[$i],
                'userID'    => auth()->id(),
            ]);
        }

        /* --------------------------------
           6️⃣ COMMIT
        -------------------------------- */
        DB::commit();

        return redirect()
            ->route('Transfer_Material')
            ->with('success', 'Record has been Added Successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', 'Something went wrong! '.$e->getMessage());
    }
}

public function edit($id)
{
    $clientId = session('selected_scheme_id');

    // 1️⃣ Parent
    $transfer = Transfer_detail::where('ClientID', $clientId)
         ->where(function($q) use ($clientId){
                $q->where('from_site', $clientId)
                ->orWhere('To_site', $clientId);
            })
        ->firstOrFail();

    // 2️⃣ Child rows (KEEP AS ELOQUENT MODELS)
    $products = Transfer_material::where('ClientID', $clientId)
        ->where('PID', $transfer->ID)
        ->get();

    // 3️⃣ Schemes
    $schemes = SchemeDetail::where('ID', $clientId)
        ->where('completFalg', 0)
        ->get();

    $allschemes = SchemeDetail::where('completFalg', 0)->get();

    // 4️⃣ Materials
    $materials = Material::select('Name')
        ->groupBy('Name')
        ->orderBy('Name')
        ->get();

    $materialNameToTypes = Material::select('Name', 'Type', 'Unit', 'ID')
        ->orderBy('Name')
        ->get()
        ->groupBy('Name');

    // 5️⃣ Mapping (USED BY BLADE)
    $materialIdToName = Material::whereIn('ID', $products->pluck('matrialID'))
        ->pluck('Name', 'ID');

    $materialIdToType = Material::whereIn('ID', $products->pluck('matrialID'))
        ->pluck('Type', 'ID');

    // 6️⃣ Return view
    return view('backend.Transfer_Material.create', [
        'isEdit'              => true,
        'transfer'            => $transfer,
        'products'            => $products,   // ✅ Eloquent objects
        'schemes'             => $schemes,
        'allschemes'          => $allschemes,
        'materials'           => $materials,
        'materialNameToTypes' => $materialNameToTypes,
        'materialIdToName'    => $materialIdToName,
        'materialIdToType'    => $materialIdToType,
        'nextsrno'            => $transfer->Srno,
        'expDate'             => Carbon::parse($transfer->Date)->format('d-m-Y'),
    ]);
}

public function update(Request $request, $id)
{
    $clientId = Session::get('selected_scheme_id');

    /* ------------------------------------
       1️⃣ VALIDATION (SAME AS STORE)
    ------------------------------------ */
    $request->validate([
        'Date'           => 'required|date_format:d-m-Y',
        'Invno'          => 'required|numeric|min:1',
        'to_scheme'      => 'required',
        'material_id'    => 'required|array|min:1',
        'material_id.*'  => 'required',
        'quantity'       => 'required|array|min:1',
        'quantity.*'     => 'required|numeric|min:0.01',
        'rate'           => 'required|array|min:1',
        'rate.*'         => 'required|numeric|min:0',
        'total'          => 'required|array|min:1',
        'total.*'        => 'required|numeric|min:0',
        'loading'        => 'nullable|numeric',
        'transport'      => 'nullable|numeric',
        'round'          => 'nullable|numeric',
        'gtotal'         => 'required|numeric|min:0',
    ]);

    /* ------------------------------------
       2️⃣ CONCURRENCY CHECK (EDIT SAFE)
    ------------------------------------ */
    $maxSrno = DB::table('transfer_detail')
        ->where('ClientID', $clientId)
        ->where('ID', '!=', $id) // 🔥 exclude current
        ->max('Srno') ?? 0;

    if ($maxSrno >= $request->Invno) {
        return back()
            ->withInput()
            ->withErrors(['Invno' => 'Concurrency error! Please refresh and try again.']);
    }

    /* ------------------------------------
       3️⃣ TRANSACTION START
    ------------------------------------ */
    DB::beginTransaction();

    try {

        /* --------------------------------
           4️⃣ UPDATE PARENT (transfer_detail)
        -------------------------------- */
        DB::table('transfer_detail')
            ->where('ID', $id)
            ->where('ClientID', $clientId)
            ->update([
                'Lastedited' => now(),
                'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'Srno'       => $request->Invno,
                'narration'  => $request->narration,
                'transport'  => $request->transport ?? 0,
                'loading'    => $request->loading ?? 0,
                'round'      => $request->round ?? 0,
                'from_site'  => $clientId,
                'To_site'    => $request->to_scheme,
                'gtotal'     => $request->gtotal,
                'userID'     => auth()->id(),
            ]);

        /* --------------------------------
           5️⃣ DELETE OLD CHILD ROWS
           (CORE LOGIC MATCH)
        -------------------------------- */
        DB::table('transfer_material')
            ->where('PID', $id)
            ->where('ClientID', $clientId)
            ->delete();

        /* --------------------------------
           6️⃣ INSERT NEW CHILD ROWS
        -------------------------------- */
        $count = count($request->material_id);

        for ($i = 0; $i < $count; $i++) {

            DB::table('transfer_material')->insert([
                'ID'        => uniqid(),
                'ClientID'  => $clientId,
                'Created'   => now(),
                'Lastedited'=> now(),
                'PID'       => $id,
                'matrialID' => $request->material_id[$i],
                'from_site' => $clientId,
                'To_site'   => $request->to_scheme,
                'qty'       => $request->quantity[$i],
                'rate'      => $request->rate[$i],
                'Amount'    => $request->total[$i],
                'userID'    => auth()->id(),
            ]);
        }

        /* --------------------------------
           7️⃣ COMMIT
        -------------------------------- */
        DB::commit();

        return redirect()
            ->route('Transfer_Material')
            ->with('success', 'Record Updated Successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', 'Update failed! '.$e->getMessage());
    }
}


  public function destroy($id)
{
    $clientId = session('selected_scheme_id');

    try {
        \DB::beginTransaction();

        // 1️⃣ Validate parent exists
        $parent = Reject_Mat::where('ClientID', $clientId)
                    ->where('ID', $id)
                    ->firstOrFail();

        // 2️⃣ Delete child rows using pid
        Rejected_Material::where('ClientID', $clientId)
            ->where('pid', $id)
            ->delete();

        // 3️⃣ Delete parent
        $parent->delete();

        \DB::commit();

        return redirect()
            ->route('Transfer_Material')
            ->with('success', 'Record deleted successfully!');

    } catch (\Exception $e) {

        \DB::rollBack();

        return redirect()
            ->route('Transfer_Material')
            ->with('error', 'Delete failed! Nothing was deleted. Error: '.$e->getMessage());
    }
}


}