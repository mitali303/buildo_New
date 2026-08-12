@extends('backend.partials.master')

@section('title','Slab Details')

@section('maincontent')
<main class="content">
<div class="container-fluid">
    <div class="mb-3">
      <h1 class="h3 d-inline align-middle">Slab Details</h1>
    </div>
    
    <div class="row" >
      <div class="col-md-12">
        <div class="card" style="padding:20px;">
    <!-- Customer Info -->
    <div class="row mb-3">
        <div class="col-md-3 text-end">Customer Name :</div>
        <div class="col-md-3">
            <input class="form-control" value="{{ $booking->CutomerName }}" readonly>
        </div>

        <div class="col-md-3 text-end">Total Flat Amount :</div>
        <div class="col-md-3">
            <input class="form-control" value="{{ $booking->TotalFlatAmt }}" readonly>
        </div>
    </div>

    <hr>

    <!-- Slab Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Stage</th>
                <th>Percentage (%)</th>
                <th>Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Plinth</td>
                <td>{{ $booking->pilnth }}</td>
                <td>{{ number_format($calc['plinth'],2) }}</td>
            </tr>
            <tr>
                <td>Slab</td>
                <td>{{ $booking->slab }}</td>
                <td>{{ number_format($calc['slab'],2) }}</td>
            </tr>
            <tr>
                <td>Bricks</td>
                <td>{{ $booking->bricks }}</td>
                <td>{{ number_format($calc['bricks'],2) }}</td>
            </tr>
            <tr>
                <td>Plaster</td>
                <td>{{ $booking->plaster }}</td>
                <td>{{ number_format($calc['plaster'],2) }}</td>
            </tr>
            <tr>
                <td>Flooring</td>
                <td>{{ $booking->floaring }}</td>
                <td>{{ number_format($calc['flooring'],2) }}</td>
            </tr>
            <tr>
                <td>Plumbing</td>
                <td>{{ $booking->plumbing }}</td>
                <td>{{ number_format($calc['plumbing'],2) }}</td>
            </tr>
            <tr>
                <td>Project</td>
                <td>{{ $booking->project }}</td>
                <td>{{ number_format($calc['project'],2) }}</td>
            </tr>
            <tr class="table-success">
                <th>Total</th>
                <th>{{ $booking->slab_total }}</th>
                <th>{{ number_format($calc['total'],2) }}</th>
            </tr>
        </tbody>
    </table><br>

    <a href="{{ route('customer_booking') }}" class="btn btn-secondary" style="width:8%;">
        Back
    </a>

</div>
</div>
</div>
</div>
</main>
@endsection
