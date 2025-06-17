<aside class="main-sidebar elevation-4 sidebar-dark-indigo sidebar-no-expand text-lg">
    <a href="/dashboard" class="brand-link">
        <img src="{{ asset('dist/img/PNHS_Logo.png') }}" alt="PNHS Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">PNHS System</span>
    </a>

    <div class="sidebar">
        <div class="user-panel d-flex align-items-center p-3">
            <div class="image">
                <img src="{{ asset('dist/img/avatar.png') }}" class="img-circle elevation-2"
                    style="height: 50px; width: 50px; object-fit: cover;" alt="User Image">
            </div>
            <div class="info ms-3">
                <a href="#" class="d-block fw-bold" style="font-size: 1rem;">
                    {{ auth()->user()->student ? auth()->user()->student->full_name_with_extension : auth()->user()->username }}
                </a>
                <span class="d-block text-muted" style="font-size: 0.85rem;">{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-collapse-hide-child nav-child-indent"
                data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('student.dashboard') }}" class="nav-link @yield('active-dashboard')">
                        <i class="nav-icon ion ion-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('student.attendance') }}" class="nav-link @yield('active-attendances')">
                        <i class="nav-icon ion ion-calendar"></i>
                        <p>Attendance</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('student.grades') }}" class="nav-link @yield('active-grades')">
                        <i class="nav-icon ion ion-document-text"></i>
                        <p>Grade</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('student.class-records') }}" class="nav-link @yield('active-class-records')">
                        <i class="nav-icon ion ion-ios-book"></i>
                        <p>Class Records</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('viewProfile') }}" class="nav-link @yield('active-profile')">
                        <i class="nav-icon ion ion-person"></i>
                        <p>Profile</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('logout') }}" class="nav-link">
                        <i class="nav-icon ion ion-log-out"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
