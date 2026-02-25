@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\quality-scores\show.blade.php --}}
@section('title', 'แสดงคะแนนคุณภาพ')
@section('content')
    {{-- บล็อกเนื้อหา --}}
    <div class="container-fluid">
        {{-- บล็อกเนื้อหา --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-star me-2"></i>รายละเอียดคะแนนคุณภาพ</h4>
                    </div>
                    <div class="card-body">
                        @if($qualityScore)
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>ผู้ใช้งาน:</strong>
                                    <p>{{ $qualityScore->user->name ?? 'N/A' }}</p>
                                    
                                    <strong>อีเมล:</strong>
                                    <p>{{ $qualityScore->user->email ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>เกณฑ์การประเมิน:</strong>
                                    <p>{{ $qualityScore->qualitySubCriteria->name ?? 'N/A' }}</p>
                                    
                                    <strong>หมวดหมู่หลัก:</strong>
                                    <p>{{ $qualityScore->qualitySubCriteria->qualityMainCriteria->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <strong>คะแนน:</strong>
                                    <h3 class="text-primary">{{ number_format($qualityScore->score, 1) }}</h3>
                                </div>
                            </div>
                        @endif
                        
                        <div class="mt-4">
                            <a href="{{ route('quality-scores.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>กลับ
                            </a>
                            <a href="{{ route('quality-scores.edit', $qualityScore->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>แก้ไข
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
