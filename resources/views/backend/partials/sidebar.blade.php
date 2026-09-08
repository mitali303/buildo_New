<style>
	/* Reports sidebar scrolling */
	#sidebar .sidebar-content {
		height: 100vh !important;
		overflow: hidden !important;
	}

	#sidebar .simplebar-wrapper {
		height: 100vh !important;
	}

	#sidebar .simplebar-mask {
		height: 100vh !important;
	}

	#sidebar .simplebar-offset {
		height: 100% !important;
	}

	#sidebar .simplebar-content-wrapper {
		height: 100% !important;
		overflow-y: auto !important;
		overflow-x: hidden !important;
	}

	#sidebar .simplebar-content {
		min-height: 100%;
	}

	#sidebar .sidebar-nav {
		padding-bottom: 100px !important;
	}

	#sidebar .sidebar-dropdown {
		overflow: visible;
	}

	#sidebar .sidebar-link {
		padding-right: 30px !important;
	}

	#sidebar .sidebar-link::after {
		right: 10px !important;
	}

	/* Sidebar parent menu arrow - fixed alignment */
	#sidebar>.sidebar-content .sidebar-nav>.sidebar-item>.sidebar-link {
		position: relative !important;
		padding-right: 40px !important;
	}

	#sidebar>.sidebar-content .sidebar-nav>.sidebar-item>.sidebar-link::after {
		position: absolute !important;
		right: 15px !important;
		top: 50% !important;
		transform: translateY(-50%) !important;
	}

	/* Keep submenu links normal */
	#sidebar .sidebar-dropdown .sidebar-link {
		padding-right: 15px !important;
	}
