@extends('backend.partials.master')

@section('title', 'Customer Payment Receipt')

@section('maincontent')
<div class="container-fluid">

    {{-- Print Button --}}
    <br><br>
    <div class="mb-3 dontPrint">
        <a href="javascript:window.print()" class="btn btn-primary" style="margin-left: 45%;">
            <b>Print</b>
        </a>
    </div>

    <div class="rcorners2 p-4 mx-auto" style="max-width:750px; border:2px solid #000; border-radius:15px;">

        {{-- Receipt Header --}}
        <table style="width:100%; font-size:18px; font-weight:bold;">
            <tr>
                <td style="width:50%;">Receipt No: {{ $payment->ReceiptNo }}</td>
                <td style="width:50%; text-align:right;">Date: {{ \Carbon\Carbon::parse($payment->date)->format('d/m/Y') }}</td>
            </tr>
        </table>

        {{-- Customer Info --}}
        <table style="width:100%; font-size:16px; margin-top:15px;">
            <tr>
                <td style="width:45%;">Received With Thanks From M/s./Shri</td>
                <td style="width:55%; border-bottom:1px solid black; text-align:center;">
                    {{ $booking->CutomerName }}
                </td>
            </tr>
        </table>
       
        {{-- Amount in Words --}}
        <table style="width:100%; font-size:16px; margin-top:15px;">
            <tr>
                <td style="width:20%;">A sum of Rs.</td>
                <td style="width:80%; border-bottom:1px solid black; text-align:center;">
                    {{ $totalInWords }}
                    
                </td>
            </tr>
        </table>

        {{-- Payment Method Details --}}
        <table style="width:100%; font-size:16px; margin-top:15px;">
            <tr>
                <td style="width:30%;">By Cash/DD/Cheque No</td>
                <td style="width:20%; border-bottom:1px solid black; text-align:center;">
                    {{ $payment->payment_method }} - {{ $payment->cheque_no ?? '-' }}
                </td>
                <td style="width:10%;">Dated</td>
                <td style="width:40%; border-bottom:1px solid black; text-align:center;">
                    {{ \Carbon\Carbon::parse($payment->date)->format('d/m/Y') }}
                </td>
            </tr>
        </table>

        {{-- Amount Box --}}
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="head1 text-center p-3 border" style="width:200px;">
                <h3 style="margin:0;">RS. {{ number_format($payment->amt_pay, 2) }}</h3>
            </div>

            <div style="flex:1;"></div>

            <div class="text-end">
                <h4>{{ $client->Name ?? '' }}</h4>
                <h5>(Authorised Signatory)</h5>
            </div>
        </div>

        <p class="mt-3">(Cheques subject to Realisation)</p>
    </div>
</div>
<br><br><br><br><br><br><br><br>
<style>
    @media print {
        .dontPrint {
            display: none;
        }
    }

    .rcorners2 {
        border-radius: 15px;
        border: 2px solid #000;
        padding: 20px;
        margin: 0 auto;
    }
</style>
@endsection
