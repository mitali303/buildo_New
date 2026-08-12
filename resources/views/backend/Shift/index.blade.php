@extends('backend.partials.master')
@section('title')
  Shift
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_Shift') == true)
        <a href="{{route('Shift.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Shift</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Shift Table</h1>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatables-buttons" class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Shift</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Action</th>
                                </tr>
                                 <tr>
                                    <th></th>

                                    <th><input type="text" placeholder="Search Shift" class="form-control"/></th>
                                    <th><input type="text" placeholder="Search In Time" class="form-control"/></th>
                                    <th><input type="text" placeholder="Search Out Time" class="form-control"/></th>


                                    <th></th>
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
    document.addEventListener("DOMContentLoaded", function() {
        // Datatables with Buttons
        var datatablesButtons = $("#datatables-buttons").DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('Shift') }}", // Your route
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'shift', name: 'shift' },
                { data: 'shift_intime', name: 'shift_intime' },
                { data: 'shift_outtime', name: 'shift_outtime' },
                { data: 'actions', name: 'actions' }
            ],
           dom: "<'row'<'col-md-6'l><'col-md-6 text-end'B>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",
            buttons: ['copy', 'excel', 'pdf', 'print',
                {
                    // extend: 'colvis',   // #xdc48; Column visibility button
                    text: 'Columns'
                }
            ],
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            drawCallback: function () {
                feather.replace(); // draw feather icons after each render
            }
        });
         // Apply search filters from second header row
    $('#datatables-buttons thead tr:eq(1) th').each(function (i) {
        var input = $(this).find("input, select");
        if (input.length) {
            $(input).on("keyup change clear", function () {
                if (datatablesButtons.column(i).search() !== this.value) {
                    datatablesButtons.column(i).search(this.value).draw();
                }
            });
        }
    });
    });
</script>
