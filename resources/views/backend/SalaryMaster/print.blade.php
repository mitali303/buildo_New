@extends('backend.partials.master')

@section('title')
    Salary Slip
@endsection
<style>
@media print {

    body *{
        visibility:hidden;
    }

    #printArea, #printArea *{
        visibility:visible;
    }

    #printArea{
        position:absolute;
        left:0;
        top:0;
        width:100%;
    }

    /* 🔥 FORCE COLORS */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        -webkit-print-color-adjust: exact !important;
    }
}
</style>
@section('maincontent')
<main class="content">
<div class="container-fluid p-4">

<div class="card shadow-sm border-0">
<div class="card-body p-4">

<div class="d-flex justify-content-end mb-3">
    <button onclick="window.print()" class="btn btn-primary btn-sm">
        Print
    </button>
</div>

<div id="printArea" class="salary-slip">

<!-- HEADER -->
<div class="header-box">

    <div class="header-left">
        <img src="{{ asset('Logos/BUILDO_LOGO.png') }}" class="logo-img">

        <div>
            <h2>BUILDO CONSTRUCTION</h2>
            <p>Building Better Tomorrow .</p>
        </div>
    </div>

    <div class="header-right">
        SALARY SLIP
    </div>

</div>

<!-- INFO -->
<div class="info-box">
    <div class="info-row">
        <div>
            <p><strong>Employee:</strong> {{ $salary->staff->Name ?? '' }}</p>
            <p><strong>Month :</strong> {{ date('F', mktime(0,0,0,$salary->month,10)) }}</p>
            <p><strong>Date :</strong> {{ $salary->date }}</p>
        </div>

        <div class="text-end">
            <p><strong>Salary No :</strong> {{ $salary->salary_no }}</p>
            <p><strong>Year :</strong> {{ $salary->year }}</p>
            <p><strong>Payment :</strong> {{ ucfirst($salary->payment_method) }}</p>
        </div>
    </div>
</div>

<!-- TABLE -->
<div class="table-box">
<table>
<thead>
<tr>
    <th>Earnings</th>
    <th>Amount</th>
    <th>Deductions</th>
    <th>Amount</th>
</tr>
</thead>

<tbody>
<tr>
    <td>Gross Salary</td>
    <td>{{ number_format($salary->gross,2) }}</td>
    <td>PF</td>
    <td>{{ number_format($salary->pf,2) }}</td>
</tr>

<tr>
    <td>Basic Salary</td>
    <td>{{ number_format($salary->basic_salary,2) }}</td>
    <td>ESI</td>
    <td>{{ number_format($salary->esi,2) }}</td>
</tr>

<tr>
    <td></td>
    <td></td>
    <td>Advance EMI</td>
    <td>{{ number_format($salary->advance_emi,2) }}</td>
</tr>
</tbody>
</table>
</div>

<!-- NET SALARY -->
<div class="net-box">
    <div class="net-left">
        <h5>Net Salary</h5>
        <small>( In Word: Ten Thousand Nine Hundred Only )</small>
    </div>

    <div class="net-right">
        ₹ {{ number_format($salary->net_salary,2) }}
    </div>
</div>

<!-- NARRATION -->
<p class="mt-3"><strong>Narration:</strong> {{ $salary->narration }}</p>

<!-- SIGNATURE -->
<div class="signature">
    <div>
        ____________________ <br>
        Employee Signature
    </div>

    <div>
        ____________________ <br>
        Authorized Signature
    </div>
</div>

</div>
</div>
</div>

</div>
</main>

<style>

/* MAIN */
.salary-slip{
    max-width: 950px;
    margin:auto;
    background:#eef3f6;
    padding:25px;
    font-family:'Segoe UI',sans-serif;
}

/* HEADER */
.header-box{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#c7d8e2;
    position:relative;
}

/* angled cut */
.header-right{
    background:#1f4e6d;
    color:#fff;
    font-weight:bold;
    font-size:28px;
    padding:30px 50px;
    clip-path: polygon(15% 0,100% 0,100% 100%,0% 100%);
}

/* LEFT */
.header-left{
    display:flex;
    align-items:center;
    gap:15px;
    padding:20px;
}

.logo-img{
    width:60px;
}

.header-left h2{
    margin:0;
    color:#1f4e6d;
    font-weight:700;
}

.header-left p{
    margin:0;
    color:#3a6c89;
    font-size:14px;
}

/* INFO */
.info-box{
    background:#d7e5ec;
    padding:25px;
    margin-top:20px;
}

.info-row{
    display:flex;
    justify-content:space-between;
}

.info-box p{
    margin:10px 0;
    font-size:20px;
    color:#1f4e6d;
}

/* TABLE */
.table-box{
    background:#d7e5ec;
    margin-top:20px;
    padding:15px;
}

.table-box table{
    width:100%;
    border-collapse:collapse;
}

.table-box th{
    background:#a8c0cf;
    padding:15px;
    text-align:center;
    font-size:18px;
    border:2px solid #cfdde6;
}

.table-box td{
    padding:15px;
    border:2px solid #cfdde6;
    font-size:18px;
}

/* NET */
.net-box{
    display:flex;
    margin-top:20px;
    border:2px solid #cfdde6;
}

.net-left{
    width:60%;
    background:#a8c0cf;
    padding:20px;
}

.net-left h5{
    margin:0;
    font-size:22px;
}

.net-right{
    width:40%;
    text-align:right;
    padding:20px;
    font-size:32px;
    font-weight:bold;
}

/* SIGNATURE */
.signature{
    display:flex;
    justify-content:space-between;
    margin-top:70px;
    color:#4b6b7f;
}

/* PRINT */
@media print{
    body *{ visibility:hidden; }
    #printArea, #printArea *{ visibility:visible; }
    #printArea{
        position:absolute;
        left:0;
        top:0;
        width:100%;
    }
}

</style>
@endsection