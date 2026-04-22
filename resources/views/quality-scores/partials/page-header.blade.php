{{-- ส่วนหัวหน้าสำหรับหน้า quality score --}}
<div class="quality-score-card">
    <div class="quality-score-card-header">
        <h4><i class="fas {{ $icon ?? 'fa-star' }} me-2"></i>{{ $title }}</h4>
        @isset($description)
            <p>{{ $description }}</p>
        @endisset
    </div>
</div>
