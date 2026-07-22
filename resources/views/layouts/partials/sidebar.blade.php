@php
    $companyLogo = \App\Models\SystemSetting::get('company_logo');
    $companyName = \App\Models\SystemSetting::get('company_name', 'Enterprise OT');
@endphp
<div id="sidebar-wrapper">
    <div class="sidebar-heading d-flex align-items-center gap-2 py-3 px-3 border-bottom">
        @if($companyLogo && file_exists(public_path($companyLogo)))
            <img src="{{ asset($companyLogo) }}" alt="Logo" style="height: 32px; max-width: 40px; object-fit: contain;">
        @else
            <i class="bi bi-clock-history fs-4 text-primary"></i>
        @endif
        <span class="fw-bold font-heading text-dark text-truncate">{{ $companyName }}</span>
    </div>
    
    <div class="list-group list-group-flush my-2">
        <!-- Dashboard Link -->
        <a href="{{ route('dashboard') }}" class="list-group-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 fs-5"></i>
            <span>แผงควบคุม (Dashboard)</span>
        </a>

        <!-- OT Request Menu (Phase 3 & 4) -->
        <a href="{{ route('overtime.index') }}" class="list-group-item {{ request()->routeIs('overtime.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-plus fs-5"></i>
            <span>ยื่นคำขอ OT (OT Requests)</span>
        </a>

        <!-- OT Approval Menu (Phase 5) -->
        @hasanyrole('Manager|Super Admin')
            <a href="{{ route('approvals.index') }}" class="list-group-item {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                <i class="bi bi-check2-square fs-5"></i>
                <span>พิจารณาอนุมัติ (Manager Inbox)</span>
            </a>
        @endhasanyrole

        <!-- Monthly Period Lock & Payroll Export (Phase 7 - HR & Admin) -->
        @hasanyrole('HR|Super Admin')
            <div class="px-3 pt-3 pb-1 text-uppercase fs-7 text-muted fw-bold">จัดการรอบเวลาและเงินเดือน</div>
            
            <a href="{{ route('monthly-locks.index') }}" class="list-group-item {{ request()->routeIs('monthly-locks.*') ? 'active' : '' }}">
                <i class="bi bi-lock-fill fs-5"></i>
                <span>ปิดรอบประจำเดือน (Period Locks)</span>
            </a>

            <a href="{{ route('payroll.index') }}" class="list-group-item {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                <i class="bi bi-currency-dollar fs-5"></i>
                <span>ส่งออกเงินเดือน (Payroll Export)</span>
            </a>
        @endhasanyrole

        <!-- Reports Menu (Phase 6) -->
        <a href="{{ route('reports.index') }}" class="list-group-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line fs-5"></i>
            <span>รายงานและสถิติ (Reports Engine)</span>
        </a>

        <!-- Notifications Link -->
        <a href="{{ route('notifications.index') }}" class="list-group-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell fs-5"></i>
            <span>การแจ้งเตือน (Notifications)</span>
        </a>

        <!-- Master Data Section -->
        @hasanyrole('Super Admin|HR')
            <div class="px-3 pt-3 pb-1 text-uppercase fs-7 text-muted fw-bold">จัดการข้อมูลหลัก (Master Data)</div>
            
            <a href="{{ route('admin.departments.index') }}" class="list-group-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                <i class="bi bi-building fs-5"></i>
                <span>จัดการแผนก (Departments)</span>
            </a>

            <a href="{{ route('admin.teams.index') }}" class="list-group-item {{ request()->routeIs('admin.teams.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3 fs-5"></i>
                <span>จัดการทีม (Teams)</span>
            </a>

            <a href="{{ route('admin.positions.index') }}" class="list-group-item {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}">
                <i class="bi bi-briefcase fs-5"></i>
                <span>ตำแหน่งงาน (Positions)</span>
            </a>

            <a href="{{ route('admin.employees.index') }}" class="list-group-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                <i class="bi bi-person-vcard fs-5"></i>
                <span>ข้อมูลพนักงาน (Employees)</span>
            </a>

            <a href="{{ route('admin.overtime-types.index') }}" class="list-group-item {{ request()->routeIs('admin.overtime-types.*') ? 'active' : '' }}">
                <i class="bi bi-clock-split fs-5"></i>
                <span>ประเภท OT (OT Types)</span>
            </a>

            <a href="{{ route('admin.holidays.index') }}" class="list-group-item {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event fs-5"></i>
                <span>วันหยุดองค์กร (Holidays)</span>
            </a>

            @role('Super Admin')
                <div class="px-3 pt-3 pb-1 text-uppercase fs-7 text-muted fw-bold">การดูแลระบบ & ความปลอดภัย</div>
                
                <a href="{{ route('admin.users.index') }}" class="list-group-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people fs-5"></i>
                    <span>จัดการผู้ใช้งาน (Users)</span>
                </a>

                <a href="{{ route('admin.audit-logs.index') }}" class="list-group-item {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check fs-5"></i>
                    <span>ประวัติใช้งานระบบ (Audit Logs)</span>
                </a>

                <a href="{{ route('admin.settings.index') }}" class="list-group-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear fs-5"></i>
                    <span>ตั้งค่าระบบ (Settings)</span>
                </a>
            @endrole
        @endhasanyrole

        <!-- User Profile & Manual -->
        <div class="px-3 pt-3 pb-1 text-uppercase fs-7 text-muted fw-bold">ช่วยเหลือ & บัญชีผู้ใช้</div>
        <a href="{{ route('manual') }}" class="list-group-item {{ request()->routeIs('manual') ? 'active' : '' }}">
            <i class="bi bi-book fs-5 text-info"></i>
            <span>คู่มือการใช้งาน (Manual)</span>
        </a>
        <a href="{{ route('profile.index') }}" class="list-group-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-circle fs-5"></i>
            <span>ข้อมูลส่วนตัว (Profile)</span>
        </a>
    </div>
</div>
