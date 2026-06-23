<table class="table table-striped table-hover">
<thead class="table-primary">
<tr>
    <th>Date</th>
    <th>Particulars</th>
    <th>Transaction</th>
    <th>Type</th>
    <th>Credit</th>
    <th>Debit</th>
</tr>
</thead>
<tbody>

<tr>
    <td></td>
    <td><strong>Opening Balance</strong></td>
    <td></td><td></td>
    <td id="OpeningBlance"></td>
    <td></td>
</tr>

@php
$total_credit = 0;
$total_debit = 0;
@endphp

@foreach($credit as $row)
@php $total_credit += $row->Amt; @endphp
<tr>
    <td>{{ date('d-m-Y', strtotime($row->Date)) }}</td>
    <td>Credit Entry</td>
    <td>{{ $row->Type }} {{ $row->cheque_no ? '(' . $row->cheque_no . ')' : '' }}</td>
    <td>Credit</td>
    <td style="color:green">{{ $row->Amt }}</td>
    <td></td>
</tr>
@endforeach

@foreach($debit as $row)
@php $total_debit += $row->Amt; @endphp
<tr>
    <td>{{ date('d-m-Y', strtotime($row->Date)) }}</td>
    <td>Debit Entry</td>
    <td>{{ $row->Type }} {{ $row->cheque_no ? '(' . $row->cheque_no . ')' : '' }}</td>
    <td>Debit</td>
    <td></td>
    <td style="color:red">{{ $row->Amt }}</td>
</tr>
@endforeach

<tr class="fw-bold">
    <td colspan="4">Total</td>
    <td>{{ $total_credit }}</td>
    <td>{{ $total_debit }}</td>
</tr>

</tbody>
</table>

<table class="table mt-3" style="background:pink">
<tr>
    <td><strong>Closing Balance</strong></td>
    <td>{{ $total_credit - $total_debit }}</td>
</tr>
</table>
