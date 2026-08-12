@extends('backend.partials.master')

@section('title')
    Quotation {{ $quotation->quotation_no }}
@endsection

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h1 class="h3 d-inline align-middle mb-0">Quotation — {{ $quotation->quotation_no }}</h1>

    <div class="d-print-none">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print"></i> Print
        </button>

        @if(!$quotation->saleOrder && !$quotation->proformaInvoice)
            @if(hasPermission('edit_Quotation'))
                <a href="{{ route('Quotation.edit', $quotation->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endif
        @endif

        <a href="{{ route('Quotation') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

{{-- Conversion status banner --}}
@if($quotation->saleOrder)
    <div class="alert alert-success d-flex justify-content-between align-items-center">
        <span><i class="fas fa-lock"></i> This Quotation has been converted to Sale Order <strong>{{ $quotation->saleOrder->sale_order_no }}</strong> and is locked from editing.</span>
        <a href="{{ route('SaleOrder') }}" class="btn btn-sm btn-success">View Sale Order</a>
    </div>
@elseif($quotation->proformaInvoice)
    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-lock"></i> Proforma Invoice <strong>{{ $quotation->proformaInvoice->proforma_no }}</strong> was already sent — editing is locked, but you can still confirm this into a Sale Order.</span>
        <span>
            <a href="{{ route('ProformaInvoice.show', $quotation->proformaInvoice->id) }}" class="btn btn-sm btn-info">View Proforma</a>
            <a href="{{ route('SaleOrder.convert', $quotation->id) }}" class="btn btn-sm btn-primary">Convert to Sale Order</a>
        </span>
    </div>
@elseif($quotation->status == 3)
    <div class="alert alert-warning d-flex justify-content-between align-items-center d-print-none">
        <span><i class="fas fa-check-circle"></i> This Quotation is Approved and ready to convert.</span>
        <span>
            <a href="{{ route('SaleOrder.convert', $quotation->id) }}" class="btn btn-sm btn-primary">Convert to Sale Order</a>
            <a href="{{ route('ProformaInvoice.convert', $quotation->id) }}" class="btn btn-sm btn-outline-primary">Convert to Proforma</a>
        </span>
    </div>
@endif

<div class="card">
<div class="card-body">

    <div class="row mb-4">
        <div class="col-6 col-md-3">
            <div class="small text-muted">Firm</div>
            <div class="fw-bold">{{ $quotation->firm->firm_name ?? '-' }}</div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small text-muted">Customer</div>
            <div class="fw-bold">{{ $quotation->customer->name ?? '-' }}</div>
        </div>
        <div class="col-6 col-md-2">
            <div class="small text-muted">Date</div>
            <div class="fw-bold">{{ $quotation->date }}</div>
        </div>
        <div class="col-6 col-md-2">
            <div class="small text-muted">Type</div>
            <div class="fw-bold">{{ $quotation->type == 1 ? 'Supply' : 'Installation' }}</div>
        </div>
        <div class="col-6 col-md-2">
            <div class="small text-muted">Status</div>
            <div class="fw-bold">
                @php
                    $labels = [0=>'Cancelled',1=>'Draft',2=>'Sent',3=>'Approved'];
                @endphp
                {{ $labels[$quotation->status] ?? '-' }}
            </div>
        </div>
    </div>

    @if($quotation->type == 2 && $quotation->estimate && $quotation->estimate->works->count())
        <h6>Estimate Works</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Work Title</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th class="text-end">Sale Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->estimate->works as $work)
                        <tr>
                            <td>{{ $work->work_title }}</td>
                            <td>{{ $work->description }}</td>
                            <td>{{ $work->qty }}</td>
                            <td class="text-end">₹ {{ number_format($work->sale_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Works Total</td>
                        <td class="text-end fw-bold">₹ {{ number_format($quotation->works_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    @if($quotation->materials->count())
        <h6>Materials</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>GST %</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->materials as $material)
                        <tr>
                            <td>{{ $material->item->name ?? '-' }}</td>
                            <td>{{ $material->qty }}</td>
                            <td>{{ number_format($material->rate, 2) }}</td>
                            <td>{{ $material->gst_percent }}%</td>
                            <td class="text-end">₹ {{ number_format($material->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Material Total</td>
                        <td class="text-end fw-bold">₹ {{ number_format($quotation->material_total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <div class="card bg-primary bg-opacity-10 border-0">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <span class="h6 mb-0">Grand Total</span>
            <span class="h4 mb-0 text-primary">₹ {{ number_format($quotation->total_amount, 2) }}</span>
        </div>
    </div>

</div>
</div>

</div>
</main>
@endsection