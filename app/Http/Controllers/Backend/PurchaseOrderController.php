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
use App\Models\Backend\PurchaseOrder;
use App\Models\Backend\PurchaseOrderProduct;
use App\Models\Backend\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {
        $clientId = session('selected_scheme_id');

        try {
            $query = PurchaseOrder::with(['vendor', 'scheme'])
                ->select(['ID', 'Date', 'Pono', 'gtotal', 'purchasefrom', 'destination'])
                ->where('ClientID', $clientId)
                ->orderBy('Pono', 'DESC');

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
                $editUrl   = route('PurchaseOrder.edit', $row->ID);
                $deleteUrl = route('PurchaseOrder.delete', $row->ID);
                $formId    = 'delete-form-' . $row->ID;
                $actions   = '';

                if (hasPermission('edit_purchase_order')) {
                    $actions .= '
                        <a href="' . $editUrl . '" class="me-2 text-primary">
                            <i data-feather="edit-2"></i>
                        </a>';
                }

                if (hasPermission('delete_purchase_order')) {
                    $actions .= '
                        <a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                            <i data-feather="trash"></i>
                        </a>
                        <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                        </form>';
                }
                
                $actions .= '
        <a href="'.route('PurchaseOrder.print', $row->ID).'" target="_blank" class="me-2 text-dark">
            <i data-feather="printer"></i>
        </a>';

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

    return view('backend.PurchaseOrder.index');
}

public function print($id)
{
    $clientId = session('selected_scheme_id');

    $purchase = PurchaseOrder::with(['vendor', 'scheme'])
        ->where('ClientID', $clientId)
        ->where('ID', $id)
        ->firstOrFail();

    $products = PurchaseOrderProduct::where('ClientID', $clientId)
        ->where('Pono', $id)
        ->get();

    return view('backend.PurchaseOrder.print', compact('purchase', 'products'));
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

    return view('backend.PurchaseOrder.row', [
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

    $maxPono = PurchaseOrder::where('ClientID', $clientId)->max('Pono');
    $nextPono = $maxPono ? $maxPono + 1 : 1;

    $expDate = Carbon::now()->format('d-m-Y');

    $schemes = SchemeDetail::where('ID', $clientId)
                ->where('completFalg', 0)
                ->get();

    $vendors = Supplier_contractor::whereIn('Type', ['VENDOR', 'CONTRACTOR'])
    // ->where('ClientID', $clientId)
    ->orderBy('Name')
    ->get();

        $materials = Material::select('Name')
        ->groupBy('Name')
        ->orderBy('Name')
        ->get();
$materialNameToTypes = \App\Models\Backend\Material::orderBy('Name')->get()->groupBy('Name');

    return view('backend.PurchaseOrder.create', [
        'cashBalance' => '',
        'schemes' => $schemes,
        'nextPono' => $nextPono,
        'expDate' => $expDate,
        'vendors' => $vendors,
        'materials' => $materials,
        'materialNameToTypes' => $materialNameToTypes,
        'products'   => [],
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
    'Date'         => ['required', 'date_format:d-m-Y'],
    'Expected'     => ['required', 'date_format:d-m-Y'],
    'purchasefrom' => ['required'],
    'Destination'  => ['required'],

    'material_group'   => ['required', 'array'],
    'material_group.*' => ['required', 'string'],

    'type'   => ['required', 'array'],
    'type.*' => ['required', 'string'],

    'quantity'   => ['required', 'array'],
    'quantity.*' => ['required', 'numeric', 'min:1'],

    'unit'   => ['required', 'array'],
    'unit.*' => ['nullable', 'string'],

    'rate'   => ['required', 'array'],
    'rate.*' => ['required', 'numeric', 'min:1'],

    'discount'   => ['nullable', 'array'],
    'discount.*' => ['nullable', 'numeric', 'min:0'],

    'taxable'   => ['nullable', 'array'],
    'taxable.*' => ['nullable', 'numeric', 'min:0'],

            'cgst' => ['required', 'array'],
'cgst.*' => ['nullable', 'numeric', 'min:0'],

'sgst' => ['required', 'array'],
'sgst.*' => ['nullable', 'numeric', 'min:0'],

'igst' => ['nullable', 'array'],
'igst.*' => ['nullable', 'numeric', 'min:0'],

    'total'   => ['required', 'array'],
    'total.*' => ['required', 'numeric', 'min:0'],
    
     'other'  => ['required','numeric', 'min:0'],
    'transport'  => ['required','numeric', 'min:0'],
    'round'  => ['required','numeric', 'min:0'],
]);
foreach ($request->cgst as $i => $cgst) {

    $sgst = $request->sgst[$i] ?? '';

    // CGST entered but SGST empty
    if ($cgst > 0 && empty($sgst)) {
        return back()
            ->withErrors([
                "sgst.$i" => "SGST is required when CGST is entered."
            ])
            ->withInput();
    }

    // SGST entered but CGST empty
    if ($sgst > 0 && empty($cgst)) {
        return back()
            ->withErrors([
                "cgst.$i" => "CGST is required when SGST is entered."
            ])
            ->withInput();
    }
}
    $poId = uniqid();
    $clientId = session('selected_scheme_id');

    // Create Purchase Order Header
    $po = new PurchaseOrder();
    $po->ID            = $poId;
    $po->ClientID      = $clientId;
    $po->Created       = now();
    $po->LastEdited    = now();
    $po->Date          = Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d');
    $po->Expected      = Carbon::createFromFormat('d-m-Y', $request->Expected)->format('Y-m-d');
    $po->Pono          = $request->Pono;
    $po->purchasefrom  = $request->purchasefrom;
    $po->destination   = $request->Destination;
    $po->total         = $request->total_taxable ?? 0;
    $po->totsgst_amt   = $request->total_sgst ?? 0;
    $po->totigst_amt   = $request->total_igst ?? 0;
    $po->totcgst_amt   = $request->total_cgst ?? 0;
    $po->other         = $request->other ?? 0;
    $po->transport     = $request->transport ?? 0;
    $po->round         = $request->round ?? 0;
    $po->gtotal        = $request->gtotal ?? 0;
    $po->narration     = $request->narration;
    $po->amt_b_tax     = $request->total_taxable ?? 0;
    $po->userID        = Auth::id();
    $po->save();

    // Save Line Items
    $count = count($request->material_group);
    for ($i = 0; $i < $count; $i++) {
        $taxable = $request->taxable[$i] ?? 0;
        $cgst    = $request->cgst[$i] ?? 0;
        $sgst    = $request->sgst[$i] ?? 0;
        $igst    = $request->igst[$i] ?? 0;

        $cgst_amt = ($taxable * $cgst) / 100;
        $sgst_amt = ($taxable * $sgst) / 100;
        $igst_amt = ($taxable * $igst) / 100;

        $materialId = Material::where('Name', $request->material_group[$i])
        ->where('Type', $request->type[$i])
        ->value('ID');

        PurchaseOrderProduct::create([
            'ID'        => uniqid(),
            'ClientID'  => $clientId,
            'Pono'      => $poId,
            'Created'   => now(),
            'LastEdited'=> now(),
            'Material'  => $materialId,
            'Qty'       => $request->quantity[$i],
            'Rate'      => $request->rate[$i],
            'Disc'      => $request->discount[$i] ?? 0,
            'Amount'    => $taxable,
            'Unit'      => $request->unit[$i] ?? '',
            'CGST'      => $cgst,
            'SGST'      => $sgst,
            'IGST'      => $igst,
            'CGST_amt'  => $cgst_amt,
            'SGST_amt'  => $sgst_amt,
            'IGST_amt'  => $igst_amt,
            'Total'     => $request->total[$i] ?? 0,
        ]);
    }

    return redirect()
        ->route('PurchaseOrder')
        ->with('success', 'Purchase Order created successfully!');
}
   
public function edit($id)
{
    $clientId = session('selected_scheme_id');

    $purchase = PurchaseOrder::where('ClientID', $clientId)->where('ID', $id)->firstOrFail();

    $products = PurchaseOrderProduct::where('ClientID', $clientId)
                ->where('Pono', $id)
                ->get();

    $vendors = Supplier_contractor::whereIn('Type', ['VENDOR', 'CONTRACTOR'])
            // ->where('ClientID', $clientId)
            ->orderBy('Name')
            ->get();

    $schemes = SchemeDetail::where('ID', $clientId)
                ->where('completFalg', 0)
                ->get();

    $materials = Material::select('Name')
                ->groupBy('Name')
                ->orderBy('Name')
                ->get();

    $materialIdToName = Material::whereIn('ID', $products->pluck('Material'))
        ->pluck('Name', 'ID');
    $materialNameToTypes = Material::select('Name', 'Type', 'Unit')
    ->groupBy('Name', 'Type', 'Unit')
    ->orderBy('Type')
    ->get()
    ->groupBy('Name');
 $materialIdToType = Material::whereIn('ID', $products->pluck('Material'))
        ->pluck('Type', 'ID');

    return view('backend.PurchaseOrder.create', [
        'purchase'   => $purchase,
        'products'   => $products,
        'schemes'    => $schemes,
        'vendors'    => $vendors,
        'materials'  => $materials,
        'expDate'    => \Carbon\Carbon::now()->format('d-m-Y'),
        'nextPono'   => $purchase->Pono ?? 1,
        'materialIdToName' => $materialIdToName,
        'materialNameToTypes' => $materialNameToTypes,
        'materialIdToType' => $materialIdToType,
    ]);
}

public function update(Request $request)
{
   $validated = $request->validate([
    'Date'         => ['required', 'date_format:d-m-Y'],
    'Expected'     => ['required', 'date_format:d-m-Y'],
    'purchasefrom' => ['required'],
    'Destination'  => ['required'],

    'material_group'   => ['required', 'array'],
    'material_group.*' => ['required', 'string'],

    'type'   => ['required', 'array'],
    'type.*' => ['required', 'string'],

    'quantity'   => ['required', 'array'],
    'quantity.*' => ['required', 'numeric', 'min:1'],

    'unit'   => ['required', 'array'],
    'unit.*' => ['nullable', 'string'],

    'rate'   => ['required', 'array'],
    'rate.*' => ['required', 'numeric', 'min:1'],

    'discount'   => ['nullable', 'array'],
    'discount.*' => ['nullable', 'numeric', 'min:0'],

    'taxable'   => ['nullable', 'array'],
    'taxable.*' => ['nullable', 'numeric', 'min:0'],

            'cgst' => ['required', 'array'],
'cgst.*' => ['nullable', 'numeric', 'min:0'],

'sgst' => ['required', 'array'],
'sgst.*' => ['nullable', 'numeric', 'min:0'],

'igst' => ['nullable', 'array'],
'igst.*' => ['nullable', 'numeric', 'min:0'],

    'total'   => ['required', 'array'],
    'total.*' => ['required', 'numeric', 'min:0'],

    'other'  => ['required','numeric', 'min:0'],
    'transport'  => ['required','numeric', 'min:0'],
    'round'  => ['required','numeric', 'min:0'],

]);
foreach ($request->cgst as $i => $cgst) {

    $sgst = $request->sgst[$i] ?? '';

    // CGST entered but SGST empty
    if ($cgst > 0 && empty($sgst)) {
        return back()
            ->withErrors([
                "sgst.$i" => "SGST is required when CGST is entered."
            ])
            ->withInput();
    }

    // SGST entered but CGST empty
    if ($sgst > 0 && empty($cgst)) {
        return back()
            ->withErrors([
                "cgst.$i" => "CGST is required when SGST is entered."
            ])
            ->withInput();
    }
}
    $clientId = session('selected_scheme_id');
    $poId = $request->id;

    // Update Purchase Order Header
    $purchase = PurchaseOrder::where('ID', $poId)->where('ClientID', $clientId)->firstOrFail();
    $purchase->LastEdited    = now();
    $purchase->Date          = Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d');
    $purchase->Expected      = Carbon::createFromFormat('d-m-Y', $request->Expected)->format('Y-m-d');
    $purchase->Pono          = $request->Pono;
    $purchase->purchasefrom  = $request->purchasefrom;
    $purchase->destination   = $request->Destination;
    $purchase->total         = $request->total_taxable ?? 0;
    $purchase->totsgst_amt   = $request->total_sgst ?? 0;
    $purchase->totigst_amt   = $request->total_igst ?? 0;
    $purchase->totcgst_amt   = $request->total_cgst ?? 0;
    $purchase->other         = $request->other ?? 0;
    $purchase->transport     = $request->transport ?? 0;
    $purchase->round         = $request->round ?? 0;
    $purchase->gtotal        = $request->gtotal ?? 0;
    $purchase->narration     = $request->narration;
    $purchase->amt_b_tax     = $request->total_taxable ?? 0;
    $purchase->userID        = Auth::id();
    $purchase->save();

    // Delete old line items
    PurchaseOrderProduct::where('ClientID', $clientId)
        ->where('Pono', $poId)
        ->delete();

    // Re-insert new line items
    $count = count($request->material_group);
    for ($i = 0; $i < $count; $i++) {
        $taxable = $request->taxable[$i] ?? 0;
        $cgst    = $request->cgst[$i] ?? 0;
        $sgst    = $request->sgst[$i] ?? 0;
        $igst    = $request->igst[$i] ?? 0;

        $cgst_amt = ($taxable * $cgst) / 100;
        $sgst_amt = ($taxable * $sgst) / 100;
        $igst_amt = ($taxable * $igst) / 100;
$materialId = Material::where('Name', $request->material_group[$i])
        ->where('Type', $request->type[$i])
        ->value('ID');

        PurchaseOrderProduct::create([
            'ID'        => uniqid(),
            'ClientID'  => $clientId,
            'Pono'      => $poId,
            'Created'   => now(),
            'LastEdited'=> now(),
            'Material'  => $materialId,
            'Unit'      => $request->unit[$i] ?? '',
            'Qty'       => $request->quantity[$i],
            'Rate'      => $request->rate[$i],
            'Disc'      => $request->discount[$i] ?? 0,
            'Amount'    => $taxable,
            'CGST'      => $cgst,
            'SGST'      => $sgst,
            'IGST'      => $igst,
            'CGST_amt'  => $cgst_amt,
            'SGST_amt'  => $sgst_amt,
            'IGST_amt'  => $igst_amt,
            'Total'     => $request->total[$i] ?? 0,
        ]);
    }

    return redirect()
        ->route('PurchaseOrder')
        ->with('success', 'Record has been updated successfully!');
}

   public function destroy($id)
{
    $po = PurchaseOrder::findOrFail($id);
    $po->delete();

    $pop = PurchaseOrderProduct::where('Pono', $id);
    $pop->delete();

    return redirect()
        ->route('PurchaseOrder')
        ->with('success', 'Record has been deleted successfully!');
}

}