<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
    <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true"
            data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-save-state="true">

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">

                <div class="menu-item">
                    <a class="menu-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                        <span class="menu-icon"><i class="bi bi-grid fs-2"></i></span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                <div class="menu-item menu-accordion {{ request()->routeIs('payroll-periods.*') ? 'here show' : '' }}" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="bi bi-cash-coin fs-2"></i></span>
                        <span class="menu-title">Payroll</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('payroll-periods.*') ? 'active' : '' }}"
                                href="{{ route('payroll-periods.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Periode Gaji</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="bi bi-receipt fs-2"></i></span>
                        <span class="menu-title">Invoicing</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"
                                href="{{ route('invoices.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Data Invoice</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                                href="{{ route('products.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Data Barang</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="menu-item menu-accordion {{ request()->routeIs('attendances.*') ? 'here show' : '' }}" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="bi bi-calendar-check fs-2"></i></span>
                        <span class="menu-title">Absensi</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('attendances.index') ? 'active' : '' }}"
                                href="{{ route('attendances.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Data Absensi</span>
                            </a>
                        </div>
                    </div>
                </div>

                
                <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="bi bi-database fs-2"></i></span>
                        <span class="menu-title">Master Data</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('employees.*') ? 'active' : '' }}"
                                href="{{ route('employees.index') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span class="menu-title">Data
                                    Karyawan</span></a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('payslip-components.*') ? 'active' : '' }}"
                                href="{{ route('payslip-components.index') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span class="menu-title">Komponen
                                    Gaji</span></a>
                        </div>
                    </div>
                </div>

                <!-- <div class="menu-item menu-accordion" data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-icon"><i class="bi bi-clipboard-data fs-2"></i></span>
                        <span class="menu-title">Laporan</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}"
                                href="{{ route('activity-logs.index') }}"><span class="menu-bullet"><span
                                        class="bullet bullet-dot"></span></span><span class="menu-title">Activity
                                    Logs</span></a>
                        </div>
                    </div>
                </div> -->

            </div>
        </div>
    </div>
</div>
