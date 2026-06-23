@extends('backend.partials.master')
@section('title')
  Stamp & Other Expenses Report
@endsection

@section('maincontent')


<main class="content">
    <div class="container-fluid p-0">
   
        
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3><strong></strong>Stamp/Other Expenses</h3>
            </div>
            
        </div>

           <form id="filterForm" class="row g-2 mb-3" method="GET"
      action="{{ route('reports.stamp_other_expense_report') }}">

    <div class="col-md-2">
        <label class="form-label">From Date</label>
        <input type="date" name="from_date" class="form-control"
               value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">To Date</label>
        <input type="date" name="to_date" class="form-control"
               value="{{ request('to_date', now()->format('Y-m-d')) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Expense Type</label>
        <select name="typesrch" class="form-control">
            <option value="">Select Type</option>
            <option value="all" {{ request('typesrch') == 'all' ? 'selected' : '' }}>
                All
            </option>

            @foreach($types as $type)
                <option value="{{ $type }}"
                    {{ request('typesrch') == $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2 align-self-end">
        <button type="submit" class="btn btn-primary">
            Search
        </button>
    </div>

</form>
        <div class="row">
          <div class="col-12">
           <div class="card">
            <div class="card-body">
            <table id="datatables-buttons" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Sr.No</th>
                        <th>Date</th>
                        <th>Paid To</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Payment Method</th>
                        <th>Scheme</th>
                        <th>Narration</th>
                       
                    </tr>
                </thead>
                <tbody>
                    @php $i = 0; @endphp
                    @foreach($records as $record)
                        @php $i++; @endphp
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->Date)->format('d-m-Y') }}</td>
                            <td>{{ $record->title }}</td>
                            <td>{{ $record->amt_pay }}</td>
                            <td>{{ $record->Exp_type }}</td>
                            <td>
                                @if($record->payment_method=='cash')
                                    Cash
                                @else
                                    {{ $accounts[$record->account_no]->Name ?? '' }} ({{ $record->cheque_no }})
                                @endif
                            </td>
                            <td>{{ $schemes[$record->schemeID]->Name ?? '' }}</td>
                            <td>{{ $record->narration }}</td>
                           
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3"><strong>Grand Total</strong></td>
                        <td colspan="6"><strong>{{ $totalAmount }}</strong></td>
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
