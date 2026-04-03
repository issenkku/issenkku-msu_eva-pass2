{{-- ตารางรายงานเกณฑ์คุณภาพตามรายงานการประเมิน --}}
<div class="table-container">
    <div class="table-header">
        <h4><i class="fas fa-table me-2"></i>รายการการประเมินคุณภาพ</h4>
    </div>

    <div class="table-responsive">
        @if(isset($reportDatas) && $reportDatas->count() > 0)
            @foreach($reportDatas as $reportData)
                <div class="mb-4">
                    <div class="version-header">
                        <h5>
                            <i class="fas fa-clipboard-list"></i>
                            {{ $reportData->report_title }}
                        </h5>
                    </div>

                    @if($reportData->criteriaVersion && $reportData->criteriaVersion->qualityMainCriterias->count() > 0)
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th style="width: 15%">ลำดับ</th>
                                    <th style="width: 40%">หมวดหมู่หลัก</th>
                                    <th style="width: 35%">เกณฑ์ย่อย</th>
                                    <th style="width: 10%">น้ำหนัก (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData->criteriaVersion->qualityMainCriterias as $mainIndex => $mainCriteria)
                                    @php
                                        $subCriteriasCount = $mainCriteria->qualitySubCriterias->count();
                                    @endphp

                                    @foreach($mainCriteria->qualitySubCriterias as $subIndex => $subCriteria)
                                        <tr>
                                            <td>{{ $mainIndex + 1 }}.{{ $subIndex + 1 }}</td>

                                            @if($subIndex === 0)
                                                <td rowspan="{{ $subCriteriasCount }}" class="align-middle" style="background-color: #f8f9fa; border-right: 2px solid #dee2e6;">
                                                    <strong>{{ $mainCriteria->name }}</strong>
                                                    @if($mainCriteria->tooltips)
                                                        <br><div>{!! $mainCriteria->tooltips !!}</div>
                                                    @endif
                                                </td>
                                            @endif

                                            <td>
                                                <strong>{{ $subCriteria->name }}</strong>
                                            </td>

                                            @if($subIndex === 0)
                                                <td rowspan="{{ $subCriteriasCount }}" class="align-middle text-center" style="background-color: #f8f9fa; border-right: 2px solid #dee2e6;">
                                                    <span class="badge bg-secondary">{{ $mainCriteria->ratio }}%</span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ยังไม่มีเกณฑ์การประเมินในรายงานนี้
                        </div>
                    @endif
                </div>

                @if(!$loop->last)
                    <hr class="version-divider">
                @endif
            @endforeach
        @else
            {{-- empty state ใช้เมื่อยังไม่มีรายงานที่พร้อมแสดงเกณฑ์คุณภาพ --}}
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h5>ยังไม่มีรายการการประเมิน</h5>
                <p>กรุณาสร้างรายงานการประเมินก่อน</p>
            </div>
        @endif
    </div>
</div>
