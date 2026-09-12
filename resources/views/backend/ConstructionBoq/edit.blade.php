@extends('backend.partials.master')

@section('title')
Edit Construction BOQ
@endsection

@section('maincontent')

<div class="container-fluid">
    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"> Edit Construction BOQ </h4>
            <small class="text-muted"> Update Project Construction Cost Estimation </small>
        </div>
        <a href="{{ route('construction-boq.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>
    {{-- SUCCESS / ERROR --}}
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif


    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif


    {{-- FORM --}}
    <form method="POST" action="{{ route('construction-boq.update', $boq->id) }}" id="boqForm">
        @csrf
        @method('PUT')
        {{-- =================================================
             PROJECT DETAILS
        ================================================== --}}

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"> Project Details </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Scheme --}}
                    <div class="col-md-6">
                        <label class="form-label">
                            Project / Site Name
                            <span class="text-danger">*</span>
                        </label>
                        <select name="scheme_id" id="scheme_id" class="form-select" required>
                            <option value="">
                                Select Project / Site
                            </option>
                            @foreach($schemes as $scheme)
                            <option value="{{ $scheme->ID }}" @selected( old( 'scheme_id' , $boq->scheme_id) == $scheme->ID )>
                                {{ $scheme->Name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Date --}}
                    <div class="col-md-3">
                        <label class="form-label">
                            BOQ Date<span class="text-danger">*</span>
                        </label>
                        <input type="date" name="estimate_date" class="form-control" value="{{ old('estimate_date', $boq->estimate_date) }}" required>
                    </div>
                    {{-- Area --}}
                    <div class="col-md-3">
                        <label class="form-label">
                            Built-up Area (Sq.ft)
                        </label>
                        <input type="number" step="0.01" min="0" name="built_up_area" class="form-control"
                            value="{{ old( 'built_up_area', $boq->built_up_area) }}">
                    </div>
                    {{-- Customer --}}
                    <div class="col-md-6">
                        <label class="form-label">
                            Customer / Owner
                        </label>
                        <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $boq->customer_name   ) }}">
                    </div>
                    {{-- Site Address --}}
                    <div class="col-md-6">
                        <label class="form-label">
                            Site Address
                        </label>
                        <textarea name="site_address" class="form-control" rows="2">{{ old( 'site_address', $boq->site_address ) }}</textarea>
                    </div>
                </div>
            </div>
        </div>


        {{-- =================================================
             BOQ ITEMS
        ================================================== --}}

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex justify-content-between">
                <h5 class="mb-0"> BOQ Items </h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="addBoqRow()">
                    + Add Item
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="boqTable">
                        <thead>
                            <tr>
                                <th width="12%">Category </th>
                                <th width="18%"> Work Description </th>
                                <th width="10%"> Material </th>
                                <th width="10%"> Agency </th>
                                <th width="7%"> Unit </th>
                                <th width="8%"> Qty </th>
                                <th width="8%"> Material Rate </th>
                                <th width="8%"> Labour Rate </th>
                                <th width="9%"> Total </th>
                                <th width="5%"> Action </th>
                            </tr>
                        </thead>
                        <tbody id="boqRows">
                            @forelse($boq->items as $index => $item)
                            <tr>
                                {{-- Category --}}
                                <td>
                                    <input type="text" name="items[{{ $index }}][category_name]" class="form-control"
                                        value="{{ old( "items.$index.category_name",  $item->category_name ) }}">
                                </td>
                                {{-- Description --}}
                                <td>
                                    <input type="text" name="items[{{ $index }}][description]" class="form-control"
                                        value="{{ old( "items.$index.description",  $item->item_description ) }}" required>
                                </td>
                                {{-- Material --}}
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][material_id]" class="material-id"
                                        value="{{ old("items.$index.material_id", $item->material_id ) }}">

                                    <input type="text" name="items[{{ $index }}][material_name]" class="form-control"
                                        value="{{ old( "items.$index.material_name", $item->material_name  ) }}">
                                </td>
                                {{-- Agency --}}
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][agency_id]" class="agency-id"
                                        value="{{ old( "items.$index.agency_id", $item->agency_id ) }}">

                                    <input type="text" name="items[{{ $index }}][agency_name]" class="form-control"
                                        value="{{ old( "items.$index.agency_name", $item->agency_name ) }}">
                                </td>
                                {{-- Unit --}}
                                <td>
                                    <input type="text" name="items[{{ $index }}][unit]" class="form-control"
                                        value="{{ old( "items.$index.unit",  $item->unit ) }}" required>
                                </td>
                                {{-- Quantity --}}
                                <td>
                                    <input type="number" step="0.001" min="0" name="items[{{ $index }}][quantity]" class="form-control qty"
                                        value="{{ old( "items.$index.quantity", $item->quantity ) }}" oninput="calculateRow(this)" required>
                                </td>
                                {{-- Material Rate --}}
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[{{ $index }}][material_rate]"
                                        class="form-control material-rate" value="{{ old( "items.$index.material_rate", $item->material_rate  ) }}"
                                        oninput="calculateRow(this)">
                                </td>
                                {{-- Labour Rate --}}
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[{{ $index }}][labour_rate]" class="form-control labour-rate"
                                        value="{{ old("items.$index.labour_rate", $item->labour_rate ) }}"
                                        oninput="calculateRow(this)">
                                </td>
                                {{-- Total --}}
                                <td>
                                    <input type="text" class="form-control row-total" value="0.00" readonly>
                                </td>
                                {{-- Delete --}}
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td>
                                    <input type="text" name="items[0][category_name]" class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="items[0][description]" class="form-control" required>
                                </td>
                                <td>
                                    <input type="hidden" name="items[0][material_id]" class="material-id">
                                    <input type="text" name="items[0][material_name]" class="form-control">
                                </td>
                                <td>
                                    <input type="hidden" name="items[0][agency_id]" class="agency-id">
                                    <input type="text" name="items[0][agency_name]" class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="items[0][unit]" class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" step="0.001" min="0" name="items[0][quantity]" class="form-control qty" oninput="calculateRow(this)" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[0][material_rate]" class="form-control material-rate" oninput="calculateRow(this)">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[0][labour_rate]" class="form-control labour-rate" oninput="calculateRow(this)">
                                </td>
                                <td>
                                    <input type="text" class="form-control row-total" value="0.00" readonly>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Material Total </th>
                                <th colspan="2">
                                    <input type="text" id="materialTotal" class="form-control"  value="0.00" readonly>
                                </th>
                                <th> Labour </th>
                                <th colspan="2">
                                    <input type="text"  id="labourTotal" class="form-control" value="0.00" readonly>
                                </th>
                            </tr>
                            <tr>
                                <th colspan="8" class="text-end">Sub Total </th>
                                <th colspan="2">
                                    <input type="text" id="subtotal"  class="form-control" value="0.00" readonly>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        {{-- =================================================
             ADDITIONAL COSTS
        ================================================== --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Additional Costs & Taxes </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Overhead --}}
                    <div class="col-md-3">
                        <label class="form-label">
                            Overhead (%)
                        </label>
                        <input type="number" step="0.01" min="0" name="overhead_percent" id="overhead_percent" class="form-control"
                            value="{{ old('overhead_percent', $boq->overhead_percent ?? 0   ) }}" oninput="calculateSummary()">
                    </div>
                    {{-- Overhead Amount --}}
                    <div class="col-md-3">
                        <label class="form-label">
                            Overhead Amount
                        </label>
                        <input type="text" id="overhead_amount" class="form-control" value="0.00"  readonly>
                    </div>
                    {{-- Contingency --}}
                    <div class="col-md-3">
                        <label class="form-label">
                            Contingency (%)
                        </label>
                        <input type="number" step="0.01"  min="0" name="contingency_percent" id="contingency_percent" class="form-control"
                            value="{{ old(  'contingency_percent',  $boq->contingency_percent ?? 0 ) }}"  oninput="calculateSummary()">
                    </div>

                    {{-- Contingency Amount --}}
                    <div class="col-md-3">
                        <label class="form-label">
                            Contingency Amount
                        </label>
                        <input type="text" id="contingency_amount" class="form-control"  value="0.00" readonly>
                    </div>


                    {{-- GST --}}
                    <div class="col-md-4">
                        <label class="form-label">
                            GST (%)
                        </label>
                        <input type="number"  step="0.01" min="0" name="gst_percent" id="gst_percent" class="form-control"
                            value="{{ old('gst_percent',   $boq->gst_percent ?? 0 ) }}" oninput="calculateSummary()">
                    </div>
                    {{-- GST Amount --}}
                    <div class="col-md-4">
                        <label class="form-label">
                            GST Amount
                        </label>
                        <input type="text" id="gst_amount" class="form-control" value="0.00" readonly>
                    </div>
                    {{-- Grand Total --}}
                    <div class="col-md-4">
                        <label class="form-label">
                            Grand Total
                        </label>
                        <input type="text" id="grand_total"class="form-control fw-bold" value="0.00" readonly>
                    </div>
                    {{-- Notes --}}
                    <div class="col-md-12">
                        <label class="form-label">
                            Notes
                        </label>
                        <textarea name="notes" class="form-control"
                            rows="3">{{ old('notes',$boq->notes ?? '' ) }}</textarea>
                    </div>
                </div>
            </div>
        </div>


        {{-- =================================================
             UPDATE BUTTON
        ================================================== --}}
        <div class="text-end mb-4">
            <a href="{{ route('construction-boq.index') }}"class="btn btn-secondary me-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-success px-4">Update Construction BOQ </button>
        </div>
    </form>
