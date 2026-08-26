@extends('backend.partials.master')

@section('title', 'Available Flats Report')
<style>
    @media (max-width: 767px) {
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #myTable {
        min-width: 800px;
    }
}
</style>
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="row mb-2 mb-xl-3">
            <div class="col-auto">
                <h3><strong>Available Flat Report</strong></h3>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @if(session('msg'))
                        <div class="alert alert-success" id="success-alert">
                            <button type="button" class="close" data-dismiss="alert">x</button>
                            {{ session('msg') }}
                        </div>
                        @endif

                        <div class="row mb-2">
                            <div class="col-auto">
                              
                            </div>
                        </div>
                    <div class="table-responsive">
                        <table id="myTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Flat Type</th>
                                    <th>Flat No</th>
                                    <th>Area</th>
                                    <th>Wing</th>
                                    <th>Total Sq Ft</th>
                                    <th>Available/Sold Flat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($db_record as $i => $flat)
                                    @php
                                        $booked = isset($flat->booking_customer) ? 'Sold' : 'Available';
                                        $color = $booked == 'Sold' ? '#CC0000' : '#10A829';
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $flat->FlatType }}</td>
                                        <td>{{ $flat->FlatNo }}</td>
                                        <td>{{ $flat->Area }}</td>
                                        <td>{{ $flat->Wing }}</td>
                                        <td>{{ $flat->TotalSqFt }}</td>
                                        <td style="color: {{ $color }}">{{ $booked }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">Record Not Found.....</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection


