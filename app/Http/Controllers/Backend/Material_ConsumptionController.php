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
use App\Models\Backend\Material_Consumption;
use App\Models\Backend\Consumption_Details;
use App\Models\Backend\Reject_Mat;
use App\Models\Backend\Inv_Product;
use App\Models\Backend\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Material_ConsumptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {
        $clientId = session('selected_scheme_id');

       $query = Consumption_Details::with([
            'Scheme',
            'materials.materialDetail'
        ])
        ->select(['ID', 'Date', 'scheme'])
        ->where('ClientID', $clientId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) {

                $search = request('search.value');

                if ($search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('Date', 'like', "%{$search}%")

                        ->orWhereHas('Scheme', function ($sq) use ($search) {
                                $sq->where('Name', 'like', "%{$search}%");
                        })

                        ->orWhereHas('materials', function ($sq) use ($search) {
                                $sq->where('material', 'like', "%{$search}%")
                                ->orWhere('Qty', 'like', "%{$search}%")
                                ->orWhere('Unit', 'like', "%{$search}%");
                        });

                    });
                }

            }, false)
            ->addColumn('Date', function ($row) {
                return \Carbon\Carbon::parse($row->Date)->format('d-m-Y');
            })

            ->addColumn('scheme', function ($row) {
                return optional($row->Scheme)->Name ?? '-';
            })

           // MATERIAL - TYPE
            ->addColumn('material', function ($row) {
                return '<table class="inner-table">' .
                    $row->materials->map(function ($m) {
                        $name = optional($m->materialDetail)->Name;
                        $type = optional($m->materialDetail)->Type;
                        return "<tr><td>{$name} - {$type}</td></tr>";
                    })->implode('') .
                '</table>';
            })

            // UNIT
            ->addColumn('Unit', function ($row) {
                return '<table class="inner-table">' .
                    $row->materials->map(function ($m) {
                        return "<tr><td>{$m->Unit}</td></tr>";
                    })->implode('') .
                '</table>';
            })

            // QUANTITY
            ->addColumn('Qty', function ($row) {
                return '<table class="inner-table">' .
                    $row->materials->map(function ($m) {
                        return "<tr><td>{$m->Qty}</td></tr>";
                    })->implode('') .
                '</table>';
            })

            ->addColumn('actions', function ($row) {

                $editUrl   = route('Material_Consumption.edit', $row->ID);
                $deleteUrl = route('Material_Consumption.delete', $row->ID);
                $formId    = 'delete-form-' . $row->ID;

                $actions = '';

                if (hasPermission('edit_material_consumption')) {
                    $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                    <i class="align-middle" data-feather="edit-2"></i>
                                </a>';
                }

                if (hasPermission('delete_material_consumption')) {
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

            ->rawColumns(['material', 'Unit', 'Qty', 'actions'])
            ->make(true);
    }

    return view('backend.Material_Consumption.index');
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

    return view('backend.Material_Consumption.row', [
        'materials' => $uniqueMaterials,              // ⬅ dropdown will be clean
        'materialNameToTypes' => $materialNameToTypes // ⬅ JS will still have all types
    ])->render();
}

    /**
     * Show the form for creating a new resource. 
     */
 public function create()
{
    $clientId = Session::get('selected_scheme_id');

    $expDate = Carbon::now()->format('d-m-Y');

    $schemes = SchemeDetail::where('ID', $clientId)
                ->where('completFalg', 0)
                ->get();

        $materials = Material::select('Name')
        ->groupBy('Name')
        ->orderBy('Name')
        ->get();
$materialNameToTypes = \App\Models\Backend\Material::orderBy('Name')->get()->groupBy('Name');

    return view('backend.Material_Consumption.create', [
        'cashBalance' => '',
        'schemes' => $schemes,
        'expDate' => $expDate,
        'materials' => $materials,
        'materialNameToTypes' => $materialNameToTypes,
        'products'   => [],
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
    $clientId = session('selected_scheme_id'); // same as $_SESSION['Client_Id']

    /* -----------------------------
       1️⃣ VALIDATION
    ----------------------------- */
    $request->validate([
        'Date'            => 'required|date_format:d-m-Y',
        'scheme'          => 'required',
        'material_id'     => 'required|array|min:1',
        'material_id.*'   => 'required',
        'unit'            => 'required|array|min:1',
        'unit.*'          => 'required|string',
        'quantity'        => 'required|array|min:1',
        'quantity.*'      => 'required|numeric|min:0.01',
    ]);

    DB::beginTransaction();

    try {

        /* -----------------------------
           2️⃣ INSERT PARENT
           (consumption_detail)
        ----------------------------- */
        $parentId = uniqid();

        DB::table('consumption_detail')->insert([
            'ID'         => $parentId,
            'ClientID'   => $clientId,
            'Created'    => now(),
            'Lastedited' => now(),
            'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'scheme'     => $request->scheme,
            'appflag'     => 0,
            'userID'     => auth()->id(),
        ]);

        /* -----------------------------
           3️⃣ INSERT CHILD ROWS
           (material_consumption)
        ----------------------------- */
        $rowCount = count($request->material_id);

        for ($i = 0; $i < $rowCount; $i++) {

            // 🔒 skip empty rows (important)
            if (
                empty($request->material_id[$i]) ||
                empty($request->quantity[$i])
            ) {
                continue;
            }

            DB::table('material_consumption')->insert([
                'ID'         => uniqid(),
                'ClientID'   => $clientId,
                'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'Cid'        => $parentId,      // 🔥 LINK TO PARENT
                'Created'    => now(),
                'Lastedited' => now(),
                'Material'   => $request->material_id[$i],
                'Unit'       => $request->unit[$i],
                'Qty'        => $request->quantity[$i],
                'userID'     => auth()->id(),
            ]);
        }

        DB::commit();

        return redirect()
            ->route('Material_Consumption')
            ->with('success', 'Record has been Added Successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', 'Something went wrong! ' . $e->getMessage());
    }
}
public function edit($id)
{
    $clientId = Session::get('selected_scheme_id');

    /* -----------------------------
       1️⃣ Parent (consumption_detail)
    ----------------------------- */
    $transfer = Consumption_Details::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

    /* -----------------------------
       2️⃣ Child rows (material_consumption)
       NOTE: Material = NAME
    ----------------------------- */
    $products = Material_Consumption::where('ClientID', $clientId)
        ->where('Cid', $transfer->ID)
        ->get();

    /* -----------------------------
       3️⃣ Schemes
    ----------------------------- */
    $schemes = SchemeDetail::where('completFalg', 0)->get();

    /* -----------------------------
       4️⃣ Materials (for dropdown)
    ----------------------------- */
    $materials = Material::select('Name')
        ->groupBy('Name')
        ->orderBy('Name')
        ->get();


        // 🔹 Build ID → Name mapping
    $materialIdToName = Material::whereIn(
        'ID',
        $products->pluck('material')
    )->pluck('Name', 'ID');

    // 🔹 Build ID → Type mapping
    $materialIdToType = Material::whereIn(
        'ID',
        $products->pluck('material')
    )->pluck('Type', 'ID');

    /* -----------------------------
       5️⃣ Name → Types → Units
       (USED DIRECTLY BY BLADE)
    ----------------------------- */
    $materialNameToTypes = Material::select('Name', 'Type', 'Unit')
        ->orderBy('Name')
        ->get()
        ->groupBy('Name');

    /* -----------------------------
       6️⃣ Return view
    ----------------------------- */
    return view('backend.Material_Consumption.create', [
        'isEdit'              => true,
        'transfer'            => $transfer,
        'products'            => $products,   // ✅ contains Material NAME
        'schemes'             => $schemes,
        'materials'           => $materials,
        'materialNameToTypes' => $materialNameToTypes,
        'materialIdToName'    => $materialIdToName,   // ✅ ADD
        'materialIdToType'    => $materialIdToType,
        'expDate'             => Carbon::parse($transfer->Date)->format('d-m-Y'),
    ]);
}


public function update(Request $request, $id)
{
    $clientId = Session::get('selected_scheme_id');

    /* -----------------------------
       1️⃣ VALIDATION
    ----------------------------- */
    $request->validate([
        'Date'          => 'required|date_format:d-m-Y',
        'scheme'        => 'required',
        'material_id'   => 'required|array|min:1',
        'material_id.*' => 'required',
        'unit'          => 'required|array|min:1',
        'unit.*'        => 'required',
        'quantity'      => 'required|array|min:1',
        'quantity.*'    => 'required|numeric|min:0.01',
    ]);

    DB::beginTransaction();

    try {

        /* -----------------------------
           2️⃣ UPDATE PARENT
           (consumption_detail)
        ----------------------------- */
        DB::table('consumption_detail')
            ->where('ID', $id)
            ->where('ClientID', $clientId)
            ->update([
                'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'LastEdited' => now(),
                'scheme'     => $request->scheme,
                'userID'     => auth()->id(),
            ]);

        /* -----------------------------
           3️⃣ DELETE OLD CHILD ROWS
           (material_consumption)
        ----------------------------- */
        DB::table('material_consumption')
            ->where('Cid', $id)
            ->where('ClientID', $clientId)
            ->delete();

        /* -----------------------------
           4️⃣ INSERT NEW CHILD ROWS
        ----------------------------- */
        $count = count($request->material_id);

        for ($i = 0; $i < $count; $i++) {

            // skip empty rows (safety)
            if (
                empty($request->material_id[$i]) ||
                empty($request->quantity[$i])
            ) {
                continue;
            }

            DB::table('material_consumption')->insert([
                'ID'         => uniqid(),
                'ClientID'   => $clientId,
                'Date'       => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
                'Cid'        => $id,
                'Created'    => now(),
                'LastEdited' => now(),
                'material'   => $request->material_id[$i],  // MATERIAL ID
                'Unit'       => $request->unit[$i],
                'Qty'        => $request->quantity[$i],
                'userID'     => auth()->id(),
            ]);
        }

        DB::commit();

        return redirect()
            ->route('Material_Consumption')
            ->with('success', 'Record Updated Successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', 'Update failed! ' . $e->getMessage());
    }
}


public function destroy($id)
{
    $clientId = session('selected_scheme_id');

    DB::beginTransaction();

    try {

        /* -----------------------------
           1️⃣ DELETE CHILD ROWS
           (material_consumption)
           OLD: Cid = Uid
        ----------------------------- */
        DB::table('material_consumption')
            ->where('Cid', $id)
            ->where('ClientID', $clientId)
            ->delete();

        /* -----------------------------
           2️⃣ DELETE PARENT
           (consumption_detail)
           OLD: ID = Uid
        ----------------------------- */
        DB::table('consumption_detail')
            ->where('ID', $id)
            ->where('ClientID', $clientId)
            ->delete();

        DB::commit();

        return redirect()
            ->route('Material_Consumption')
            ->with('success', 'Record Deleted Successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->route('Material_Consumption')
            ->with('error', 'Delete failed! ' . $e->getMessage());
    }
}


}