</div>
<script>
   

    let rowIndex = {
        {  $boq - > items - > count()
        }
    };


    /*
    |--------------------------------------------------------------------------
    | ADD NEW ROW
    |--------------------------------------------------------------------------
    */

    function addBoqRow() {
        let html = `
        <tr>
            <td>
                <input type="text"  name="items[${rowIndex}][category_name]"  class="form-control">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][description]" class="form-control" required>
            </td>
            <td>
                <input type="hidden" name="items[${rowIndex}][material_id]" class="material-id">
                <input type="text" name="items[${rowIndex}][material_name]" class="form-control">
            </td>
            <td>
                <input type="hidden"  name="items[${rowIndex}][agency_id]" class="agency-id">
                <input type="text" name="items[${rowIndex}][agency_name]" class="form-control">
            </td>
            <td>
                <input type="text"  name="items[${rowIndex}][unit]" class="form-control" required>
            </td>
            <td>
                <input type="number" step="0.001" min="0" name="items[${rowIndex}][quantity]" class="form-control qty" oninput="calculateRow(this)" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][material_rate]" class="form-control material-rate"  oninput="calculateRow(this)">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][labour_rate]" class="form-control labour-rate" oninput="calculateRow(this)">
            </td>
            <td>
                <input type="text" class="form-control row-total" value="0.00" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm"  onclick="removeRow(this)">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;


        document
            .getElementById('boqRows')
            .insertAdjacentHTML(
                'beforeend',
                html
            );


        rowIndex++;
    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE ROW
    |--------------------------------------------------------------------------
    */

    function removeRow(button) {
        const rows = document.querySelectorAll( '#boqRows tr' );

        if (rows.length <= 1) {
            alert(  'At least one BOQ item is required.'  );
            return;
        }
        button
            .closest('tr')
            .remove();

        calculateSummary();
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE ROW
    |--------------------------------------------------------------------------
    */
    function calculateRow(input) {
        const row = input.closest('tr');
        const qty = parseFloat(  row.querySelector('.qty')?.value  ) || 0;
        const materialRate =  parseFloat( row.querySelector('.material-rate')?.value ) || 0;
        const labourRate = parseFloat(   row.querySelector('.labour-rate')?.value ) || 0;
        const materialAmount = qty * materialRate;
        const labourAmount = qty * labourRate;
        const total = materialAmount + labourAmount;
        row.querySelector('.row-total').value = total.toFixed(2);
        calculateSummary();
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE SUMMARY
    |--------------------------------------------------------------------------
    */

    function calculateSummary() {
        let materialTotal = 0;
        let labourTotal = 0;
        document
            .querySelectorAll('#boqRows tr')
            .forEach(function(row) {

                const qty =  parseFloat(  row.querySelector('.qty')?.value ) || 0;
                const materialRate =  parseFloat(  row.querySelector('.material-rate')?.value  ) || 0;
                const labourRate = parseFloat(  row.querySelector('.labour-rate')?.value) || 0;
                materialTotal += qty * materialRate;
                labourTotal += qty * labourRate;
                const rowTotal =  row.querySelector('.row-total');
                if (rowTotal) {
                    rowTotal.value =
                        (
                            qty * materialRate +
                            qty * labourRate
                        ).toFixed(2);
                }

            });

        const subtotal =  materialTotal + labourTotal;
        const overheadPercent = parseFloat( document.getElementById(  'overhead_percent' ).value ) || 0;
        const overheadAmount = subtotal * overheadPercent / 100;
        const contingencyPercent = parseFloat( document.getElementById('contingency_percent' ).value  ) || 0;
        const contingencyAmount = subtotal *contingencyPercent / 100;
        const taxableAmount = subtotal + overheadAmount +contingencyAmount;
        const gstPercent = parseFloat(document.getElementById( 'gst_percent' ).value ) || 0;
        const gstAmount = taxableAmount * gstPercent / 100;
        const grandTotal = taxableAmount + gstAmount;

        document.getElementById( 'materialTotal' ).value = materialTotal.toFixed(2);
        document.getElementById('labourTotal'  ).value = labourTotal.toFixed(2);
        document.getElementById('subtotal' ).value = subtotal.toFixed(2);
        document.getElementById( 'overhead_amount' ).value = overheadAmount.toFixed(2);
        document.getElementById('contingency_amount').value =contingencyAmount.toFixed(2);
        document.getElementById('gst_amount').value = gstAmount.toFixed(2);
        document.getElementById( 'grand_total' ).value = grandTotal.toFixed(2);
    }
    /*
    |--------------------------------------------------------------------------
    | INITIAL CALCULATION
    |--------------------------------------------------------------------------
    */
    document.addEventListener( 'DOMContentLoaded', function() { calculateSummary(); }
    );
</script>

@endsection