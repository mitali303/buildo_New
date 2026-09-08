@extends('backend.partials.master')

@section('title', 'Customer Payment')

@section('maincontent')
<div class="container-fluid">

    {{-- Header --}}
    <br>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between" style="
    background-color: #c8def0;">
            <h5><i class="fa fa-tag"></i> Customer Payment Details</h5>
            <div>
                <a href="{{ route('customer_payment') }}" class="btn btn-sm btn-secondary">
                    ← Back
                </a>
                <a href="{{ route('customer_payment.print', $payment->ID ?? '') }}"
                    class="btn btn-sm btn-primary" target="_blank">
                    <i class="fa fa-print"></i> Print
                </a>
            </div>
        </div>
    </div>

    {{-- BASIC DETAILS --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4"><strong>Date:</strong>
                    {{ \Carbon\Carbon::parse($booking->BookingDate)->format('d-m-Y') }}
                </div>
                <div class="col-md-4"><strong>Scheme:</strong>
                    {{ $payment->schemes->Name ?? '' }}
                </div>
                <div class="col-md-4"><strong>Flat No:</strong>
                    <!-- {{ $booking->flat->FlatNo ?? '' }} -->
                    {{ $payment->flat->FlatNo ?? '' }}

                </div>
            </div>

            <div class="row">
                <div class="col-md-4"><strong>Customer:</strong>
                    {{ $booking->CutomerName }}
                </div>
                <div class="col-md-4"><strong>Total Amount:</strong>
                    {{ number_format($booking->TotalFlatAmt + $extraWork, 2) }}
                </div>
                <div class="col-md-4"><strong>Downpayment:</strong>
                    {{ number_format($downPayment, 2) }}
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4"><strong>Agreement Amount:</strong>
                    {{ $booking->stamp_amt }}
                </div>
                <div class="col-md-4"><strong>Stamp Duty ({{ $booking->vatAmtPer }}%):</strong>
                    {{ $booking->vatAmt }}
                </div>
                <div class="col-md-4"><strong>GST ({{ $booking->servicetaxPer }}%):</strong>
                    {{ $booking->servicetax }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-4"><strong>Registration ({{ $booking->regiChrgPer }}%):</strong>
                    {{ $booking->regiChrg }}
                </div>
                <div class="col-md-4"><strong>Total Tax:</strong>
                    {{ $booking->TaxamtTotal }}
                </div>
                <div class="col-md-4"><strong>Round Up:</strong>
                    {{ $booking->roundUp }}
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Bank Sanction:</strong>
                    {{ $booking->loan_sanction_amt }}
                </div>
                <div class="col-md-4"><strong>Bank Paid:</strong>
                    {{ $bankPaid }}
                </div>
                <div class="col-md-4"><strong>Self Paid:</strong>
                    {{ $selfPaid }}
                </div>
            </div>
        </div>
    </div>



    {{-- PAYMENT HISTORY --}}
    <div class="card mb-3">

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Receipt No</th>
                            <th>Payment Method</th>
                            <th>Account No</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Payment By</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($payments as $k => $pay)
                        <tr>
                            <td>{{ $k+1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($pay['date'])->format('d-m-Y') }}</td>
                            <td>{{ $pay['receipt_no'] }}</td>
                            <td>{{ $pay['payment_method'] }}</td>
                            <td>{{ $pay['account'] }}</td>
                            <td>{{ $pay['amount'] }}</td>
                            <td>{{ $pay['type'] }}</td>
                            <td>{{ ucfirst($pay['pay_by']) }}</td>
                            <td>
                                @if($pay['action'])
                                <a href="{{ route('customer_payment.edit', $pay['id']) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="fa fa-pencil"></i>
                                </a>

                                <a href="{{ route('customer_payment.print', $pay['id']) }}"
                                    class="btn btn-sm btn-secondary"
                                    target="_blank">
                                    <i class="fa fa-print"></i>
                                </a>

                                <form method="POST"
                                    action="{{ route('customer_payment.delete', $pay['id']) }}"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        {{-- TOTALS --}}
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end">Total Paid Amount</td>
                            <td colspan="4">{{ number_format($totalPaid, 2) }} Rs.</td>
                        </tr>

                        <tr class="fw-bold text-danger">
                            <td colspan="7" class="text-end">
                                {{ number_format($grandTotal, 2) }}
                                -
                                {{ number_format($totalPaid, 2) }}
                                =
                                {{ number_format($pendingAmount, 2) }} Rs.
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
    @endsection