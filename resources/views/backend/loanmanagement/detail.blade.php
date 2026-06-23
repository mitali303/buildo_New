@extends('backend.partials.master')

@section('title', 'Loan Management Detail')

@section('maincontent')
<main class="content">
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
  

    <div class="card">
        

        <div class="card-body">
            <table class="table table-striped table-hover" >
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Transaction Type</th>
                        <th>Type</th>
                        <th>Payment Type</th>
                        <th>Credit</th>
                        <th>Debit</th>
                        <th>Narration</th>
                       {{-- @if(request('rtype') != 'report')--}}
                            <th>Action</th>
                       {{-- @endif --}}
                    </tr>
                </thead>

                <tbody>
                @php
                    $i = 1;
                    $totalCredit = 0;
                    $totalDebit = 0;
                @endphp

                @foreach($loans as $loan)
                    @php
                        $isPaid = $loan->paytype === 'Paid';
                        $color = $isPaid ? '#E80000' : '#289A47';
                        $type  = $isPaid ? 'Given' : 'Taken';
                        $transType = $loan->transType == 0 ? 'Payment' : 'Interest';
                        $paymentType = $loan->cheque_no ?: 'Cash';

                        $v_record = DB::table('partners')
                        ->where('ID', $loan->customer)
                        ->first();
                    @endphp

                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ \Carbon\Carbon::parse($loan->Date)->format('d-m-Y') }}</td>
                        <td>{{ $v_record->Name }}</td>
                        <td>{{ $transType }}</td>
                        <td>{{ $type }}</td>
                        <td>{{ $paymentType }}</td>

                        @if($isPaid)
                            <td></td>
                            <td style="color:red">{{ $loan->amt_pay }}</td>
                            @php $totalDebit += $loan->amt_pay; @endphp
                        @else
                            <td style="color:green">{{ $loan->amt_pay }}</td>
                            <td></td>
                            @php $totalCredit += $loan->amt_pay; @endphp
                        @endif

                        <td>{{ $loan->narration }}</td>

                       {{-- @if(request('rtype') != 'report') --}}
                        <td>
                            <a href="{{ route('loan_management.edit', $loan->ID) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fa fa-pencil"></i>
                            </a>

                            <form action="{{ route('loan_management.delete', $loan->ID) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Delete this record?')"
                                        class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                      {{--  @endif --}}
                    </tr>
                @endforeach

                <tr class="fw-bold">
                    <td colspan="6" class="text-end">Grand Total</td>
                    <td>{{ $totalCredit }}</td>
                    <td>{{ $totalDebit }}</td>
                    <td colspan="2"></td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>
</main>
@endsection

@push('scripts')
<script>
$(function(){
    $('#loan-detail-table').DataTable();
});
</script>
@endpush
