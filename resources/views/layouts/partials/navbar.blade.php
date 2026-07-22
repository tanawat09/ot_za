<nav class="navbar navbar-expand-lg navbar-custom border-bottom">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold text-dark fs-5">
            @yield('header', 'ระบบบริหารจัดการการขอทำงานล่วงเวลา')
        </span>

        <div class="d-flex align-items-center gap-3">
            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                        {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="text-start d-none d-md-block">
                        <div class="fw-semibold text-dark fs-6">{{ Auth::user()->name }}</div>
                        <div class="text-muted fs-7">
                            <span class="badge bg-secondary badge-role">
                                {{ Auth::user()->roles->first()?->name ?? 'User' }}
                            </span>
                        </div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.index') }}">
                            <i class="bi bi-person"></i> ข้อมูลส่วนตัว
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
