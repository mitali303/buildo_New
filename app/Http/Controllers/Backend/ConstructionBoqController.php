<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\ConstructionBoq;
use App\Models\Backend\ConstructionBoqItem;
use App\Models\Backend\SchemeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Backend\Agency;

class ConstructionBoqController extends Controller
{
    /** BOQ LIST */
    public function index()
    {
        $boqs = ConstructionBoq::orderByDesc('id')->get();

        return view(
            'backend.ConstructionBoq.index',
            compact('boqs')
        );
    }

    /** CREATE FORM */
    public function create()
    {
        $schemes = SchemeDetail::orderBy('Name')->get();
        $agencies = Agency::orderBy('Name')->get();

        return view(
            'backend.ConstructionBoq.create',
            compact('schemes', 'agencies')
        );
    }
    /*  STORE BOQ */
    public function store(Request $request)
    {
        $request->validate([
            'scheme_id' => 'required',
            'estimate_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.unit' => 'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.material_rate' =>  'nullable|numeric|min:0',
            'items.*.labour_rate' => 'nullable|numeric|min:0',
        ]);
        DB::beginTransaction();
        try {

            /* GET SCHEME */
            $scheme = SchemeDetail::where(
                'ID',
                $request->scheme_id
            )->firstOrFail();

            /**CALCULATE ITEMS*/
            $materialTotal = 0;
            $labourTotal = 0;

            $preparedItems = [];

            foreach ($request->items as $index => $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $materialRate = (float) ($item['material_rate'] ?? 0);
                $labourRate = (float) ($item['labour_rate'] ?? 0);
                /**
                 * Material amount
                 *
                 * Quantity × Material Rate
                 */
                $materialAmount = round( $quantity * $materialRate,2);

                /**
                 * Labour amount
                 *
                 * Quantity × Labour Rate
                 */
                $labourAmount = round( $quantity * $labourRate, 2 );

                /**
                 * Item total
                 */
                $totalAmount = round( $materialAmount + $labourAmount, 2 );

                $materialTotal += $materialAmount;
                $labourTotal += $labourAmount;

                $preparedItems[] = [
                    'category_name' => $item['category_name'] ?? null,
                    'item_description' => $item['description'],
                    'material_id' => $item['material_id'] ?? null,
                    'material_name' =>  $item['material_name'] ?? null,
                    'agency_id' => $item['agency_id'] ?? null,
                    'agency_name' => $item['agency_name'] ?? null,
                    'unit' => $item['unit'],
                    'quantity' => $quantity,
                    'material_rate' => $materialRate,
                    'labour_rate' => $labourRate,
                    'material_amount' => $materialAmount,
                    'labour_amount' =>  $labourAmount,
                    'total_amount' => $totalAmount,
                    'remarks' => $item['remarks'] ?? null,
                    'sort_order' => $index + 1,
                ];
            }

            /** SUBTOTAL */
            $materialTotal =  round($materialTotal, 2);
            $labourTotal = round($labourTotal, 2);
            $subtotal =  round( $materialTotal + $labourTotal, 2 );
            /** OVERHEAD */
            $overheadPercent = (float) ($request->overhead_percent ?? 0);
            $overheadAmount = round( $subtotal * $overheadPercent /  100,2 );
            /** CONTINGENCY */
            $contingencyPercent = (float) ($request->contingency_percent ?? 0);
            $contingencyAmount = round( $subtotal * $contingencyPercent / 100, 2  );
            /* TAXABLE AMOUNT */
            $taxableAmount = round( $subtotal + $overheadAmount + $contingencyAmount, 2 );
            /* GST */
            $gstPercent = (float) ($request->gst_percent ?? 0);
            $gstAmount = round(  $taxableAmount *  $gstPercent /100, 2 );
            /**GRAND TOTAL*/
            $grandTotal = round( $taxableAmount + $gstAmount, 2);
            /* BOQ NUMBER */
            $nextId = ((int) ConstructionBoq::max('id')) + 1;

            $boqNo =
                'BOQ-' .
                now()->format('Ym') .
                '-' .
                str_pad(
                    $nextId,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            /* CREATE MAIN RECORD */
            $boq = ConstructionBoq::create([
                'boq_no' => $boqNo,
                /**
                 * IMPORTANT:
                 * Scheme ID is permanently saved.
                 */
                'scheme_id' =>  $scheme->ID,
                'scheme_name' => $scheme->Name,
                'estimate_date' => $request->estimate_date,
                'customer_name' => $request->customer_name,
                'site_address' => $request->site_address,
                'built_up_area' =>  $request->filled('built_up_area')  ? (float) $request->built_up_area : null,
                'material_total' => $materialTotal,
                'labour_total' => $labourTotal,
                'subtotal' => $subtotal,
                'overhead_percent' => $overheadPercent,
                'overhead_amount' => $overheadAmount,
                'contingency_percent' =>  $contingencyPercent,
                'contingency_amount' => $contingencyAmount,
                'taxable_amount' => $taxableAmount,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'grand_total' =>  $grandTotal,
                'notes' => $request->notes,
                'status' => $request->status ?? 1,
                'createdby' => Auth::id(),
            ]);

            /* INSERT BOQ ITEMS */
            foreach ($preparedItems as $item) {
                $item['boq_id'] =
                    $boq->id;
                ConstructionBoqItem::create(
                    $item
                );
            }
            DB::commit();
            return redirect()
                ->route('construction-boq.index' )
                ->with( 'success',  'Construction BOQ created successfully.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /* VIEW */
    public function show($id)
    {
        $boq = ConstructionBoq::with([
            'items.agency'
        ])->findOrFail($id);

        return view('backend.ConstructionBoq.show', compact('boq'));
    }

    /* EDIT */
    public function edit($id)
    {
        $boq = ConstructionBoq::with('items')
            ->findOrFail($id);

        $schemes = SchemeDetail::orderBy('Name')->get();
        $agencies = Agency::orderBy('Name')->get();

        return view(
            'backend.ConstructionBoq.edit',
            compact('boq', 'schemes', 'agencies')
        );
    }

    /* UPDATE */
    public function update(
        Request $request,
        $id
    ) {
        $request->validate([
            'scheme_id' => 'required',
            'estimate_date' => 'required|date',
            'items' =>'required|array|min:1',
            'items.*.description' =>'required|string|max:500',
            'items.*.unit' =>  'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0',
        ]);
        DB::beginTransaction();
        try {

            $boq = ConstructionBoq::findOrFail($id);
            $scheme = SchemeDetail::where( 'ID', $request->scheme_id  )->firstOrFail();
            $materialTotal = 0;
            $labourTotal = 0;

            /* Delete old items. */
            $boq->items()->delete();
            foreach ($request->items as $index => $item) {
                $quantity =  (float) ($item['quantity'] ?? 0);
                $materialRate = (float) ($item['material_rate'] ?? 0);
                $labourRate = (float) ($item['labour_rate'] ?? 0);
                $materialAmount = round(  $quantity *  $materialRate,  2 );
                $labourAmount = round( $quantity * $labourRate,  2);
                $totalAmount = round( $materialAmount + $labourAmount,  2 );
                $materialTotal += $materialAmount;
                $labourTotal += $labourAmount;

                ConstructionBoqItem::create([
                    'boq_id' => $boq->id,
                    'category_name' => $item['category_name'] ?? null,
                    'item_description' => $item['description'],
                    'material_id' => $item['material_id'] ?? null,
                    'material_name' => $item['material_name'] ?? null,
                    'agency_id' => $item['agency_id'] ?? null,
                    'agency_name' => $item['agency_name'] ?? null,
                    'unit' => $item['unit'],
                    'quantity' => $quantity,
                    'material_rate' => $materialRate,
                    'labour_rate' => $labourRate,
                    'material_amount' => $materialAmount,
                    'labour_amount' => $labourAmount,
                    'total_amount' => $totalAmount,
                    'remarks' => $item['remarks'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
            $materialTotal = round($materialTotal, 2);
            $labourTotal = round($labourTotal, 2);
            $subtotal = round( $materialTotal +$labourTotal,2 );
            $overheadPercent =  (float) (  $request->overhead_percent ?? 0 );
            $overheadAmount = round(  $subtotal * $overheadPercent /  100,2 );
            $contingencyPercent =  (float) ( $request->contingency_percent ?? 0 );
            $contingencyAmount = round( $subtotal *$contingencyPercent /  100,  2);
            $taxableAmount = round( $subtotal + $overheadAmount + $contingencyAmount, 2  );
            $gstPercent = (float) (  $request->gst_percent ?? 0  );
            $gstAmount = round( $taxableAmount * $gstPercent / 100, 2 );
            $grandTotal = round( $taxableAmount + $gstAmount, 2 );
            $boq->update([
                'scheme_id' => $scheme->ID,
                'scheme_name' => $scheme->Name,
                'estimate_date' => $request->estimate_date,
                'customer_name' => $request->customer_name,
                'site_address' =>  $request->site_address,
                'built_up_area' => $request->filled('built_up_area')
                    ? (float) $request->built_up_area
                    : null,
                'material_total' =>  $materialTotal,
                'labour_total' => $labourTotal,
                'subtotal' => $subtotal,
                'overhead_percent' => $overheadPercent,
                'overhead_amount' =>  $overheadAmount,
                'contingency_percent' =>  $contingencyPercent,
                'contingency_amount' =>  $contingencyAmount,
                'taxable_amount' => $taxableAmount,
                'gst_percent' => $gstPercent,
                'gst_amount' =>$gstAmount,
                'grand_total' => $grandTotal,
                'notes' => $request->notes,
                'status' => $request->status ?? 1,
            ]);
            DB::commit();
            return redirect()
                ->route(
                    'construction-boq.index'
                )
                ->with(
                    'success',
                    'Construction BOQ updated successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /*
     * DELETE */
    public function destroy($id)
    {
        $boq =
            ConstructionBoq::findOrFail($id);

        $boq->delete();

        return redirect()
            ->route(
                'construction-boq.index'
            )
            ->with(
                'success',
                'Construction BOQ deleted successfully.'
            );
    }
}