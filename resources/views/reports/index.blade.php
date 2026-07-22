@extends('layouts.app')

@section('title', 'รายงานและสถิติ')
@section('header', 'ศูนย์รายงานและสถิติ (Comprehensive Reports Engine)')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2">
                <i class="bi bi-bar-chart-line text-primary me-2"></i>เลือกรูปแบบรายงานที่ต้องการสรุปข้อมูล (16 รายงาน)
            </h5>

            <div class="row g-3">
                @foreach($reportTypes as $key => $title)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-custom h-100 p-3 hover-shadow border border-light-subtle">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary-subtle text-primary font-heading fs-7">REPORT #{{ $loop->iteration }}</span>
                                <i class="bi bi-file-earmark-bar-graph text-secondary fs-4"></i>
                            </div>
                            <h6 class="fw-bold font-heading text-dark mb-2">{{ $title }}</h6>
                            <p class="fs-7 text-muted mb-3">สรุปสถิติข้อมูล OT ส่งออกเป็นไฟล์ Excel และ PDF ได้</p>
                            <div class="mt-auto">
                                <a href="{{ route('reports.show', $key) }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> เรียกดูรายงาน
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
