@extends('layouts.app')

@section('title', 'แผงควบคุมหลัก')
@section('header', 'แผงควบคุมและสรุปสถิติระบบ OT (Comprehensive Executive Dashboard)')

@section('content')
<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom p-4 shadow-sm border" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-color: #cbd5e1 !important;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary text-white px-3 py-1 fs-7 font-heading shadow-sm">
                            <i class="bi bi-shield-lock me-1"></i> {{ $user->roles->first()?->name ?? 'User' }}
                        </span>
                        <span class="fs-7 text-secondary fw-semibold">รหัสพนักงาน: {{ $user->emp_code }}</span>
                    </div>
                    <h3 class="fw-bold font-heading mb-1 text-dark">ยินดีต้อนรับ, {{ $user->name }}</h3>
                    <p class="mb-0 text-muted fs-7">
                        ระบบบริหารจัดการการขอทำงานล่วงเวลา (Enterprise Overtime Management System)
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('overtime.create') }}" class="btn btn-primary fw-bold font-heading shadow-sm px-3 py-2">
                        <i class="bi bi-plus-circle me-1"></i> สร้างคำขอ OT ใหม่
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comprehensive Period & Master Data Filters -->
<div class="card card-custom p-4 mb-4 border shadow-sm">
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
        <h6 class="fw-bold font-heading mb-0 text-dark">
            <i class="bi bi-sliders text-primary me-2"></i>ตัวกรองสถิติการใช้งาน (Dashboard Filters)
        </h6>
        <span class="badge bg-info-subtle text-info border border-info fs-7 font-heading">
            โหมดปัจจุบัน: 
            @if($periodType === 'daily')
                รายวัน ({{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }})
            @elseif($periodType === 'yearly')
                รายปี (ปี {{ $selectedYear }})
            @elseif($periodType === 'custom')
                กำหนดช่วงเวลา ({{ $startDate }} ถึง {{ $endDate }})
            @else
                รายเดือน (เดือน {{ $selectedMonth }}/{{ $selectedYear }})
            @endif
        </span>
    </div>

    <form method="GET" action="{{ route('dashboard') }}" id="filterForm" class="row g-3 align-items-end">
        <!-- Period Mode Switcher -->
        <div class="col-md-3">
            <label for="period_type" class="form-label font-heading fs-7 text-dark fw-bold">รูปแบบการแสดงผล</label>
            <select name="period_type" id="period_type" class="form-select form-select-sm fw-semibold">
                <option value="daily" {{ $periodType === 'daily' ? 'selected' : '' }}>📅 รายวัน (Daily)</option>
                <option value="monthly" {{ $periodType === 'monthly' ? 'selected' : '' }}>🗓️ รายเดือน (Monthly)</option>
                <option value="yearly" {{ $periodType === 'yearly' ? 'selected' : '' }}>📊 รายปี (Yearly)</option>
                <option value="custom" {{ $periodType === 'custom' ? 'selected' : '' }}>📆 กำหนดช่วงวันที่ (Custom Range)</option>
            </select>
        </div>

        <!-- Dynamic Controls -->
        <div class="col-md-3 period-input" id="input_daily" style="{{ $periodType === 'daily' ? '' : 'display:none;' }}">
            <label for="date" class="form-label font-heading fs-7 text-dark fw-bold">เลือกวันที่</label>
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $selectedDate }}">
        </div>

        <div class="col-md-3 period-input" id="input_monthly" style="{{ $periodType === 'monthly' ? '' : 'display:none;' }}">
            <div class="row g-1">
                <div class="col-6">
                    <label for="month" class="form-label font-heading fs-7 text-dark fw-bold">เดือน</label>
                    <select name="month" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>เดือน {{ $m }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-6">
                    <label for="year_m" class="form-label font-heading fs-7 text-dark fw-bold">ปี</label>
                    <select name="year" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-3 period-input" id="input_yearly" style="{{ $periodType === 'yearly' ? '' : 'display:none;' }}">
            <label for="year_y" class="form-label font-heading fs-7 text-dark fw-bold">เลือกปี</label>
            <select name="year" class="form-select form-select-sm">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>ปี {{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="col-md-3 period-input" id="input_custom" style="{{ $periodType === 'custom' ? '' : 'display:none;' }}">
            <div class="row g-1">
                <div class="col-6">
                    <label class="form-label font-heading fs-7 text-dark fw-bold">เริ่มวันที่</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-6">
                    <label class="form-label font-heading fs-7 text-dark fw-bold">ถึงวันที่</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
            </div>
        </div>

        <!-- Master Data Filters -->
        <div class="col-md-2">
            <label for="department_id" class="form-label font-heading fs-7 text-dark">แผนก</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">-- ทุกแผนก --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name_th }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label for="overtime_type_id" class="form-label font-heading fs-7 text-dark">ประเภท OT</label>
            <select name="overtime_type_id" class="form-select form-select-sm">
                <option value="">-- ทุกประเภท --</option>
                @foreach($overtimeTypes as $type)
                    <option value="{{ $type->id }}" {{ request('overtime_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name_th }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary w-100 font-heading fw-bold">
                <i class="bi bi-filter me-1"></i> กรองข้อมูล
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary" title="ล้างค่า">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-primary border-4 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted fs-7 font-heading">คำขอทั้งหมดในงวด</div>
                    <div class="fs-2 fw-bold text-dark font-heading">{{ $summary['totalRequests'] }} <span class="fs-6">รายการ</span></div>
                    <div class="fs-7 text-muted">อนุมัติแล้ว {{ $summary['approvedRequests'] }} รายการ</div>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-journal-text fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-success border-4 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted fs-7 font-heading">ชั่วโมง OT อนุมัติรวม</div>
                    <div class="fs-2 fw-bold text-success font-heading">{{ $summary['totalApprovedHours'] }} <span class="fs-6">ชม.</span></div>
                    <div class="fs-7 text-muted">ชั่วโมงที่ขอรวม: {{ $summary['totalPlannedHours'] }} ชม.</div>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-info border-4 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted fs-7 font-heading">พนักงานที่ทำ OT ในงวด</div>
                    <div class="fs-2 fw-bold text-info font-heading">{{ $summary['totalEmployeesCount'] }} <span class="fs-6">คน</span></div>
                    <div class="fs-7 text-muted">เฉลี่ย {{ $summary['totalEmployeesCount'] > 0 ? round($summary['totalApprovedHours'] / $summary['totalEmployeesCount'], 1) : 0 }} ชม./คน</div>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-warning border-4 shadow-sm h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted fs-7 font-heading">รออนุมัติ / ส่งกลับ</div>
                    <div class="fs-2 fw-bold text-warning font-heading">{{ $summary['pendingRequests'] }} <span class="fs-6">รายการ</span></div>
                    <div class="fs-7 text-muted">ถูกส่งกลับแก้ไข: {{ $summary['returnedRequests'] }} รายการ</div>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                    <i class="bi bi-exclamation-circle fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Charts Grid (Chart.js) -->
<div class="row g-4 mb-4">
    <!-- Main Trend Line Chart -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <h5 class="fw-bold font-heading mb-0 text-dark">
                    <i class="bi bi-graph-up text-primary me-2"></i>แนวโน้มชั่วโมง OT และจำนวนคำขอ
                </h5>
                <span class="fs-7 text-muted">
                    @if($periodType === 'daily') (สัปดาห์ปัจจุบัน) @elseif($periodType === 'yearly') (ประจำปี) @else (ประจำเดือน) @endif
                </span>
            </div>
            <div style="position: relative; height: 320px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Ratio Doughnut Chart -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 shadow-sm">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2 text-dark">
                <i class="bi bi-pie-chart text-primary me-2"></i>สัดส่วนสถานะคำขอ OT
            </h5>
            <div style="position: relative; height: 320px;">
                <canvas id="statusDoughnutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Department Hours Bar Chart -->
    <div class="col-lg-6">
        <div class="card card-custom p-4 shadow-sm">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2 text-dark">
                <i class="bi bi-bar-chart-steps text-primary me-2"></i>ชั่วโมง OT ที่อนุมัติแยกตามแผนก
            </h5>
            <div style="position: relative; height: 280px;">
                <canvas id="deptBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Overtime Types Comparison Bar Chart -->
    <div class="col-lg-6">
        <div class="card card-custom p-4 shadow-sm">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2 text-dark">
                <i class="bi bi-clock-split text-primary me-2"></i>เปรียบเทียบชั่วโมง OT แยกตามประเภท (Type)
            </h5>
            <div style="position: relative; height: 280px;">
                <canvas id="otTypeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Data Tables Section -->
<div class="row g-4 mb-4">
    <!-- Department Performance Summary Table -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2 text-dark">
                <i class="bi bi-building text-primary me-2"></i>สรุปสถิติ OT รายแผนกประจำงวด
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>แผนก</th>
                            <th class="text-center">คำขอทั้งหมด</th>
                            <th class="text-center">อนุมัติแล้ว</th>
                            <th class="text-center">รออนุมัติ</th>
                            <th class="text-end">ชั่วโมง OT อนุมัติ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deptStats as $dept)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-folder2-open text-primary me-1"></i> {{ $dept['name'] }} ({{ $dept['code'] }})
                                </td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $dept['total_requests'] }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $dept['approved_requests'] }}</span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark">{{ $dept['pending_requests'] }}</span></td>
                                <td class="text-end fw-bold text-primary">{{ $dept['approved_hours'] }} ชม.</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลสถิติแผนก</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top 5 OT Employees Table -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 shadow-sm">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2 text-dark">
                <i class="bi bg-star-fill me-1 text-warning"></i> Top 5 พนักงาน OT สูงสุด
            </h5>
            <div class="list-group list-group-flush">
                @forelse($topEmployees as $index => $top)
                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge rounded-pill bg-primary me-2">{{ $index + 1 }}</span>
                            <span class="fw-bold text-dark fs-7">{{ $top->employee?->full_name }}</span>
                            <div class="fs-7 text-muted ms-4">{{ $top->employee?->department?->name_th }}</div>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-primary">{{ $top->total_hours }} ชม.</span>
                            <div class="fs-7 text-muted">({{ $top->total_requests }} คำขอ)</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted fs-7">ยังไม่มีข้อมูลชั่วโมง OT อนุมัติ</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Requests Table -->
    <div class="col-12">
        <div class="card card-custom p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold font-heading mb-0 text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>รายการคำขอ OT ล่าสุด
                </h5>
                <a href="{{ route('overtime.index') }}" class="btn btn-sm btn-outline-primary">ดูคำขอทั้งหมด</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>เลขที่เอกสาร</th>
                            <th>วันที่ทำ OT</th>
                            <th>แผนก</th>
                            <th>ประเภท OT</th>
                            <th>เวลา</th>
                            <th>จำนวนคน</th>
                            <th>ชั่วโมงรวม</th>
                            <th>สถานะ</th>
                            <th class="text-end">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $req)
                            <tr>
                                <td class="fw-bold text-primary">{{ $req->document_no }}</td>
                                <td class="fw-semibold">{{ $req->request_date->format('d/m/Y') }}</td>
                                <td>{{ $req->department?->name_th }}</td>
                                <td><span class="badge bg-secondary">{{ $req->overtimeType?->name_th }}</span></td>
                                <td class="fs-7">{{ substr($req->start_time, 0, 5) }} - {{ substr($req->end_time, 0, 5) }} น.</td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $req->employees->count() }} คน</span></td>
                                <td class="fw-bold text-dark">{{ $req->total_hours }} ชม.</td>
                                <td><span class="badge {{ $req->status->badgeClass() }}">{{ $req->status->label() }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('overtime.show', $req) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">ไม่พบรายการคำขอ OT ล่าสุด</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Period Filter Controls
        const periodSelect = document.getElementById('period_type');
        const periodInputs = document.querySelectorAll('.period-input');

        periodSelect.addEventListener('change', function() {
            const val = this.value;
            periodInputs.forEach(el => el.style.display = 'none');
            const activeInput = document.getElementById('input_' + val);
            if (activeInput) activeInput.style.display = '';
        });

        // 1. Trend Line Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($trendChart['labels']),
                datasets: [
                    {
                        label: 'ชั่วโมง OT อนุมัติ (ชม.)',
                        data: @json($trendChart['approvedHours']),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'จำนวนคำขอ (รายการ)',
                        data: @json($trendChart['totalRequests']),
                        borderColor: '#f59e0b',
                        borderDash: [5, 5],
                        backgroundColor: 'transparent',
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { type: 'linear', display: true, position: 'left', beginAtZero: true, title: { display: true, text: 'ชั่วโมง (ชม.)' } },
                    y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, beginAtZero: true, title: { display: true, text: 'จำนวนรายการ' } }
                }
            }
        });

        // 2. Status Ratio Doughnut Chart
        const statusCtx = document.getElementById('statusDoughnutChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['อนุมัติแล้ว', 'รออนุมัติ', 'ร่างคำขอ', 'ส่งกลับแก้ไข', 'ปฏิเสธ'],
                datasets: [{
                    data: [
                        {{ $summary['approvedRequests'] }},
                        {{ $summary['pendingRequests'] }},
                        {{ $summary['draftRequests'] }},
                        {{ $summary['returnedRequests'] }},
                        {{ $summary['rejectedRequests'] }}
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#6b7280', '#eab308', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // 3. Department Bar Chart
        const deptCtx = document.getElementById('deptBarChart').getContext('2d');
        const deptNames = @json(collect($deptStats)->pluck('name'));
        const deptHours = @json(collect($deptStats)->pluck('approved_hours'));
        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: deptNames,
                datasets: [{
                    label: 'ชั่วโมง OT อนุมัติ (ชม.)',
                    data: deptHours,
                    backgroundColor: '#3b82f6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // 4. Overtime Type Bar Chart
        const otTypeCtx = document.getElementById('otTypeChart').getContext('2d');
        const otTypeNames = @json(collect($otTypeStats)->pluck('name'));
        const otTypeHours = @json(collect($otTypeStats)->pluck('hours'));
        new Chart(otTypeCtx, {
            type: 'bar',
            data: {
                labels: otTypeNames,
                datasets: [{
                    label: 'ชั่วโมง OT รวม (ชม.)',
                    data: otTypeHours,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    });
</script>
@endpush
@endsection
