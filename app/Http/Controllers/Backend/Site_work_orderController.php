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
use App\Models\Backend\Workorder_details;
use App\Models\Backend\Workorder_material;
use App\Models\Backend\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Site_work_orderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $clientId = session('selected_scheme_id');

            try {
                $query = Workorder_details::with(['Contractor', 'Scheme'])
                    ->select([
                        'ID',
                        'Date',
                        'gtotal',
                        'TDSAmt',
                        'Total',
                        'retain_amt',
                        'ContractorID',
                        'SiteLocation'
                    ])
                    ->where('ClientID', $clientId)
                    ->orderBy('Date', 'DESC');

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('Date', function ($row) {
                        return Carbon::parse($row->Date)->format('d-m-Y');
                    })
                    ->addColumn('ContractorID', function ($row) {
                        return optional($row->Contractor)->Name ?? '-';
                    })
                    ->addColumn('SiteLocation', function ($row) {
                        return optional($row->Scheme)->Name ?? '-';
                    })
                    ->filterColumn('ContractorID', function ($query, $keyword) {
                        $query->whereHas('Contractor', function ($q) use ($keyword) {
                            $q->where('Name', 'like', "%{$keyword}%");
                        });
                    })

                    ->filterColumn('SiteLocation', function ($query, $keyword) {
                        $query->whereHas('Scheme', function ($q) use ($keyword) {
                            $q->where('Name', 'like', "%{$keyword}%");
                        });
                    })
                    ->addColumn('actions', function ($row) {

                        $editUrl = route('Site_work_order.edit', $row->ID);
                        $deleteUrl = route('Site_work_order.delete', $row->ID);
                        $formId = 'delete-form-' . $row->ID;

                        $actions = '';

                        if (hasPermission('edit_site_work_order')) {
                            $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                    </a>';
                        }

                        if (hasPermission('delete_site_work_order')) {
                            $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '">
                                        <i class="align-middle" data-feather="trash"></i>
                                    </a>
                                    <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                        ' . csrf_field() . '
                                        ' . method_field("DELETE") . '
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

        return view('backend.Site_work_order.index');
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

        $units = DB::table('workorder_material')
            ->select('Unit')
            ->groupBy('Unit')
            ->get();

        return view('backend.Site_work_order.row', [
            'units' => $units,
            'materials' => $uniqueMaterials,              // ⬅ dropdown will be clean
            'materialNameToTypes' => $materialNameToTypes // ⬅ JS will still have all types
        ])->render();
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('file')) {

            $filename = time() . '_' . $request->file('file')->getClientOriginalName();

            $request->file('file')->move(public_path('Uploads/sitework'), $filename);

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
        $path = public_path('Uploads/sitework/' . $filename);

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

        $maxInvno = Workorder_details::where('ClientID', $clientId)->max('WorkorderNo');
        $nextInvno = $maxInvno ? $maxInvno + 1 : 1;

        $expDate = Carbon::now()->format('d-m-Y');

        $schemes = SchemeDetail::where('ID', $clientId)
            ->where('completFalg', 0)
            ->get();

        $vendors = Supplier_contractor::where('Type', 'CONTRACTOR')
            ->where('ClientID', $clientId)
            ->get();

        $materials = Material::select('Name')
            ->groupBy('Name')
            ->orderBy('Name')
            ->get();

        $worktypes = DB::table('workorder_detail')
            ->select('worktype')
            ->groupBy('worktype')
            ->get();

        $units = DB::table('workorder_material')
            ->select('Unit')
            ->groupBy('Unit')
            ->get();

        $materialNameToTypes = \App\Models\Backend\Material::orderBy('Name')->get()->groupBy('Name');

        return view('backend.Site_work_order.create', [
            'cashBalance' => '',
            'schemes' => $schemes,
            'nextInvno' => $nextInvno,
            'expDate' => $expDate,
            'vendors' => $vendors,
            'materials' => $materials,
            'worktypes' => $worktypes,
            'units' => $units,
            'materialNameToTypes' => $materialNameToTypes,
            'products' => [],
        ]);
    }

    public function getRetain(Request $request)
    {
        $vendor = DB::table('vendor')
            ->where('ID', $request->vendor_id)
            ->select('retain_per')
            ->first();

        return response()->json([
            'retain_per' => $vendor->retain_per ?? 0
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'Date' => ['required', 'date_format:d-m-Y'],
            'Startdate' => ['required', 'date_format:d-m-Y'],
            'Completiondate' => ['required', 'date_format:d-m-Y'],
            'purchasefrom' => ['required'],
            'Destination' => ['required'],
            'worktype' => ['required'],

            // Rows
            $request->validate([
                'scope' => ['required', 'array'],
            ]),


            'amount' => ['nullable', 'array'],
            'amount.*' => ['nullable', 'numeric', 'min:0'],

            // Totals
            'Total' => ['required', 'numeric'],
            'tax' => ['required', 'numeric', 'min:0', 'max:100'],
            'tds' => ['required', 'numeric', 'min:0', 'max:100'],
            'TaxAmt' => ['required', 'numeric'],
            'TDSAmt' => ['required', 'numeric'],
            'round' => ['required', 'numeric'],
            'gtotal' => ['required', 'numeric'],
            'retain_per' => ['required', 'numeric'],
            'retain_amt' => ['required', 'numeric'],

        ]);

        $errors = [];

        foreach ($request->scope as $i => $scope) {

            $isLS = $request->lumpsum[$i] ?? 0;

            if (empty($scope)) {
                $errors["scope.$i"][] = "Scope Details is required.";
            }

            if ($isLS == 0) {
                if (empty($request->qty[$i])) {
                    $errors["qty.$i"][] = "Quantity is required.";
                }

                if (empty($request->unit[$i])) {
                    $errors["unit.$i"][] = "Unit is required.";
                }

                if (empty($request->rate[$i])) {
                    $errors["rate.$i"][] = "Rate is required.";
                }
            } else {
                if (empty($request->ls_amount[$i])) {
                    $errors["ls_amount.$i"][] = "Lump-sum Amount is required.";
                }
            }
        }

        if (!empty($errors)) {
            return back()
                ->withErrors($errors)
                ->withInput();
        }


        $today = now()->format('Y-m-d');
        $start = Carbon::createFromFormat('d-m-Y', $request->Startdate)->format('Y-m-d');
        $end = Carbon::createFromFormat('d-m-Y', $request->Completiondate)->format('Y-m-d');

        if ($today < $start) {
            $flag = 0;   // Not Started
        } elseif ($today > $end) {
            $flag = 2;   // Completed
        } else {
            $flag = 1;   // In Progress
        }

        $clientId = session('selected_scheme_id');
        $invId = uniqid();

        // ---------- Save attachment files ----------
        $attachments = $request->uploadfile ?? [];
        $files = implode(',', $attachments);

        // ---------- Insert Header ----------
        $header = new Workorder_details();
        $header->ID = $invId;
        $header->ClientID = $clientId;
        $header->Created = now();
        $header->LastEdited = now();
        $header->narration = $request->narration;
        $header->Date = Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d');
        $header->Startdate = Carbon::createFromFormat('d-m-Y', $request->Startdate)->format('Y-m-d');
        $header->Completiondate = Carbon::createFromFormat('d-m-Y', $request->Completiondate)->format('Y-m-d');
        $header->WorkorderNo = $request->Invno;
        $header->WorkOrder = "site"; // or detect if needed
        $header->ContractorID = $request->purchasefrom;
        $header->SiteLocation = $request->Destination;
        $header->worktype = $request->worktype;
        $header->Total = $request->Total;
        $header->tax = $request->tax;
        $header->tds = $request->tds;
        $header->flag = $flag;
        $header->PaymentFlag = 0;
        $header->TaxAmt = $request->TaxAmt;
        $header->TDSAmt = $request->TDSAmt;
        $header->round = $request->round;
        $header->gtotal = $request->gtotal;
        $header->retain_amt = $request->retain_amt;
        $header->retain_per = $request->retain_per;
        $header->scanimg = $files;
        $header->userID = Auth::id();

        $header->save();

        // ---------- Save Row Items ----------
        $rowCount = count($request->scope);

        for ($i = 0; $i < $rowCount; $i++) {

            $lumpsumAmount = floatval($request->ls_amount[$i] ?? 0);
            $qty = floatval($request->qty[$i] ?? 0);
            $rate = floatval($request->rate[$i] ?? 0);
            $amount = floatval($request->amount[$i] ?? 0);
            $isLS = !empty($request->lumpsum[$i]) ? 1 : 0;

            Workorder_material::create([
                'ID' => uniqid(),
                'ClientID' => $clientId,
                'Created' => now(),
                'Lastedited' => now(),

                'workorderID' => $invId,
                'scope' => $request->scope[$i] ?? '',
                'Islumpsum' => $isLS,
                'Unit' => $request->unit[$i] ?? '',
                'Qty' => $qty,
                'Rate' => $rate,
                'lumsumAmt' => $lumpsumAmount,   // ✔ always numeric
                'Amount' => $amount,          // ✔ always numeric
                'userID' => Auth::id(),          // ✔ always numeric
            ]);

        }

        return redirect()
            ->route('Site_work_order')
            ->with('success', 'Work Order created successfully!');
    }


    public function edit($id)
    {
        $clientId = session('selected_scheme_id');

        // Load header
        $invoice = Workorder_details::where('ClientID', $clientId)
            ->where('ID', $id)
            ->firstOrFail();

        // Load work order items
        $items = Workorder_material::where('ClientID', $clientId)
            ->where('workorderID', $id)
            ->get();

        // Dropdown Data
        $schemes = SchemeDetail::where('ID', $clientId)
            ->where('completFalg', 0)
            ->get();

        $vendors = Supplier_contractor::where('Type', 'CONTRACTOR')
            ->where('ClientID', $clientId)
            ->get();

        $materials = Material::select('Name')
            ->groupBy('Name')
            ->orderBy('Name')
            ->get();

        $worktypes = DB::table('workorder_detail')
            ->select('worktype')
            ->groupBy('worktype')
            ->get();

        $units = DB::table('workorder_material')
            ->select('Unit')
            ->groupBy('Unit')
            ->get();

        $materialNameToTypes = Material::orderBy('Name')->get()->groupBy('Name');

        $existingImages = [];

        if (!empty($invoice->scanimg)) {
            // stored as comma-separated list
            $existingImages = explode(',', $invoice->scanimg);
        }

        return view('backend.Site_work_order.create', [
            'invoice' => $invoice,
            'items' => $items,
            'schemes' => $schemes,
            'vendors' => $vendors,
            'materials' => $materials,
            'worktypes' => $worktypes,
            'units' => $units,
            'nextInvno' => $invoice->WorkorderNo,
            'existingImages' => $existingImages,
            'materialNameToTypes' => $materialNameToTypes
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'Date' => ['required', 'date_format:d-m-Y'],
            'Startdate' => ['required', 'date_format:d-m-Y'],
            'Completiondate' => ['required', 'date_format:d-m-Y'],
            'purchasefrom' => ['required'],
            'Destination' => ['required'],
            'worktype' => ['required'],

            $request->validate([
                'scope' => ['required', 'array'],
            ]),

            'Total' => ['required', 'numeric'],
            'tax' => ['required', 'numeric', 'min:0', 'max:100'],
            'tds' => ['required', 'numeric', 'min:0', 'max:100'],
            'TaxAmt' => ['required', 'numeric'],
            'TDSAmt' => ['required', 'numeric'],
            'round' => ['required', 'numeric'],
            'gtotal' => ['required', 'numeric'],
            'retain_per' => ['required', 'numeric'],
            'retain_amt' => ['required', 'numeric'],
        ]);
        $errors = [];

        foreach ($request->scope as $i => $scope) {

            $isLS = $request->lumpsum[$i] ?? 0;

            if (empty($scope)) {
                $errors["scope.$i"][] = "Scope Details is required.";
            }

            if ($isLS == 0) {
                if (empty($request->qty[$i])) {
                    $errors["qty.$i"][] = "Quantity is required.";
                }

                if (empty($request->unit[$i])) {
                    $errors["unit.$i"][] = "Unit is required.";
                }

                if (empty($request->rate[$i])) {
                    $errors["rate.$i"][] = "Rate is required.";
                }
            } else {
                if (empty($request->ls_amount[$i])) {
                    $errors["ls_amount.$i"][] = "Lump-sum Amount is required.";
                }
            }
        }

        if (!empty($errors)) {
            return back()
                ->withErrors($errors)
                ->withInput();
        }

        $clientId = session('selected_scheme_id');
        $id = $request->id;

        // Load Header
        $header = Workorder_details::where('ClientID', $clientId)
            ->where('ID', $id)
            ->firstOrFail();

        // Flag Logic
        $today = now()->format('Y-m-d');
        $start = Carbon::createFromFormat('d-m-Y', $request->Startdate)->format('Y-m-d');
        $end = Carbon::createFromFormat('d-m-Y', $request->Completiondate)->format('Y-m-d');

        if ($today < $start)
            $flag = 0;
        elseif ($today > $end)
            $flag = 2;
        else
            $flag = 1;

        // Update Header
        $header->update([
            'Date' => Carbon::createFromFormat('d-m-Y', $request->Date)->format('Y-m-d'),
            'Startdate' => $start,
            'Completiondate' => $end,
            'ContractorID' => $request->purchasefrom,
            'SiteLocation' => $request->Destination,
            'worktype' => $request->worktype,
            'narration' => $request->narration,
            'Total' => $request->Total,
            'tax' => $request->tax,
            'tds' => $request->tds,
            'TaxAmt' => $request->TaxAmt,
            'TDSAmt' => $request->TDSAmt,
            'round' => $request->round,
            'gtotal' => $request->gtotal,
            'retain_amt' => $request->retain_amt,
            'retain_per' => $request->retain_per,
            'scanimg' => implode(',', $request->uploadfile ?? []),
            'flag' => $flag,
            'Lastedited' => now(),
        ]);

        // Delete old rows
        Workorder_material::where('ClientID', $clientId)
            ->where('workorderID', $id)
            ->delete();

        // Insert updated rows
        for ($i = 0; $i < count($request->scope); $i++) {

            $lumpsumAmount = floatval($request->ls_amount[$i] ?? 0);
            $qty = floatval($request->qty[$i] ?? 0);
            $rate = floatval($request->rate[$i] ?? 0);
            $amount = floatval($request->amount[$i] ?? 0);
            $isLS = !empty($request->lumpsum[$i]) ? 1 : 0;

            Workorder_material::create([
                'ID' => uniqid(),
                'ClientID' => $clientId,
                'Created' => now(),
                'Lastedited' => now(),
                'workorderID' => $id,
                'scope' => $request->scope[$i],
                'Islumpsum' => $isLS,
                'Unit' => $request->unit[$i] ?? '',
                'Qty' => $qty,
                'Rate' => $rate,
                'lumsumAmt' => $lumpsumAmount,
                'Amount' => $amount,
                'userID' => Auth::id(),
            ]);
        }

        return redirect()
            ->route('Site_work_order')
            ->with('success', 'Work Order updated successfully!');
    }

    public function destroy($id)
    {
        $clientId = session('selected_scheme_id');

        // Fetch work order header
        $header = Workorder_details::where('ClientID', $clientId)
            ->where('ID', $id)
            ->firstOrFail();

        if (!canDeleteRecord('workorder_payment', 'WorkorderID', $id)) {
            return redirect()
                ->route('Site_work_order')
                ->with('error', 'Cannot delete its used.');
        }

        // Delete attachments from folder
        if (!empty($header->scanimg)) {
            $images = explode(',', $header->scanimg);
            foreach ($images as $img) {
                $path = public_path('Uploads/sitework/' . $img);
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        // Delete material rows
        Workorder_material::where('ClientID', $clientId)
            ->where('workorderID', $id)
            ->delete();

        // Delete header
        $header->delete();

        return redirect()
            ->route('Site_work_order')
            ->with('success', 'Work Order deleted successfully!');
    }

}