{{-- ส่วนเกณฑ์เชิงคุณภาพแบบกรอกคะแนน --}}
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
                            @if ($listEva->quality_main_criteria_id === $currentMainId)
                                <tr>
                                    <td class="text-left">{{ $listEva->name }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="scores[{{ $listEva->id }}]"
                                            value=""
                                            min="0"
                                            max="{{ $listEva->num_score }}"
                                            step="0.1"
                                            class="score-input @error('scores.' . $listEva->id) is-invalid @enderror"
                                        />
                                        @error('scores.' . $listEva->id)
                                            <div class="invalid-feedback" style="color: red; font-size: 0.875rem;">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </td>
                                </tr>
                            @else
                                @php
                                    $currentMainId = $mainId;
                                @endphp
                                <tr class="bg-lime-100">
                                    <td class="text-left">{{ $listEva->mainCriteria->name }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-left">{{ $listEva->name }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="scores[{{ $listEva->id }}]"
                                            value=""
                                            min="0"
                                            max="{{ $listEva->num_score }}"
                                            step="0.1"
                                            class="score-input @error('scores.' . $listEva->id) is-invalid @enderror"
                                        />
                                        @error('scores.' . $listEva->id)
                                            <div class="invalid-feedback" style="color: red; font-size: 0.875rem;">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="evidence-box">
                <h6>ลิงก์หลักฐาน:</h6>
                <ul>
                    @if (!empty($listcard->qualitySubCriterias[0]->evidence_links[0]))
                        <li>
                            <a href="{{ $listcard->qualitySubCriterias[0]->evidence_links[0] }}" target="_blank">ดูหลักฐาน</a>
                        </li>
                    @else
                        <li>ไม่มีหลักฐาน</li>
                    @endif
                </ul>
            </div>
        </div>
    @endforeach
@endif
