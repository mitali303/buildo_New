<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Estimate;
use App\Models\Backend\SchemeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstimateController extends Controller
{
    public function index(Request $request)
    {
        $query = Estimate::latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('estimate_no', 'like', "%{$search}%")
                    ->orWhere('scheme_name', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', (int) $request->status);
        }

        $estimates = $query->paginate(12)->withQueryString();
        $estimates->getCollection()->transform(function (Estimate $estimate) {
            $actions = '<a href="' . route('Estimate.edit', $estimate->id) . '" class="text-primary me-2" title="Edit estimate">'
                . '<i data-feather="edit-2"></i></a>'
                . '<form action="' . route('Estimate.delete', $estimate->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this estimate?\')">'
                . csrf_field()
                . method_field('DELETE')
                . '<button type="submit" class="border-0 bg-transparent p-0 text-danger" title="Delete estimate">'
                . '<i data-feather="trash-2"></i></button></form>';

            $estimate->setAttribute('actions', $actions);
            return $estimate;
        });

        $summary = [
            'total' => Estimate::count(),
            'approved' => Estimate::where('status', 3)->count(),
            'pending' => Estimate::whereIn('status', [1, 2])->count(),
            'value' => Estimate::where('status', '!=', 0)->sum('grand_total'),
        ];

        return view('backend.Estimate.index', compact('estimates', 'summary'));
    }

    public function create()
    {
        return view('backend.Estimate.create', [
            'estimate' => new Estimate(['date' => now()->toDateString(), 'status' => 1, 'tax_percent' => 0]),
            'schemes' => SchemeDetail::select('ID', 'Name')->orderBy('Name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $estimate = new Estimate();
        $this->saveEstimate($request, $estimate);

        return redirect()->route('Estimate')->with('success', 'Estimate created successfully.');
    }

    public function edit(int $id)
    {
        $estimate = Estimate::findOrFail($id);
        $raw = $estimate->getAttributes();

        $estimate->setAttribute(
            'built_up_area',
            $raw['built_up_area'] ?? $raw['total_area'] ?? null
        );
        $estimate->setAttribute('rate_per_sqft', $raw['rate_per_sqft'] ?? null);
        $estimate->setAttribute('scheme_name', $raw['scheme_name'] ?? $raw['project_name'] ?? null);
        $estimate->setAttribute('items', $estimate->items ?? []);

        if (! $estimate->scheme_id && $estimate->scheme_name) {
            $scheme = SchemeDetail::where('Name', $estimate->scheme_name)->first();

            if ($scheme) {
                $estimate->setAttribute('scheme_id', $scheme->ID);
                $estimate->setAttribute('scheme_name', $scheme->Name);
            }
        }

        return view('backend.Estimate.create', [
            'estimate' => $estimate,
            'schemes' => SchemeDetail::select('ID', 'Name')->orderBy('Name')->get(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $estimate = Estimate::findOrFail($id);
        $this->saveEstimate($request, $estimate);

        return redirect()->route('Estimate')->with('success', 'Estimate updated successfully.');
    }

    public function destroy(int $id)
    {
        Estimate::findOrFail($id)->delete();

        return redirect()->route('Estimate')->with('success', 'Estimate deleted successfully.');
    }

    private function saveEstimate(Request $request, Estimate $estimate): void
    {
        $data = $request->validate([
            'scheme_id' => ['required', 'string', 'exists:scheme_step1,ID'],
            'date' => ['required', 'date'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'site_address' => ['nullable', 'string', 'max:2000'],
            'tax_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:0,1,2,3'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'built_up_area' => ['required', 'numeric', 'gt:0'],
            'rate_per_sqft' => ['required', 'numeric', 'gt:0'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['nullable', 'string', 'max:300'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
            'items.*.material_type' => ['nullable', 'string', 'max:100'],
            'items.*.qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $scheme = SchemeDetail::findOrFail($data['scheme_id']);
        $submittedItems = array_key_exists('items', $data)
            ? $data['items']
            : ($estimate->items ?? []);

        $items = collect($submittedItems)->filter(fn (array $item) => filled($item['description'] ?? null))->map(function (array $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);

            return [
                'description' => trim($item['description']),
                'unit' => $item['unit'] ?? null,
                'material_type' => $item['material_type'] ?? null,
                'qty' => $qty,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
            ];
        })->values()->all();

        $base = round((float) ($data['built_up_area'] ?? 0) * (float) ($data['rate_per_sqft'] ?? 0), 2);
        $hasMaterialRows = collect($items)->isNotEmpty();
        $materialTotal = $hasMaterialRows || ! $estimate->exists
            ? round(collect($items)->sum('amount'), 2)
            : (float) $estimate->material_total;
        $subtotal = round($base + $materialTotal, 2);
        $tax = round($subtotal * ((float) $data['tax_percent'] / 100), 2);

        $attributes = [
            'estimate_no' => $estimate->estimate_no ?: 'EST-' . now()->format('Ym') . '-' . str_pad((string) (Estimate::max('id') + 1), 4, '0', STR_PAD_LEFT),
            'scheme_id' => $scheme->ID,
            'scheme_name' => $scheme->Name,
            'built_up_area' => $data['built_up_area'],
            'rate_per_sqft' => $data['rate_per_sqft'],
            'customer_name' => $data['customer_name'] ?? null,
            'site_address' => $data['site_address'] ?? null,
            'date' => $data['date'],
            'notes' => $data['notes'] ?? null,
            'material_total' => $materialTotal,
            'tax_percent' => $data['tax_percent'],
            'tax_amount' => $tax,
            'grand_total' => $subtotal + $tax,
            'status' => $data['status'],
            'createdby' => $estimate->createdby ?: Auth::id(),
        ];

        $attributes['construction_total'] = $base;
        $attributes['items'] = $items;

        $estimate->fill($attributes)->save();
    }
}
