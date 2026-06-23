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
use App\Models\Backend\Reject_Mat;
use App\Models\Backend\Inv_Product;
use App\Models\Backend\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


class Rejected_MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {
        $clientId = session('selected_scheme_id');

        try {
            
            $query = Reject_Mat::with(['vendor', 'scheme'])
            ->selectRaw('
                MIN(ID) as ID,
                MIN(Date) as Date,
                Invno,
                srno,
                purchasefrom,
                destination
            ')
            ->where('ClientID', $clientId)
            ->groupBy('Invno', 'purchasefrom', 'destination', 'srno')
            ->orderBy('Invno', 'DESC');


            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('Date', function ($row) {
                    return Carbon::parse($row->Date)->format('d-m-Y');
                })
                ->addColumn('vendor_name', function ($row) {
                    return optional($row->vendor)->Name ?? '-';
                })
                ->addColumn('scheme_name', function ($row) {
                    return optional($row->scheme)->Name ?? '-';
                })
                ->filterColumn('vendor_name', function ($query, $keyword) {
                    $query->whereHas('vendor', function ($q) use ($keyword) {
                        $q->where('Name', 'like', "%{$keyword}%");
                    });
                })
            
                ->filterColumn('scheme_name', function ($query, $keyword) {
                    $query->whereHas('scheme', function ($q) use ($keyword) {
                        $q->where('Name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('actions', function ($row) {

                    $editUrl   = route('Rejected_Material.edit', $row->ID);
                    $deleteUrl = route('Rejected_Material.delete', $row->ID);
                    $formId    = 'delete-form-' . $row->ID;

                    $actions = '';

                    if (hasPermission('edit_Return_Material')) {
                        $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_Return_Material')) {
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

                ->rawColumns(['actions'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    return view('backend.Rejected_Material.index');
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

    return view('backend.Rejected_Material.row', [
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
    $currentInvId = $request->currentInvId; // 👈 pass during edit

    $invIds = Inv_Detail::where('destination', $schemeId)
        ->where('purchasefrom', $purchaseFrom)
        ->where('ClientID', $clientId)
        ->pluck('ID');

    $usedInvNos = Rejected_Material::where('purchasefrom', $purchaseFrom)
        ->when($currentInvId, function ($q) use ($currentInvId) {
            $q->where('Invno', '!=', $currentInvId); // 👈 allow current
        })
        ->pluck('Invno');

    $availableInvoices = Inv_Detail::whereIn('ID', $invIds)
        ->whereNotIn('ID', $usedInvNos)
        ->orderBy('Invno')
        ->get();

    $html = '<option value="">Select</option>';
    foreach ($availableInvoices as $inv) {
        $selected = $inv->ID == $currentInvId ? 'selected' : '';
        $html .= "<option value='{$inv->ID}' $selected>{$inv->Invno}</option>";
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

    $maxsrno = Rejected_Material::max('srno');
    $nextsrno = $maxsrno ? $maxsrno + 1 : 1;

    $expDate = Carbon::now()->format('d-m-Y');

    $schemes = SchemeDetail::where('ID', $clientId)
                ->where('completFalg', 0)
                ->get();

    $vendors = Supplier_contractor::where('Type', 'VENDOR')
            ->where('ClientID', session('selected_scheme_id'))
        ->get();
        $contractors = Supplier_contractor::where('Type', 'CONTRACTOR')
            ->where('ClientID', session('selected_scheme_id'))
            ->get();

        $materials = Material::select('Name')
        ->groupBy('Name')
        ->orderBy('Name')
        ->get();
$materialNameToTypes = \App\Models\Backend\Material::orderBy('Name')->get()->groupBy('Name');

    return view('backend.Rejected_Material.create', [
        'cashBalance' => '',
        'schemes' => $schemes,
        'nextsrno' => $nextsrno,
        'expDate' => $expDate,
        'vendors' => $vendors,
            'contractors' => $contractors,
        'materials' => $materials,
        'materialNameToTypes' => $materialNameToTypes,
        'products'   => [],
    ]);
}

public function store(Request $request)
{
    // Validate main required fields
    $request->validate([
        'Date'         => 'required|date_format:d-m-Y',
        'purchasefrom' => 'required',
        'Destination'  => 'required',
        'inv_no'       => 'required',
        'srno'         => 'required|numeric',
        'cnt'          => 'required|numeric|min:1',

        'rate.*'       => 'nullable|numeric|min:0',
    'return_qty.*' => 'nullable|numeric|min:0',
    ]);

    $clientId = session('selected_scheme_id');
    $srno     = $request->srno;
    $invno    = $request->inv_no;
    $cnt      = (int)$request->cnt;

    /* ---------------------------------------------
       1️⃣  CHECK DUPLICATE INVOICE NUMBER
       --------------------------------------------- */
    $invno_count = Rejected_Material::where('Invno', $invno)->count();

    /* ---------------------------------------------
       2️⃣ CHECK MAX SRNO & CONCURRENCY
       --------------------------------------------- */
    $maxSrno = Rejected_Material::max('srno') ?? 0;

    if (!($maxSrno < $srno && $invno_count == 0)) {
        return back()->with('error', 'Concurrency Error! Record Not Added!');
    }

    $parent = Reject_Mat::create([
        'ID'          => uniqid(),
        'ClientID'    => $clientId,
        'created_at'     => now(),
        'updated_at'  => now(),
        'Date'        => date('Y-m-d', strtotime($request->Date)),
        'srno'        => $srno,
        'Invno'       => $invno,
        'purchasefrom'=> $request->purchasefrom,
        'destination' => $request->Destination
    ]);

    /* ---------------------------------------------
       4️⃣ INSERT DETAIL ROWS (LOOP LIKE OLD PHP)
       --------------------------------------------- */
       
   
            $materials = $request->input('material_id', []);
            $types = $request->input('type', []);
            $qtys = $request->input('quantity', []);
            $rqtys = $request->input('return_qty', []);
            $rates = $request->input('rate', []);
            $discs = $request->input('discount', []);
            $taxables = $request->input('taxable', []);
            $cgsts = $request->input('cgst', []);
            $sgsts = $request->input('sgst', []);
            $igsts = $request->input('igst', []);
            $totals = $request->input('total', []);

            for ($i = 0; $i < $cnt; $i++) {

                $material = $materials[$i] ?? null;
                $type = $types[$i] ?? null;

                if (!$material)
                    continue; // 🚀 prevents crash

                $qty = $qtys[$i] ?? 0;
                $rejectedQty = $rqtys[$i] ?? 0;
                $rate = $rates[$i] ?? 0;
                $disc = $discs[$i] ?? 0;
                $taxable = $taxables[$i] ?? 0;

                $cgst = $cgsts[$i] ?? 0;
                $sgst = $sgsts[$i] ?? 0;
                $igst = $igsts[$i] ?? 0;
                $total = $totals[$i] ?? 0;

                $taxable = (float) ($taxables[$i] ?? 0);
                $cgst    = (float) ($cgsts[$i] ?? 0);
                $sgst    = (float) ($sgsts[$i] ?? 0);
                $igst    = (float) ($igsts[$i] ?? 0);

                $cgst_amt = ($taxable * $cgst) / 100;
                $sgst_amt = ($taxable * $sgst) / 100;
                $igst_amt = ($taxable * $igst) / 100;

                Rejected_Material::create([
                    'ID' => uniqid(),
                    'ClientID' => $clientId,
                    'Created' => now(),
                    'Lastedited' => now(),
                    'pid' => $parent->ID,
                    'Date' => date('Y-m-d', strtotime($request->Date)),
                    'srno' => $srno,
                    'Invno' => $invno,
                    'purchasefrom' => $request->purchasefrom,
                    'destination' => $request->Destination,
                    'Material' => $material,
                    'Description' => $type,
                    'Qty' => $qty,
                    'rejected_qty' => $rejectedQty,
                    'Rate' => $rate,
                    'Amount' => $taxable,
                    'CGST' => $cgst,
                    'SGST' => $sgst,
                    'IGST' => $igst,
                    'CGST_amt' => $cgst_amt,
                    'SGST_amt' => $sgst_amt,
                    'IGST_amt' => $igst_amt,
                    'Total' => $total,
                    'Disc' => $disc,
                    'userID' => Auth::id(),
                ]);
            }



    return redirect()->route('Rejected_Material')
                     ->with('success', 'Record Added Successfully!');
}

   
public function edit($id)
{
    $clientId = session('selected_scheme_id');

    // 🔹 1) Get parent row from main table (Reject_Mat) using ID from route
    $invoice = Reject_Mat::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

    // 🔹 2) Use its Invno to load detail rows, exactly like your old logic
    $Invno = $invoice->Invno;

    $products = Rejected_Material::where('ClientID', $clientId)
        ->where('Invno', $Invno)
        ->get();

    // 🔹 3) Vendors
    $vendors = Supplier_contractor::where('Type', 'VENDOR')
            ->where('ClientID', session('selected_scheme_id'))
        ->get();
        $contractors = Supplier_contractor::where('Type', 'CONTRACTOR')
            ->where('ClientID', session('selected_scheme_id'))
            ->get();
            
    // 🔹 4) Schemes
    $schemes = SchemeDetail::where('ID', $clientId)
        ->where('completFalg', 0)
        ->get();

    // 🔹 5) Materials for dropdown
    $materials = Material::select('Name')
        ->groupBy('Name')
        ->orderBy('Name')
        ->get();

    // 🔹 6) Mappings for table binding
    $materialIdToName = Material::whereIn('ID', $products->pluck('Material'))
        ->pluck('Name', 'ID');

    $materialNameToTypes = Material::select('Name', 'Type', 'Unit')
        ->groupBy('Name', 'Type', 'Unit')
        ->orderBy('Type')
        ->get()
        ->groupBy('Name');

    $materialIdToType = Material::whereIn('ID', $products->pluck('Material'))
        ->pluck('Type', 'ID');

    // 🔹 7) Invoice header from Inv_Detail
    $invoiceNumbers = Inv_Detail::where('ID', $Invno)->first();

    // 🔹 8) SR No. (same as before)
    $maxsrno  = Rejected_Material::max('srno');
    $nextsrno = $maxsrno ? $maxsrno + 1 : 1;

    // 🔹 9) Send EXACT SAME variables that your Blade expects
    return view('backend.Rejected_Material.create', [
        'invoice'             => $invoice,          // now from Reject_Mat
        'products'            => $products,         // detail rows
        'schemes'             => $schemes,
        'vendors'             => $vendors,
            'contractors' => $contractors,
        'materials'           => $materials,
        'materialIdToName'    => $materialIdToName,
        'materialNameToTypes' => $materialNameToTypes,
        'materialIdToType'    => $materialIdToType,
        'Invno'               => $Invno,
        'nextsrno'            => $nextsrno,
        'expDate'             => \Carbon\Carbon::now()->format('d-m-Y'),
        'invoiceNumbers'      => $invoiceNumbers,
    ]);
}


public function update(Request $request)
{
    $id = $request->id;  // Parent Reject_Mat ID
    $clientId = session('selected_scheme_id');

    // Validation
    $request->validate([
        'Date'         => 'required|date_format:d-m-Y',
        'purchasefrom' => 'required',
        'Destination'  => 'required',
        'inv_no'       => 'required',
        'srno'         => 'required|numeric',
        'cnt'          => 'required|numeric|min:1',

        'rate.*'       => 'required|numeric|min:0',
    'return_qty.*' => 'required|numeric|min:0',
    ]);

    // ---------------------------
    // 1️⃣ UPDATE PARENT ROW
    // ---------------------------
    $parent = Reject_Mat::where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

    $parent->update([
        'Date'        => date('Y-m-d', strtotime($request->Date)),
        'srno'        => $request->srno,
        'Invno'       => $request->inv_no,
        'purchasefrom'=> $request->purchasefrom,
        'destination' => $request->Destination,
        'updated_at'  => now(),
    ]);

    // ---------------------------
    // 2️⃣ UPDATE EACH CHILD ROW
    // ---------------------------
    $cnt = (int)$request->cnt;

    for ($i = 0; $i < $cnt; $i++) {

        $detailId = $request->detail_id[$i]; // CHILD ROW ID

        $materialName = $request->material_group[$i];
        $type         = $request->type[$i];

        $materialId = Material::where('Name', $materialName)
            ->where('Type', $type)
            ->value('ID');

        $qty     = $request->quantity[$i];
        $rQty    = $request->return_qty[$i];
        $rate    = $request->rate[$i];
        $disc    = $request->discount[$i];
        $taxable = $request->taxable[$i];

        $cgst  = $request->cgst[$i];
        $sgst  = $request->sgst[$i];
        $igst  = $request->igst[$i];
        $total = $request->total[$i];

        $taxable = (float) ($taxables[$i] ?? 0);
        $cgst    = (float) ($cgsts[$i] ?? 0);
        $sgst    = (float) ($sgsts[$i] ?? 0);
        $igst    = (float) ($igsts[$i] ?? 0);

        $cgst_amt = ($taxable * $cgst) / 100;
        $sgst_amt = ($taxable * $sgst) / 100;
        $igst_amt = ($taxable * $igst) / 100;

        // ---------------------------
        // UPDATE CHILD RECORD
        // ---------------------------
        Rejected_Material::where('ID', $detailId)
            ->where('ClientID', $clientId)
            ->update([
                'Lastedited'   => now(),
                'Material'     => $materialId,
                'Description'  => $type,
                'Qty'          => $qty,
                'rejected_qty' => $rQty,
                'Rate'         => $rate,
                'Disc'         => $disc,
                'Amount'       => $taxable,
                'CGST'         => $cgst,
                'SGST'         => $sgst,
                'IGST'         => $igst,
                'CGST_amt'     => $cgst_amt,
                'SGST_amt'     => $sgst_amt,
                'IGST_amt'     => $igst_amt,
                'Total'        => $total,
            ]);
    }

    return redirect()
        ->route('Rejected_Material')
        ->with('success', 'Record Updated Successfully!');
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
            ->route('Rejected_Material')
            ->with('success', 'Record deleted successfully!');

    } catch (\Exception $e) {

        \DB::rollBack();

        return redirect()
            ->route('Rejected_Material')
            ->with('error', 'Delete failed! Nothing was deleted. Error: '.$e->getMessage());
    }
}


}