@extends('backend.partials.master')

@section('title')
Salary Report
@endsection

@section('maincontent')

<main class="content">

<div class="container-fluid p-0">

<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">

    <h4 class="mb-0">Salary Report</h4>

    <a
        href="{{ url()->previous() }}"
        class="btn btn-secondary">

        <i class="fas fa-arrow-left"></i>
        Back

    </a>

</div>

<div class="card-body">

<div class="row mb-3">

<div class="col-md-3">

<select id="month" class="form-control">

<option value="">All Month</option>

@foreach([
'January','February','March','April',
'May','June','July','August',
'September','October','November','December'
] as $month)

<option
    value="{{ $month }}"
    {{ request('month') == $month ? 'selected' : '' }}>
    {{ $month }}
</option>

@endforeach

</select>

</div>

<div class="col-md-3">

<select id="year" class="form-control">

<option value="">All Year</option>

@for($y=date('Y');$y>=2020;$y--)

<option
    value="{{ $y }}"
    {{ request('year') == $y ? 'selected' : '' }}>
    {{ $y }}
</option>

@endfor

</select>

</div>

<div class="col-md-2">

<button class="btn btn-primary"
id="searchBtn">

Search

</button>

</div>

</div>

<table
id="salaryReportTable"
class="table table-bordered">

<thead>

<tr>

<th>#</th>
<th>Employee</th>
<th>Month</th>
<th>Gross</th>
<th>Advance</th>
<th>Net Salary</th>

</tr>

</thead>

</table>

</div>

</div>

</div>

</main>

@endsection

@push('scripts')

<script>

function loadTable(){

$('#salaryReportTable').DataTable({

destroy:true,

processing:true,

serverSide:true,

ajax:{
url:"{{ route('SalaryReport.data') }}",
data:function(d){

d.month=$("#month").val();
d.year=$("#year").val();

}
},

columns:[

{
data:'DT_RowIndex',
name:'DT_RowIndex',
orderable:false,
searchable:false
},

{
data:'employee_name',
name:'employee_name'
},

{
data:'salary_month',
name:'salary_month'
},

{
data:'gross',
name:'gross'
},

{
data:'advance_emi',
name:'advance_emi'
},

{
data:'net_salary',
name:'net_salary'
}

]

});

}

loadTable();

$('#searchBtn').click(function(){

loadTable();

});

</script>

@endpush