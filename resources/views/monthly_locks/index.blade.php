@extends('layouts.app')

@section('title', 'ปิดรอบประจำเดือน')
@section('header', 'จัดการปิดรอบประจำเดือน (Monthly Period Locks)')

@section('content')
<div class="row g-4">
    <!-- Lock Form Card -->
    <div class="col-md-5">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2">
                <i class="bi bi-lock-fill text-primary me-2"></i>ตั้งค่าปิด/เปิดรอบประจำเดือน
            </h5>

            <form method="POST" action="{{ route('monthly-locks.toggle') }}">
                @csrf

                <div class="mb-3">
                    <label for="year" class="form-label font-heading text-dark">ปี พ.ศ. / ค.ศ. <span class="text-danger">*</span></label>
                    <input type="number" name="year" id="year" class="form-control" value="{{ date('Y') }}" required>
                </div>

                <div class="mb-3">
                    <label for="month" class="form-label font-heading text-dark">เดือน <span class="text-danger">*</span></label>
                    <select name="month" id="month" class="form-select" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 10)) }} (เดือน {{ $m }})
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="mb-3">
                    <label for="department_id" class="form-label font-heading text-dark">แผนก (ถ้าไม่เลือกจะปิดทั้งองค์กร)</label>
                    <select name="department_id" id="department_id" class="form-select">
                        <option value="">-- ทั้งองค์กร (All Departments) --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name_th }} ({{ $dept->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="remarks" class="form-label font-heading text-dark">หมายเหตุ</label>
                    <input type="text" name="remarks" id="remarks" class="form-control" placeholder="เหตุผลการปิดรอบประจำเดือน...">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="action" value="LOCK" class="btn btn-danger w-50 fw-bold">
                        <i class="bi bi-lock-fill me-1"></i> ปิดรอบ (Lock)
                    </button>
                    <button type="submit" name="action" value="UNLOCK" class="btn btn-success w-50 fw-bold">
                        <i class="bi bi-unlock-fill me-1"></i> เปิดรอบ (Unlock)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Locks History List -->
    <div class="col-md-7">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2">
                <i class="bi bi-list-check text-primary me-2"></i>ประวัติการตั้งค่าปิดรอบประจำเดือน
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>งวดเดือน/ปี</th>
                            <th>แผนก</th>
                            <th>สถานะ</th>
                            <th>ผู้ดำเนินการ</th>
                            <th>วันที่ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locks as $lock)
                            <tr>
                                <td class="fw-bold text-dark">{{ $lock->month }}/{{ $lock->year }}</td>
                                <td>{{ $lock->department?->name_th ?? 'ทั้งองค์กร' }}</td>
                                <td>
                                    @if($lock->status === 'LOCKED')
                                        <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i> ปิดรอบแล้ว</span>
                                    @else
                                        <span class="badge bg-success"><i class="bi bi-unlock-fill me-1"></i> เปิดรอบ</span>
                                    @endif
                                </td>
                                <td class="fs-7 text-muted">{{ $lock->lockedBy?->name ?? '-' }}</td>
                                <td class="fs-7 text-muted">{{ $lock->locked_at ? $lock->locked_at->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">ยังไม่มีการตั้งค่าปิดรอบประจำเดือน</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $locks->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
