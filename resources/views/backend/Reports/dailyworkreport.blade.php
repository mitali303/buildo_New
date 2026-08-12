@extends('backend.partials.master')

@section('title','Daily Work Report')

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
            <h1 class="h3 d-inline align-middle">Daily Work Report</h1>
        </div>
           <div class="d-flex align-items-center mb-3">
            <input type="date" id="fromDate" class="form-control form-control-sm me-2" style="width:170px;">

<input type="date" id="toDate" class="form-control form-control-sm me-2" style="width:170px;">

    <select id="siteFilter" class="form-select form-select-sm" style="width:200px;">
        <option value="">Select Site</option>

        @foreach($reports->pluck('sitename')->unique() as $site)
            <option value="{{ $site }}">
                {{ $site }}
            </option>
        @endforeach

    </select>

    <button type="button" id="siteSearchBtn" class="btn btn-primary btn-sm ms-2">
        Search
    </button>

    <button type="button" id="clearSiteBtn" class="btn btn-secondary btn-sm ms-2">
        Clear
    </button>

</div>


        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif


                        <table id="daily-work-report"
                               class="table table-striped"
                               style="width:100%">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Site Name</th>
                                    <th>Work Done</th>
                                    <th>User</th>
                                </tr>
                            </thead>

                    <tbody>

                        @foreach($reports as $report)

                        <tr>
                            <td>{{ $report->ID }}</td>

                            <td>{{ date('d-m-Y', strtotime($report->date)) }}</td>

                            <td>{{ $report->sitename }}</td>

                            <td>{{ $report->workdone }}</td>

                            <td>{{ $report->username }}</td>

                            <!-- <td>
                                <form action="{{ route('dailyworkreport.destroy',$report->ID) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this report?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-danger btn-sm">
                                        Delete
                                    </button>

                                </form>
                            </td> -->

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


@section('scripts')

<script>
$(document).ready(function () {

    var table = $('#daily-work-report').DataTable({

        responsive: false,

        scrollX: true,

        scrollCollapse: true,

        autoWidth: false,

        pageLength: 25,
        order: [[0, 'desc']],

        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ Entries",
            info: "Showing _START_ to _END_ of _TOTAL_ Entries"
        }

    });


    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {

        var from = $('#fromDate').val();
        var to = $('#toDate').val();
        var site = $('#siteFilter').val();


        // Table मधील Date (dd-mm-yyyy)
        var rowDateText = data[1];

        if(!rowDateText){
            return false;
        }


        var parts = rowDateText.split('-');

        var rowDate = new Date(
            parts[2],
            parts[1] - 1,
            parts[0]
        );


        var dateMatch = true;


        // From Date
        if(from){

            var fromDate = new Date(from);

            if(rowDate < fromDate){
                dateMatch = false;
            }

        }


        // To Date
        if(to){

            var toDate = new Date(to);

            if(rowDate > toDate){
                dateMatch = false;
            }

        }



        // Site Match
        var siteMatch = true;

        if(site){

            if(data[2].trim() != site.trim()){
                siteMatch = false;
            }

        }


        return dateMatch && siteMatch;

    });



    $('#siteSearchBtn').click(function(){

        table.draw();

    });



    $('#clearSiteBtn').click(function(){

        $('#fromDate').val('');
        $('#toDate').val('');
        $('#siteFilter').val('');

        table.draw();

    });


});
</script>

@endsection