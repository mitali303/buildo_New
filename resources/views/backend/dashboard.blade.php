@extends('backend.partials.master')
@section('title')
    Dashboard
@endsection
@section('maincontent')
<!-- main content -->
 <main class="content">
    <!-- <div class="container-fluid p-0"> -->
 
<style>
/* Dashboard Layout */
body {
    background: #f5f7fb;
    font-family: 'Segoe UI', sans-serif;
}

    .content {
        padding: 1rem 1rem 1.5rem;
    }

/* Card Container */
.dashboard-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

/* Header */
.card-header-custom {
    background: linear-gradient(135deg, #222e3c, #2f3b52);
    color: #ffffff;
    padding: 14px 18px;
    font-size: 18px;
    font-weight: 600;
    letter-spacing: 0.4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Body */
.card-body-custom {
    padding: 20px 22px;
    flex: 1;
}

/* Rows */
.row-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed #e1e5eb;
    font-size: 15px;
}

.row-line:last-child {
    border-bottom: none;
}

/* Values */
.value {
    font-weight: 700;
    color: #222e3c;
}

.value-green {
    font-weight: 700;
    color: #233346;
}

.total-highlight {
    font-size: 18px;
    font-weight: 800;
    color: #222e3c;
}

/* Dropdown */
.selectpicker {
    max-width: 260px;
    font-size: 14px;
}

.bootstrap-select .dropdown-toggle {
    border-radius: 8px;
    border: 1px solid #dce1e8;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.bootstrap-select .dropdown-menu {
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
/* Choices dropdown - exact UI like screenshot */

.choices {
    width: 260px;
}

.choices__inner {
    border-radius: 6px;
    border: 1px solid #cfd7df;
    min-height: 38px;
    padding: 6px 10px;
    background: #fff;
    font-size: 14px;
}

/* Dropdown box */
.choices__list--dropdown {
    border-radius: 6px;
    border: 1px solid #d6dde6;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-top: 5px;
}

/* Search input */
.choices__input {
    border: 1px solid #000 !important;
    border-radius: 4px;
    padding: 6px 8px;
    font-size: 13px;
}

/* Dropdown items */
.choices__list--dropdown .choices__item {
    padding: 8px 12px;
    font-size: 14px;
}

/* Hover + active item */
.choices__list--dropdown .choices__item--selectable.is-highlighted {
    background: #4b8cca;
    color: #fff;
}

/* Selected item */
.choices__item--selectable.is-selected {
    background: #4b8cca;
    color: #fff;
}

.p-4 {
    padding: 0.0rem !important;
}
.filter-btn {
    background-color: #233346;
    color: #ffffff;
    border: none;
}

.filter-btn:hover {
    background-color: #233346;
    color: #ffffff !important;
}

.filter-btn:focus,
.filter-btn:active,
.filter-btn:focus-visible {
    background-color: #233346 !important;
    color: #ffffff !important;
    box-shadow: none !important;
    outline: none !important;
}

</style>

<main class="content">
<div class="container-fluid p-4">
    <form method="GET" action="{{ route('dashboard') }}">
    <div class="row mb-4 align-items-end">

        <div class="col-md-3">
            <label><strong>From Date</strong></label>
            <input type="date"
                   name="from_date"
                   class="form-control"
                   value="{{ request('from_date', $fromDate) }}">
        </div>

        <div class="col-md-3">
            <label><strong>To Date</strong></label>
            <input type="date"
                   name="to_date"
                   class="form-control"
                   value="{{ request('to_date', $toDate) }}">
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn filter-btn w-100">
                Filter
            </button>
        </div>

    </div>
</form>
    <div class="row g-4">

        <!-- Payment Detail -->
         @if (hasPermission('Payment_Detail_read'))
        <div class="col-lg-6 col-md-12 d-flex">

            <div class="dashboard-card w-100">
                   <a href="{{ route('report.income_expense') }}"
   style="color:#212529; text-decoration:none; font-weight:600;">
                <div class="card-header-custom">💳 Payment Detail</div>
                <div class="card-body-custom">
                    <div class="row-line">
                        <span>Total Income</span>
                        <span class="value">{{ number_format($totreceive) }} Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Total Expenses</span>
                        <span class="value">{{ number_format($totPaid) }} Rs</span>
                    </div>
                </div>
                </a>
            </div>
            
        </div>
    @endif
        <!-- Balance -->
         @if (hasPermission('Balance_read'))
        <div class="col-lg-6 col-md-12 d-flex">
            <div class="dashboard-card w-100">
                <div class="card-header-custom">💰 Balance</div>
                <div class="card-body-custom">

                    <div class="row-line">
                        <span>Cash In Hand</span>
                        <span class="value-green" id="dashboard_cash">0.00</span>
                    </div>

                    <!-- <div class="row-line align-items-center">          
                    <select id="dashboard_account_no"
                            class="form-control" style="width:55%;"
                            onchange="getDashboardBalance()">
                        <option value="All">All Account Total</option>

                        @foreach($accounts as $acc)
                            <option value="{{ $acc->ID }}">
                                {{ $acc->ACNo }} - {{ $acc->Name }}
                            </option>
                        @endforeach
                    </select>


                    <span class="value-green ms-2" id="dashboard_balance">0.00</span>
                    </div> -->
                    <div class="row-line align-items-center">
                        <select id="dashboard_account_no"
                                class="form-control"
                                onchange="getDashboardBalance()">

                            <option value="All">All Account Total</option>

                            @foreach($accounts as $acc)
                                <option value="{{ $acc->ID }}">
                                    {{ $acc->ACNo }} - {{ $acc->Name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="value-green ms-2" id="dashboard_balance">0.00</span>
                    </div>
                    <div class="row-line mt-3">
                        <span><strong>Total</strong></span>
                        <span class="total-highlight" id="dashboard_total">0.00</span>
                    </div>

                </div>
            </div>
        </div>
    @endif
        <!-- Construction Cost -->
         @if (hasPermission('Construction_Cost_read'))
        <div class="col-lg-6 col-md-12 d-flex">
            <div class="dashboard-card w-100">
                 <a href="{{ route('Flat') }}"
   style="color:#212529; text-decoration:none; font-weight:600;">
                <div class="card-header-custom">🏗 Construction Cost</div>
                <div class="card-body-custom">
                    <div class="row-line">
                        <span>Project Builtup Area</span>
                        <span class="value">{{ number_format($builtupArea) }} Sq.Ft</span>
                    </div>
                    <div class="row-line">
                        <span>Construction Expense</span>
                        <span class="value">{{ number_format($constexp) }} Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Construction Cost per Sq.Ft</span>
                        <span class="value">{{ number_format($costPerSqft,2) }} Rs</span>
                    </div>
                </div>
                </a>
            </div>
        </div>
    @endif
        <!-- Salable Area -->
         @if (hasPermission('Saleable_Area_read'))
        <div class="col-lg-6 col-md-12 d-flex">
            <div class="dashboard-card w-100">
                 <a href="{{ route('Flat') }}"
   style="color:#212529; text-decoration:none; font-weight:600;">
                <div class="card-header-custom">📐 Saleable Area</div>
                <div class="card-body-custom">
                    <div class="row-line">
                        <span>Project Saleable Area</span>
                        <span class="value">{{ number_format($projectArea) }} Sq.Ft</span>
                    </div>
                    <div class="row-line">
                        <span>Project Booking Area</span>
                        <span class="value">{{ number_format($flatssold_Area) }} Sq.Ft</span>
                    </div>
                    <div class="row-line">
                        <span>Balance Area</span>
                        <span class="value">{{ number_format($balanceArea) }} Sq.Ft</span>
                    </div>
                </div>
                </a>
            </div>
        </div>
        @endif
        <!-- Owner Payment-->
         @if (hasPermission('Owner_Payment_read'))
        <div class="col-lg-6 col-md-12 d-flex">
            <div class="dashboard-card w-100">
                <a href="{{ route('Owner_Pay') }}"
   style="color:#212529; text-decoration:none; font-weight:600;">
                <div class="card-header-custom">🤝 Owner Payment</div>
                <div class="card-body-custom">
                    <div class="row-line">
                        <span>Total Payment</span>
                        <span class="value">{{ number_format($owner_total) }}Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Received Amount</span>
                        <span class="value">{{ number_format($owner_received) }}Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Pending Amount</span>
                        <span class="value">{{ number_format($owner_pending) }}Rs</span>
                    </div>
                </div>
                </a>
            </div>
        </div>
    @endif
        <!-- Material Payment-->
          @if (hasPermission('Material_Payment_read'))
        <div class="col-lg-6 col-md-12 d-flex">
            <div class="dashboard-card w-100">
                <a href="{{ route('Material_pay') }}"
   style="color:#212529; text-decoration:none; font-weight:600;">
                <div class="card-header-custom">🧱 Material Payment</div>
                <div class="card-body-custom">
                    <div class="row-line">
                        <span>Total Payment</span>
                        <span class="value">{{ number_format($material_total) }}Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Paid Amount</span>
                        <span class="value">{{ number_format($material_received) }}Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Pending Amount</span>
                        <span class="value">{{ number_format($material_pending) }}Rs</span>
                    </div>
                </div>
                </a>
            </div>
        </div>
    @endif
        <!-- Site Expences -->
          @if (hasPermission('Site_Expences_read'))
        <div class="col-lg-6 col-md-12 d-flex">
            <div class="dashboard-card w-100">
                  <a href="{{ route('Site_exp_pay') }}"
   style="color:#212529; text-decoration:none; font-weight:600;">
                <div class="card-header-custom">🧾 Site Expences</div>
                <div class="card-body-custom">
                    <div class="row-line">
                        <span>Investment Or Loan</span>
                        <span class="value">{{ number_format($inv_exp) }}Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Office Expense</span>
                        <span class="value">{{ number_format($off_exp) }}Rs</span>
                    </div>
                </div>
                </a>
            </div>
        </div>
    @endif
        <!-- Labour & contractor -->
         @if (hasPermission('Labour_&_contractor_read'))
        <div class="col-lg-6 col-md-12 d-flex">
            <div class="dashboard-card w-100">
                 <a href="{{ route('report.Site_lbr_pay') }}"
   style="color:#212529; text-decoration:none; font-weight:600;">
                <div class="card-header-custom">👷 Labour & contractor</div>
                <div class="card-body-custom">
                    <div class="row-line">
                        <span>Total Payment</span>
                        <span class="value">{{ number_format($lbr_total) }}Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Paid Amount</span>
                        <span class="value">{{ number_format($lbr_paid) }}Rs</span>
                    </div>
                    <div class="row-line">
                        <span>Pending Amount</span>
                        <span class="value">{{ number_format($lbr_pending) }}Rs</span>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>
</div>
 @endif

            <!-- <div class="col-12 col-lg-4 col-xxl-3 d-flex">
                <div class="card flex-fill w-100">
                    <div class="card-header">
                        <div class="card-actions float-end">
                            <div class="dropdown position-relative">
                                <a href="#" data-bs-toggle="dropdown" data-bs-display="static">
                                    <i class="align-middle" data-feather="more-horizontal"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </div>
                            </div>
                        </div>
                        <h5 class="card-title mb-0">Monthly Sales</h5>
                    </div>
                    <div class="card-body d-flex w-100">
                        <div class="align-self-center chart chart-lg">
                            <canvas id="chartjs-dashboard-bar"></canvas>
                        </div>
                    </div>
                </div>
            </div> -->
        
</main>
@endsection
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
@push('scripts')
<script>
		document.addEventListener("DOMContentLoaded", function() {
			var ctx = document.getElementById("chartjs-dashboard-line").getContext("2d");
			var gradientLight = ctx.createLinearGradient(0, 0, 0, 225);
			gradientLight.addColorStop(0, "rgba(215, 227, 244, 1)");
			gradientLight.addColorStop(1, "rgba(215, 227, 244, 0)");
			var gradientDark = ctx.createLinearGradient(0, 0, 0, 225);
			gradientDark.addColorStop(0, "rgba(51, 66, 84, 1)");
			gradientDark.addColorStop(1, "rgba(51, 66, 84, 0)");
			// Line chart
			new Chart(document.getElementById("chartjs-dashboard-line"), {
				type: "line",
				data: {
					labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
					datasets: [{
						label: "Sales ($)",
						fill: true,
						backgroundColor: window.theme.id === "light" ? gradientLight : gradientDark,
						borderColor: window.theme.primary,
						data: [
							2115,
							1562,
							1584,
							1892,
							1587,
							1923,
							2566,
							2448,
							2805,
							3438,
							2917,
							3327
						]
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false
					},
					tooltips: {
						intersect: false
					},
					hover: {
						intersect: true
					},
					plugins: {
						filler: {
							propagate: false
						}
					},
					scales: {
						xAxes: [{
							reverse: true,
							gridLines: {
								color: "rgba(0,0,0,0.0)"
							}
						}],
						yAxes: [{
							ticks: {
								stepSize: 1000
							},
							display: true,
							borderDash: [3, 3],
							gridLines: {
								color: "rgba(0,0,0,0.0)",
								fontColor: "#fff"
							}
						}]
					}
				}
			});
		});
	</script>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			// Pie chart
			new Chart(document.getElementById("chartjs-dashboard-pie"), {
				type: "pie",
				data: {
					labels: ["Chrome", "Firefox", "IE", "Other"],
					datasets: [{
						data: [4306, 3801, 1689, 3251],
						backgroundColor: [
							window.theme.primary,
							window.theme.warning,
							window.theme.danger,
							"#E8EAED"
						],
						borderWidth: 5,
						borderColor: window.theme.white
					}]
				},
				options: {
					responsive: !window.MSInputMethodContext,
					maintainAspectRatio: false,
					legend: {
						display: false
					},
					cutoutPercentage: 70
				}
			});
		});
	</script>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			// Bar chart
			new Chart(document.getElementById("chartjs-dashboard-bar"), {
				type: "bar",
				data: {
					labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
					datasets: [{
						label: "This year",
						backgroundColor: window.theme.primary,
						borderColor: window.theme.primary,
						hoverBackgroundColor: window.theme.primary,
						hoverBorderColor: window.theme.primary,
						data: [54, 67, 41, 55, 62, 45, 55, 73, 60, 76, 48, 79],
						barPercentage: .75,
						categoryPercentage: .5
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false
					},
					scales: {
						yAxes: [{
							gridLines: {
								display: false
							},
							stacked: false,
							ticks: {
								stepSize: 20
							}
						}],
						xAxes: [{
							stacked: false,
							gridLines: {
								color: "transparent"
							}
						}]
					}
				}
			});
		});
	</script>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			var markers = [{
					coords: [31.230391, 121.473701],
					name: "Shanghai"
				},
				{
					coords: [28.704060, 77.102493],
					name: "Delhi"
				},
				{
					coords: [6.524379, 3.379206],
					name: "Lagos"
				},
				{
					coords: [35.689487, 139.691711],
					name: "Tokyo"
				},
				{
					coords: [23.129110, 113.264381],
					name: "Guangzhou"
				},
				{
					coords: [40.7127837, -74.0059413],
					name: "New York"
				},
				{
					coords: [34.052235, -118.243683],
					name: "Los Angeles"
				},
				{
					coords: [41.878113, -87.629799],
					name: "Chicago"
				},
				{
					coords: [51.507351, -0.127758],
					name: "London"
				},
				{
					coords: [40.416775, -3.703790],
					name: "Madrid "
				}
			];
			var map = new jsVectorMap({
				map: "world",
				selector: "#world_map",
				zoomButtons: true,
				markers: markers,
				markerStyle: {
					initial: {
						r: 9,
						stroke: window.theme.white,
						strokeWidth: 7,
						stokeOpacity: .4,
						fill: window.theme.primary
					},
					hover: {
						fill: window.theme.primary,
						stroke: window.theme.primary
					}
				},
				regionStyle: {
					initial: {
						fill: window.theme["gray-200"]
					}
				},
				zoomOnScroll: false
			});
			window.addEventListener("resize", () => {
				map.updateSize();
			});
			setTimeout(function() {
				map.updateSize();
			}, 250);
		});
	</script>
	<script>
		document.addEventListener("DOMContentLoaded", function() {
			var date = new Date(Date.now() - 5 * 24 * 60 * 60 * 1000);
			var defaultDate = date.getUTCFullYear() + "-" + (date.getUTCMonth() + 1) + "-" + date.getUTCDate();
			document.getElementById("datetimepicker-dashboard").flatpickr({
				inline: true,
				prevArrow: "<span class=\"fas fa-chevron-left\" title=\"Previous month\"></span>",
				nextArrow: "<span class=\"fas fa-chevron-right\" title=\"Next month\"></span>",
				defaultDate: defaultDate
			});
		});
	</script>

