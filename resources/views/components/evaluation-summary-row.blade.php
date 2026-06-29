<tr data-evaluation-row data-status-group="{{ $row['status_group'] }}" class="transition-colors hover:bg-gray-50 {{ $row['is_recent'] ? 'bg-blue-50' : '' }}">
    <td class="border-b p-4 text-gray-500">
        {{ $row['index'] }}
        @if ($row['is_recent'])
            <span class="ml-2 inline-block h-2 w-2 rounded-full bg-blue-500" title="รายการล่าสุด"></span>
        @endif
    </td>

    <td class="border-b p-4">
        <div class="font-medium text-gray-800">{{ $row['title'] }}</div>
        @if ($row['is_recent'])
            <div class="mt-1 text-xs text-blue-600">รายการล่าสุด</div>
        @endif
    </td>

    <td class="border-b p-4 text-gray-500">
        @if ($row['start'] !== '-')
            <div class="font-medium">{{ $row['start']['date'] }}</div>
            <div class="text-xs text-gray-400">{{ $row['start']['time'] }}</div>
        @else
            -
        @endif
    </td>

    <td class="border-b p-4 text-gray-500">
        @if ($row['end'] !== '-')
            <div class="font-medium">{{ $row['end']['date'] }}</div>
            <div class="text-xs text-gray-400">{{ $row['end']['time'] }}</div>
        @else
            -
        @endif
    </td>

    <td class="min-w-[150px] border-b p-4 align-top text-gray-500">
        <div class="max-w-[280px]">
            @if ($row['primary_reviewer'])
                <div class="break-words font-medium text-gray-700">
                    {{ $row['primary_reviewer']['name'] }}
                </div>
                @if ($row['additional_reviewer_count'] > 0)
                    <button
                        type="button"
                        data-evaluatee-reviewer-open
                        data-evaluatee-reviewers='@json($row["reviewers"])'
                        class="mt-1 text-xs font-medium text-blue-600 underline hover:text-blue-800"
                    >
                        เพิ่มเติม ({{ $row['additional_reviewer_count'] }} คน)
                    </button>
                @endif
            @else
                -
            @endif
        </div>
    </td>

    <td class="min-w-[180px] border-b px-2 py-4 text-center">
        <span class="rounded-full px-3 py-1 text-sm font-medium {{ $row['status_classes'] }}">
            {{ $row['status'] }}
        </span>
    </td>

    <td class="border-b p-4 text-center">
        @if ($row['action'])
            <a
                href="{{ $row['url'] }}"
                class="inline-block min-w-[140px] rounded-xl px-4 py-2 text-sm font-medium shadow transition duration-200 {{ $row['action']['classes'] }}">
                {{ $row['action']['label'] }}
            </a>
        @else
            <span class="text-sm text-gray-400">-</span>
        @endif
    </td>
</tr>
