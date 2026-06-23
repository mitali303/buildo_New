@extends('backend.partials.master')
@section('title')
  Loan Management
@endsection
@section('maincontent')
<main class="content">
    <style>
        div.dataTables_wrapper div.dataTables_length select {
   
            width: 45%;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
           
            padding: 0px;
        }   
    </style>
    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3><strong>Loan Management</strong></h3>
            </div>
            <div class="col-auto ms-auto text-end">
                @if (hasPermission('create_loan_management'))
                    <a href="{{ route('loan_management.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                @endif
            </div>
        </div>

        <form action="{{ route('loan_management') }}" method="GET" class="mb-3 row g-2">
           <div class="col-md-2">
                <input type="date" name="FromDate" class="form-control" value="{{ $fromDate }}">
            </div>

            <div class="col-md-2">
                <input type="date" name="ToDate" class="form-control" value="{{ $toDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <div class="row">
        <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped" id="bootstrap-table">
                    <thead >
                        <tr>
                            <th>Sr.No</th>
                            <th>Name</th>
                            <th>Total Credit Amount</th>
                            <th>Total Debit Amount</th>
                            <th>Interest Credit</th>
                            <th>Interest Debit</th>
                            <th>Balance</th>
                            @if(request('rtype') != 'report')
                                <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i = 1;
                            $credittotl = 0;
                            $debittotl = 0;
                            $intrestcredit = 0;
                            $intrestdebit = 0;
                            $totalbal = 0;
                        @endphp

                        @foreach($loanData as $data)
                            @php
                                $credittotl += $data['loanAmtTaken'];
                                $debittotl += $data['loanAmtGive'];
                                $intrestcredit += $data['interestRec'];
                                $intrestdebit += $data['interestPaid'];
                                $totalbal += $data['balance'];
                            @endphp
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>
                                   
                                        {{ $data['customer']->Name }}
                                  
                                </td>
                                <td><span style="color:#289A47">{{ $data['loanAmtTaken'] }}</span></td>
                                <td><span style="color:#E80000">{{ $data['loanAmtGive'] }}</span></td>
                                <td><span style="color:#289A47">{{ $data['interestRec'] }}</span></td>
                                <td><span style="color:#E80000">{{ $data['interestPaid'] }}</span></td>
                                <td><span style="color:{{ $data['balance'] > 0 ? '#289A47' : '#E80000' }}">{{ $data['balance'] }}</span></td>
                                @if(request('rtype') != 'report')
                                    <td>
                                        <a href="{{ route('loan_detail', ['customer' => $data['customer']->ID]) }}" class="btn btn-xs btn-primary">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach

                        <tr>
                            <td></td>
                            <td>Grand Total</td>
                            <td>{{ $credittotl }}</td>
                            <td>{{ $debittotl }}</td>
                            <td>{{ $intrestcredit }}</td>
                            <td>{{ $intrestdebit }}</td>
                            <td>{{ $totalbal }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
      </div>
    </div>
</main>
@endsection

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>

<script>
$(document).ready(function () {

    $('#bootstrap-table').DataTable({
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        paging: true,
        info: true,
        responsive: true,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [-1] }
        ]
    });

    $('.date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    });

});
</script>

