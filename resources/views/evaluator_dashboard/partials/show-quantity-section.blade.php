{{-- ส่วนเกณฑ์เชิงปริมาณ --}}
@php
    $quantityLists = $category->evaluationLists->filter(function ($list) {
        return $list->quantitySubCriterias->isNotEmpty();
    });
@endphp

@if ($quantityLists->isNotEmpty())
    <div class="criteria-section quantity-section">
        <div class="criteria-header">
            <h5>จำนวนชั่วโมงการสอน</h5>
            <span class="criteria-subtitle">ข้อมูลจากจำนวนชั่วโมงการสอนจริงในแต่ละภาคการศึกษา</span>
        </div>
        <div class="table-container">
            <table class="evaluation-table">
                <thead>
                    <tr>
                        <th>ชื่อเกณฑ์ย่อย</th>
                        <th>ค่าน้ำหนัก (A)</th>
                        <th>ภาระงานมาตรฐาน (B)</th>
                        <th>ภาระงานที่ทำได้ (C)</th>
                        <th>คำนวณ (D = A x C / B)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quantityLists as $list)
                        @foreach ($list->quantitySubCriterias as $criteria)
                            @php
                                $scoreA = $criteria->score_a;
                                $scoreB = $criteria->score_b;
                                $scoreC = $criteria->score_c ?? null;
                                $scoreD = $criteria->score_d ?? null;
                            @endphp
                            <tr>
                                <td class="text-left">{{ $criteria->name }}</td>
                                <td>{{ number_format($scoreA) }}</td>
                                <td>{{ number_format($scoreB) }}</td>
                                <td>{{ $scoreC !== null ? number_format($scoreC) : '-' }}</td>
                                <td>{{ $scoreD !== null ? number_format($scoreD) : '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="evidence-box">
            <h6>ลิงก์หลักฐาน:</h6>
            <ul>
                @if (!empty($quantityLists[0]->quantitySubCriterias[0]->evidence_link))
                    <li>
                        <a href="{{ $quantityLists[0]->quantitySubCriterias[0]->evidence_link }}" target="_blank">ดูหลักฐาน</a>
                    </li>
                @else
                    <li>ไม่มีหลักฐาน</li>
                @endif
            </ul>
        </div>
    </div>
@endif
