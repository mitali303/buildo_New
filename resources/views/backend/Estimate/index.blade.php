@extends('backend.partials.master')

@section('title', 'Estimates')

@section('maincontent')
<main class="content">
	<div class="container-fluid p-0">
		<div class="d-flex justify-content-between align-items-center mb-4">
			<h1 class="h3 mb-0">Construction Estimates</h1>
			<a href="{{ route('Estimate.create') }}" class="btn btn-primary"><i data-feather="plus"></i> New Estimate</a>
		</div>

		@if(session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
		@endif

		<div class="row g-3 mb-4">
			<div class="col-md-4">
				<div class="card">
					<div class="card-body"><small class="text-muted">Total Estimates</small>
						<div class="h3 mb-0">{{ $summary['total'] }}</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card">
					<div class="card-body"><small class="text-muted">Approved</small>
						<div class="h3 mb-0 text-success">{{ $summary['approved'] }}</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card">
					<div class="card-body"><small class="text-muted">Estimated Value</small>
						<div class="h3 mb-0">₹ {{ number_format($summary['value'], 2) }}</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body border-bottom">
				<form class="row g-2">
					<div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search estimate, site or customer"></div>
					<div class="col-md-3"><select name="status" class="form-select">
							<option value="all">All statuses</option>@foreach(\App\Models\Backend\Estimate::statusLabels() as $value => $label)<option value="{{ $value }}" @selected((string) request('status')===(string) $value)>{{ $label }}</option>@endforeach
						</select></div>
					<div class="col-md-3"><button class="btn btn-dark w-100">Filter</button></div>
				</form>
			</div>
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead>
						<tr>
							<th>Estimate No.</th>
							<th>Scheme / Site</th>
							<th>Customer</th>
							<th>Date</th>
							<th>Status</th>
							<th class="text-end">Construction</th>
							<th class="text-end">Material</th>
							<th class="text-end">Grand total</th>
							<th class="text-end">Action</th>
						</tr>
					</thead>
					<tbody>
						@forelse($estimates as $estimate)
						@php $badge = [0 => 'danger', 1 => 'secondary', 2 => 'info', 3 => 'success'][$estimate->status] ?? 'secondary'; @endphp
						<tr>
							<td class="fw-semibold">{{ $estimate->estimate_no }}</td>
							<td>
								<div class="fw-semibold">{{ $estimate->scheme_name ?: '-' }}</div>
							</td>
							<td>{{ $estimate->customer_name ?: '-' }}</td>
							<td>{{ optional($estimate->date)->format('d M Y') }}</td>
							<td><span class="badge bg-{{ $badge }}">{{ $estimate->status_label }}</span></td>
							<td class="text-end">₹ {{ number_format((float) ($estimate->construction_total ?? 0), 2) }}</td>
							<td class="text-end">₹ {{ number_format((float) $estimate->material_total, 2) }}</td>
							<td class="text-end fw-semibold">₹ {{ number_format($estimate->total_amount, 2) }}</td>
							<td class="text-end text-nowrap">{!! $estimate->actions !!}</td>
						</tr>
						@empty
						<tr>
							<td colspan="9" class="text-center py-4 text-muted">No estimates found.</td>
						</tr>
						@endforelse
					</tbody>
				</table>
			</div>
			<div class="card-footer">{{ $estimates->links() }}</div>
		</div>
	</div>
</main>
@endsection

@section('scripts')
<script>
	document.addEventListener('DOMContentLoaded', function() {
		if (window.feather) feather.replace();
	});
</script>
@endsection