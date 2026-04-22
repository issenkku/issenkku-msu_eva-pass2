{{-- ส่วนเกณฑ์เชิงคุณภาพ --}}
@php
    $qualityLists = $category->evaluationLists->filter(function ($list) {
        return $list->qualitySubCriterias->isNotEmpty();
    });
@endphp

@if ($qualityLists->isNotEmpty())
    @foreach ($qualityLists as $listcard)
        <div class="criteria-section quality-section">
            <div class="criteria-header">
                <h5>{{ $listcard->name }}</h5>
                <span class="criteria-subtitle">{{ $listcard->annotation }}</span>
            </div>
            <div class="table-container">
                <table class="evaluation-table">
                    <thead>
                        <tr>
                            <th>ชื่อเกณฑ์ย่อย</th>
                            <th>คะแนนเต็ม</th>
                            <th>คะแนนที่ให้</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $currentMainId = '';
                        @endphp
                        @foreach ($listcard->qualitySubCriterias as $listEva)
                            @php
                                $mainId = $listEva->mainCriteria->id;
                            @endphp
                            @if ($listEva->quality_main_criteria_id !== $currentMainId)
                                @php
                                    $currentMainId = $mainId;
                                @endphp
                                <tr class="bg-lime-100">
                                    <td class="text-left">{{ $listEva->mainCriteria->name }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-left criteria-name-cell">
                                    <div class="criteria-name">{{ $listEva->name }}</div>
                                    @if (!empty($listEva->description))
                                        <div class="criteria-description">{!! $listEva->description !!}</div>
                                    @else
                                        <div class="text-gray-500">ไม่มีคำอธิบาย</div>
                                    @endif
                                </td>
                                <td>{{ number_format($listEva->num_score, 2) }}</td>
                                <td>{{ is_numeric($listEva->filled_score) ? number_format($listEva->filled_score, 2) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="evidence-box">
                <h6>ลิงก์หลักฐาน:</h6>
                <ul>
                    @if (!empty($listcard->qualitySubCriterias[0]->evidence_link))
                        <li>
                            <a href="{{ $listcard->qualitySubCriterias[0]->evidence_link }}" target="_blank">ดูหลักฐาน</a>
                        </li>
                    @else
                        <li>ไม่มีหลักฐาน</li>
                    @endif
                </ul>
            </div>
        </div>
    @endforeach
@endif
