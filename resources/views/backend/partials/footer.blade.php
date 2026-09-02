<footer class="footer">
    <div class="container-fluid">
        <div class="row text-muted">
            <div class="col-6 text-start">
                <p class="mb-0">
                    <a href="{{route('dashboard')}}" target="_blank" class="text-muted"><strong>{{ company_setting('company_name') ?? 'Buildo Construction' }}</strong></a> &copy;
                </p>
            </div>
            <div class="col-6 text-end">
                <ul class="list-inline">
                    <li class="list-inline-item">
                        <a class="text-muted" href="http://webotix.in/">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
</div>
</div>

<script src="{{asset ('backend/js/app.js')}}"></script>
<script src="{{asset ('backend/js/datatables.js')}}"></script>
<script src="{{ asset('js/marathi-typing.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
        toastr.error("{{ session('error') }}");
    @endif

    @if(session('info'))
        toastr.info("{{ session('info') }}");
    @endif

    @if(session('warning'))
        toastr.warning("{{ session('warning') }}");
    @endif
</script>

<!-- searchable dropdown script start-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Choices.js
        new Choices(document.querySelector(".choices-single"));
        new Choices(document.querySelector(".choices-multiple"));
        // Flatpickr
        flatpickr(".flatpickr-minimum");
        flatpickr(".flatpickr-datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
        });
        flatpickr(".flatpickr-human", {
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
        });
        flatpickr(".flatpickr-multiple", {
            mode: "multiple",
            dateFormat: "Y-m-d"
        });
        flatpickr(".flatpickr-range", {
            mode: "range",
            dateFormat: "Y-m-d"
        });
        flatpickr(".flatpickr-time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        });
    });
</script>
<!-- searchable dropdown script end-->

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
message: "Welcome to Buildo Construction Admin Panel. <u><a class=\"text-white\" href=\"\" target=\"_blank\">More info</a></u> 🚀",
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

<!-- Buttons extension -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- Dependencies for Excel/PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- For Are you sure confirmation logic alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('click', function (e) {
            if (e.target.closest('.delete-confirm')) {
                e.preventDefault();
                const btn = e.target.closest('.delete-confirm');
                const formId = btn.getAttribute('data-id');

                Swal.fire({
                    text: 'Are you sure you want to delete this entry?',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#0f569a',
                   
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    width: '350px',
                    customClass: {
                        popup: 'p-2', // Bootstrap padding
                        confirmButton: 'btn py-1 px-3 fw-semibold btn-danger',
                        cancelButton: 'btn py-1 px-3 fw-semibold btn-primary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(formId).submit();
                    }
                });
            }
        });
    });
</script>
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<!-- ✅ THEN Bootstrap -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let selectedScheme = null;

function loadSchemes() {

    const schemesUrl = "{{ url('schemes') }}";
    const sessionUrl = "{{ url('set-session') }}";
    const currentScheme = "{{ session('selected_scheme_id') }}";

    fetch(schemesUrl)
        .then(res => res.json())
        .then(data => {

            let html = '';

            data.forEach(scheme => {

                let active = currentScheme == scheme.ID ? 'active' : '';

                html += `
                <div class="scheme-col-5 mb-3">
                    <div class="card scheme-card text-center p-3 ${active}"
                         data-id="${scheme.ID}">
                        <h6 class="mb-1">${scheme.Name}</h6>
                    </div>
                </div>
                `;

                if(currentScheme == scheme.ID){
                    selectedScheme = scheme.ID;
                }
            });

            document.getElementById('schemeCardsContainer').innerHTML = html;

            document.querySelectorAll('.scheme-card').forEach(card => {

                card.addEventListener('click', function(){

                    document.querySelectorAll('.scheme-card')
                        .forEach(c => c.classList.remove('active'));

                    this.classList.add('active');
                    selectedScheme = this.dataset.id;

                });

            });

        });

    document.getElementById('saveUserBtn').onclick = function(){

        if(!selectedScheme){
            alert("Please select a scheme");
            return;
        }

        fetch(sessionUrl,{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body: JSON.stringify({ scheme_id: selectedScheme })
        })
        .then(() => location.reload());
    };
}

document.addEventListener('DOMContentLoaded', function () {

    loadSchemes(); // always load schemes

   @php
    $isSuperAdmin = Auth::user()->UserID == 'superadmin';
    $hasScheme = DB::table('scheme_step1')->exists();
@endphp

// @if(!session('selected_scheme_id'))

//     @if($isSuperAdmin && !$hasScheme)

//         {{-- SuperAdmin आहे आणि scheme नाही त्यामुळे popup नाही --}}

//     @else

//         const modal = new bootstrap.Modal('#defaultModalSuccess');
//         modal.show();

//     @endif

// @endif
@if(
    !session('selected_scheme_id')
    && request()->route()->getName() != 'Scheme.create'
)

    @if($isSuperAdmin && !$hasScheme)

    @else

        const modal = new bootstrap.Modal(document.getElementById('defaultModalSuccess'));
        modal.show();

    @endif

@endif

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    loadSchemes();   // always load

    @php
    $isSuperAdmin = Auth::user()->UserID == 'superadmin';
    $hasScheme = DB::table('scheme_step1')->exists();
@endphp

// @if(!session('selected_scheme_id'))

//     @if($isSuperAdmin && !$hasScheme)

//         {{-- SuperAdmin आहे आणि scheme नाही त्यामुळे popup नाही --}}

//     @else

//         const modal = new bootstrap.Modal('#defaultModalSuccess');
//         modal.show();

//     @endif

// @endif
@if(
    !session('selected_scheme_id')
    && request()->route()->getName() != 'Scheme.create'
)

    @if($isSuperAdmin && !$hasScheme)

    @else

        const modal = new bootstrap.Modal(document.getElementById('defaultModalSuccess'));
        modal.show();

    @endif

@endif


});
</script>

@stack('scripts')
<script>
document.addEventListener("input", function(e) {
    if (
        e.target.tagName === "INPUT" &&
        e.target.type === "text" &&
        !e.target.classList.contains("no-uppercase")
    ) {
        e.target.value = e.target.value.toUpperCase();
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    let currentUrl = window.location.pathname;

    document.querySelectorAll(".sidebar-link").forEach(function (link) {

        let href = link.getAttribute("href");

        if (href && href !== "#" && currentUrl.includes(new URL(href).pathname)) {

            // active child menu
            let parentLi = link.closest(".sidebar-item");
            if (parentLi) {
                parentLi.classList.add("active");
            }


            // parent dropdown open
            let dropdown = link.closest(".sidebar-dropdown");

            if (dropdown) {
                dropdown.classList.add("show");

                let parentMenu = dropdown.closest(".sidebar-item");

                if (parentMenu) {
                    parentMenu.classList.add("active");

                    let parentLink = parentMenu.querySelector(".sidebar-link");

                    if (parentLink) {
                        parentLink.classList.remove("collapsed");
                    }
                }
            }
        }

    });

});
</script>
</body>
</html>