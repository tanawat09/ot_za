@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งาน')
@section('header', 'จัดการผู้ใช้งาน (User Management)')

@section('content')
<div class="card card-custom p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-people-fill text-primary me-2"></i>รายชื่อผู้ใช้งานทั้งหมด
        </h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> เพิ่มผู้ใช้งานใหม่
        </a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ, อีเมล หรือรหัสพนักงาน..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">-- บทบาททั้งหมด --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- สถานะทั้งหมด --</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>เปิดใช้งาน (Active)</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>ปิดใช้งาน (Inactive)</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> ค้นหา</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>รหัสพนักงาน</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>อีเมล</th>
                    <th>บทบาท (Role)</th>
                    <th>สถานะ</th>
                    <th>เข้าใช้งานล่าสุด</th>
                    <th class="text-end">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="fw-bold text-muted">{{ $user->emp_code ?? '-' }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $user->name }}</div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $r)
                                <span class="badge bg-primary badge-role me-1">{{ $r->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i> Active
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle-fill me-1"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="fs-7 text-muted">
                            {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'ยังไม่เคยเข้าใช้' }}
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline" onsubmit="return confirm('ยืนยันเปลี่ยนสถานะบัญชีนี้?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $user->is_active ? 'ระงับการใช้งาน' : 'เปิดใช้งาน' }}">
                                        <i class="bi {{ $user->is_active ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="d-inline" onsubmit="return confirm('ยืนยันรีเซ็ตรหัสผ่านของผู้ใช้นี้เป็น Password123!?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="รีเซ็ตรหัสผ่าน">
                                        <i class="bi bi-key"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลผู้ใช้งาน</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $users->withQueryString()->links() }}
    </div>
</div>
@endsection
