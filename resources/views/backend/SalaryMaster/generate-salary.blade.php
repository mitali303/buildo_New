@extends('backend.partials.master')

@section('title')
Generate Salary
@endsection

@section('maincontent')

<main class="content">

<div class="container-fluid p-0">

<div class="card">

    <div class="card-header">

        <h4>Generate Salary</h4>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-4">

                <label>Month</label>

                <select id="month" class="form-control">

                    <option value="">Select Month</option>

                    <option value="January">January</option>
                    <option value="February">February</option>
                    <option value="March">March</option>
                    <option value="April">April</option>
                    <option value="May">May</option>
                    <option value="June">June</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                    <option value="September">September</option>
                    <option value="October">October</option>
                    <option value="November">November</option>
                    <option value="December">December</option>

                </select>

            </div>

            <div class="col-md-4">

                <label>Year</label>

                <select id="year" class="form-control">

                    @for($y=date('Y')+1;$y>=2020;$y--)

                        <option value="{{$y}}">
                            {{$y}}
                        </option>

                    @endfor

                </select>

            </div>

            <div class="col-md-4">

                <br>

                <button
                    type="button"
                    id="generateSalary"
                    class="btn btn-success">

                    Generate Salary

                </button>

            </div>

        </div>

    </div>

</div>

<div class="card mt-3">
    <div class="card-header">
        <h4>Generated Salary Months</h4>
    </div>

        <div class="card-body">

            <table class="table table-bordered" id="generatedMonthTable">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Total Employees</th>
                        <th>Total Salary</th>
                        <th>Generated On</th>
                        <th width="250">Action</th>
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

$(document).ready(function () {

    // Current Month Auto Select
    let currentMonth = new Date().toLocaleString('en-US', {
        month: 'long'
    });

    $('#month').val(currentMonth);

    loadSalarySummary();
    loadSalaryTable();
    loadGeneratedMonthTable();
});


function loadGeneratedMonthTable()
{
    $('#generatedMonthTable').DataTable({

        destroy:true,
        processing:true,
        serverSide:true,

        ajax:
        "{{ route('salary.generated.month.list') }}",

        columns:[

            {
                data:'DT_RowIndex',
                searchable:false,
                orderable:false
            },

            {
                data:'month'
            },

            {
                data:'year'
            },

            {
                data:'total_employee'
            },

            {
                data:'total_salary'
            },

            {
                data:'generated_on'
            },

            {
                data:'action',
                searchable:false,
                orderable:false
            }
        ]
    });
}

$(document).on('click','.viewSalary',function(){

    let month = $(this).data('month');
    let year  = $(this).data('year');

    window.location.href =
        "{{ route('salary.report') }}" +
        "?month=" + month +
        "&year=" + year;
});


/* =====================================================
    SALARY SUMMARY
===================================================== */

function loadSalarySummary()
{
    let month = $('#month').val();
    let year  = $('#year').val();

    if(month == '')
    {
        return;
    }

    $.ajax({

        url:"{{ route('salary.summary') }}",

        type:"POST",

        data:{
            _token:"{{ csrf_token() }}",
            month:month,
            year:year
        },

        success:function(res){

            $('#total_emp').html(res.total_employees);
            $('#generated_emp').html(res.generated);
            $('#pending_emp').html(res.pending);
        }
    });
}

/* =====================================================
    SALARY TABLE
===================================================== */

function loadSalaryTable()
{
    $('#salaryTable').DataTable({

        destroy:true,

        processing:true,

        serverSide:true,

        pageLength:25,

        ajax:{
            url:"{{ route('SalaryReport.data') }}",

            data:function(d){

                d.month = $('#month').val();
                d.year  = $('#year').val();
            }
        },

        columns:[

            {
                data:'DT_RowIndex',
                name:'DT_RowIndex',
                searchable:false,
                orderable:false
            },

            {
                data:'salary_no',
                name:'salary_no'
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
                data:'net_salary',
                name:'net_salary'
            },

            {
                data:'date',
                name:'date'
            }
        ]
    });
}

/* =====================================================
    MONTH YEAR CHANGE
===================================================== */

$('#month,#year').change(function(){

    loadSalarySummary();
    loadSalaryTable();

});

/* =====================================================
    GENERATE SALARY
===================================================== */

$('#generateSalary').click(function(){

    let month = $('#month').val();
    let year  = $('#year').val();

    if(month == '')
    {
        alert('Please Select Month');
        return;
    }

    $.ajax({

        url:"{{ route('salary.generate') }}",

        type:"POST",

        data:{

            _token:"{{ csrf_token() }}",

            month:month,
            year:year
        },

        beforeSend:function(){

            $('#generateSalary')
                .prop('disabled',true)
                .html('Generating...');
        },

        success:function(res){

            alert(res.message);

            loadSalarySummary();

            if($.fn.DataTable.isDataTable('#generatedMonthTable'))
            {
                $('#generatedMonthTable')
                    .DataTable()
                    .ajax
                    .reload(null,false);
            }

            if($.fn.DataTable.isDataTable('#salaryTable'))
            {
                $('#salaryTable')
                    .DataTable()
                    .ajax
                    .reload(null,false);
            }
        },

        complete:function(){

            $('#generateSalary')
                .prop('disabled',false)
                .html('Generate Salary');
        }
    });
});

/* =====================================================
    DELETE SALARY
===================================================== */

$(document).on('click','.deleteSalaryMonth',function(){

    let month = $(this).data('month');
    let year  = $(this).data('year');

    if(!confirm(
        'Are you sure you want to delete salary for '
        + month + ' ' + year + ' ?'
    )){
        return;
    }

    $.ajax({

        url:"{{ route('salary.delete.month') }}",

        type:"POST",

        data:{
            _token:"{{ csrf_token() }}",
            month:month,
            year:year
        },

        success:function(res){

            alert(res.message);

            loadSalarySummary();

            if($.fn.DataTable.isDataTable('#salaryTable')){
                $('#salaryTable').DataTable().ajax.reload(null,false);
            }

            if($.fn.DataTable.isDataTable('#generatedMonthTable')){
                $('#generatedMonthTable').DataTable().ajax.reload(null,false);
            }

        },

        error:function(){

            alert('Something went wrong');
        }
    });

});

/* =====================================================
    DELETE & REGENERATE
===================================================== */

$(document).on('click','.regenerateSalaryMonth',function(){

    let month = $(this).data('month');
    let year  = $(this).data('year');

    if(!confirm(
        'Delete existing salary and regenerate again ?'
    )){
        return;
    }

    $.ajax({

        url:"{{ route('salary.delete.month') }}",

        type:"POST",

        data:{
            _token:"{{ csrf_token() }}",
            month:month,
            year:year
        },

        success:function(){

            $.ajax({

                url:"{{ route('salary.generate') }}",

                type:"POST",

                data:{
                    _token:"{{ csrf_token() }}",
                    month:month,
                    year:year
                },

                success:function(res){

                    alert(res.message);

                    loadGeneratedMonthTable();
                    loadSalarySummary();
                    loadSalaryTable();
                },

                error:function(){

                    alert('Salary regeneration failed');
                }
            });
        }
    });

});

</script>

@endpush
