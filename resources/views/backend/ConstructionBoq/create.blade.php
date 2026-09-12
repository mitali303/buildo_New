@extends('backend.partials.master')
@section('title')
Construction BOQ
@endsection
@section('maincontent')
<div class="container-fluid">
    {{-- =====================================================
         PAGE HEADER
    ====================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Construction BOQ</h4>
            <!-- <small class="text-muted">
                Project Construction Cost Estimation
            </small> -->
        </div>
        <a href="{{ route('construction-boq.index') }}" class="btn btn-secondary"> Back</a>
    </div>
    {{-- SUCCESS / ERROR --}}
    @if(session('success'))
        <div class="alert alert-success">  {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"> {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('construction-boq.store') }}" id="boqForm">
        @csrf
        {{--  PROJECT DETAILS --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"> Project Details </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                     {{-- Project / Site Name --}}
                    <div class="col-md-4 mb-1">
                         <label class="form-label"> Project / Site Name  <span class="text-danger">*</span> </label>
                        <select name="scheme_id" id="scheme_id" class="form-select" required>
                                <option value="">
                                    Select Project / Site
                                </option>
                             @foreach($schemes as $scheme)
                            <option value="{{ $scheme->ID }}"
                                    @selected(old('scheme_id') == $scheme->ID)>
                                     {{ $scheme->Name }}
                             </option>
                             @endforeach
                        </select>
                    </div>
                     {{-- BOQ Date --}}
                    <div class="col-md-4 mb-1">
                        <label class="form-label"> BOQ Date <span class="text-danger">*</span> </label>
                        <input type="date"  name="estimate_date" class="form-control" value="{{ old('estimate_date', date('Y-m-d')) }}"  required>
                    </div>
                     {{-- Built-up Area --}}
                     <div class="col-md-4 mb-1">
                         <label class="form-label">  Built-up Area (Sq.ft) </label>
                         <input type="number" step="0.01" min="0" name="built_up_area" class="form-control"
                               value="{{ old('built_up_area') }}">
                     </div>
                     {{-- Customer / Owner --}}
                    <div class="col-md-4 mb-1">
                        <label class="form-label"> Customer / Owner </label>
                         <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}">
                    </div>
                    {{-- Site Address --}}
                    <div class="col-md-4 mb-1">
                         <label class="form-label">  Site Address </label>
                          <textarea name="site_address" class="form-control" rows="1">{{ old('site_address') }}</textarea>
                     </div>

                </div>
            </div>
        </div>

        {{--  BOQ ITEMS --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex justify-content-between">
                <h5 class="mb-0"> BOQ Items </h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="addBoqRow()"> + Add Item </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="boqTable">
                        <thead>
                            <tr>
                                <th width="12%">  Category </th>
                                <th width="18%"> Work Description </th>
                                <th width="10%"> Material </th>
                                <th width="10%"> Agency </th>
                                <th width="7%">  Unit </th>
                                <th width="8%">  Qty </th>
                                <th width="8%"> Material Rate</th>
                                <th width="8%">  Labour Rate </th>
                                <th width="9%"> Total</th>
                                <th width="5%"> Action </th>
                            </tr>
                        </thead>
                        <tbody id="boqRows">
                            {{-- First row --}}
                            <tr>
                                <td>
                                    <input type="text" name="items[0][category_name]" class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="items[0][description]" class="form-control" required>
                                </td>
                                <td>
                                    <input type="hidden" name="items[0][material_id]" class="material-id">
                                    <input type="text"  name="items[0][material_name]" class="form-control">
                                </td>
                               <td>
                                    <input type="hidden" name="items[0][agency_id]" class="agency-id">
                                    <select name="items[0][agency_name]" class="form-select agency-select" onchange="setAgencyId(this)">
                                        <option value="">Select Agency</option>
                                        @foreach($agencies as $agency)
                                            <option value="{{ $agency->Name }}"
                                                    data-id="{{ $agency->ID }}">
                                                {{ $agency->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[0][unit]" class="form-control" placeholder="m3" required>
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
                                    <button type="button" class="btn btn-danger btn-sm"  onclick="removeRow(this)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end"> Material Total</th>
                                <th colspan="2">
                                    <input type="text" id="materialTotal" class="form-control" value="0.00" readonly>
                                </th>
                                <th>Labour </th>
                                <th colspan="2">
                                    <input type="text" id="labourTotal" class="form-control" value="0.00" readonly>
                                </th>
                            </tr>
                            <tr>
                                <th colspan="8" class="text-end"> Sub Total </th>
                                <th colspan="2">
                                    <input type="text"  id="subtotal" class="form-control" value="0.00"  readonly>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        {{-- ADDITIONAL COSTS --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"> Additional Costs & Taxes </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label"> Overhead (%)</label>
                        <input type="number" step="0.01" min="0" name="overhead_percent" id="overhead_percent" class="form-control" value="0" oninput="calculateSummary()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"> Overhead Amount</label>
                        <input type="text" id="overhead_amount" class="form-control" value="0.00" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"> Contingency (%) </label>
                        <input type="number" step="0.01" min="0" name="contingency_percent" id="contingency_percent" class="form-control"
                               value="0"  oninput="calculateSummary()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contingency Amount</label>
                        <input type="text" id="contingency_amount" class="form-control" value="0.00" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> GST (%) </label>
                        <input type="number" step="0.01" min="0" name="gst_percent" id="gst_percent" class="form-control" value="0"
                               oninput="calculateSummary()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> GST Amount</label>
                        <input type="text" id="gst_amount" class="form-control" value="0.00" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> Grand Total</label>
                        <input type="text" id="grand_total" class="form-control fw-bold" value="0.00" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label"> Notes </label>
                        <textarea name="notes" class="form-control"rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        {{--  SAVE--}}
        <div class="text-end mb-4">
            <button type="submit" class="btn btn-success px-4">Save Construction BOQ </button>
        </div>
    </form>
</div>
<script>
/* BOQ ROW COUNTER */
let rowIndex = 1;
/* ADD NEW BOQ ROW */
function addBoqRow()
{
    let html = `
        <tr>
            <td>
                <input type="text" name="items[${rowIndex}][category_name]" class="form-control">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][description]" class="form-control" required>
            </td>
            <td>
                <input type="hidden"  name="items[${rowIndex}][material_id]" class="material-id">
                <input type="text" name="items[${rowIndex}][material_name]" class="form-control">
            </td>
            <td>
                <input type="hidden" name="items[${rowIndex}][agency_id]" class="agency-id">
                <select name="items[${rowIndex}][agency_name]" class="form-select agency-select"
                        onchange="setAgencyId(this)">

                    <option value="">Select Agency</option>

                    @foreach($agencies as $agency)
                        <option value="{{ $agency->Name }}"
                                data-id="{{ $agency->ID }}">
                            {{ $agency->Name }}
                        </option>
                    @endforeach

                </select>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][unit]" class="form-control" required>
            </td>
            <td>
                <input type="number" step="0.001" min="0" name="items[${rowIndex}][quantity]" class="form-control qty" oninput="calculateRow(this)" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][material_rate]" class="form-control material-rate"
                       oninput="calculateRow(this)">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][labour_rate]" class="form-control labour-rate" oninput="calculateRow(this)">
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
    `;

    document
        .getElementById('boqRows')
        .insertAdjacentHTML(
            'beforeend',
            html
        );

    rowIndex++;
}


/* REMOVE ROW */
function removeRow(button)
{
    const rows =
        document.querySelectorAll(
            '#boqRows tr'
        );

    if(rows.length <= 1)
    {
        alert(
            'At least one BOQ item is required.'
        );

        return;
    }

    button
        .closest('tr')
        .remove();

    calculateSummary();
}


/* CALCULATE INDIVIDUAL ROW */
function calculateRow(input)
{
    const row = input.closest('tr');
    const qty = parseFloat( row.querySelector('.qty')?.value) || 0;
    const materialRate = parseFloat( row.querySelector('.material-rate')?.value ) || 0;
    const labourRate = parseFloat( row.querySelector('.labour-rate')?.value ) || 0;
    const materialAmount = qty * materialRate;
    const labourAmount = qty * labourRate;
    const total = materialAmount + labourAmount;
    row.querySelector('.row-total').value = total.toFixed(2);
    calculateSummary();
}
/*
|--------------------------------------------------------------------------
| CALCULATE COMPLETE SUMMARY
|--------------------------------------------------------------------------
*/
function calculateSummary()
{
    let materialTotal = 0;
    let labourTotal = 0;

    document
        .querySelectorAll('#boqRows tr')
        .forEach(function(row)
        {
            const qty = parseFloat( row.querySelector('.qty')?.value) || 0;
            const materialRate = parseFloat(  row.querySelector('.material-rate')?.value ) || 0;
            const labourRate =  parseFloat( row.querySelector('.labour-rate')?.value) || 0;

            materialTotal += qty * materialRate;
            labourTotal += qty * labourRate;
        });


    const subtotal = materialTotal + labourTotal;
    const overheadPercent = parseFloat( document.getElementById( 'overhead_percent' ).value ) || 0;
    const overheadAmount =  subtotal * overheadPercent / 100;
    const contingencyPercent = parseFloat( document.getElementById('contingency_percent' ).value) || 0;
    const contingencyAmount = subtotal * contingencyPercent / 100;
    const taxableAmount = subtotal + overheadAmount + contingencyAmount;
    const gstPercent = parseFloat( document.getElementById( 'gst_percent' ).value  ) || 0;
    const gstAmount = taxableAmount *gstPercent / 100;
    const grandTotal = taxableAmount + gstAmount;

    document.getElementById( 'materialTotal'  ).value = materialTotal.toFixed(2);
    document.getElementById('labourTotal').value =labourTotal.toFixed(2);
    document.getElementById( 'subtotal' ).value = subtotal.toFixed(2);
    document.getElementById( 'overhead_amount').value = overheadAmount.toFixed(2);
    document.getElementById( 'contingency_amount' ).value =contingencyAmount.toFixed(2);
    document.getElementById( 'gst_amount' ).value = gstAmount.toFixed(2);
    document.getElementById('grand_total' ).value = grandTotal.toFixed(2);
}


/* INITIAL CALCULATION */
document.addEventListener(
    'DOMContentLoaded',
    function()
    {
        calculateSummary();
    }
);

function setAgencyId(select)
{
    const row = select.closest('tr');

    const selectedOption =
        select.options[select.selectedIndex];

    const agencyId =
        selectedOption.getAttribute('data-id') || '';

    row.querySelector('.agency-id').value = agencyId;
}
</script>

@endsection