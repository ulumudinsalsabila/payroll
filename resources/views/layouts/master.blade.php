<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    @if(View::hasSection('page_title'))
        <title>PT. Hasna Utama - @yield('page_title')</title>
    @else
        <title>PT. Hasna Utama</title>
    @endif

    <base href="{{ asset('') }}">
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <!-- Vendor Stylesheets (optional, bisa dipakai untuk datatables, calendar dll) -->
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- Global Stylesheets Bundle -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        [data-bs-theme="light"] #kt_app_sidebar_logo .sidebar-title {
            color: #fff !important;
        }
    </style>

    @stack('styles')
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="false" class="app-default">

    <!--begin::Theme mode setup (Metronic default)-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;

        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }

            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }

            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup-->

    <!--begin::App root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <!--begin::Header-->
            <div id="kt_app_header" class="app-header" data-kt-sticky="true"
                data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize"
                data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
                <!--begin::Header container-->
                <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
                    id="kt_app_header_container">

                    <!--begin::Sidebar mobile toggle-->
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Tampilkan menu">
                        <div class="btn btn-icon btn-active-color-primary w-35px h-35px"
                            id="kt_app_sidebar_mobile_toggle">
                            <i class="bi bi-list fs-1"></i>
                        </div>
                    </div>
                    <!--end::Sidebar mobile toggle-->

                    <!--begin::Header logo & title-->
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="{{ auth()->check() ? route('payroll-periods.index') : route('login') }}"
                            class="d-flex align-items-center">
                            <img alt="Logo" src="{{ asset('assets/media/logos/default-small.svg') }}"
                                class="h-30px" />
                            <div class="menu-item px-3 my-0">
                                PT. Hasna Utama
                            </div>
                        </a>
                    </div>
                    <!--end::Header logo-->

                    <!--begin::Navbar-->
                    <div class="d-flex align-items-center flex-shrink-0">
                        <div class="me-3">
                            <button type="button" id="theme_toggle"
                                class="btn btn-icon btn-light btn-active-light-primary w-35px h-35px"
                                aria-label="Ubah tema">
                                <i id="theme_toggle_icon" class="bi"></i>
                            </button>
                        </div>
                        @auth
                            <div class="d-flex align-items-center">
                                <a href="{{ route('payroll-periods.index') }}"
                                    class="d-flex align-items-center text-decoration-none">
                                    <div class="d-none d-md-flex flex-column me-3 text-start">
                                        <span class="fw-semibold text-gray-600 fs-7">Login sebagai</span>
                                        <span class="fw-bold text-gray-800 fs-6">
                                            {{ Auth::user()->name ?? 'User' }}
                                        </span>
                                        <span class="text-muted fs-8 text-uppercase">
                                            {{ Auth::user()->role ?? '-' }}
                                        </span>
                                    </div>

                                    <div class="symbol symbol-35px symbol-circle">
                                        <span class="symbol-label bg-primary text-white fw-bold">
                                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                        </span>
                                    </div>
                                </a>

                                <div class="ms-3">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light btn-active-light-primary">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth
                    </div>
                    <!--end::Navbar-->

                </div>
                <!--end::Header container-->
            </div>
            <!--end::Header-->

            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <!--begin::Sidebar-->
                <div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true"
                    data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}"
                    data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start"
                    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

                    <!--begin::Sidebar logo-->
                    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
                        <a href="{{ url('/') }}" class="d-flex align-items-center">
                            <i class="bi bi-layers text-primary fs-1"></i>
                            <span class="ms-3 fw-semibold text-gray-800 fs-6 sidebar-title">PT. Hasna Utama</span>
                        </a>
                    </div>
                    <!--end::Sidebar logo-->

                    <!--begin::Sidebar menu-->
                    @include('layouts.sidebar')
                    <!--end::Sidebar menu-->
                </div>
                <!--end::Sidebar-->

                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">
                        <!--begin::Content-->
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div class="app-container container-fluid py-5">
                                @yield('content')
                            </div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Content wrapper-->

                    <!--begin::Footer-->
                    <div class="app-footer py-3 d-flex flex-column flex-md-row flex-center flex-md-stack"
                        id="kt_app_footer">
                        <div class="text-gray-900 order-2 order-md-1">
                            <span class="text-gray-600 fw-semibold">PT. Hasna Utama</span>
                        </div>
                    </div>
                    <!--end::Footer-->
                </div>
                <!--end::Main-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::App root-->

    <!-- Global Javascript Bundle -->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <!-- Vendor (optional) -->
    <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

    <script>
        (function() {
            function applyIcon(mode) {
                var icon = document.getElementById('theme_toggle_icon');
                if (!icon) return;
                icon.className = 'bi ' + (mode === 'dark' ? 'bi-moon' : 'bi-sun');
            }

            function getMode() {
                return document.documentElement.getAttribute('data-bs-theme') || 'light';
            }

            function setMode(mode) {
                document.documentElement.setAttribute('data-bs-theme', mode);
                try {
                    localStorage.setItem('data-bs-theme', mode);
                } catch (e) {}
                applyIcon(mode);
            }

            document.addEventListener('DOMContentLoaded', function() {
                applyIcon(getMode());
                var btn = document.getElementById('theme_toggle');
                if (btn) {
                    btn.addEventListener('click', function() {
                        var next = getMode() === 'dark' ? 'light' : 'dark';
                        setMode(next);
                    });
                }
            });
        })();
    </script>

    <script>
        // Global: enforce smaller buttons across the app
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn').forEach(function(el) {
                if (!el.classList.contains('btn-sm')) {
                    el.classList.add('btn-sm');
                }
                if (el.classList.contains('btn-lg')) {
                    el.classList.remove('btn-lg');
                }
            });
        });
    </script>

    @auth
        <script>
            (function() {
                var tz = null;
                try {
                    tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                } catch (e) {
                    tz = null;
                }

                if (!tz) return;

                var serverTz = @json(Auth::user()->timezone);
                if (serverTz === tz) {
                    try {
                        localStorage.setItem('client_timezone', tz);
                    } catch (e) {}
                    return;
                }

                fetch('{{ route('timezone.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            timezone: tz
                        })
                    })
                    .then(function(resp) {
                        if (!resp.ok) return;
                        try {
                            localStorage.setItem('client_timezone', tz);
                        } catch (e) {}
                    })
                    .catch(function() {});
            })
            ();
        </script>
    @endauth

    @stack('scripts')
</body>

</html>
