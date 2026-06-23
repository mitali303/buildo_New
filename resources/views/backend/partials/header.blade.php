<!DOCTYPE html>
<html lang="en">

<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
	<meta name="author" content="Buildo Construction">
	<meta name="keywords" content="wms, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

	<link rel="preconnect" href="https://fonts.gstatic.com/">
	<link rel="shortcut icon" href="{{asset ('backend/img/icons/icon-48x48.png')}}" />

	<!-- toster -->
	<!-- <link href="{{ asset('vendor/toastr/toastr.min.css') }}" rel="stylesheet"/> -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="{{ asset('jquery-upload-file-master/uploadfile.css') }}" rel="stylesheet">



	<link rel="canonical" href="index.html" />

	<title>Buildo Construction - @yield('title')</title>

	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&amp;display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

	<!-- Choose your prefered color scheme -->
	<!-- <link href="css/light.css" rel="stylesheet"> -->
	<!-- <link href="css/dark.css" rel="stylesheet"> -->

	<!-- BEGIN SETTINGS -->
	<!-- Remove this after purchasing -->
	<link class="js-stylesheet" href="{{asset ('backend/css/light.css')}}" rel="stylesheet">
	<!-- <script src="{{asset ('backend/js/settings.js') }}"></script> -->
	<style>
		body {
			opacity: 0;
		}
		/* Toastr message text color overrides */
		.toast-success {
			color:rgb(223, 226, 224) !important; /* dark green for success */
			background-color:rgb(11, 146, 42) !important;
		}

		.toast-error {
			color:rgb(235, 227, 228) !important; /* dark red for error */
			background-color:rgb(226, 72, 85) !important;
		}

		.toast-info {
			color:rgb(224, 230, 231) !important; /* dark blue for info */
			background-color:rgb(52, 182, 205) !important;
		}

		.toast-warning {
			color:rgb(231, 228, 222) !important; /* dark yellow for warning */
			background-color:rgb(186, 149, 29) !important;
		}

		.scheme-col-5 {
    flex: 0 0 20%;
    max-width: 20%;
}

.scheme-card {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid #dee2e6;
}

.scheme-card:hover {
    transform: scale(1.02);
    border-color: #0d6efd;
}

.scheme-card.active {
    border-color: #0d6efd;
    background: #f0f6ff;
}
.filter-btn {
    background-color: #233346;
    color: #ffffff;
    border: none;
}
	</style>
	<!-- END SETTINGS -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-120946860-10"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-120946860-10', { 'anonymize_ip': true });
</script></head>
<!--
  HOW TO USE: 
  data-theme: default (default), dark, light, colored
  data-layout: fluid (default), boxed
  data-sidebar-position: left (default), right
  data-sidebar-layout: default (default), compact
-->
<!-- Global Modal -->
<!-- Modal code remains unchanged -->
<div class="modal fade" id="defaultModalSuccess" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Select Scheme</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div class="mb-3">
					<label for="userSelector" class="form-label">Select Scheme</label>
					<div class="row" id="schemeCardsContainer">
						<!-- Cards will be loaded here -->
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-success" id="saveUserBtn">Okay</button>
			</div>
		</div>
	</div>
</div>
<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
	<div class="wrapper">