</style>
<nav id="sidebar" class="sidebar js-sidebar">
	<div class="sidebar-content js-simplebar">
		<a class='sidebar-brand' href="{{route('dashboard')}}">
			<span class="sidebar-brand-text align-middle">
				<img src="{{ asset('Logos/BUILDO_LOGO.png') }}"
					alt="Buildo Construction"
					height="100" style="margin-left:27%">
			</span>
			<svg class="sidebar-brand-icon align-middle" width="32px" height="32px" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.5"
				stroke-linecap="square" stroke-linejoin="miter" color="#FFFFFF" style="margin-left: -3px">
				<path d="M12 4L20 8.00004L12 12L4 8.00004L12 4Z"></path>
				<path d="M20 12L12 16L4 12"></path>
				<path d="M20 16L12 20L4 16"></path>
			</svg>
		</a>

		<div class="sidebar-user">
			<div class="d-flex justify-content-center">
				<div class="flex-shrink-0">

					{{-- <img src="{{ asset($setting->logo_path) }}" class="avatar img-fluid rounded me-1" height="80" alt="Logo"><br>
				</div>
				<div class="flex-grow-1 ps-2">
					<a class="sidebar-user-title dropdown-toggle" href="#" data-bs-toggle="dropdown">
						{{ auth()->user()->name }}
					</a>
					<div class="dropdown-menu dropdown-menu-start">
						<a class='dropdown-item' href='#'><i class="align-middle me-1" data-feather="user"></i> Profile</a>
						<a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="pie-chart"></i> Analytics</a>
						<div class="dropdown-divider"></div>
						<a class='dropdown-item' href='pages-settings.html'><i class="align-middle me-1" data-feather="settings"></i> Settings &
							Privacy</a>
						<a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="help-circle"></i> Help Center</a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>
						<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
							@csrf
						</form>
					</div>

					<div class="sidebar-user-subtitle">Designer</div>
				</div>
			</div>
		</div>--}}

		<ul class="sidebar-nav">
			<!-- <li class="sidebar-header">
						Pages
						</li> -->
			<li class="sidebar-item {{ request()->is('/') ? 'active' : '' }}">
				<a class='sidebar-link' href="{{route('dashboard')}}">
					<i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Dashboard</span>
				</a>
			</li>
			<li class="sidebar-item {{ request()->is('estimates*') || request()->is('construction-estimates*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('Estimate') }}">
					<i class="align-middle" data-feather="file-text"></i>
					<span class="align-middle">Construction Estimate</span>
				</a>
			</li>
			<!-- masters -->
			@php
			$activeMastersRoutes = request()->is(
			// 'users', 'users/create', 'users/edit*',
			// 'roles', 'roles/create', 'roles/edit*',
			'supplier-contractors', 'supplier-contractors/create', 'supplier-contractors/edit*',
			'payment-slab', 'payment-slab/create', 'payment-slab/edit*',
			'agency', 'agency/create', 'agency/edit*',
			'materials', 'materials/create', 'materials/edit*',
			'staff', 'staff/create', 'staff/edit*',
			'partner-loan-investors', 'partner-loan-investors/create', 'partner-loan-investors/edit*',
			'bank-loan-requests', 'bank-loan-requests/create', 'bank-loan-requests/edit*',
			'bank-accounts', 'bank-accounts/create', 'bank-accounts/edit*',

			);
			@endphp
			@if (
			// hasPermission('user_read') ||
			// hasPermission('role_read') ||
			hasPermission('agency_read') ||
			hasPermission('supplier_contractor_read') ||
			hasPermission('payment_slab_read') ||
			hasPermission('Material_read') ||
			hasPermission('staff_read') ||
			hasPermission('partner_loan_investor_read') ||
			hasPermission('bank_loan_request_read') ||
			hasPermission('Bank_Account_read') ||
			hasPermission('Shift_read')
			)

			<li class="sidebar-item {{ $activeMastersRoutes ? 'active' : '' }}">
				<a data-bs-target="#masters" data-bs-toggle="collapse" class="sidebar-link {{ $activeMastersRoutes ? '' : 'collapsed' }}">
					<i class="align-middle" data-feather="layout"></i>
					<span class="align-middle">Masters</span>
				</a>
				<ul id="masters" class="sidebar-dropdown list-unstyled collapse {{ $activeMastersRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">

					<!--@if (hasPermission('user_read'))-->
					<!--<li class="sidebar-item {{ request()->is('users', 'users/create', 'users/edit*') ? 'active' : '' }}">-->
					<!--	<a class='sidebar-link' href="{{route('users')}}">User</a>-->
					<!--</li>-->
					<!--@endif-->

					<!--@if (hasPermission('role_read'))-->
					<!--<li class="sidebar-item {{ request()->is('roles', 'roles/create', 'roles/edit*') ? 'active' : '' }}">-->
					<!--	<a class='sidebar-link' href="{{route('roles')}}">Role</a>-->
					<!--</li>-->
					<!--@endif-->

					@if (hasPermission('agency_read'))
					<li class="sidebar-item {{ request()->is('agency', 'agency/create', 'agency/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('Agency')}}">Agency</a>
					</li>
					@endif

					@if (hasPermission('supplier_contractor_read'))
					<li class="sidebar-item {{ request()->is('supplier-contractors', 'supplier-contractors/create', 'supplier-contractors/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('SupplierContractor')}}">Supplier / Contractor</a>
					</li>
					@endif

					@if (hasPermission('payment_slab_read'))
					<li class="sidebar-item {{ request()->is('payment-slab', 'payment-slab/create', 'payment-slab/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('PaymentSlab')}}">Payment Slab</a>
					</li>
					@endif

					@if (hasPermission('Material_read'))
					<li class="sidebar-item {{ request()->is('materials', 'materials/create', 'materials/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('Material')}}">Material</a>
					</li>
					@endif

					@if (hasPermission('staff_read'))
					<li class="sidebar-item {{ request()->is('staff', 'staff/create', 'staff/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('Staff')}}">Staff</a>
					</li>
					@endif

					@if (hasPermission('partner_loan_investor_read'))
					<li class="sidebar-item {{ request()->is('partner-loan-investors', 'partner-loan-investors/create', 'partner-loan-investors/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('PartnerLoanInvestor')}}">Partners / Loan / Investor</a>
					</li>
					@endif

					@if (hasPermission('bank_loan_request_read'))
					<li class="sidebar-item {{ request()->is('bank-loan-requests', 'bank-loan-requests/create', 'bank-loan-requests/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('BankForm')}}">Bank Loan Request</a>
					</li>
					@endif

					@if (hasPermission('Bank_Account_read'))
					<li class="sidebar-item {{ request()->is('bank-accounts', 'bank-accounts/create', 'bank-accounts/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('BankAcc')}}">Bank Accounts</a>
					</li>
					@endif
					@if (hasPermission('Shift_read'))
					<li class="sidebar-item {{ request()->is('Shift', 'Shift/create', 'Shift/edit*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{ route('Shift') }}">Shift</a>
					</li>
					@endif

				</ul>
			</li>
			@endif

			<!-- Scheme Master -->
			@php
			$activeSchemeRoutes = request()->is(
			'scheme-details*', 'scheme-details/create', 'scheme-details/edit*',
			'flat-details*', 'flat-details/create', 'flat-details/edit*',
			'scheme-completions*', 'scheme-completions/create', 'scheme-completions/edit*'
			);
			@endphp

			@if (
			hasPermission('scheme_detail_read') || hasPermission('create_scheme_detail') ||
			hasPermission('flat_detail_read') || hasPermission('create_flat_detail') ||
			hasPermission('scheme_completion_read') || hasPermission('create_scheme_completion')
			)
			<li class="sidebar-item {{ $activeSchemeRoutes ? 'active' : '' }}">
				<a data-bs-target="#schemes" data-bs-toggle="collapse" class="sidebar-link {{ $activeSchemeRoutes ? '' : 'collapsed' }}">
					<i class="align-middle" data-feather="layers"></i>
					<span class="align-middle">Scheme Master</span>
				</a>
				<ul id="schemes" class="sidebar-dropdown list-unstyled collapse {{ $activeSchemeRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">
					@if (hasPermission('scheme_detail_read'))
					<li class="sidebar-item {{ request()->is('scheme-details*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('Scheme')}}">Scheme Details</a>
					</li>
					@endif
					@if (hasPermission('flat_detail_read'))
					<li class="sidebar-item {{ request()->is('flat-details*') ? 'active' : '' }}">
						<a class='sidebar-link' href="{{route('Flat')}}">Flat Detail</a>
					</li>
					@endif
					{{-- @if (hasPermission('scheme_completion_read'))	
						<li class="sidebar-item {{ request()->is('scheme-completions*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{route('SchemeComplete')}}">Scheme Completion</a>
			</li>
			@endif --}}
		</ul>
		</li>
		@endif

		<!-- Scheme Management -->
		@php
		$activeSchemeManagementRoutes = request()->is(
		'customer-demand-raise', 'customer-demand-raise/create',
		'cancel-booking', 'cancel-booking/create',
		'customer-booking', 'customer-booking/create',
		'add-bill', 'add-bill/create'
		)
		|| request()->routeIs('reports.available_flat_report');
		@endphp
		@if (
		hasPermission('customer_demand_raise_read') ||
		hasPermission('cancel_booking_read') ||
		hasPermission('customer_booking_read') ||
		hasPermission('add_bill_read') ||
		hasPermission('available_flat_report_read')
		)
		<li class="sidebar-item {{ $activeSchemeManagementRoutes ? 'active' : '' }}">
			<a data-bs-target="#schememanagement" data-bs-toggle="collapse" class="sidebar-link {{ $activeSchemeManagementRoutes ? '' : 'collapsed' }}">
				<i class="align-middle" data-feather="file-text"></i>
				<span class="align-middle">Scheme Management</span>
			</a>
			<ul id="schememanagement" class="sidebar-dropdown list-unstyled collapse {{ $activeSchemeManagementRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">

				@if (hasPermission('customer_demand_raise_read'))
				<li class="sidebar-item {{ request()->is('customer-demand-raise') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('demand_raise') }}">Customer Demand Raise</a>
				</li>
				@endif

				@if (hasPermission('cancel_booking_read'))
				<li class="sidebar-item {{ request()->is('cancel-booking') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('booking_cancel') }}">Cancel Booking</a>
				</li>
				@endif

				@if (hasPermission('customer_booking_read'))
				<li class="sidebar-item {{ request()->is('customer_booking','customer_booking/create', 'customer_booking/edit*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('customer_booking') }}">Customer Booking</a>
				</li>
				@endif

				@if (hasPermission('add_bill_read'))
				<li class="sidebar-item {{ request()->is('add-bill') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Add_bill') }}">Add Bill</a>
				</li>
				@endif

				{{-- Available Flat Report --}}
				@if (hasPermission('available_flat_report_read'))
				<li class="sidebar-item {{ request()->routeIs('reports.available_flat_report') ? 'active' : '' }}">
					<a
						class="sidebar-link"
						href="{{ route('reports.available_flat_report') }}">
						Booking Management Report
					</a>
				</li>
				@endif

			</ul>
		</li>
		@endif

		@php
		$activeCRMRoutes = request()->is(
		'enquiries*',
		'CallOutcome*',
		'CallStatus*',
		'CallPurpose*',
		'CreateLead*',
		'DailyWork*'
		);
		@endphp
		@if(
		Auth::user()->Role == 2 ||
		hasPermission('call_outcome_read') ||
		hasPermission('call_status_read') ||
		hasPermission('call_purpose_read') ||
		hasPermission('lead_read') ||
		hasPermission('daily_work_read') ||
		Auth::user()->Role == 2
		)

		<li class="sidebar-item {{ $activeCRMRoutes ? 'active' : '' }}">
			<a data-bs-target="#crm" data-bs-toggle="collapse" class="sidebar-link {{ $activeCRMRoutes ? '' : 'collapsed' }}">
				<i class="align-middle fas fa-headset"></i>
				<span class="align-middle">CRM</span>
			</a>
			<ul id="crm" class="sidebar-dropdown list-unstyled collapse {{ $activeCRMRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">
				<!-- @if(hasPermission('enquiry_read') || Auth::user()->Role == 2)
									<li class="sidebar-item">
										<a class="sidebar-link" href="{{ route('enquiries.index') }}">
											Enquiry List
										</a>
									</li>
								@endif -->
				@if(Auth::user()->Role == 2)
				<li class="sidebar-item">
					<a class="sidebar-link" href="{{ route('enquiries.index') }}">
						Enquiry List
					</a>
				</li>
				@endif

				@if(hasPermission('call_outcome_read'))
				<li class="sidebar-item">
					<a class="sidebar-link" href="{{ route('CallOutcome') }}">
						Call Outcome
					</a>
				</li>
				@endif
				@if(hasPermission('call_status_read'))
				<li class="sidebar-item">
					<a class="sidebar-link" href="{{ route('CallStatus') }}">
						Call Status
					</a>
				</li>
				@endif
				@if(hasPermission('call_purpose_read'))
				<li class="sidebar-item">
					<a class="sidebar-link" href="{{ route('CallPurpose') }}">
						Call Purpose
					</a>
				</li>
				@endif
				@if(hasPermission('lead_read'))
				<li class="sidebar-item {{ request()->is('CreateLead','CreateLead/create','CreateLead/edit*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('CreateLead') }}">
						Lead / Enquiry
					</a>
				</li>
				@endif
				<!-- @if(hasPermission('daily_work_read'))
								<li class="sidebar-item">
									<a class="sidebar-link" href="{{ route('DailyWork') }}">
										<i data-feather="clipboard"></i>
										<span>Daily Work Entry</span>
									</a>
								</li>
								@endif -->
			</ul>
		</li>
		@endif
		{{-- ========================================================= --}}
		{{-- DAILY WORK --}}
		{{-- ========================================================= --}}

		@php
		$activeDailyWorkRoutes =
		request()->routeIs('DailyWork') ||
		request()->routeIs('dailyworkreport.index') ||
		request()->routeIs('material_request.*');
		@endphp

		@if (hasPermission('daily_work_read'))

		<li class="sidebar-item {{ $activeDailyWorkRoutes ? 'active' : '' }}">

			{{-- Parent --}}
			<a
				data-bs-target="#dailywork"
				data-bs-toggle="collapse"
				class="sidebar-link {{ $activeDailyWorkRoutes ? '' : 'collapsed' }}"
				href="#dailywork"
				aria-expanded="{{ $activeDailyWorkRoutes ? 'true' : 'false' }}">
				<i class="align-middle fas fa-clipboard"></i>

				<span class="align-middle">
					Daily Work
				</span>
			</a>

			{{-- Child Reports --}}
			<ul
				id="dailywork"
				class="sidebar-dropdown list-unstyled collapse {{ $activeDailyWorkRoutes ? 'show' : '' }}"
				data-bs-parent="#sidebar">

				{{-- Daily Work Entry --}}
				<li class="sidebar-item {{ request()->routeIs('DailyWork') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('DailyWork') }}">
						Daily Work Entry
					</a>
				</li>


				{{-- Daily Work Report --}}
				@if (hasPermission('daily_work_report_read'))
				<li class="sidebar-item {{ request()->routeIs('dailyworkreport.index') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('dailyworkreport.index') }}">
						Daily Work Report
					</a>
				</li>
				@endif


				{{-- Material Request --}}
				<li class="sidebar-item {{ request()->routeIs('material_request.*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('material_request.list') }}">
						<i class="align-middle" data-feather="file-text"></i>
						<span class="align-middle">
							Material Request
						</span>
					</a>
				</li>
			</ul>
		</li>
		@endif



		<!--  Work/Material Order -->
		@php
		$activeWorkRoutes = request()->is(
		'purchase-orders*', 'purchase-orders/create', 'purchase-orders/edit*',
		'purchase-invoices*', 'purchase-invoices/create', 'purchase-invoices/edit*',
		'return-materials*', 'return-materials/create', 'return-materials/edit*',
		'material-transfers*', 'material-transfers/create', 'material-transfers/edit*',
		'site-work-orders*', 'site-work-orders/create', 'site-work-orders/edit*',
		'Labour_Work*', 'Labour_Work/create', 'Labour_Work/edit*',
		'material-consumptions*', 'material-consumptions/create', 'material-consumptions/edit*'
		);
		@endphp
		@if (
		hasPermission('purchase_order_read') || hasPermission('purchase_invoice_read') ||
		hasPermission('Return_Material_read') || hasPermission('stocktransfer_read') ||
		hasPermission('stockadjustment_read') || hasPermission('site_work_order_read') ||
		hasPermission('labour_work_read') || hasPermission('material_consumption_read')
		)
		<li class="sidebar-item {{ $activeWorkRoutes ? 'active' : '' }}">
			<a data-bs-target="#workorders" data-bs-toggle="collapse" class="sidebar-link {{ $activeWorkRoutes ? '' : 'collapsed' }}">
				<i class="align-middle" data-feather="tool"></i>
				<span class="align-middle">Work/Material Order</span>
			</a>
			<ul id="workorders" class="sidebar-dropdown list-unstyled collapse {{ $activeWorkRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">
				@if (hasPermission('purchase_order_read'))
				<li class="sidebar-item {{ request()->is('purchase-orders*') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('PurchaseOrder')}}">Purchase Order</a></li>
				@endif
				@if (hasPermission('purchase_invoice_read'))
				<li class="sidebar-item {{ request()->is('purchase-invoices*') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('PurchaseInvoice')}}">Purchase Invoice</a></li>
				@endif
				@if (hasPermission('labour_work_read'))
				<li class="sidebar-item {{ request()->is('Labour_Work*') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('Labour_Work')}}">Labour Work</a></li>
				@endif
				@if (hasPermission('Return_Material_read'))
				<li class="sidebar-item {{ request()->is('return-materials*') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('Rejected_Material')}}">Return Material</a></li>
				@endif
				@if (hasPermission('material_transfer_read'))
				<li class="sidebar-item {{ request()->is('material-transfers*') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('Transfer_Material')}}">Material Transfer</a></li>
				@endif
				@if (hasPermission('site_work_order_read'))
				<li class="sidebar-item {{ request()->is('site-work-orders*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Site_work_order') }}">Site Work Order</a>
				</li>
				@endif
				@if (hasPermission('material_consumption_read'))
				<li class="sidebar-item {{ request()->is('material-consumptions') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('Material_Consumption')}}">Material Consumption</a></li>
				@endif
			</ul>
		</li>
		@endif

		<!-- Loan Management -->
		@php
		$activeLoanRoutes = request()->is('loan_management', 'loan_management/create', 'loan_management/edit*');
		@endphp
		@if (hasPermission('loan_management_read') || hasPermission('create_loan_management'))
		<li class="sidebar-item {{ $activeLoanRoutes ? 'active' : '' }}">
			<a data-bs-target="#loan_management" data-bs-toggle="collapse" class="sidebar-link {{ $activeLoanRoutes ? '' : 'collapsed' }}">
				<i class="align-middle" data-feather="box"></i>
				<span class="align-middle">Loan Management</span>
			</a>
			<ul id="loan_management" class="sidebar-dropdown list-unstyled collapse {{ $activeLoanRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">

				@if (hasPermission('loan_management_read'))
				<li class="sidebar-item {{ request()->is('loan_management', 'loan_management/create', 'loan_management/edit*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('loan_management') }}">Add Loan</a>
				</li>
				@endif
			</ul>
		</li>
		@endif



		<!-- Financial Accounting -->
		@php
		$activeFinanceRoutes = request()->is(
		'site-work-order-payments','site-work-order-payments/create','site-work-order-payments/edit*',
		'owner-payments', 'owner-payments/create', 'owner-payments/edit*',
		'labour-payments', 'labour-payments/create', 'labour-payments/edit*',
		'material-payments', 'material-payments/create', 'material-payments/edit*',
		'payments/partner*', 'payments/investor*',
		'site-expenses', 'site-expenses/create', 'site-expenses/edit*',
		'return-payments', 'return-payments/create', 'return-payments/edit*',
		'tds-payments*', 'tds-payments/create*', 'tds-payments/edit*',
		'view-payment-tds*',
		'bank_reconciliation*', 'account_transfer*', 'customer_payment*',
		'post_dated_cheque*',
		'customer_refund*',
		'land_expenses', 'land_expenses/create', 'land_expenses/edit*',
		'stamp_other_expenses', 'stamp_other_expenses/create', 'stamp_other_expenses/edit*'
		);
		@endphp

		@if (
		hasPermission('site_work_order_payment_read') || hasPermission('material_payment_read') ||
		hasPermission('labour_work_payment_read') || hasPermission('owner_payment_read') ||
		hasPermission('partners_payment_read') || hasPermission('investors_payment_read') ||
		hasPermission('site_expenses_read') || hasPermission('return_payment_read') ||
		hasPermission('tds_payment_read') || hasPermission('bank_reconciliation_read') ||
		hasPermission('account_transfer_read') || hasPermission('customer_payment_read') ||
		hasPermission('post_dated_cheque_read') || hasPermission('customer_refund_read') ||
		hasPermission('land_expenses_read') || hasPermission('stamp_other_expenses_read')
		)

		<li class="sidebar-item {{ $activeFinanceRoutes ? 'active' : '' }}">

			<a data-bs-target="#finance" data-bs-toggle="collapse"
				class="sidebar-link {{ $activeFinanceRoutes ? '' : 'collapsed' }}"
				aria-expanded="{{ $activeFinanceRoutes ? 'true' : 'false' }}">

				<i class="align-middle" data-feather="dollar-sign"></i>
				<span class="align-middle">Financial Accounting</span>
			</a>

			<ul id="finance" class="sidebar-dropdown list-unstyled collapse {{ $activeFinanceRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">


				{{-- Site Work --}}
				@if (hasPermission('site_work_order_payment_read'))
				<li class="sidebar-item {{ request()->is('site-work-order-payments*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Site_work_pay') }}">Site Work Order Payment</a>
				</li>
				@endif

				{{-- Labour --}}
				@if (hasPermission('labour_work_payment_read'))
				<li class="sidebar-item {{ request()->is('labour-payments*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Labour_work_pay') }}">Labour Work Payment</a>
				</li>
				@endif

				{{-- Material --}}
				@if (hasPermission('material_payment_read'))
				<li class="sidebar-item {{ request()->is('material-payments*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Material_pay') }}">Material Payment</a>
				</li>
				@endif

				{{-- Customer Payment --}}
				@if (hasPermission('customer_payment_read'))
				<li class="sidebar-item {{ request()->is('customer_payment*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('customer_payment') }}">Customer Payment</a>
				</li>
				@endif

				{{-- Owner --}}
				@if (hasPermission('owner_payment_read'))
				<li class="sidebar-item {{ request()->is('owner-payments*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Owner_Pay') }}">Owner Payment</a>
				</li>
				@endif

				{{-- Site Expenses --}}
				@if (hasPermission('site_expenses_read'))
				<li class="sidebar-item {{ request()->is('site-expenses*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Site_exp_pay') }}">Site Expenses</a>
				</li>
				@endif



				{{-- Partners --}}
				@if (hasPermission('partners_payment_read'))
				<li class="sidebar-item {{ request()->is('payments/partner*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Partner_pay', ['type' => 'partner']) }}">Partners Payment</a>
				</li>
				@endif

				{{-- Investors --}}
				@if (hasPermission('investors_payment_read'))
				<li class="sidebar-item {{ request()->is('payments/investor*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Partner_pay', ['type' => 'investor']) }}">
						Investors Payment </a>
				</li>
				@endif

				{{-- ✅ Return Payment FIXED --}}
				@if (hasPermission('return_payment_read'))
				<li class="sidebar-item {{ request()->is('return-payments*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Return_pay') }}">Return Payment</a>
				</li>
				@endif
				@php
				$isTdsActive = request()->is(
				'tds-payments*',
				'view-payment-tds*'
				);
				@endphp
				{{-- TDS --}}
				@if (hasPermission('tds_payment_read'))
				<li class="sidebar-item {{ $isTdsActive ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('Tds_pay') }}">
						TDS Payment
					</a>
				</li>
				@endif

				{{-- Account Transfer --}}
				@if (hasPermission('account_transfer_read'))
				<li class="sidebar-item {{ request()->is('account_transfer*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('account_transfer') }}">Account Transfer</a>
				</li>
				@endif

				{{-- Refund --}}
				@if (hasPermission('customer_refund_read'))
				<li class="sidebar-item {{ request()->is('customer_refund*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('customer_refund') }}">Customer Refund</a>
				</li>
				@endif

				{{-- Land --}}
				@if (hasPermission('land_expenses_read'))
				<li class="sidebar-item {{ request()->is('land_expenses*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('land_expenses') }}">Land Expenses</a>
				</li>
				@endif

				{{-- Stamp --}}
				@if (hasPermission('stamp_other_expenses_read'))
				<li class="sidebar-item {{ request()->is('stamp_other_expenses*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('stamp_other_expenses') }}">Stamp & Other Expenses</a>
				</li>
				@endif

				{{-- Bank --}}
				@if (hasPermission('bank_reconciliation_read'))
				<li class="sidebar-item {{ request()->is('bank_reconciliation*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('bank_reconciliation') }}">Bank Reconciliation</a>
				</li>
				@endif

				{{-- PDC --}}
				@if (hasPermission('post_dated_cheque_read'))
				<li class="sidebar-item {{ request()->is('post_dated_cheque*') ? 'active' : '' }}">
					<a class='sidebar-link' href="{{ route('post_dated_cheque') }}">Post Dated Cheque</a>
				</li>
				@endif

			</ul>
		</li>

		@endif

		<!-- Payroll Management -->
		@php
		$activePayrollRoutes = request()->is(
		'EmployeeAdvance', 'EmployeeAdvance/create', 'EmployeeAdvance/edit*',
		'AttendanceMaster', 'AttendanceMaster/create', 'AttendanceMaster/edit*',
		'SalaryMaster', 'SalaryMaster/create', 'SalaryMaster/edit*',
		'LateMarkCalculation', 'LateMarkCalculation/create', 'LateMarkCalculation/edit*',
		'UserShift', 'UserShift/create', 'UserShift/edit*',
		'salary/generate*',
		'salary-report*'
		);
		@endphp

		@if (hasPermission('EmployeeAdvance_read') || hasPermission('EmployeeAdvance_read') || hasPermission('UserShift_read') )
		<li class="sidebar-item {{ $activePayrollRoutes ? 'active' : '' }}">
			<a data-bs-target="#payrollMenu" data-bs-toggle="collapse"
				class="sidebar-link {{ $activePayrollRoutes ? '' : 'collapsed' }}">
				<i class="align-middle" data-feather="briefcase"></i>
				<span class="align-middle">Payroll</span>
			</a>

			<ul id="payrollMenu"
				class="sidebar-dropdown list-unstyled collapse {{ $activePayrollRoutes ? 'show' : '' }}"
				data-bs-parent="#sidebar">

				@if (hasPermission('UserShift_read'))
				<li class="sidebar-item {{ request()->is('UserShift', 'UserShift/create', 'UserShift/edit*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('UserShift') }}">Assign Shift</a>
				</li>
				@endif

				@if (hasPermission('EmployeeAdvance_read'))
				<li class="sidebar-item {{ request()->is('EmployeeAdvance', 'EmployeeAdvance/create', 'EmployeeAdvance/edit*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('EmployeeAdvance') }}">Employee Advance</a>
				</li>
				@endif

				@if (hasPermission('AttendanceMaster_read'))
				<li class="sidebar-item {{ request()->is('AttendanceMaster', 'AttendanceMaster/create', 'AttendanceMaster/edit*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('AttendanceMaster') }}">Attendance Master</a>
				</li>
				@endif

				@if (hasPermission('LateMarkCalculation_read'))
				<li class="sidebar-item {{ request()->is('LateMarkCalculation', 'LateMarkCalculation/create', 'LateMarkCalculation/edit*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('LateMarkCalculation') }}">Late Mark Calculation Master</a>
				</li>
				@endif

				{{-- Generate Salary --}}
				<li class="sidebar-item {{ request()->is('salary/generate*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('salary.generate.page') }}">
						Generate Salary
					</a>
				</li>

				@if (hasPermission('SalaryMaster_read'))
				<li class="sidebar-item {{ request()->is('SalaryMaster', 'SalaryMaster/create', 'SalaryMaster/edit*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('SalaryMaster') }}">Salary Master</a>
				</li>
				@endif
				{{-- Salary Report --}}
				<li class="sidebar-item {{ request()->is('salary-report*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('salary.report') }}">
						Salary Report
					</a>
				</li>


			</ul>
		</li>
		@endif

		{{-- ========================================================= --}}
		{{-- REPORTS MAIN MENU --}}
		{{-- ========================================================= --}}

		@php
		$reportsActive =
		request()->routeIs('report.Site_lbr_pay') ||
		request()->routeIs('reports.lbrpay_report') ||

		request()->routeIs('report.Material_pay') ||
		request()->routeIs('report.material_cunsum') ||
		request()->routeIs('report.material_transfer') ||
		request()->routeIs('report.material_recieve') ||
		request()->routeIs('reports.material_purchase_report') ||
		request()->routeIs('reports.stock_report') ||

		request()->routeIs('report.partner_pay') ||
		request()->routeIs('reports.loan_payment_report') ||
		request()->routeIs('report.return_pay') ||

		request()->routeIs('report.daily_diary') ||
		request()->routeIs('report.income_expense') ||
		request()->routeIs('bank.transaction.report') ||
		request()->routeIs('reports.site_expenses_report') ||
		request()->routeIs('reports.land_expense_report') ||
		request()->routeIs('reports.stamp_other_expense_report') ||
		request()->routeIs('report.tds') ||

		request()->routeIs('reports.customer_payment.customer') ||
		request()->routeIs('reports.customer_payment.bank') ||
		request()->routeIs('reports.customer_payment.self') ||
		request()->routeIs('reports.customer.payment.report') ||
		request()->routeIs('reports.customer_refund') ||

		request()->routeIs('reports.rera_report') ||
		request()->routeIs('reports.gst_report');
		@endphp

		<li class="sidebar-item">

			<a data-bs-target="#reportsMenu" data-bs-toggle="collapse" class="sidebar-link {{ $reportsActive ? '' : 'collapsed' }}"
				aria-expanded="{{ $reportsActive ? 'true' : 'false' }}">
				<i class="align-middle" data-feather="bar-chart-2"></i>
				<span class="align-middle"> Reports </span>
			</a>

			<ul id="reportsMenu" class="sidebar-dropdown list-unstyled collapse {{ $reportsActive ? 'show' : '' }}">


				{{-- ========================================================= --}}
				{{-- WORK ORDER & LABOUR PAYMENT --}}
				{{-- ========================================================= --}}

				@php
				$siteWorkPaymentActive = request()->routeIs('report.Site_lbr_pay');
				$labourPaymentActive = request()->routeIs('reports.lbrpay_report');

				$workOrderLabourActive =
				$siteWorkPaymentActive || $labourPaymentActive;
				@endphp

				@if (
				hasPermission('site_labour_payment_report_read') ||
				hasPermission('lbrwp_report_read')
				)

				<li class="sidebar-item">

					{{-- Parent --}}
					<a data-bs-target="#workOrderLabourPayment" data-bs-toggle="collapse" class="sidebar-link {{ $workOrderLabourActive ? '' : 'collapsed' }}"
						aria-expanded="{{ $workOrderLabourActive ? 'true' : 'false' }}">
						<i class="align-middle" data-feather="briefcase"></i>
						<span class="align-middle small" style="font-size: 11px !important;">
							Work Order &amp; Labour Payment
						</span>
					</a>

					{{-- Child Reports --}}
					<ul id="workOrderLabourPayment" class="sidebar-dropdown list-unstyled collapse {{ $workOrderLabourActive ? 'show' : '' }}">

						{{-- Work Order Payment Report --}}
						@if (hasPermission('site_labour_payment_report_read'))
						<li class="sidebar-item {{ $siteWorkPaymentActive ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.Site_lbr_pay') }}">
								Work Order Payment
							</a>
						</li>
						@endif


						{{-- Labour Payment Report --}}
						@if (hasPermission('lbrwp_report_read'))
						<li class="sidebar-item {{ $labourPaymentActive ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('reports.lbrpay_report') }}">
								Labour Payment
							</a>
						</li>
						@endif
					</ul>
				</li>

				@endif



				{{-- ========================================================= --}}
				{{-- SUPPLIER & MATERIAL REPORTS --}}
				{{-- ========================================================= --}}

				@php
				$supplierMaterialActive =
				request()->routeIs('report.Material_pay') ||
				request()->routeIs('report.material_cunsum') ||
				request()->routeIs('report.material_transfer') ||
				request()->routeIs('report.material_recieve') ||
				request()->routeIs('reports.material_purchase_report') ||
				request()->routeIs('reports.stock_report');
				@endphp

				@if (
				hasPermission('supplier_payment_report_read') ||
				hasPermission('material_consumption_read') ||
				hasPermission('material_transferred_report_read') ||
				hasPermission('material_received_report_read') ||
				hasPermission('material_purchase_report_read') ||
				hasPermission('stock_report_read')
				)

				<li class="sidebar-item">

					{{-- Parent --}}
					<a data-bs-target="#supplierMaterialReports" data-bs-toggle="collapse" class="sidebar-link {{ $supplierMaterialActive ? '' : 'collapsed' }}"
						aria-expanded="{{ $supplierMaterialActive ? 'true' : 'false' }}">
						<i class="align-middle" data-feather="package"></i>
						<span class="align-middle">
							Supplier &amp; Material
						</span>
					</a>

					{{-- Child Reports --}}
					<ul id="supplierMaterialReports" class="sidebar-dropdown list-unstyled collapse {{ $supplierMaterialActive ? 'show' : '' }}">

						{{-- Supplier Payment Report --}}
						@if (hasPermission('supplier_payment_report_read'))
						<li class="sidebar-item {{ request()->routeIs('report.Material_pay') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.Material_pay') }}">
								Supplier Payment Report
							</a>
						</li>
						@endif


						{{-- Material Consumption Report --}}
						@if (hasPermission('material_consumption_read'))
						<li class="sidebar-item {{ request()->routeIs('report.material_cunsum') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.material_cunsum') }}">
								Material Consumption Report
							</a>
						</li>
						@endif


						{{-- Material Transferred Report --}}
						@if (hasPermission('material_transferred_report_read'))
						<li class="sidebar-item {{ request()->routeIs('report.material_transfer') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.material_transfer') }}">
								Material Transferred Report
							</a>
						</li>
						@endif


						{{-- Material Received Report --}}
						@if (hasPermission('material_received_report_read'))
						<li class="sidebar-item {{ request()->routeIs('report.material_recieve') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.material_recieve') }}">
								Material Received Report
							</a>
						</li>
						@endif


						{{-- Material Purchase Report --}}
						@if (hasPermission('material_purchase_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.material_purchase_report') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('reports.material_purchase_report') }}">
								Material Purchase Report
							</a>
						</li>
						@endif


						{{-- Stock Report --}}
						@if (hasPermission('stock_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.stock_report') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('reports.stock_report') }}">
								Stock Report
							</a>
						</li>
						@endif

					</ul>
				</li>
				@endif


				{{-- ========================================================= --}}
				{{-- PARTNER & LOAN REPORTS --}}
				{{-- ========================================================= --}}

				@php
				$partnerLoanActive =
				(request()->routeIs('report.partner_pay') && in_array(request('type'), ['partner', 'investor'])) ||
				request()->routeIs('reports.loan_payment_report') ||
				request()->routeIs('report.return_pay');
				@endphp

				@if (
				hasPermission('partners_payment_report_read') ||
				hasPermission('investors_payment_report_read') ||
				hasPermission('loan_payment_report_read') ||
				hasPermission('return_payment_report_read')
				)

				<li class="sidebar-item">

					{{-- Parent --}}
					<a data-bs-target="#partnerLoanReports" data-bs-toggle="collapse" class="sidebar-link {{ $partnerLoanActive ? '' : 'collapsed' }}"
						aria-expanded="{{ $partnerLoanActive ? 'true' : 'false' }}">
						<i class="align-middle" data-feather="users"></i>
						<span class="align-middle">
							Partner &amp; Loan
						</span>
					</a>

					{{-- Child Reports --}}
					<ul id="partnerLoanReports" class="sidebar-dropdown list-unstyled collapse {{ $partnerLoanActive ? 'show' : '' }}">

						{{-- Partners Payment Report --}}
						@if (hasPermission('partners_payment_report_read'))
						@php
						$isPartner =
						request()->routeIs('report.partner_pay') &&
						request('type') === 'partner';
						@endphp

						<li class="sidebar-item {{ $isPartner ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.partner_pay', ['type' => 'partner']) }}">
								Partners Payment
							</a>
						</li>
						@endif


						{{-- Investors Payment Report --}}
						@if (hasPermission('investors_payment_report_read'))
						@php
						$isInvestor =
						request()->routeIs('report.partner_pay') &&
						request('type') === 'investor';
						@endphp

						<li class="sidebar-item {{ $isInvestor ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.partner_pay', ['type' => 'investor']) }}">
								Investors Payment
							</a>
						</li>
						@endif


						{{-- Loan Payment Report --}}
						@if (hasPermission('loan_payment_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.loan_payment_report') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('reports.loan_payment_report') }}">
								Loan Payment
							</a>
						</li>
						@endif


						{{-- Return Payment Report --}}
						@if (hasPermission('return_payment_report_read'))
						<li class="sidebar-item {{ request()->routeIs('report.return_pay') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.return_pay') }}">
								Return Payment
							</a>
						</li>
						@endif
					</ul>
				</li>
				@endif

				{{-- ========================================================= --}}
				{{-- FINANCIAL & EXPENSE REPORTS --}}
				{{-- ========================================================= --}}

				@php
				$financialExpenseActive =
				request()->routeIs('report.daily_diary') ||
				request()->routeIs('report.income_expense') ||
				request()->routeIs('bank.transaction.report') ||
				request()->routeIs('reports.site_expenses_report') ||
				request()->routeIs('reports.land_expense_report') ||
				request()->routeIs('reports.stamp_other_expense_report') ||
				request()->routeIs('report.tds');
				@endphp

				@if (
				hasPermission('daily_diary_read') ||
				hasPermission('income_expense_report_read') ||
				hasPermission('bank_transaction_report_read') ||
				hasPermission('site_expenses_report_read') ||
				hasPermission('land_expense_report_read') ||
				hasPermission('stamp_other_expense_report_read') ||
				hasPermission('tds_report_read')
				)

				<li class="sidebar-item">

					{{-- Parent --}}
					<a
						data-bs-target="#financialExpenseReports"
						data-bs-toggle="collapse"
						class="sidebar-link {{ $financialExpenseActive ? '' : 'collapsed' }}"
						aria-expanded="{{ $financialExpenseActive ? 'true' : 'false' }}">
						<i class="align-middle" data-feather="dollar-sign"></i>

						<span class="align-middle">
							Financial &amp; Expense
						</span>
					</a>

					{{-- Child Reports --}}
					<ul
						id="financialExpenseReports"
						class="sidebar-dropdown list-unstyled collapse {{ $financialExpenseActive ? 'show' : '' }}">

						{{-- Daily Diary --}}
						@if (hasPermission('daily_diary_read'))
						<li class="sidebar-item {{ request()->routeIs('report.daily_diary') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.daily_diary') }}">
								Daily Diary
							</a>
						</li>
						@endif


						{{-- Income Expense Report --}}
						@if (hasPermission('income_expense_report_read'))
						<li class="sidebar-item {{ request()->routeIs('report.income_expense') ? 'active' : '' }}">
							<a class="sidebar-link" href="{{ route('report.income_expense') }}">
								Income Expense Report
							</a>
						</li>
						@endif


						{{-- Bank Transaction Report --}}
						@if (hasPermission('bank_transaction_report_read'))
						<li class="sidebar-item {{ request()->routeIs('bank.transaction.report') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('bank.transaction.report') }}">
								Bank Transaction Report
							</a>
						</li>
						@endif


						{{-- Site Expenses Report --}}
						@if (hasPermission('site_expenses_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.site_expenses_report') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.site_expenses_report') }}">
								Site Expenses Report
							</a>
						</li>
						@endif


						{{-- Land Expense Report --}}
						@if (hasPermission('land_expense_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.land_expense_report') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.land_expense_report') }}">
								Land Expense Report
							</a>
						</li>
						@endif


						{{-- Stamp & Other Expenses --}}
						@if (hasPermission('stamp_other_expense_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.stamp_other_expense_report') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.stamp_other_expense_report') }}">
								Stamp &amp; Other Expenses
							</a>
						</li>
						@endif


						{{-- TDS Report --}}
						@if (hasPermission('tds_report_read'))
						<li class="sidebar-item {{ request()->routeIs('report.tds') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('report.tds') }}">
								TDS Report
							</a>
						</li>
						@endif

					</ul>

				</li>

				@endif


				{{-- ========================================================= --}}
				{{-- CUSTOMER PAYMENT & REFUND REPORTS --}}
				{{-- ========================================================= --}}

				@php
				$customerPaymentRefundActive =
				request()->routeIs('reports.customer_payment.customer') ||
				request()->routeIs('reports.customer_payment.bank') ||
				request()->routeIs('reports.customer_payment.self') ||
				request()->routeIs('reports.customer.payment.report') ||
				request()->routeIs('reports.customer_refund');
				@endphp

				@if (
				hasPermission('customer_payment_report_read') ||
				hasPermission('customer_refund_report_read')
				)

				<li class="sidebar-item">

					{{-- Parent --}}
					<a
						data-bs-target="#customerPaymentRefundReports"
						data-bs-toggle="collapse"
						class="sidebar-link {{ $customerPaymentRefundActive ? '' : 'collapsed' }}"
						aria-expanded="{{ $customerPaymentRefundActive ? 'true' : 'false' }}">
						<i class="align-middle" data-feather="users"></i>

						<span class="align-middle small">
							Customer Payment
						</span>
					</a>

					{{-- Child Reports --}}
					<ul
						id="customerPaymentRefundReports"
						class="sidebar-dropdown list-unstyled collapse {{ $customerPaymentRefundActive ? 'show' : '' }}">

						{{-- Customer Payment --}}
						@if (hasPermission('customer_payment_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.customer_payment.customer') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.customer_payment.customer') }}">
								Customer Payment
							</a>
						</li>
						@endif


						{{-- Bank Payment --}}
						@if (hasPermission('customer_payment_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.customer_payment.bank') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.customer_payment.bank') }}">
								Bank Payment
							</a>
						</li>
						@endif


						{{-- Self Payment --}}
						@if (hasPermission('customer_payment_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.customer_payment.self') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.customer_payment.self') }}">
								Self Payment
							</a>
						</li>
						@endif


						{{-- Receipt Report --}}
						@if (hasPermission('customer_payment_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.customer.payment.report') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.customer.payment.report') }}">
								Receipt
							</a>
						</li>
						@endif


						{{-- Customer Refund Report --}}
						@if (hasPermission('customer_refund_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.customer_refund') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.customer_refund') }}">
								Customer Refund
							</a>
						</li>
						@endif

					</ul>

				</li>

				@endif

				{{-- ========================================================= --}}
				{{-- COMPLIANCE REPORTS --}}
				{{-- ========================================================= --}}

				@php
				$complianceActive =

				request()->routeIs('reports.rera_report') ||
				request()->routeIs('reports.gst_report');
				@endphp

				@if (

				hasPermission('rera_report_read') ||
				hasPermission('gst_report_read')
				)

				<li class="sidebar-item">

					{{-- Parent --}}
					<a
						data-bs-target="#ComplianceReports"
						data-bs-toggle="collapse"
						class="sidebar-link {{ $complianceActive ? '' : 'collapsed' }}"
						aria-expanded="{{ $complianceActive ? 'true' : 'false' }}">
						<i class="align-middle" data-feather="file-text"></i>

						<span class="align-middle">
							Compliance Reports
						</span>
					</a>

					{{-- Child Reports --}}
					<ul
						id="ComplianceReports"
						class="sidebar-dropdown list-unstyled collapse {{ $complianceActive ? 'show' : '' }}">



						{{-- RERA Report --}}
						@if (hasPermission('rera_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.rera_report') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.rera_report') }}">
								RERA Report
							</a>
						</li>
						@endif


						{{-- GST Report --}}
						@if (hasPermission('gst_report_read'))
						<li class="sidebar-item {{ request()->routeIs('reports.gst_report') ? 'active' : '' }}">
							<a
								class="sidebar-link"
								href="{{ route('reports.gst_report') }}">
								GST Report
							</a>
						</li>
						@endif

					</ul>

				</li>

				@endif

			</ul>

		</li>

		<!-- App Report -->
		{{--@php
				$activeAppReportRoutes = request()->is('appreport');
			@endphp
			@if (hasPermission('app_report_read'))
			<li class="sidebar-item {{ $activeAppReportRoutes ? 'active' : '' }}">
		<a data-bs-target="#appreport" data-bs-toggle="collapse" class="sidebar-link {{ $activeAppReportRoutes ? '' : 'collapsed' }}">
			<i class="align-middle" data-feather="monitor"></i>
			<span class="align-middle">App Report</span>
		</a>
		<ul id="appreport" class="sidebar-dropdown list-unstyled collapse {{ $activeAppReportRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">
			<li class="sidebar-item {{ request()->is('appreport') ? 'active' : '' }}">
				<a class='sidebar-link' href="#">App Report</a>
			</li>
		</ul>
		</li>
		@endif --}}


		<!-- users and roles -->
		@php
		$activeUserRoutes = request()->is('users', 'users/create', 'users/edit*', 'users/store', 'roles', 'roles/create', 'roles/edit*');
		@endphp
		@if (hasPermission('user_read') || hasPermission('create_user') || hasPermission('role_read') || hasPermission('role_create'))
		<li class="sidebar-item {{ $activeUserRoutes ? 'active' : '' }}">
			<a data-bs-target="#users" data-bs-toggle="collapse" class="sidebar-link {{ $activeUserRoutes ? '' : 'collapsed' }}">
				<i class="align-middle" data-feather="users"></i> <span class="align-middle">User & Roles</span>
			</a>
			<ul id="users" class="sidebar-dropdown list-unstyled collapse {{ $activeUserRoutes ? 'show' : '' }}" data-bs-parent="#sidebar">
				@if (hasPermission('user_read'))
				<li class="sidebar-item {{ request()->is('users*') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('users')}}">Users</a></li>
				@endif
				{{-- @if (hasPermission('create_user'))
						<li class="sidebar-item {{ request()->is('users/create') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('users.create')}}">Create user</a>
		</li>-->
		@endif--}}
		@if (hasPermission('role_read'))
		<li class="sidebar-item {{ request()->is('roles*') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('roles')}}">Roles </a></li>
		@endif
		{{-- @if (hasPermission('role_create'))
						<li class="sidebar-item {{ request()->is('roles/create') ? 'active' : '' }}"><a class='sidebar-link' href="{{route('roles.create')}}">Create Role</a></li>
		@endif --}}
		</ul>
		</li>
		@endif
		@if (hasPermission('pushnotification_read'))
		<li class="sidebar-item {{ request()->is('notifications') ? 'active' : '' }}">
			<a class="sidebar-link {{ request()->is('notifications') ? 'active' : '' }}" href="{{route('notifications')}}">
				<i class="align-middle" data-feather="bell"></i> <span class="align-middle">Push Notification</span>
			</a>
		</li>
		@endif
		@if (hasPermission('administerbk_read'))
		<li class="sidebar-item {{ request()->is('database-backup','database-backup/list') ? 'active' : '' }}">
			<a class="sidebar-link {{ request()->is('database-backup','database-backup/list') ? 'active' : '' }}" href="{{route('database.backuplist')}}">
				<i class="align-middle" data-feather="hard-drive"></i> <span class="align-middle">Administer Backup</span>
			</a>
		</li>
		@endif
		@if (hasPermission('companysetting_read'))
		<li class="sidebar-item {{ request()->is('company-setting') ? 'active' : '' }}">
			<a class="sidebar-link {{ request()->is('company-setting') ? 'active' : '' }}" href="{{route('companysettings')}}">
				<i class="align-middle" data-feather="settings"></i> <span class="align-middle">Company Settings</span>
			</a>
		</li>
		@endif
		</ul>
	</div>
</nav>