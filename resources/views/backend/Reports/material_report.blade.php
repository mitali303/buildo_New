@extends('backend.partials.master')

@section('title')
Material Purchase Report
@endsection

@section('maincontent')
<style>
@media print {

    body * {
        visibility: hidden !important;
    }

    #print-area, #print-area * {
        visibility: visible !important;
    }

    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    table {
        font-size: 12px !important;
        width: 100% !important;
        border: 1px solid #000;
    }

     td {
        border: 1px solid #000 !important;
        padding: 4px !important;
        color: #000 !important;
    }

    .no-print {
        display: none !important;
    }

}
.table-responsive {
    overflow-x: auto;
    width: 100%;
}

#datatables-buttons {
    min-width: 1600px;
    white-space: nowrap;
}
</style>

<main class="content">
    <div class="container-fluid p-0">

        <div class="row mb-2 mb-xl-3">
            <div class="col-auto d-none d-sm-block">
                <h3><strong>Material Purchase Report</strong></h3>
            </div>
            <div class="col-auto ms-auto text-end mt-n1">
                 <button class="btn btn-primary" onclick="printReport()">Print</button>

                  <!-- <button type="button" class="btn btn-info" onclick="exportExcel()">Export to Excel</button> -->
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                {{-- Filters --}}
                <form method="GET" action="{{ route('reports.material_purchase_report') }}">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <input type="date" name="from_date" class="form-control"
                                   value="{{ $fromDate }}">
                        </div>

                        <div class="col-md-2">
                            <input type="date" name="to_date" class="form-control"
                                   value="{{ $toDate }}">
                        </div>

                        <div class="col-md-2">
                            <select name="material_id" class="form-control">
                                <option value="">Select Material</option>
                                @foreach($materials as $material)
                                    <option value="{{ $material->id }}"
                                        {{ request('material_id') == $material->id ? 'selected' : '' }}>
                                        {{ $material->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="vendor_id" class="form-control">
                                <option value="">Select Supplier</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}"
                                        {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="scheme_id" class="form-control">
                                <option value="">Select Site</option>
                                @foreach($schemes as $scheme)
                                    <option value="{{ $scheme->id }}"
                                        {{ (session('selected_scheme_id') == $scheme->id) ? 'selected' : '' }}>
                                        {{ $scheme->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>

                {{-- Table --}}
                <div id="print-area" class="table-responsive">
                <table id="datatables-buttons" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Invoice No</th>
                            <th>Supplier</th>
                            <th>Material - Type</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>GST Amt</th>
                            <th>Scheme</th>
                            <th>Total GST</th>
                            <th>G.Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $key => $invoice)
                            @php $totalGst = 0; @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->Date)->format('d-m-Y') }}</td>
                                <td>{{ $invoice->Invno }}</td>
                                <td>{{ $invoice->supplier_name ?? '-' }}</td>
                                <td colspan="5">
                                    <table class="w-100">
                                        @foreach($invoice->products as $product)
                                            @php
                                                $gst = ($product->Amount * $product->Vat) / 100;
                                                $totalGst += $gst;
                                            @endphp
                                            <tr>
                                                <td width="20%">
                                                    {{ $product->Name }} - {{ $product->Type }}
                                                </td>
                                                <td width="20%" class="text-center">{{ $product->Qty }}</td>
                                                <td width="20%">{{ $product->Rate }}</td>
                                                <td width="20%">{{ $product->Amount }}</td>
                                                <td width="20%">{{ number_format($gst,2) }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>

                                <td>{{ $invoice->scheme ?? '' }}</td>
                                <td>{{ number_format($totalGst,2) }}</td>
                                <td>{{ number_format($invoice->gtotal,2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</main>
@endsection

<script>
    $('#datatables-buttons').DataTable({
    responsive: false,
    autoWidth: false,
    scrollX: true
});
function printReport() {
    window.print();
}

// 🔹 Export Excel (same page)
function exportExcel() {
    let fdate = $('input[name="FromDate"]').val();
    let tdate = $('input[name="ToDate"]').val();

    window.location.href =
        "{{ route('reports.material_purchase_report') }}?type=excel&FromDate=" + fdate + "&ToDate=" + tdate;
}

</script>