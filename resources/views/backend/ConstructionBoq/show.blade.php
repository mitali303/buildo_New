@extends('backend.partials.master')
@section('title')
Construction BOQ
@endsection
@section('maincontent')

<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h4> Construction BOQ</h4>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa fa-print"></i>  Print</button>
            <a href="{{ route('construction-boq.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
    </div>
    {{-- =====================================================
         HEADER
    ====================================================== --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>BOQ No:</strong><br> {{ $boq->boq_no }}
                </div>
                <div class="col-md-4">
                    <strong>Project / Site:</strong><br> {{ $boq->scheme_name }}
                </div>
                <!-- <div class="col-md-4">
                    <strong>Scheme ID:</strong><br>{{ $boq->scheme_id }}
                </div> -->
                <div class="col-md-4 mt-3">
                    <strong>Date:</strong><br>{{ optional($boq->estimate_date)->format('d-m-Y') }}
                </div>
                <div class="col-md-4 mt-3"> 
                    <strong>Customer:</strong><br> {{ $boq->customer_name ?: '-' }}
                </div>
                <div class="col-md-4 mt-3">
                    <strong>Built-up Area:</strong><br>{{ $boq->built_up_area ?: '-' }} Sq.ft
                </div>
            </div>
        </div>
    </div>
    {{-- =====================================================
         BOQ TABLE
    ====================================================== --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Material</th>
                            <th>Agency</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Material Rate</th>
                            <th>Labour Rate</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($boq->items as $item)
                            <tr>
                                <td> {{ $loop->iteration }} </td>
                                <td> {{ $item->category_name ?: '-' }}</td>
                                <td>{{ $item->item_description }}</td>
                                <td>{{ $item->material_name ?: '-' }}</td>
                                <td> {{ $item->agency->Name ?? '-' }} </td>
                                <td>{{ $item->unit }}</td>
                                <td> {{ number_format($item->quantity, 3) }}</td>
                                <td> ₹ {{ number_format($item->material_rate, 2) }}</td>
                                <td> ₹ {{ number_format($item->labour_rate, 2) }}</td>
                                <td> ₹ {{ number_format($item->total_amount, 2) }} </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- =================================================
                 SUMMARY
            ================================================== --}}
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <table class="table table-bordered">
                        <tr>
                            <th> Material Total</th>
                            <td class="text-end">  ₹ {{ number_format($boq->material_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Labour Total</th>
                            <td class="text-end"> ₹ {{ number_format($boq->labour_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Sub Total</th>
                            <td class="text-end"> ₹ {{ number_format($boq->subtotal, 2) }} </td>
                        </tr>
                        <tr>
                            <th> Overhead ({{ $boq->overhead_percent }}%) </th>
                            <td class="text-end"> ₹ {{ number_format($boq->overhead_amount, 2) }} </td>
                        </tr>
                        <tr>
                            <th> Contingency ({{ $boq->contingency_percent }}%) </th>
                            <td class="text-end">
                                ₹ {{ number_format($boq->contingency_amount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th> GST ({{ $boq->gst_percent }}%)</th>
                            <td class="text-end">
                                ₹ {{ number_format($boq->gst_amount, 2) }}
                            </td>
                        </tr>
                        <tr class="table-success">
                            <th> Grand Total</th>
                            <th class="text-end"> ₹ {{ number_format($boq->grand_total, 2) }}  </th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection