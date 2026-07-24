@props(['histories' => []])

@if(!empty($histories))
    <details class="mt-3 rounded-lg border border-slate-200 bg-white p-3">
        <summary class="cursor-pointer text-sm font-semibold text-slate-700">
            ประวัติการแก้ไข ({{ count($histories) }})
        </summary>
        <div class="space-y-3">
            @foreach($histories as $history)
                <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                    <p>
                        {{ $history['previous_value'] ?? 'ไม่มีคะแนน' }}
                        <span aria-hidden="true">→</span>
                        {{ $history['new_value'] ?? 'ไม่มีคะแนน' }}
                    </p>
                    <p class="mt-1">เหตุผล: {{ $history['reason'] ?? '-' }}</p>
                    <p class="mt-1 text-xs text-slate-500">
                        แก้ไขโดย {{ $history['modified_by_name'] ?: '-' }}
                        @if(!empty($history['modified_by_role']))
                            ({{ $history['modified_by_role'] }})
                        @endif
                        @if(!empty($history['created_at']))
                            · {{ $history['created_at'] }}
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
    </details>
@endif
