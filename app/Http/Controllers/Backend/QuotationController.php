<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Estimate;
use App\Models\Backend\Product;
use App\Models\Backend\Quotation;
use App\Models\Backend\QuotationMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Quotation::select([
                'quotations.id',
                'quotations.quotation_no',
                'quotations.type',
                'quotations.date',
                'quotations.total_amount',
                'quotations.status',
                'quotations.firm_id',
                'quotations.customer_id',
            ])
                ->with(['firm', 'customer', 'saleOrder', 'proformaInvoice'])
                ->orderByDesc('id');

            return DataTables::of($query)

                ->addIndexColumn()

                ->addColumn('firm_name', fn ($row) => $row->firm->firm_name ?? '-')

                ->addColumn('customer_name', fn ($row) => $row->customer->name ?? '-')

                ->editColumn('type', function ($row) {
                    return $row->type == Quotation::TYPE_SUPPLY
                        ? '<span class="badge bg-info">Supply</span>'
                        : '<span class="badge bg-warning text-dark">Installation</span>';
                })

                ->editColumn('total_amount', fn ($row) => number_format($row->total_amount, 2))

                ->editColumn('status', function ($row) {
                    $labels = [
                        0 => '<span class="badge bg-danger">Cancelled</span>',
                        1 => '<span class="badge bg-secondary">Draft</span>',
                        2 => '<span class="badge bg-info">Sent</span>',
                        3 => '<span class="badge bg-success">Approved</span>',
                    ];
                    return $labels[$row->status] ?? '-';
                })

                ->filterColumn('status', function ($query, $keyword) {
                    if (is_numeric($keyword)) {
                        $query->where('quotations.status', $keyword);
                    }
                })

                ->addColumn('actions', function ($row) {

                    $viewUrl = route('Quotation.show', $row->id);
                    $editUrl = route('Quotation.edit', $row->id);
                    $deleteUrl = route('Quotation.delete', $row->id);
                    $formId = 'delete-form-' . $row->id;

                    $actions = '<a href="' . $viewUrl . '" class="me-2 text-secondary" title="View">
                                    <i class="align-middle" data-feather="eye"></i>
                                </a>';

                    $hasSaleOrder = (bool) $row->saleOrder;
                    $hasProforma = (bool) $row->proformaInvoice;

                    // A real Sale Order is the only thing that fully closes a quotation.
                    if ($hasSaleOrder) {
                        $actions .= '<span class="text-muted me-2" title="Converted to Sale Order — locked">
                                        <i class="align-middle" data-feather="lock"></i>
                                     </span>';
                        return $actions;
                    }

                    // No Sale Order yet — editing is locked as soon as a Proforma exists
                    // (so the numbers can't drift from what was already sent to the customer),
                    // but the path to Sale Order must stay open — Proforma is just a side branch.
                    if ($hasProforma) {
                        $actions .= '<span class="text-muted me-2" title="Proforma sent — edit locked, but you can still convert to Sale Order">
                                        <i class="align-middle" data-feather="lock"></i>
                                     </span>';

                        $actions .= '<a href="' . route('SaleOrder.convert', $row->id) . '" class="me-2 text-success" title="Convert to Sale Order">
                                        <i class="align-middle" data-feather="shopping-cart"></i>
                                     </a>';

                        return $actions;
                    }

                    // Not converted at all yet — Approved quotations get both Convert shortcuts
                    if ($row->status == 3) {

                        $actions .= '<a href="' . route('SaleOrder.convert', $row->id) . '" class="me-2 text-success" title="Convert to Sale Order">
                                        <i class="align-middle" data-feather="shopping-cart"></i>
                                     </a>';

                        $actions .= '<a href="' . route('ProformaInvoice.convert', $row->id) . '" class="me-2 text-info" title="Convert to Proforma Invoice">
                                        <i class="align-middle" data-feather="file"></i>
                                     </a>';
                    }

                    if (hasPermission('edit_Quotation')) {
                        $actions .= '<a href="' . $editUrl . '" class="me-2 text-primary" title="Edit">
                                        <i class="align-middle" data-feather="edit-2"></i>
                                     </a>';
                    }

                    if (hasPermission('delete_Quotation')) {
                        $actions .= '<a href="#" class="text-danger delete-confirm" data-id="' . $formId . '" title="Delete">
                                        <i class="align-middle" data-feather="trash"></i>
                                     </a>

                                     <form id="' . $formId . '" action="' . $deleteUrl . '" method="POST" class="d-none">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                     </form>';
                    }

                    return $actions;
                })

                ->rawColumns(['type', 'status', 'actions'])

                ->make(true);
        }

        return view('backend.Quotation.index');
    }

    /**
     * Show Create Form
     */
    public function create()
    {
        // Only estimates that are Approved and not yet converted are offered
        $estimates = Estimate::with('firm', 'customer')
            ->where('status', 3)
            ->orderByDesc('id')
            ->get();

        $items = Product::orderBy('name')->get();

        return view('backend.Quotation.create', compact('estimates', 'items'));
    }

    /**
     * View (read-only) — used for locked/converted quotations, and as a general
     * "view before editing" screen. Shows works, materials, totals and conversion status.
     */
    public function show(string $id)
    {
        $quotation = Quotation::with([
            'firm', 'customer', 'estimate.works', 'materials.item', 'saleOrder', 'proformaInvoice'
        ])->findOrFail($id);

        return view('backend.Quotation.show', compact('quotation'));
    }


    /**
     * AJAX: fetch an estimate's firm / customer / works so the form
     * can auto-fill the (read-only) firm and show works for Installation type.
     */
    public function getEstimateDetails(string $id)
    {
        $estimate = Estimate::with(['firm', 'customer', 'works'])->findOrFail($id);

        return response()->json([
            'firm_id' => $estimate->firm_id,
            'firm_name' => $estimate->firm->firm_name ?? '-',
            'customer_id' => $estimate->customer_id,
            'customer_name' => $estimate->customer->name ?? '-',
            'works_total' => $estimate->works->sum('sale_price'),
            'works' => $estimate->works->map(function ($work) {
                return [
                    'work_title' => $work->work_title,
                    'description' => $work->description,
                    'qty' => $work->qty,
                    'sale_price' => $work->sale_price,
                ];
            }),
        ]);
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        $validated = $this->validateQuotation($request);

        DB::beginTransaction();

        try {

            $estimate = Estimate::with('works')->findOrFail($validated['estimate_id']);

            $quotation = new Quotation();
            $quotation->firm_id = $estimate->firm_id; // always taken from the estimate, never from the form
            $quotation->customer_id = $estimate->customer_id;
            $quotation->estimate_id = $estimate->id;
            $quotation->quotation_no = $this->generateQuotationNo();
            $quotation->type = $validated['type'];
            $quotation->date = $validated['date'];
            $quotation->status = $validated['status'];
            $quotation->createdby = Auth::id();

            // Works total only applies to Installation type
            $quotation->works_total = $validated['type'] == Quotation::TYPE_INSTALLATION
                ? $estimate->works->sum('sale_price')
                : 0;

            $quotation->material_total = 0;
            $quotation->total_amount = 0;
            $quotation->save();

            $materialTotal = $this->saveMaterials($quotation, $validated['materials'] ?? []);

            $quotation->material_total = $materialTotal;
            $quotation->total_amount = $quotation->works_total + $materialTotal;
            $quotation->save();

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }

        return redirect()
            ->route('Quotation')
            ->with('success', 'Quotation created successfully!');
    }

    /**
     * Show Edit Form
     */
    public function edit(string $id)
    {
        $old = Quotation::with(['materials', 'estimate.works', 'firm', 'customer', 'saleOrder', 'proformaInvoice'])->findOrFail($id);

        if ($old->saleOrder || $old->proformaInvoice) {
            return redirect()
                ->route('Quotation')
                ->with('error', 'This Quotation has already been converted to a ' .
                    ($old->saleOrder ? 'Sale Order' : 'Proforma Invoice') .
                    ' and can no longer be edited. Make changes there instead, or create a new Quotation.');
        }

        $estimates = Estimate::with('firm', 'customer')
            ->where(function ($q) use ($old) {
                $q->where('status', 3)->orWhere('id', $old->estimate_id);
            })
            ->orderByDesc('id')
            ->get();

        $items = Product::orderBy('name')->get();

        return view('backend.Quotation.create', compact('old', 'estimates', 'items'));
    }

    /**
     * Update
     */
    public function update(Request $request)
    {
        $id = $request->id;

        $quotation = Quotation::with('saleOrder', 'proformaInvoice')->findOrFail($id);

        if ($quotation->saleOrder || $quotation->proformaInvoice) {
            return redirect()
                ->route('Quotation')
                ->with('error', 'This Quotation has already been converted and can no longer be edited.');
        }

        $validated = $this->validateQuotation($request);

        DB::beginTransaction();

        try {

            $estimate = Estimate::with('works')->findOrFail($validated['estimate_id']);

            $quotation->firm_id = $estimate->firm_id;
            $quotation->customer_id = $estimate->customer_id;
            $quotation->estimate_id = $estimate->id;
            $quotation->type = $validated['type'];
            $quotation->date = $validated['date'];
            $quotation->status = $validated['status'];

            $quotation->works_total = $validated['type'] == Quotation::TYPE_INSTALLATION
                ? $estimate->works->sum('sale_price')
                : 0;

            $quotation->save();

            // Replace materials (simplest & safest for a calculator-style form)
            $quotation->materials()->delete();

            $materialTotal = $this->saveMaterials($quotation, $validated['materials'] ?? []);

            $quotation->material_total = $materialTotal;
            $quotation->total_amount = $quotation->works_total + $materialTotal;
            $quotation->save();

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
        }

        return redirect()
            ->route('Quotation')
            ->with('success', 'Quotation updated successfully!');
    }

    /**
     * Delete
     */
    public function destroy(string $id)
    {
        $quotation = Quotation::with('saleOrder', 'proformaInvoice')->findOrFail($id);

        if ($quotation->saleOrder || $quotation->proformaInvoice) {
            return redirect()
                ->route('Quotation')
                ->with('error', 'This Quotation has already been converted and cannot be deleted.');
        }

        $quotation->delete(); // materials cascade-delete via FK

        return redirect()
            ->route('Quotation')
            ->with('success', 'Quotation deleted successfully!');
    }

    /**
     * Shared validation for store & update
     */
    private function validateQuotation(Request $request): array
    {
        $rules = [
            'estimate_id' => ['required', 'exists:estimates,id'],
            'type' => ['required', 'in:1,2'], // 1=Supply, 2=Installation
            'date' => ['required', 'date'],
            'status' => ['required', 'in:0,1,2,3'],

            'materials' => ['nullable', 'array'],
            'materials.*.item_id' => ['required_with:materials', 'exists:items,id'],
            'materials.*.qty' => ['required_with:materials', 'numeric', 'min:0.01'],
            'materials.*.rate' => ['required_with:materials', 'numeric', 'min:0'],
            'materials.*.gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];

        // Supply quotations must have at least one material selected.
        // Installation quotations can be works-only, materials are optional.
        if ($request->input('type') == \App\Models\Backend\Quotation::TYPE_SUPPLY) {
            $rules['materials'] = ['required', 'array', 'min:1'];
        }

        return $request->validate($rules, [
            'estimate_id.required' => 'Please select an Estimate to convert.',
            'type.required' => 'Please select Quotation Type.',
            'date.required' => 'Date is required.',
            'status.required' => 'Please select status.',
            'materials.required' => 'Please select at least one material for a Supply quotation.',
            'materials.min' => 'Please select at least one material for a Supply quotation.',
        ]);
    }

    /**
     * Recalculates every material row server-side and saves it. Returns material grand total.
     */
    private function saveMaterials(Quotation $quotation, array $materials): float
    {
        $materialTotal = 0;

        foreach ($materials as $material) {

            $qty = (float) ($material['qty'] ?? 0);
            $rate = (float) ($material['rate'] ?? 0);
            $gstPercent = (float) ($material['gst_percent'] ?? 0);

            $amount = $qty * $rate;
            $totalAmount = $amount + ($amount * $gstPercent / 100);

            QuotationMaterial::create([
                'quotation_id' => $quotation->id,
                'item_id' => $material['item_id'],
                'qty' => $qty,
                'rate' => $rate,
                'gst_percent' => $gstPercent,
                'amount' => $amount,
                'total_amount' => $totalAmount,
            ]);

            $materialTotal += $totalAmount;
        }

        return $materialTotal;
    }

    /**
     * Simple sequential quotation number, e.g. QTN-000123
     */
    private function generateQuotationNo(): string
    {
        $lastId = Quotation::max('id') ?? 0;
        return 'QTN-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
    }
}