@extends('backend.partials.master')

@section('title')
Labour Work Details
@endsection

@section('maincontent')
<main class="content">
<div class="container-fluid p-0">

    <div class="row mb-3">
        <div class="col-6">
            <h1 class="h3 mb-0">Labour Work Details</h1>
        </div>

        <div class="col-6 text-end">
            <a href="{{ route('Labour_work_pay') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            
            <table id="datatables-buttons" class="table table-striped w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Work Date</th>
                        <th>Grand Total</th>
                        <th>Paid Amount</th>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th id="total-paid"></th>
                    </tr>
                </tfoot>
            </table>

            

        </div>
    </div>

</div>
</main>
@endsection


@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    var table = $("#datatables-buttons").DataTable({

        responsive   : true,
        processing   : true,
        serverSide   : true,

        ajax: {
            url: "{{ route('Labour_work_pay.details', $ids) }}",
            type: "GET"
        },

        columns : [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { 
                data: 'work_date',
                render: function(data){
                    return data ? data : '-';
                }
            },
            { 
                data: 'total_gtotal',
                render: function(data){
                    return '₹ ' + parseFloat(data || 0).toFixed(2);
                }
            },
            { 
                data: 'paid_amt',
                render: function(data){
                    return '₹ ' + parseFloat(data || 0).toFixed(2);
                }
            }
        ],

        footerCallback: function (row, data) {

            let totalPaid = 0;

            data.forEach(function(row){
                totalPaid += parseFloat(row.paid_amt) || 0;
            });

            $('#total-paid').html(
                '<strong>Total: ₹ ' + totalPaid.toFixed(2) + '</strong>'
            );
        },

        drawCallback : function () {
            feather.replace();
        }

    });

});
</script>
@endsection