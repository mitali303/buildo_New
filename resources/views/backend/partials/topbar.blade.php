<div class="main">
	<nav class="navbar navbar-expand navbar-light navbar-bg">
		<a class="sidebar-toggle js-sidebar-toggle">
			<i class="hamburger align-self-center"></i>
		</a>

		<!-- <form class="d-none d-sm-inline-block">
			<div class="input-group input-group-navbar">
				<input type="text" class="form-control" placeholder="Search…" aria-label="Search">
				<button class="btn" type="button">
					<i class="align-middle" data-feather="search"></i>
				</button>
			</div>
		</form> -->


		<div class="navbar-collapse collapse">
			<ul class="navbar-nav navbar-align d-flex align-items-center">
				<li class="nav-item dropdown">
					<!-- <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
						<div class="position-relative">
							<i class="align-middle" data-feather="bell"></i>
							<span class="indicator">4</span>
						</div>
					</a> -->

					<li class="nav-item">
						<button type="button" style="border-radius: 5px;"
								class="btn btn-sm filter-btn me-2"
								id="openSchemeModalBtn" style="background-color: #233346; color: #ffffff; border: none;">
							Select Scheme
						</button>
					</li>

					<div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
						<div class="dropdown-menu-header">
							4 New Notifications
						</div>
						<div class="list-group">
							<a href="#" class="list-group-item">
								<div class="row g-0 align-items-center">
									<div class="col-2">
										<i class="text-danger" data-feather="alert-circle"></i>
									</div>
									<div class="col-10">
										<div class="text-dark">Update completed</div>
										<div class="text-muted small mt-1">Restart server 12 to complete the update.</div>
										<div class="text-muted small mt-1">30m ago</div>
									</div>
								</div>
							</a>
							<a href="#" class="list-group-item">
								<div class="row g-0 align-items-center">
									<div class="col-2">
										<i class="text-warning" data-feather="bell"></i>
									</div>
									<div class="col-10">
										<div class="text-dark">Lorem ipsum</div>
										<div class="text-muted small mt-1">Aliquam ex eros, imperdiet vulputate hendrerit et.</div>
										<div class="text-muted small mt-1">2h ago</div>
									</div>
								</div>
							</a>
							<a href="#" class="list-group-item">
								<div class="row g-0 align-items-center">
									<div class="col-2">
										<i class="text-primary" data-feather="home"></i>
									</div>
									<div class="col-10">
										<div class="text-dark">Login from 192.186.1.8</div>
										<div class="text-muted small mt-1">5h ago</div>
									</div>
								</div>
							</a>
							<a href="#" class="list-group-item">
								<div class="row g-0 align-items-center">
									<div class="col-2">
										<i class="text-success" data-feather="user-plus"></i>
									</div>
									<div class="col-10">
										<div class="text-dark">New connection</div>
										<div class="text-muted small mt-1">Christina accepted your request.</div>
										<div class="text-muted small mt-1">14h ago</div>
									</div>
								</div>
							</a>
						</div>
						<div class="dropdown-menu-footer">
							<a href="#" class="text-muted">Show all notifications</a>
						</div>
					</div>
				</li>
				
				
				<li class="nav-item">
					<a class="nav-icon js-fullscreen d-none d-lg-block" href="#">
						<div class="position-relative">
							<i class="align-middle" data-feather="maximize"></i>
						</div>
					</a>
				</li>
				<li class="nav-item dropdown">
					@php
						$schemeId = session('selected_scheme_id');
						$schemeName = \DB::table('scheme_step1')->where('ID', $schemeId)->value('Name');
					@endphp

					<a class="nav-icon pe-md-0 dropdown-toggle fw-semibold text-dark d-flex align-items-center gap-2"
						href="#"
						data-bs-toggle="dropdown"
						style="text-decoration:none;">
							<!-- <i class="fa fa-building"></i> -->
							{{ $schemeName ?? 'Select Scheme' }}
					</a>
					<div class="dropdown-menu dropdown-menu-end">
						<!-- <a class='dropdown-item' href='pages-profile.html'><i class="align-middle me-1" data-feather="user"></i> Profile</a>
						<a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="pie-chart"></i> Analytics</a>
						<div class="dropdown-divider"></div>
						<a class='dropdown-item' href='pages-settings.html'><i class="align-middle me-1" data-feather="settings"></i> Settings &
							Privacy</a>
						<a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="help-circle"></i> Help Center</a>
						<div class="dropdown-divider"></div> -->
						{{-- <a class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a> --}}
						<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
							@csrf
						</form>
					</div>
				</li>
				<li class="nav-item">
    <a class="btn btn-sm btn-danger ms-2"
       href="#"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fa fa-power-off me-1"></i> Logout
    </a>
</li>
			</ul>
		</div>
	</nav>

	<script>
		document.addEventListener('DOMContentLoaded', function () {

			const openBtn = document.getElementById('openSchemeModalBtn');

			if (openBtn) {
				openBtn.addEventListener('click', function () {

					const modalEl = document.getElementById('defaultModalSuccess');
					const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

					modal.show();
				});
			}

		});
	</script>
