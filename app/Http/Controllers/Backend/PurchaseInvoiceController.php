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
use App\Models\Backend\Inv_Product;
use App\Models\Backend\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


class PurchaseInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if ($request->ajax()) {
        $clientId = session('selected_scheme_id');

        try {
            $query = Inv_Detail::with(['vendor', 'scheme'])
                ->select(['ID', 'Date', 'Invno', 'gtotal', 'purchasefrom', 'destination'])
                ->where('ClientID', $clientId)
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

                    $editUrl   = route('PurchaseInvoice.edit', $row->ID);
                    $deleteUrl = route('PurchaseInvoice.delete', $row->ID);
                    $formId    = 'delete-form-' . $row->ID;

                    $actions = '';

                    if (hasPermission('edit_purchase_invoice')) {
                        $actions .= '<a href="'.$editUrl.'" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </a>';
                    }

                    if (hasPermission('delete_purchase_invoice')) {
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

    return view('backend.PurchaseInvoice.index');
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

    return view('backend.PurchaseInvoice.row', [
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

public function deleteImage(Request $request)
{
    $filename = $request->name;
    $path = public_path('Uploads/invattachment/' . $filename);

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

    $maxInvno = Inv_Detail::where('ClientID', $clientId)->max('Invno');
    $nextInvno = $maxInvno ? $maxInvno + 1 : 1;

    $expDate = Carbon::now()->format('d-m-Y');

    $schemes = SchemeDetail::where('ID', $clientId)
                ->where('completFalg', 0)
                ->get();

     $vendors = Supplier_contractor::whereIn('Type', ['VENDOR', 'CONTRACTOR'])
        // ->where('ClientID', $clientId)
        ->orderBy('Name')
        ->get();
        $contractors = Supplier_contractor::where('Type', 'CONTRACTOR')
            ->where('ClientID', $clientId)
            ->get();
            
        $materials = Material::select('Name')
        ->groupBy('Name')
        ->orderBy('Name')
        ->get();
        
$materialNameToTypes = \App\Models\Backend\Material::orderBy('Name')->get()->groupBy('Name');

    return view('backend.PurchaseInvoice.create', [
        'cashBalance' => '',
        'schemes' => $schemes,
        'nextInvno' => $nextInvno,
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
    
    $validated = $request->validate([
    'Date'         => ['required', 'date_format:d-m-Y'],
    'purchasefrom' => ['required'],
    'Destination'  => ['required'],

    'material_group'   => ['required', 'array'],
    'material_group.*' => ['required', 'string'],

    'type'   => ['required', 'array'],
    'type.*' => ['required', 'string'],

    'quantity'   => ['required', 'array'],
    'quantity.*' => ['required', 'numeric', 'min:0'],

    'unit'   => ['required', 'array'],
    'unit.*' => ['nullable', 'string'],

    'rate'   => ['required', 'array'],
    'rate.*' => ['required', 'numeric', 'min:0'],

    'discount'   => ['nullable', 'array'],
    'discount.*' => ['nullable', 'numeric', 'min:0'],

    'taxable'   => ['nullable', 'array'],
    'taxable.*' => ['nullable', 'numeric', 'min:0'],

            'cgst' => ['required', 'array'],
            'cgst.*' => ['required', 'numeric', 'min:0', 'required_with:sgst.*'],

            'sgst' => ['required', 'array'],
            'sgst.*' => ['nullable', 'numeric', 'min:0', 'required_with:cgst.*'],

    'igst'   => ['nullable', 'array'],
    'igst.*' => ['nullable', 'numeric', 'min:0'],

    'total'   => ['required', 'array'],
    'total.*' => ['required', 'numeric', 'min:0'],
    
     'other'  => ['required','numeric', 'min:0'],
    'transport'  => ['required','numeric', 'min:0'],
    'round'  => ['required','numeric', 'min:0'],
]);

    $invId = uniqid();
    $clientId = session('selected_scheme_id');
    $attachments = $request->uploadfile ?? [];
    // Create Purchase Order Header
    $inv = new Inv_Detail();
    $inv->ID            = $invId;
    $inv->attachment = implode(',', $attachments);
    $inv->ClientID      = $clientId;
    $inv->Created       = now();
    $inv->LastEdited    = now();
    $inv->Date          = Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d');
    $inv->Invno          = $request->Invno;
    $inv->purchasefrom  = $request->purchasefrom;
    $inv->destination   = $request->Destination;
    $inv->total         = $request->total_taxable ?? 0;
    $inv->totsgst_amt   = $request->total_sgst ?? 0;
    $inv->totigst_amt   = $request->total_igst ?? 0;
    $inv->totcgst_amt   = $request->total_cgst ?? 0;
    $inv->other         = $request->other ?? 0;
    $inv->transport     = $request->transport ?? 0;
    $inv->round         = $request->round ?? 0;
    $inv->loading         = $request->loading ?? 0;
    $inv->unloading         = $request->unloading ?? 0;
    $inv->gtotal        = $request->gtotal ?? 0;
    $inv->otrnar     = $request->narration;
    $inv->narration     = $request->narration;
    $inv->amt_b_tax     = $request->total_taxable ?? 0;
    $inv->userID        = Auth::id();
    $inv->save();

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

        Inv_Product::create([
            'ID'        => uniqid(),
            'ClientID'  => $clientId,
            'Invno'      => $invId,
            'Created'   => now(),
            'LastEdited'=> now(),
            'Material'  => $materialId,
            'Vat'  => 0,
            'Qty'       => $request->quantity[$i],
            'Rate'      => $request->rate[$i],
            'Disc' => is_numeric($request->discount[$i] ?? null)
            ? $request->discount[$i]
            : 0,
            'Amount'    => $taxable,
            'Unit'      => $request->unit[$i] ?? '',
            'CGST'      => $cgst,
            'SGST'      => $sgst,
            'IGST'      => $igst,
            'CGST_amt'  => $cgst_amt,
            'SGST_amt'  => $sgst_amt,
            'IGST_amt'  => $igst_amt,
            'userID'  => Auth::id(),
            'Total'     => $request->total[$i] ?? 0,
        ]);
    }

    return redirect()
        ->route('PurchaseInvoice')
        ->with('success', 'Purchase Order created successfully!');
}
   
public function edit($id)
{
    $clientId = session('selected_scheme_id');

    $invoice = Inv_Detail::where('ClientID', $clientId)->where('ID', $id)->firstOrFail();

    $products = Inv_Product::where('ClientID', $clientId)
                ->where('Invno', $id)
                ->get();

    $vendors = Supplier_contractor::whereIn('Type', ['VENDOR', 'CONTRACTOR'])
    // ->where('ClientID', $clientId)
    ->orderBy('Name')
    ->get();
        $contractors = Supplier_contractor::where('Type', 'CONTRACTOR')
            ->where('ClientID', $clientId)
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

        $existingImages = [];

    if (!empty($invoice->attachment)) {
        // stored as comma-separated list
        $existingImages = explode(',', $invoice->attachment);
    }

    return view('backend.PurchaseInvoice.create', [
        'invoice'   => $invoice,
        'products'   => $products,
        'schemes'    => $schemes,
        'vendors'    => $vendors,
            'contractors' => $contractors,
        'materials'  => $materials,
        'expDate'    => \Carbon\Carbon::now()->format('d-m-Y'),
        'nextInvno'   => $invoice->Invno ?? 1,
        'materialIdToName' => $materialIdToName,
        'existingImages' => $existingImages,
        'materialNameToTypes' => $materialNameToTypes,
        'materialIdToType' => $materialIdToType,
    ]);
}

public function update(Request $request)
{
   $validated = $request->validate([
    'Date'         => ['required', 'date_format:d-m-Y'],
    'purchasefrom' => ['required'],
    'Destination'  => ['required'],

    'material_group'   => ['required', 'array'],
    'material_group.*' => ['required', 'string'],

    'type'   => ['required', 'array'],
    'type.*' => ['required', 'string'],

    'quantity'   => ['required', 'array'],
    'quantity.*' => ['required', 'numeric', 'min:0'],

    'unit'   => ['required', 'array'],
    'unit.*' => ['nullable', 'string'],

    'rate'   => ['required', 'array'],
    'rate.*' => ['required', 'numeric', 'min:0'],

    'discount'   => ['nullable', 'array'],
    'discount.*' => ['nullable', 'numeric', 'min:0'],

    'taxable'   => ['nullable', 'array'],
    'taxable.*' => ['nullable', 'numeric', 'min:0'],
            'cgst' => ['required', 'array'],
            'cgst.*' => ['required', 'numeric', 'min:0', 'required_with:sgst.*'],

            'sgst' => ['required', 'array'],
            'sgst.*' => ['nullable', 'numeric', 'min:0', 'required_with:cgst.*'],

    'igst'   => ['nullable', 'array'],
    'igst.*' => ['nullable', 'numeric', 'min:0'],

    'total'   => ['required', 'array'],
    'total.*' => ['required', 'numeric', 'min:0'],

    'other'  => ['required','numeric', 'min:0'],
    'transport'  => ['required','numeric', 'min:0'],
    'round'  => ['required','numeric', 'min:0'],

]);

    $clientId = session('selected_scheme_id');
    $invId = $request->id;

    // Update Purchase Order Header
    $invoice = Inv_Detail::where('ID', $invId)->where('ClientID', $clientId)->firstOrFail();

    $attachments = $request->uploadfile ?? [];
$invoice->attachment = implode(',', $attachments);

    $invoice->LastEdited    = now();
    $invoice->Date          = Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d');
    $invoice->Invno          = $request->Invno;
    $invoice->purchasefrom  = $request->purchasefrom;
    $invoice->destination   = $request->Destination;
    $invoice->total         = $request->total_taxable ?? 0;
    $invoice->totsgst_amt   = $request->total_sgst ?? 0;
    $invoice->totigst_amt   = $request->total_igst ?? 0;
    $invoice->totcgst_amt   = $request->total_cgst ?? 0;
    $invoice->other         = $request->other ?? 0;
    $invoice->transport     = $request->transport ?? 0;
    $invoice->round         = $request->round ?? 0;
    $invoice->gtotal        = $request->gtotal ?? 0;
    $invoice->narration     = $request->narration;
    $invoice->amt_b_tax     = $request->total_taxable ?? 0;
    $invoice->userID        = Auth::id();
    $invoice->save();

    // Delete old line items
    Inv_Product::where('ClientID', $clientId)
        ->where('Invno', $invId)
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

        Inv_Product::create([
            'ID'        => uniqid(),
            'ClientID'  => $clientId,
            'Invno'      => $invId,
            'Vat'      => 0,
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
            'userID'  => Auth::id(),
            'Total'     => $request->total[$i] ?? 0,
        ]);
    }

    return redirect()
        ->route('PurchaseInvoice')
        ->with('success', 'Record has been updated successfully!');
}

   public function destroy($id)
{
    $inv = Inv_Detail::findOrFail($id);
    $inv->delete();

    $invp = Inv_Product::where('Invno', $id);
    $invp->delete();

    return redirect()
        ->route('PurchaseInvoice')
        ->with('success', 'Record has been deleted successfully!');
}

}