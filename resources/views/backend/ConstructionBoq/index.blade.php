@extends('backend.partials.master')
@section('title')
    Construction BOQ
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-2 mb-xl-3">
            <div class="col-auto">
                <h3>Construction BOQ</h3>
            </div>
            <div class="col-auto ms-auto text-end mt-n1">
                <a href="{{ route('construction-boq.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New BOQ
                </a>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatables-buttons" class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Sr No.</th>
                                        <th>BOQ No</th>
                                        <th>Project / Site</th>
                                        <th>Date</th>
                                        <th>Material</th>
                                        <th>Labour</th>
                                        <th>Grand Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($boqs as $boq)
                                        <tr>
                                            <td> {{ $loop->iteration }}</td>
                                            <td> <strong>{{ $boq->boq_no }}</strong></td>
                                            <td> {{ $boq->scheme_name }} </td>
                                            <td>{{ optional($boq->estimate_date)->format('d-m-Y') }}</td>
                                            <td> ₹ {{ number_format($boq->material_total, 2) }}</td>
                                            <td> ₹ {{ number_format($boq->labour_total, 2) }} </td>
                                            <td><strong> ₹ {{ number_format($boq->grand_total, 2) }} </strong> </td>
                                            <td>
                                                @if($boq->status == 1)
                                                    <span class="badge bg-success"> Active</span>
                                                @else
                                                    <span class="badge bg-secondary"> Inactive </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('construction-boq.show', $boq->id) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('construction-boq.edit', $boq->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('construction-boq.destroy', $boq->id) }}"  method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete this BOQ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                No Construction BOQ found.
                                            </td>
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
<script>

document.addEventListener("DOMContentLoaded", function () {

    let table = $('#datatables-buttons').DataTable({
        responsive: true,
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],

        columnDefs: [
            {
                orderable: false,
                searchable: false,
                targets: [0, 8]
            }
        ],

        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ BOQs",
            infoEmpty: "Showing 0 to 0 of 0 BOQs",
            zeroRecords: "No matching BOQ found",
            emptyTable: "No Construction BOQ found"
        },

        drawCallback: function () {

            if (typeof feather !== 'undefined') {
                feather.replace();
            }

        }

    });


    // Column Search

    $('#datatables-buttons thead tr:eq(1) th').each(function (i) {

        $('input', this).on('keyup change', function () {

            if (table.column(i).search() !== this.value) {

                table
                    .column(i)
                    .search(this.value)
                    .draw();

            }

        });

    });

});

</script>

