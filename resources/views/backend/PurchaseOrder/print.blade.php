@extends('backend.partials.master')

@section('title', 'Purchase Order')

@section('maincontent')
<div class="container-fluid">

    {{-- Print Button --}}
    <br><br>
    <div class="mb-3 dontPrint text-center">
    
    <a href="javascript:window.print()" class="btn btn-primary">
        <b>Print</b>
    </a>

    <a href="{{ route('PurchaseOrder') }}" class="btn btn-secondary">
    Back
</a>

</div>

    <div class="mx-auto p-4" style="max-width:900px; border:2px solid #000;">

        {{-- HEADER --}}
        {{-- <div class="text-center mb-3" style="border-bottom:2px solid #000;">
            <h3 style="margin:0;">PURCHASE ORDER</h3>
            <div style="font-size:13px; color:#555;">GST INVOICE / PURCHASE DOCUMENT</div>
        </div> --}}
                    {{-- Company Name Header --}}
                    <div class="text-center mb-3" style="border-bottom:2px solid #000;">
                        <h3 style="margin:0;">
                             {{ company_setting('company_name') ?? 'BUILDO' }}
                        </h3>
                        <p>{{ company_setting('address') }}</p>
                    </div>
        {{-- INFO --}}
        <div class="d-flex justify-content-between mb-2" style="font-size:14px;">
            <div>
                <p><b>PONO:</b> {{ $purchase->Pono }}</p>
                <p><b>Date:</b> {{ \Carbon\Carbon::parse($purchase->Date)->format('d-m-Y') }}</p>
                <p><b>Expected:</b> {{ \Carbon\Carbon::parse($purchase->Expected)->format('d-m-Y') }}</p>
            </div>

            <div>
                <p><b>Vendor:</b> {{ $purchase->vendor->Name ?? '' }}</p>
                <p><b>Scheme:</b> {{ $purchase->scheme->Name ?? '' }}</p>
                <p><b>Transport:</b> {{ $purchase->transport }}</p>
            </div>
        </div>

        {{-- TABLE --}}
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="background:#f4f4f4;">
                    <th style="border:1px solid #ddd; padding:8px;">#</th>
                    <th style="border:1px solid #ddd; padding:8px;">Material</th>
                    <th style="border:1px solid #ddd; padding:8px;">Qty</th>
                    <th style="border:1px solid #ddd; padding:8px;">Rate</th>
                    <th style="border:1px solid #ddd; padding:8px;">Taxable</th>
                    <th style="border:1px solid #ddd; padding:8px;">GST %</th>
                    <th style="border:1px solid #ddd; padding:8px;">Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach($products as $i => $p)
                @php
                    $materials = \App\Models\Backend\Material::pluck('Name', 'id');
                @endphp
                <tr>
                    <td style="border:1px solid #ddd; padding:8px;">{{ $i+1 }}</td>
                    <td style="border:1px solid #ddd; padding:8px;">{{ $materials[$p->Material] ?? '' }}</td>
                    <td style="border:1px solid #ddd; padding:8px; text-align:right;">{{ $p->Qty }}</td>
                    <td style="border:1px solid #ddd; padding:8px; text-align:right;">{{ $p->Rate }}</td>
                    <td style="border:1px solid #ddd; padding:8px; text-align:right;">{{ $p->Amount }}</td>
                    <td style="border:1px solid #ddd; padding:8px; text-align:right;">
                        {{ $p->CGST + $p->SGST + $p->IGST }}
                    </td>
                    <td style="border:1px solid #ddd; padding:8px; text-align:right;">{{ $p->Total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTALS --}}
        @if(isset($purchase))
<div style="margin-top:20px; width:320px; float:right;">
    <table style="width:100%; font-size:13px;">

        @if($purchase->total != 0)
        <tr><td>Taxable</td><td style="text-align:right;">{{ $purchase->total }}</td></tr>
        @endif

        @if($purchase->totcgst_amt != 0)
        <tr><td>CGST</td><td style="text-align:right;">{{ $purchase->totcgst_amt }}</td></tr>
        @endif

        @if($purchase->totsgst_amt != 0)
        <tr><td>SGST</td><td style="text-align:right;">{{ $purchase->totsgst_amt }}</td></tr>
        @endif

        @if($purchase->totigst_amt != 0)
        <tr><td>IGST</td><td style="text-align:right;">{{ $purchase->totigst_amt }}</td></tr>
        @endif

        @if($purchase->other != 0)
        <tr><td>Other</td><td style="text-align:right;">{{ $purchase->other }}</td></tr>
        @endif

        @if($purchase->transport != 0)
        <tr><td>Transport</td><td style="text-align:right;">{{ $purchase->transport }}</td></tr>
        @endif

        @if($purchase->round != 0)
        <tr><td>Round</td><td style="text-align:right;">{{ $purchase->round }}</td></tr>
        @endif

        <tr style="font-weight:bold; border-top:2px solid #000;">
            <td>GRAND TOTAL</td>
            <td style="text-align:right;">{{ $purchase->gtotal }}</td>
        </tr>

    </table>
</div>
@endif

        <div style="clear:both;"></div>

    </div>
</div>

<style>
@media print {
    .dontPrint,
    .navbar,
    .topbar,
    .header,
    .main-header,
    .sidebar,
    footer,
    nav {
        display: none !important;
    }

    body, .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
    }

    * {
        box-shadow: none !important;
    }
}
</style>

@endsection