@extends('backend.partials.master')

@section('title', 'Bank Letter Receipt')

@section('maincontent')

<div class="container-fluid">

    {{-- Print Button --}}
    <div class="mb-3 dontPrint text-center">
        <a href="javascript:window.print()" class="btn btn-primary">
            <b>Print</b>
        </a>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
        <b>Back</b></a>
    </div>

    {{-- Main Card --}}
    <div class="receipt-box mx-auto">

        {{-- Header --}}
        <div class="mb-3">
            <div>
                <p class="mb-0"><strong>To,</strong></p>
                <p class="mb-0"><strong>Mr/Miss</strong> {{ $bankform->to_name }}</p>
                <p class="mb-0">{{ $bankform->bank_name }},</p>
                <p class="mb-0">{{ $bankform->address }}</p>
                <p class="mb-0">Pincode: {{ $bankform->pincode }}</p>
                <p class="mb-0"><strong>Date:</strong> {{ date('d-m-Y') }}</p>
            </div>

        </div>
    {{-- Dear --}}
        <div class="mb-2">
            <p>Dear Sir/Madam,</p>
        </div>

        {{-- Subject --}}
        <div class="text-center mb-3">
            <p><strong>Subject: {{ $bankform->subject }}</strong></p>
        </div>

        {{-- Content --}}
        <div class="text-content mb-4">
    <p class="justify-text">
        {{ $bankform->loan_request }}
    </p>
</div>

        {{-- Footer --}}
        <div class="d-flex justify-content-end mt-5">
    <div class="text-end">
        <p>Your Faithfully,</p>
    </div>
</div>

    </div>
</div>

<style>
.justify-text {
    text-align: justify;
    text-justify: inter-word;
    line-height: 1.8;
    
    margin: 0;                 /* remove default p margin */
    padding: 0;

    word-spacing: 0.2em;      /* smooth spacing */
    letter-spacing: 0.02em;

    word-break: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}
.receipt-box {
    max-width: 750px;
    border: 2px solid #000;
    border-radius: 15px;
    padding: 30px;
    background: #fff;
}

/* .content {
    line-height: 1.8;
    text-align: justify;
    text-justify: inter-word; /* ensures spacing is distributed between words */
    word-wrap: break-word; /* breaks long words to avoid overflow */
    hyphens: auto; /* allows hyphenation for smoother line breaks */
} */
.receipt-box {
    max-width: 750px;
    min-width: 600px; /* optional: prevents too narrow layout */
    border: 2px solid #000;
    border-radius: 15px;
    padding: 30px;
    background: #fff;
    word-break: break-word;
}
@media print {

    @page {
        size: A4;
        margin: 20mm;
    }

    .text-content {
        width: 100%;
    }

    .justify-text {
        text-align: justify;
        text-justify: inter-word;
    }

    html, body {
        width: 210mm;
        height: 297mm;
    }
    /* Hide everything first */
    body * {
        visibility: hidden;
    }

    /* Show only receipt box */
    .receipt-box, .receipt-box * {
        visibility: visible;
    }

    /* Optional: make receipt box appear at top-left of page */
     .receipt-box {
        width: 100%;
        max-width: 250mm; /* fits inside A4 margins */
        margin: 0 auto;
        padding: 20mm;
        border: 2px solid #000;
        border-radius: 10px;
        box-sizing: border-box;
        min-height: 257mm; /* ensures full A4 height even if content is small */
    }


    /* Hide print button */
    .dontPrint {
        display: none;
    }

    body {
        background: none !important;
    }
}

</style>

@endsection

