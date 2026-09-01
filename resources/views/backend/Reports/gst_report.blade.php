@extends('backend.partials.master')

@section('title')
GST Report
@endsection
<style>
    @media (max-width: 767px) {

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_filter {
        justify-content: flex-start;
    }

    .dataTables_wrapper .dataTables_filter input {
        width: 120px;
        margin-left: 5px;
    }

    .dataTables_wrapper .dataTables_length select {
        width: auto !important;
        margin-left: 5px;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-3">
            <div class="col">
                <h3><strong>GST Report</strong></h3>
            </div>
        </div>
            <form method="GET" action="{{ route('reports.gst_report') }}" class="row mb-3">
                    <div class="col-6 col-md-3 d-flex align-items-center">
                        <label class="me-3 mb-0">From </label>
                        <input type="date"
               name="from_date"
               value="{{ \Carbon\Carbon::parse($fromDate)->format('Y-m-d') }}"
               class="form-control">
                    </div>

                    <div class="col-6 col-md-3 d-flex align-items-center">
                        <label class="me-3 mb-0">To </label>
                        <input type="date"
               name="to_date"
               value="{{ \Carbon\Carbon::parse($toDate)->format('Y-m-d') }}"
               class="form-control">
                    </div>

                    <div class="col-12 col-md-3 text-center mt-2">
                        <button class="btn btn-primary">Search</button>
                    </div>
                </form>
        
        
    <div class="row">
          <div class="col-12">
        <div class="card">
            <div class="card-body">
            <div class="table-responsive">
                <table id="datatables-buttons" class="table table-striped" style="width:100%; white-space: nowrap;">
                    <thead >
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Agreement Amount</th>
                            <th>GST %</th>
                            <th>Total GST</th>
                            <th>Till Date Received</th>
                            <th>Till Date GST</th>
                            <th>Current Month Received</th>
                            <th>Current Month GST</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $key => $row)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                            <td>{{ $row['customer_name'] }}</td>
                            <td>{{ number_format($row['agreement_amt'], 2) }}</td>
                            <td>{{ $row['gst_per'] }}</td>
                            <td>{{ number_format($row['gst_total'], 2) }}</td>
                            <td>{{ number_format($row['received_till'], 2) }}</td>
                            <td>{{ number_format($row['gst_till'], 2) }}</td>
                            <td>{{ number_format($row['current_received'], 2) }}</td>
                            <td>{{ number_format($row['current_gst'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>
        </div>
      </div>
    </div>
</main>
@endsection


