@extends('layouts.app')

@section('title', 'แก้ไขตำแหน่ง')
@section('header', 'แก้ไขตำแหน่ง (Edit Position)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-pencil-square text-primary me-2"></i>แก้ไขตำแหน่ง: {{ $position->title_th }}
            </h5>

            <form method="POST" action="{{ route('admin.positions.update', $position) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="code" class="form-label font-heading text-dark">รหัสตำแหน่ง <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $position->code) }}" required>
                    @error('code')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="title_th" class="form-label font-heading text-dark">ชื่อตำแหน่ง (ภาษาไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title_th') is-invalid @enderror" id="title_th" name="title_th" value="{{ old('title_th', $position->title_th) }}" required>
                    @error('title_th')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="title_en" class="form-label font-heading text-dark">ชื่อตำแหน่ง (ภาษาอังกฤษ)</label>
                    <input type="text" class="form-control" id="title_en" name="title_en" value="{{ old('title_en', $position->title_en) }}">
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $position->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label font-heading text-dark" for="is_active">เปิดใช้งานตำแหน่งนี้</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.positions.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> อัปเดตข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
