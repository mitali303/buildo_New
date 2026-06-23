@extends('backend.partials.master')
@section('title')
    Role Create
@endsection
@section('maincontent')
<main class="content">
    <div class="container-fluid p-0">

        <div class="mb-3">
            <h1 class="h3 d-inline align-middle">{{ !empty($role) ? 'Update' : 'Create' }} Role</h1>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="@if(!empty($role)){{route('roles.update')}}@else{{route('roles.store')}}@endif" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!empty($role))
                             @method('PUT')
                                <input type="hidden" name="id" id="id" value=" {{$role->id}}"/>
                            @endif
                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputEmail4">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" value="@if(!empty($role)){{ $role->name }}@endif" name="name" id="name" placeholder="Name">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="inputState">Status</label>
                                    <select id="inputState" name="status" class="form-control">
                                        <option @if(!empty($role)) {{ $role->status == '1' ? 'selected' : '' }} @endif value="1">Active</option>
                                        <option @if(!empty($role)) {{ $role->status == '0' ? 'selected' : '' }} @endif value="0">Inactive</option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-check form-check-inline">
                                    <input class="form-check-input" id="check-all-global" type="checkbox" />
                                    <span class="form-check-label fw-bold">Admin All Permissions</span>
                                </label>
                            </div>
                            <div class="mb-3 col-md-12">
                            <table class="table table-striped">
									<thead>
										<tr>
											<th>Modules</th>
											<th style="width:90%">Permissions</th>
										</tr>
									</thead>
									<tbody>
                                    @foreach ($permissions as $permission)
                                        <tr>
                                            <td>{{ $permission->attribute }}</td>
                                            <td>
                                                @if (!empty($permission->keywords))
                                                <div class="keyword-group">
                                                    @foreach ($permission->keywords as $key => $keyword)
                                                        <label class="form-check form-check-inline">
                                                            <input @if(!empty($rolePermissions) && in_array($keyword, $rolePermissions)) checked @endif class="form-check-input permission-checkbox" type="checkbox" value="{{ $keyword }}" name="permissions[]" />
                                                            <span class="form-check-label">{{ ucfirst($key) }}</span>
                                                        </label>
                                                    @endforeach
                                                    <!-- Check All within this row only -->
                                                    <label class="form-check form-check-inline">
                                                        <input class="form-check-input check-all" type="checkbox" />
                                                        <span class="form-check-label">Check all</span>
                                                    </label>
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
									</tbody>
								</table>
                            </div>

                            <button type="submit" class="btn btn-primary">{{ !empty($role) ? 'Update' : 'Create' }}</button>
                            <a href="{{ route('roles') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>


        </div>

    </div>
</main>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const globalCheckAll = document.getElementById("check-all-global");
        const allPermissionCheckboxes = document.querySelectorAll(".permission-checkbox");

        // Global Check All toggle
        globalCheckAll.addEventListener("change", function () {
            const checked = this.checked;
            allPermissionCheckboxes.forEach(cb => cb.checked = checked);
            document.querySelectorAll(".check-all").forEach(rowCheckAll => {
                rowCheckAll.checked = checked;
            });
        });

        // Row-level "Check All" toggle
        document.querySelectorAll(".check-all").forEach(function (rowCheckAll) {
            rowCheckAll.addEventListener("change", function () {
                const container = this.closest(".keyword-group");
                const checkboxes = container.querySelectorAll(".permission-checkbox");
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateGlobalCheckAll();
            });
        });

        // Sync logic between row checkboxes and global checkbox
        document.querySelectorAll(".keyword-group").forEach(function (group) {
            const rowCheckAll = group.querySelector(".check-all");
            const checkboxes = group.querySelectorAll(".permission-checkbox");

            checkboxes.forEach(cb => {
                cb.addEventListener("change", function () {
                    const allChecked = Array.from(checkboxes).every(box => box.checked);
                    rowCheckAll.checked = allChecked;
                    updateGlobalCheckAll();
                });
            });

            // ✅ On load: Check if row "Check All" should be checked
            const allChecked = Array.from(checkboxes).every(box => box.checked);
            rowCheckAll.checked = allChecked;
        });

        // ✅ On load: Check if global "Admin All Permissions" should be checked
        updateGlobalCheckAll();

        function updateGlobalCheckAll() {
            const allChecked = Array.from(allPermissionCheckboxes).every(cb => cb.checked);
            globalCheckAll.checked = allChecked;
        }
    });
</script>


