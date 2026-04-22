{{-- การ์ดหมวดหมู่หลัก --}}
<div class="category-card">
    <div class="category-header">
        <h4>{{ $category->main_categories }}</h4>
        <div class="category-info">
            <div class="category-detail">
                <span class="detail-label">ชื่อรายการ:</span>
                <span>{{ $category->evaluationLists->first()->name ?? '-' }}</span>
                @if ($category->evaluationLists->sum('sum_score'))
                    <span class="score-badge">คะแนนรวม: {{ number_format($category->evaluationLists->sum('sum_score'), 2) }}</span>
                @endif
            </div>
            <div class="category-detail">
                <span class="detail-label">หมายเหตุ:</span>
                <span>{{ $category->evaluationLists->first()->annotation ?? '-' }}</span>
            </div>
        </div>
    </div>

    @include('evaluator_dashboard.partials.show-quantity-section', ['category' => $category])
    @include('evaluator_dashboard.partials.show-quality-section', ['category' => $category])
</div>
