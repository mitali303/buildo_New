@extends('backend.partials.master')
@section('title')
    Scheme Details
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        <a href="{{route('Scheme.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Scheme Details</a>
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Scheme Completion</h1> 
        </div>

        <div class="row">
            <div class="col-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Contact No.</th>
                                    <th>Contact Person</th>
                                    <th>Completed</th>
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
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const datatablesButtons = $("#datatables-buttons").DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('SchemeComplete') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'Name', name: 'Name' },
                { data: 'Location', name: 'Location' },
                { data: 'cnumber', name: 'cnumber' },
                { data: 'contactperson', name: 'contactperson' },
                { data: 'completFalg', name: 'completFalg', orderable: false, searchable: false }
            ],
            lengthChange: true,
            buttons: ['copy', 'print'],
            drawCallback: function () {
                feather.replace();

                $('#datatables-buttons')
                    .off('change', '.flag-toggle')
                    .on('change', '.flag-toggle', function () {
                        const $box = $(this);
                        const id = $box.data('id');
                        const flag = $box.is(':checked') ? 1 : 0;

                        $.post("{{ route('SchemeComplete.toggle') }}", {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            flag: flag
                        }).fail(() => {
                            toastr.error('Could not update status');
                            $box.prop('checked', !flag);
                        });
                    });
            }
        });

        datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    });
</script>


