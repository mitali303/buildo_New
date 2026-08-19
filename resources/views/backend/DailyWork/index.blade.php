@extends('backend.partials.master')

@section('title')
    Daily Work List
@endsection

<style>
.nowrap {
    white-space: nowrap;
}
.workdone-column {
    white-space: normal !important;
    word-break: break-word !important;
    overflow-wrap: anywhere !important;
    min-width: 300px;
}
</style>

@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">
            <div class="mb-3">
                <h1 class="h3 d-inline align-middle">Daily Work List</h1>
                    <a href="{{ route('DailyWork.create') }}" class="btn btn-primary float-end"> Add Daily Work</a>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatables-buttons"class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Site Name</th>
                                        <th>Work Report</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                             </table>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function(){


var datatablesButtons = $("#datatables-buttons").DataTable({

responsive:false,
scrollX:true,
scrollCollapse:true,
autoWidth:false,
processing:true,
serverSide:true,

ajax:"{{ route('DailyWork') }}",



columns:[
            { data:'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false },
            { data:'date', name:'date', className:'nowrap' },
            { data:'sitename', name:'sitename' },
            { data:'workdone', name:'workdone' },
            { data: 'img', name: 'img', orderable: false, searchable: false },
            { data:'actions', name:'actions', orderable:false, searchable:false }
        ],



lengthChange:true,
buttons:[ 'copy', 'print' ],


        drawCallback:function(){
            feather.replace();
         }

});


datatablesButtons
.buttons()
.container()
.appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");

});

</script>


@endsection