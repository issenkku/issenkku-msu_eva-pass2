@forelse(($workloadView['groups'] ?? collect()) as $groupView)
    <section class="workload-panel">
        <div class="workload-panel-header">
            <h2>{{ $groupView['name'] ?? '' }}</h2>
        </div>
        <div class="workload-panel-body">
            @forelse(($groupView['items'] ?? []) as $itemView)
                <details class="workload-item-dropdown">
                    <summary class="workload-item-summary">
                        <div class="workload-item-summary-left">
                            <span class="workload-item-summary-title">{{ $itemView['name'] ?? '-' }}</span>
                        </div>
                        <div class="workload-item-summary-right">
                            <span class="workload-item-summary-hint">กดเพื่อดูรายละเอียด</span>
                            <span class="workload-item-summary-icon" aria-hidden="true"></span>
                        </div>
                    </summary>
                    <div class="workload-subtable">
                        <div class="workload-table-wrap">
                            <table class="workload-table">
                                <thead>
                                    <tr>
                                        <th>กิจกรรม/โครงการ/งาน</th>
                                        @if(!empty($itemView['requires_subject']))
                                            <th>รายวิชาที่เลือก</th>
                                        @endif
                                        @if(!empty($itemView['show_level_column']))
                                            <th>ระดับ</th>
                                        @endif
                                        @forelse(($itemView['table_fields'] ?? []) as $fieldView)
                                            <th>{{ $fieldView['label'] ?? '-' }}</th>
                                        @empty
                                            <th>-</th>
                                        @endforelse
                                        <th>ภาระงาน</th>
                                        <th>ลิงก์เอกสาร</th>
                                        <th>
                                            @unless($readonly)
                                                <button
                                                    class="workload-mini-btn workload-add-btn"
                                                    type="button"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#workloadAddModal"
                                                    data-default-form-id="{{ $itemView['form_id'] ?? '' }}"
                                                    data-group-id="{{ $groupView['id'] }}"
                                                    data-group-name="{{ $groupView['name'] ?? '' }}"
                                                    data-requires-subject="{{ !empty($itemView['requires_subject']) ? 1 : 0 }}"
                                                    data-item-id="{{ $itemView['id'] }}"
                                                >
                                                    <i class="fas fa-plus"></i>
                                                    เพิ่มข้อมูล
                                                </button>
                                            @endunless
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($itemView['rows'] ?? []) as $rowView)
                                        <tr>
                                            <td>{{ $rowView['item_name'] ?? '-' }}</td>
                                            @if(!empty($rowView['requires_subject']))
                                                <td>{{ $rowView['subject_display'] ?? '-' }}</td>
                                            @endif
                                            @if(!empty($rowView['show_level_column']))
                                                <td>
                                                    {{ $rowView['level_text'] ?? '-' }}
                                                    @if(($rowView['item_score'] ?? null) !== null)
                                                        <span class="workload-item-score">({{ $rowView['item_score'] }})</span>
                                                    @endif
                                                </td>
                                            @endif
                                            @forelse(($rowView['display_columns'] ?? []) as $columnView)
                                                <td>{{ $columnView['value'] ?? '-' }}</td>
                                            @empty
                                                <td>-</td>
                                            @endforelse
                                            <td>{{ $rowView['score_display'] ?? '-' }}</td>
                                            <td>
                                                @forelse(($rowView['evidence_links'] ?? collect()) as $link)
                                                    <a href="{{ $link }}" target="_blank" rel="noopener noreferrer">{{ $link }}</a><br>
                                                @empty
                                                    -
                                                @endforelse
                                            </td>
                                            <td>@include('evaluatee.partials.workload-row-actions')</td>
                                        </tr>
                                    @empty
                                        @include('evaluatee.partials.workload-item-empty-row')
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            @empty
                @include('evaluatee.partials.workload-group-empty-table')
            @endforelse

            <div class="workload-total-row">
                <span>รวมภาระงาน</span>
                <span class="workload-total-value">{{ $groupView['group_total_score'] ?? 0 }}</span>
            </div>
        </div>
    </section>
@empty
    @include('evaluatee.partials.workload-groups-empty-state')
@endforelse
