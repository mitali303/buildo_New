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
         <a href="{{ route('Site_work_pay.viewPayment', $data->WorkorderID) }}" class="btn btn-secondary">
    <b>Back</b>
</a>
    </div>

    <div class="rcorners2 p-4 mx-auto" style="max-width:750px; border:2px solid #000; border-radius:15px;">


            {{-- Company Name Header --}}
                    <div class="text-center mb-3">
                        <h3 style="margin:0;">
                             {{ company_setting('company_name') ?? 'BUILDO' }}
                        </h3>
                        <p>{{ company_setting('address') }}</p>
                    </div>

        {{-- Receipt Header --}}
        <table style="width:100%; font-size:18px; font-weight:bold;">
            <tr>
                 <td style="width:50%;"> Voucher No : {{ $data->receipt_no }}</td>
                <td style="width:50%; text-align:right;">Date : {{ $date }}</td>
            </tr>
        </table>


         {{-- Customer Info --}}
        <table style="width:100%; font-size:16px; margin-top:15px;">
            <tr>
                <td style="width:45%;">Total Amount Paid To M/s./Shri</td>
                <td style="width:55%; border-bottom:1px solid black; text-align:center;">
                    {{ $data->ContractorName ?? '' }}
                </td>
            </tr>
        </table>
        {{-- 👇 NEW (Site Name) --}}
            <table style="width:100%; font-size:16px; margin-top:10px;">
           <tr>
    <td>Site Name :</td>
    <td style="border-bottom:1px solid black; text-align:center;">
        {{ $data->SiteName ?? '' }}
    </td>
</tr>
        </table>
        {{-- Amount in Words --}}
        <table style="width:100%; font-size:16px; margin-top:15px;">
           <tr>
    <td style="width:20%;">A sum of Rs.</td>
    <td style="width:80%; border-bottom:1px solid black; text-align:center;">
        {{ ucfirst((new \NumberFormatter("en", \NumberFormatter::SPELLOUT))->format(round($data->amt_pay))) }} rupees only
    </td>
</tr>
        </table>

         {{-- Payment Method Details --}}
        <table style="width:100%; font-size:16px; margin-top:15px;">
            <tr>
                <td style="width:30%;">By Cash/DD/Cheque No</td>
                <td style="width:20%; border-bottom:1px solid black; text-align:center;">
                    {{ $data->payment_method }} - {{ $data->cheque_no }}
                </td>
                <td style="width:10%;">Dated</td>
                <td style="width:40%; border-bottom:1px solid black; text-align:center;">
                    {{ $date }}
                </td>
            </tr>
        </table>

        {{-- Amount Box --}}
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="head1 text-center p-3 border" style="width:200px;">
                <h3 style="margin:0;">RS. {{ $data->amt_pay }}</h3>
            </div>

            <div style="flex:1;"></div>

            <div class="text-end">
                {{-- <h4>{{ $client->company_name ?? '' }}</h4> --}}
                <h4>{{ company_setting('company_name') ?? 'BUILDO' }}</h4>
                <h5>(Authorised Signatory)</h5>
            </div>
        </div>

        <p class="mt-3">(Cheques subject to Realisation)</p>
    </div>
</div>
<br><br><br><br><br><br><br><br>
<style>
    @media print {
        .dontPrint,
        .navbar,
        .topbar,
        .header,
        .main-header,
        .sidebar,
        footer,
        .footer,
        nav {
            display: none !important;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Optional: remove shadows/borders from layout */
        * {
            box-shadow: none !important;
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

