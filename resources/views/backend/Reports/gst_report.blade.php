@extends('backend.partials.master')

@section('title')
GST Report
@endsection

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-3">
            <div class="col">
                <h3><strong>GST Report</strong></h3>
            </div>
        </div>
<form method="GET" action="{{ route('reports.gst_report') }}" class="row mb-3">
                    <div class="col-md-3 d-flex align-items-center">
                        <label class="me-3 mb-0">From </label>
                        <input type="date" name="from_date" class="form-control datepicker"
                               value="{{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }}">
                    </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                    <div class="col-md-3 d-flex align-items-center">
                        <label class="me-3 mb-0">To </label>&nbsp;
                        <input type="date" name="to_date" class="form-control datepicker"
                               value="{{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}">
                    </div>&nbsp;&nbsp;

                    <div class="col-md-3 align-self-end">
                        <button class="btn btn-primary">Search</button>
                    </div>
                </form>
        
        
    <div class="row">
          <div class="col-12">
        <div class="card">
            <div class="card-body">

                <table id="datatables-buttons" class="table table-striped" style="width:100%">
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
</main>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
    $(function () {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true
        });
    });
</script>

