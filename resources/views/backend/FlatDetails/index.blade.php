@extends('backend.partials.master')
@section('title')
    Flat Details
@endsection

@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">
        @if (hasPermission('create_flat_detail'))
        <a href="{{route('Flat.create')}}" class="btn btn-primary float-end mt-n1"><i class="fas fa-plus"></i> New Flat Details</a>
        @endif
        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">Flat / Project Details</h1> 
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
                                    <th>Address</th>
                                    <th>Contact Person</th>
                                    <th>Saleable Area</th>
                                    <th>No. of Flats</th>
                                    <th>Sold Flats</th>
                                    <th>Average Rate</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Modal for Viewing Flat Details -->
<div class="modal fade" id="flatDetailsModal" tabindex="-1" aria-labelledby="flatDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="flatDetailsModalLabel">Flat Details - <span id="schemeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="flatDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#datatables-buttons').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('Flat') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'Name', name: 'Name' },
            { data: 'Address', name: 'Address' },
            { data: 'cperson', name: 'cperson' },
            { data: 'Area', name: 'Area' },
            { data: 'TotalFlats', name: 'TotalFlats' },
            { data: 'SoldFlats', name: 'SoldFlats' },
            { data: 'AvgRate', name: 'AvgRate' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        lengthChange: true,
        buttons: ['copy', 'print'],
        drawCallback: function() {
            // Re-initialize Feather icons after each draw
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            
            // Attach click event to view buttons
            $('.view-flat-details').off('click').on('click', function(e) {
                e.preventDefault();
                var schemeId = $(this).data('scheme-id');
                var schemeName = $(this).data('scheme-name');
                viewFlatDetails(schemeId, schemeName);
            });
            
            // Attach delete confirmation
            $('.delete-confirm').off('click').on('click', function(e) {
                e.preventDefault();
                var formId = $(this).data('id');
                if (confirm('Are you sure you want to delete this record?')) {
                    $('#' + formId).submit();
                }
            });
        }
    });
    
    // Append buttons to container
    table.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");
    
    // Function to get flat types and counts for a scheme
    function getFlatTypesAndCounts(schemeId, callback) {
        $.ajax({
            url: "{{ route('get.flat.types') }}", // You need to create this route
            type: "GET",
            data: { scheme_id: schemeId },
            success: function(response) {
                callback(response);
            },
            error: function() {
                callback(null);
            }
        });
    }
    
    // Function to view flat details
    function viewFlatDetails(schemeId, schemeName) {
        // Show modal with loading state
        $('#schemeName').text(schemeName);
        $('#flatDetailsContent').html(`
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $('#flatDetailsModal').modal('show');
        
        // First, fetch the flat types and counts for this scheme
        $.ajax({
            url: "{{ route('get.flat.types') }}",
            type: "GET",
            data: { scheme_id: schemeId },
            success: function(flatTypesData) {
                if (flatTypesData && flatTypesData.length > 0) {
                    // Build FlatsTp string in the required format (e.g., "2bhk##97@@3bhk##21@@")
                    let flatsTpString = '';
                    let totalFlatsCount = 0;
                    
                    $.each(flatTypesData, function(index, item) {
                        flatsTpString += item.Type + '##' + item.NoOfFlat + '@@';
                        totalFlatsCount += parseInt(item.NoOfFlat);
                    });
                    
                    // Now call getFlatDetails with the constructed FlatsTp string
                    $.ajax({
                        url: "{{ route('scheme.flatDetails') }}",
                        type: "POST",
                        data: {
                            FlatRow: totalFlatsCount,
                            FlatsTp: flatsTpString,
                            SchmID: schemeId,
                            Task: 'update',
                            _token: "{{ csrf_token() }}"
                        },
                        //success: function(response) {
                           // $('#flatDetailsContent').html(response);
                            
                            // Make all inputs readonly and disable buttons in view mode
                           // $('#flatDetailsContent input, #flatDetailsContent select, #flatDetailsContent textarea')
                              //  .prop('readonly', true)
                            //    .prop('disabled', true);
                            
                            // Hide all action buttons
                            // $('#flatDetailsContent button, #flatDetailsContent .btn').hide();
                            
                            // Hide the action column header and all action cells
                            // $('#flatDetailsContent th:last-child, #flatDetailsContent td:last-child').hide();
 			success: function(response) {
                            $('#flatDetailsContent').html(response);
                        
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                        },
                        error: function(xhr) {
                            $('#flatDetailsContent').html(`
                                <div class="alert alert-danger">
                                    <strong>Error!</strong> Failed to load flat details. Please try again.
                                    <br><small>${xhr.responseText || 'Unknown error'}</small>
                                </div>
                            `);
                        }
                    });
                } else {
                    $('#flatDetailsContent').html(`
                        <div class="alert alert-info">
                            <strong>No flats found!</strong> No flat details available for this scheme.
                        </div>
                    `);
                }
            },
            error: function() {
                $('#flatDetailsContent').html(`
                    <div class="alert alert-danger">
                        <strong>Error!</strong> Failed to fetch flat types. Please try again.
                    </div>
                `);
            }
        });
    }
});
</script>
<script>
function updateHiddenValues(rid){
    var did = rid.split("_");
    var id = did[1];
    var val = $("#FlatType"+id).val()+"##"+$("#Wing"+id).val()+"##"+$("#Floor"+id).val()+"##"+$("#FlatNo"+id).val()+"##"+$("#Area"+id).val()+"##"+$("#Other1"+id).val()+"##"+$("#Other2"+id).val()+"##"+$("#Terrace"+id).val()+"##"+$("#Attribute"+id).val()+"##"+$("#TotalSqFt"+id).val()+"##"+$("#TotalSqMtr"+id).val();
    $("#flatdet_"+id).val(val);
}

function calTotalsq(i) {
    var totalSqft=0;
    var Area=$("#Area"+i).val();
    var Terrace=$("#Terrace"+i).val();
    var Other1=$("#Other1"+i).val();
    var Other2=$("#Other2"+i).val();
    if(Area=='') Area=0;
    if(Terrace=='') Terrace=0;
    if(Other1=='') Other1=0;
    if(Other2=='') Other2=0;
    totalSqft=parseFloat(Area)+parseFloat(Terrace)+parseFloat(Other1)+parseFloat(Other2);
    $("#TotalSqFt"+i).val(totalSqft);
}

function saveFlatRow(rowId) {
    const data = {
        FlatType: $("#FlatType" + rowId).val(),
        Wing: $("#Wing" + rowId).val(),
        Floor: $("#Floor" + rowId).val(),
        FlatNo: $("#FlatNo" + rowId).val(),
        Area: $("#Area" + rowId).val(),
        Other1: $("#Other1" + rowId).val(),
        Other2: $("#Other2" + rowId).val(),
        Terrace: $("#Terrace" + rowId).val(),
        FlatAttribute: $("#Attribute" + rowId).val(),
        TotalSqFt: $("#TotalSqFt" + rowId).val(),
        TotalSqMtr: $("#TotalSqMtr" + rowId).val(),
        _token: "{{ csrf_token() }}"
    };

    $.ajax({
        url: "{{ route('flatDetails.saveRow') }}",
        method: 'POST',
        data: data,
        success: function(response) {
            alert("Saved successfully");
            const btn = $("#saveBtn" + rowId);
            btn.removeClass("btn-primary").addClass("btn-success").text("Saved");
        },
        error: function(xhr) {
            alert("Error: " + xhr.responseText);
        }
    });
}

function updateFlatRow(flatId, rowId) {
    const data = {
        ID: flatId,
        FlatType: $("#FlatType" + rowId).val(),
        Wing: $("#Wing" + rowId).val(),
        Floor: $("#Floor" + rowId).val(),
        FlatNo: $("#FlatNo" + rowId).val(),
        Area: $("#Area" + rowId).val(),
        Other1: $("#Other1" + rowId).val(),
        Other2: $("#Other2" + rowId).val(),
        Terrace: $("#Terrace" + rowId).val(),
        FlatAttribute: $("#Attribute" + rowId).val(),
        TotalSqFt: $("#TotalSqFt" + rowId).val(),
        TotalSqMtr: $("#TotalSqMtr" + rowId).val(),
        _token: "{{ csrf_token() }}"
    };

    $.ajax({
        url: "{{ route('flatDetails.updateRow') }}",
        type: 'POST',
        data: data,
        success: function(res) {
            alert("Updated successfully");
            const btn = $("#saveBtn" + rowId);
            btn.removeClass('btn-primary').addClass('btn-success').text('Updated');
        },
        error: function(err) {
            alert("Error updating flat row: " + err.responseText);
        }
    });
}
</script>

<style>
    .view-flat-details {
        cursor: pointer;
        transition: opacity 0.2s;
    }
    
    .view-flat-details:hover {
        opacity: 0.7;
    }
    
    #flatDetailsModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    #flatDetailsModal input,
    #flatDetailsModal select,
    #flatDetailsModal textarea {
        background-color: #f8f9fa;
    }
    
    /* Style for readonly mode */
    #flatDetailsModal input:read-only,
    #flatDetailsModal select:disabled,
    #flatDetailsModal textarea:read-only {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
</style>
@endpush