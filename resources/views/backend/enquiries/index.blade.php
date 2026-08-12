@extends('backend.partials.master')

@section('title')
    Enquiry List
@endsection

<style>
.nowrap {
    white-space: nowrap;
}
</style>

@section('maincontent')

<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Enquiry List</h1>
       
        
            <a href="{{ route('enquiries.create') }}"
            class="btn btn-primary float-end">
            Add Enquiry
        </a>
    </div>

        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        <table id="datatables-buttons" class="table table-striped nowrap" style="width:100%">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Customer Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Query</th>
                                    <th>Status</th>
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

document.addEventListener("DOMContentLoaded", function() {

    var datatablesButtons = $("#datatables-buttons").DataTable({

        responsive: false,
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,

        processing: true,
        serverSide: true,

        ajax: "{{ route('enquiries.index') }}",
        columns: [

    { 
        data: 'DT_RowIndex', 
        name: 'DT_RowIndex', 
        orderable: false, 
        searchable: false 
    },

    { 
        data: 'created_at', 
        name: 'created_at', 
        className: 'nowrap' 
    },

    { 
        data: 'lead_name', 
        name: 'lead_name' 
    },

    { 
        data: 'mobile_1', 
        name: 'mobile_1' 
    },

    { 
        data: 'email_1', 
        name: 'email_1' 
    },

    { 
        data: 'address', 
        name: 'address'
    },

    { 
        data: 'description', 
        name: 'description' 
    },

    { 
        data: 'status', 
        name: 'status'
    },

    { 
        data: 'action', 
        name: 'action', 
        orderable: false, 
        searchable: false 
    }

],

        lengthChange: true,

        buttons: [
            'copy',
            'print'
        ],

        drawCallback: function () {
            feather.replace();
        }

    });

    datatablesButtons
        .buttons()
        .container()
        .appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");

});

</script>
<script>

$(document).on('click', '.deleteBtn', function () {

    if(confirm('Delete this record ?'))
    {
        let id = $(this).data('id');

        $.ajax({

            url: "{{ route('enquiries.destroy', ':id') }}"
                    .replace(':id', id),

            type: 'DELETE',

            data: {
                _token: '{{ csrf_token() }}'
            },

            success: function(response)
            {
                $('#datatables-buttons')
                    .DataTable()
                    .ajax
                    .reload();

                toastr.success('Record Deleted Successfully');
            },

            error: function(xhr)
            {
                console.log(xhr.responseText);
                toastr.error('Delete Failed');
            }

        });
    }

});

</script>

@endsection