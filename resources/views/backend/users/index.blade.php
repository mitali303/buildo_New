@extends('backend.partials.master')

@section('title', 'Users')

@section('maincontent')
<main class="content">
  <div class="container-fluid p-0">
    @if (hasPermission('create_user'))
      <a href="{{ route('users.create') }}" class="btn btn-primary float-end mt-n1">
          <i class="fas fa-plus"></i> New User
      </a>
    @endif
      <div class="mb-3">
          <h1 class="h3 d-inline align-middle">Users</h1>
      </div>

      <div class="row">
          <div class="col-12">
              <div class="card">
                  <div class="card-body">
                      <table id="datatables-buttons" class="table table-striped w-100">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Name</th>
                                  <th>UserID</th>
                                  <th>Role</th>
                                  <th>Scheme</th>
                                  <th>Access Type</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($users as $index => $user)
                                  <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $user->Name }}</td>
                                            <td>{{ $user->UserID }}</td>
                                            <td>{{ $user->role_name }}</td>  
                                            <td>{{ $user->scheme_name }}</td> 
                                            <td>{{ $user->access_type_name }}</td> <!-- show access_type -->
                                      <td>
                                            @if (hasPermission('edit_user'))
                                              <a href="{{ route('users.edit', $user->ID) }}"
                                                 class="me-2 text-primary">
                                                  <i data-feather="edit-2"></i>
                                              </a>
                                              @endif
                                            @if (hasPermission('delete_user'))
                                              <a href="{{ route('users.delete', $user->ID) }}"
                                                 class="text-danger"
                                                 onclick="event.preventDefault();
                                                    document.getElementById('delete-{{ $user->ID }}').submit();">
                                                  <i data-feather="trash"></i>
                                              </a>
                                            @endif
                                              <form id="delete-{{ $user->ID }}"
                                                    action="{{ route('users.delete', $user->ID) }}"
                                                    method="POST" class="d-none">
                                                  @csrf
                                                  @method('DELETE')
                                              </form>
                                      </td>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    $('#datatables-buttons').DataTable({
        // pure client‑side: NO ajax, NO serverSide
        responsive: true,
        lengthChange: true,
        buttons: ['copy', 'print'],
        drawCallback: () => feather.replace()
    }).buttons()
      .container()
      .appendTo('#datatables-buttons_wrapper .col-md-6:eq(0)');
});
</script>
@endpush
