@extends('backend.partials.master')

@section('title')
Salary Slip
@endsection

@section('maincontent')

<style>

body{
    background:#eef2f7;
    font-family:Arial, Helvetica, sans-serif;
}

/* =====================================================
    CONTAINER
===================================================== */

.salary-container{
    padding:12px;
}

/* =====================================================
    BUTTON
===================================================== */

.top-actions{
    margin-bottom:15px;
}

.print-btn{
    background:linear-gradient(135deg,#16a34a,#166534);
    border:none;
    color:#fff;
    padding:10px 22px;
    border-radius:8px;
    font-size:14px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.print-btn:hover{
    background:linear-gradient(135deg,#15803d,#14532d);
}

/* =====================================================
    PRINT AREA
===================================================== */

#printArea{
    width:210mm;
    min-height:285mm;
    margin:auto;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 4px 20px rgba(0,0,0,0.08);
}

/* =====================================================
    HEADER
===================================================== */

.header{
    background:#ffffff;
    padding:14px 20px;
    border-bottom:4px solid #16a34a;
}

.header-flex{
    display:flex;
    align-items:center;
    gap:16px;
}

.logo-box{
    width:78px;
    height:78px;
    border-radius:10px;
    background:#f0fdf4;
    border:1px solid #bbf7d0;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:6px;
    flex-shrink:0;
}

.company-logo{
    width:100%;
    height:100%;
    object-fit:contain;
}

.company-details{
    flex:1;
    text-align:right;
}

.company-name{
    font-size:28px;
    font-weight:700;
    color:#166534;
    margin-bottom:4px;
}

.company-address{
    font-size:12px;
    color:#4b5563;
    line-height:18px;
}

.company-contact{
    margin-top:3px;
    font-size:12px;
    color:#374151;
    font-weight:600;
}

/* =====================================================
    TITLE
===================================================== */

.salary-title{
    text-align:center;
    padding:12px 15px 5px;
}

.salary-title h2{
    font-size:28px;
    letter-spacing:2px;
    color:#111827;
    margin-bottom:3px;
}

.salary-title p{
    font-size:13px;
    color:#6b7280;
}

/* =====================================================
    SECTION
===================================================== */

.section{
    padding:0 18px 12px;
}

.section-title{
    font-size:16px;
    font-weight:700;
    color:#166534;
    margin-bottom:8px;
    border-left:4px solid #22c55e;
    padding-left:8px;
}

/* =====================================================
    INFO TABLE
===================================================== */

.info-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.info-table td{
    border:1px solid #d1fae5;
    padding:8px 10px;
    font-size:12px;
    word-wrap:break-word;
}

.info-label{
    background:#f0fdf4;
    font-weight:700;
    color:#166534;
    width:18%;
}

.info-table td:nth-child(2),
.info-table td:nth-child(4){
    width:32%;
}

/* =====================================================
    SALARY TABLE
===================================================== */

.salary-table{
    width:100%;
    border-collapse:collapse;
    margin-top:5px;
}

.salary-table th{
    background:linear-gradient(135deg,#16a34a,#166534);
    color:#fff;
    padding:9px;
    border:1px solid #16a34a;
    font-size:12px;
    text-transform:uppercase;
}

.salary-table td{
    border:1px solid #e5e7eb;
    padding:8px 10px;
    font-size:12px;
}

.salary-table tbody tr:nth-child(even){
    background:#f9fafb;
}

.text-right{
    text-align:right;
}

.gross-row{
    background:#dcfce7 !important;
    font-weight:700;
}

.net-row{
    background:linear-gradient(135deg,#16a34a,#14532d);
    color:#fff;
    font-size:14px;
    font-weight:700;
}

.net-row td{
    padding:10px;
    border-color:#16a34a;
}

/* =====================================================
    NOTE
===================================================== */

.note-box{
    margin:0 18px 12px;
    background:#f8fafc;
    border-left:4px solid #22c55e;
    padding:10px 12px;
    border-radius:8px;
    font-size:12px;
    color:#374151;
    line-height:20px;
}

/* =====================================================
    FOOTER
===================================================== */

.footer{
    padding:12px 18px 20px;
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
}

.generated-text{
    font-size:11px;
    color:#6b7280;
}

.sign-box{
    width:200px;
    text-align:center;
    font-size:12px;
    color:#111827;
}

.sign-line{
    margin-top:35px;
    border-top:2px solid #111827;
    padding-top:6px;
    font-weight:700;
}

/* =====================================================
    PRINT
===================================================== */

@media print{

    body *{
        visibility:hidden;
    }

    #printArea,
    #printArea *{
        visibility:visible;
    }

    #printArea{
        position:absolute;
        left:0;
        top:0;
        width:100%;
        min-height:auto;
        box-shadow:none;
        border-radius:0;
    }

    .no-print{
        display:none !important;
    }

    .salary-container{
        padding:0;
    }

    body{
        background:#fff;
    }

    @page{
        size:A4 portrait;
        margin:4mm;
    }
}

</style>

<div class="container-fluid salary-container">

    {{-- PRINT BUTTON --}}

    <div class="top-actions no-print" style="text-align: center;">

        <button onclick="window.print()"
                class="print-btn">

            🖨 Print Salary Slip

        </button>

    </div>

    {{-- PRINT AREA --}}

    <div id="printArea">

        {{-- HEADER --}}

        <div class="header">

            <div class="header-flex">

                <div class="logo-box">

                    @if(!empty($company->logo_path))

                        <img src="{{ asset($company->logo_path) }}"
                             class="company-logo">

                    @endif

                </div>

                <div class="company-details">

                    <div class="company-name">
                        {{ $company->company_name ?? 'Company Name' }}
                    </div>

                    <div class="company-address">
                        {{ $company->address ?? '' }}
                    </div>

                    <div class="company-contact">

                        📞 {{ $company->mobile_number ?? '' }}

                        &nbsp;&nbsp; | &nbsp;&nbsp;

                        GSTIN : {{ $company->gstin ?? '' }}

                    </div>

                    <div class="company-contact">

                        🌐 {{ $company->web_url ?? '' }}

                    </div>

                </div>

            </div>

        </div>

        {{-- TITLE --}}

        <div class="salary-title">

            <h2>SALARY SLIP</h2>

            <p>
                Salary For {{ $salary->month ?? '' }} - {{ $salary->year ?? '' }}
            </p>

        </div>

        {{-- EMPLOYEE DETAILS --}}

        <div class="section">

            <div class="section-title">
                Employee Details
            </div>

            <table class="info-table">

                <tr>

                    <td class="info-label">Employee Name</td>
                    <td>{{ $salary->user->Name ?? '' }}</td>

                    <td class="info-label">Salary No</td>
                    <td>{{ $salary->salary_no ?? '' }}</td>

                </tr>

                <tr>

                    <td class="info-label">Employee ID</td>
                    <td>{{ $salary->user->emp_id ?? '' }}</td>

                    <td class="info-label">Designation</td>
                    <td>{{ $salary->user->designation ?? '' }}</td>

                </tr>

                <tr>

                    <td class="info-label">Joining Date</td>
                    <td>{{ $salary->user->date_joining ?? '' }}</td>

                    <td class="info-label">Salary Date</td>
                    <td>{{ date('d-m-Y', strtotime($salary->date)) }}</td>

                </tr>

                <tr>

                    <td class="info-label">Bank Name</td>
                    <td>{{ $salary->user->bank_name ?? '' }}</td>

                    <td class="info-label">Account No</td>
                    <td>{{ $salary->user->account_no ?? '' }}</td>

                </tr>

                <tr>

                    <td class="info-label">IFSC Code</td>
                    <td>{{ $salary->user->IFSC ?? '' }}</td>

                    <td class="info-label">Payment Method</td>
                    <td>{{ ucfirst($salary->payment_method ?? '') }}</td>

                </tr>

            </table>

        </div>

        {{-- ATTENDANCE SUMMARY --}}

        <div class="section">

            <div class="section-title">
                Attendance Summary
            </div>

            <table class="info-table">

                <tr>

                    <td class="info-label">Working Days</td>
                    <td>{{ $salary->working_days ?? 0 }}</td>

                    <td class="info-label">Present Days</td>
                    <td>{{ $salary->total_present_days ?? 0 }}</td>

                </tr>

                <tr>

                    <td class="info-label">Absent Days</td>
                    <td>{{ $salary->total_absent_days ?? 0 }}</td>

                    <td class="info-label">Paid Leaves</td>
                    <td>{{ $salary->paid_leaves ?? 0 }}</td>

                </tr>

                <tr>

                    <td class="info-label">Per Day Salary</td>
                    <td>
                        ₹ {{ number_format($salary->per_day_salary ?? 0,2) }}
                    </td>

                    <td class="info-label">Absent Deduction</td>
                    <td>
                        ₹ {{ number_format($salary->absent_deduction ?? 0,2) }}
                    </td>

                </tr>

                <tr>

                    <td class="info-label">OT Hours</td>
                    <td>
                        {{ number_format($salary->overtime_hours ?? 0,2) }}
                    </td>

                    <td class="info-label">OT Rate / Hour</td>
                    <td>
                        ₹ {{ number_format($salary->per_hour_ot_rate ?? 0,2) }}
                    </td>

                </tr>

            </table>

        </div>

        {{-- SALARY BREAKDOWN --}}

        <div class="section">

            <div class="section-title">
                Salary Breakdown
            </div>

            <table class="salary-table">

                <thead>

                    <tr>

                        <th>Earnings</th>
                        <th width="20%">Amount</th>

                        <th>Deductions</th>
                        <th width="20%">Amount</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>Basic Salary</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->basic_salary ?? 0,2) }}
                        </td>

                        <td>PF</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->pf ?? 0,2) }}
                        </td>

                    </tr>

                    <tr>

                        <td>HRA Allowance</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->user->hra_allowance_amount ?? 0,2) }}
                        </td>

                        <td>ESI</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->esi ?? 0,2) }}
                        </td>

                    </tr>

                    <tr>

                        <td>Other Allowance</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->user->allowance_amount ?? 0,2) }}
                        </td>

                        <td>Advance EMI</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->advance_emi ?? 0,2) }}
                        </td>

                    </tr>

                    <tr>

                        <td>Overtime Amount</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->overtime_amount ?? 0,2) }}
                        </td>

                        <td>Late Deduction</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->late_deduction ?? 0,2) }}
                        </td>

                    </tr>

                    <tr>

                        <td>Absent Deduction</td>

                        <td class="text-right">
                            ₹ 0.00
                        </td>

                        <td>Absent Salary Cut</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->absent_deduction ?? 0,2) }}
                        </td>

                    </tr>

                    <tr class="gross-row">

                        <td>Gross Salary</td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->gross ?? 0,2) }}
                        </td>

                        <td>Total Deduction</td>

                        <td class="text-right">

                            ₹
                            {{
                                number_format(
                                    ($salary->pf ?? 0)
                                    +
                                    ($salary->esi ?? 0)
                                    +
                                    ($salary->advance_emi ?? 0)
                                    +
                                    ($salary->late_deduction ?? 0)
                                    +
                                    ($salary->absent_deduction ?? 0),
                                    2
                                )
                            }}

                        </td>

                    </tr>

                    <tr class="net-row">

                        <td colspan="3" class="text-right">
                            NET SALARY
                        </td>

                        <td class="text-right">
                            ₹ {{ number_format($salary->net_salary ?? 0,2) }}
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        {{-- NOTE --}}

        <div class="note-box">

            <strong>Narration :</strong>

            {{ $salary->narration ?? 'Salary credited successfully.' }}

        </div>

        {{-- FOOTER --}}

        <div class="footer">

            <div class="generated-text">

                This is system generated salary slip.

            </div>

            <div class="sign-box">

                For {{ $company->company_name ?? '' }}

                <div class="sign-line">

                    Authorized Signatory

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
