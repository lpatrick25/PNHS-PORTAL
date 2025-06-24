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
                <a href="#" class="d-block fw-bold" style="font-size: 1rem;">Administrator</a>
                <span class="d-block text-muted" style="font-size: 0.85rem;">{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-collapse-hide-child nav-child-indent"
                data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link @yield('active-dashboard')">
                        <i class="nav-icon ion ion-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.students') }}" class="nav-link @yield('active-student-list')">
                        <i class="nav-icon ion ion-university"></i>
                        <p>Students</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.teachers') }}" class="nav-link @yield('active-teacher-list')">
                        <i class="nav-icon ion ion-university"></i>
                        <p>Teachers</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.principals') }}" class="nav-link @yield('active-principal-list')">
                        <i class="nav-icon ion ion-university"></i>
                        <p>Principals</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.advisers') }}" class="nav-link @yield('active-adviser-list')">
                        <i class="nav-icon ion ion-university"></i>
                        <p>Advisers</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.subjects') }}" class="nav-link @yield('active-subject-list')">
                        <i class="nav-icon ion ion-university"></i>
                        <p>Subjects</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.teacherSubjects') }}" class="nav-link @yield('active-teacher-subject-list')">
                        <i class="nav-icon ion ion-university"></i>
                        <p>Teacher Subject</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings') }}" class="nav-link @yield('active-settings')">
                        <i class="nav-icon ion ion-person"></i>
                        <p>Settings</p>
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
