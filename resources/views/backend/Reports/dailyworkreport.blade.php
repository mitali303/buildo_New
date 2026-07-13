@extends('backend.partials.master')

@section('title','Daily Work Report')

@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Daily Work Report</h1>
        </div>
           <div class="d-flex align-items-center mb-3">

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

        <div class="card">
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                
                <table id="daily-work-report" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Site Name</th>
                            <th>Work Done</th>
                            <th>User</th>
                            <th>Action</th>
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

                            <td>
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
                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>
        </div>

    </div>
</main>

@endsection

@push('scripts')

<script>
$(document).ready(function () {

    var table = $('#daily-work-report').DataTable({

        responsive: true,

        pageLength: 25,

        order: [[0, 'desc']],

        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ Entries",
            info: "Showing _START_ to _END_ of _TOTAL_ Entries",
            paginate: {
                previous: "Previous",
                next: "Next"
            }
        }

    });



    // Search Button
    $('#siteSearchBtn').click(function () {

        var site = $('#siteFilter').val();

        table
            .column(2)
            .search(site)
            .draw();

    });



    // Clear Button
    $('#clearSiteBtn').click(function () {

        $('#siteFilter').val('');

        table
            .column(2)
            .search('')
            .draw();

    });


});
</script>

@endpush