<script>
  document.addEventListener("DOMContentLoaded", function(event) { 
    setTimeout(function(){
      if(localStorage.getItem('popState') !== 'shown'){
        window.notyf.open({
          type: "success",
          message: "Get access to all 500+ components and 45+ pages with AdminKit PRO. <u><a class=\"text-white\" href=\"https://adminkit.io/pricing\" target=\"_blank\">More info</a></u> 🚀",
          duration: 10000,
          ripple: true,
          dismissible: false,
          position: {
            x: "left",
            y: "bottom"
          }
        });

        localStorage.setItem('popState','shown');
      }
    }, 15000);
  });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Choices('.choices-select', {
        searchEnabled: true,
        placeholder: true,
        placeholderValue: 'All Account Total',
        searchPlaceholderValue: 'Search account...',
        itemSelectText: '',
        shouldSort: false
    });
});

let dashboardCash = 0;

function loadCashBalance() {

    $.ajax({
        url: "{{ route('ajax.getBalance') }}",
        type: "POST",
        data: {
            matid: 'Cash In Hand',
            payid: '',
            date: '',
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {

            dashboardCash = parseFloat(response.balance) || 0;

            $("#dashboard_cash").text(dashboardCash.toFixed(2));

            updateDashboardTotal();
        }
    });
}

function getDashboardBalance() {

    let accId = $("#dashboard_account_no").val();

    if (accId === 'All') {

        $.ajax({
            url: "{{ route('ajax.getBalance') }}",
            type: "POST",
            data: {
                matid: 'All',
                payid: '',
                date: '',
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {

                let balance = parseFloat(response.balance) || 0;

                $("#dashboard_balance").text(balance.toFixed(2));

                updateDashboardTotal(balance);
            }
        });

    } else {

        $.ajax({
            url: "{{ route('ajax.getBalance') }}",
            type: "POST",
            data: {
                matid: accId,
                payid: '',
                date: '',
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {

                let balance = parseFloat(response.balance) || 0;

                $("#dashboard_balance").text(balance.toFixed(2));

                updateDashboardTotal(balance);
            }
        });
    }
}

function updateDashboardTotal(bankBalance = 0) {

    if (!bankBalance) {
        bankBalance = parseFloat($("#dashboard_balance").text()) || 0;
    }

    let total = dashboardCash + bankBalance;

    $("#dashboard_total").text(total.toFixed(2));
}

document.addEventListener("DOMContentLoaded", function () {

    loadCashBalance();       // 🔥 load cash automatically
    getDashboardBalance();   // 🔥 load All account total automatically

});


</script>
<script>
$(document).ready(function () {
    $('#dashboard_account_no').select2({
        width: '55%',
        placeholder: 'Search Account'
    });
});
</script>

@endpush
