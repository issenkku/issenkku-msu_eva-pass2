{{-- แถวเดียวของตาราง list โดยรับข้อมูลที่จัดรูปเสร็จแล้วจาก controller --}}
<tr class="hover:bg-gray-50 transition-all">
    <td class="px-4 py-3 align-top">
        {{-- วันที่ถูก format มาแล้วจาก controller จึง render ได้ตรง ๆ --}}
        <div class="text-sm font-medium text-gray-900 break-words leading-snug">
            {{ $assignmentRow['date_range']['start'] }} <br><span class="text-gray-400">-</span> {{ $assignmentRow['date_range']['end'] }}
        </div>
        <div class="text-xs text-gray-500 mt-0.5">
            {{ $assignmentRow['date_range']['days'] }} วัน
        </div>
    </td>

    <td class="px-4 py-3 align-top">
        {{-- title และ description ถูก bundle มาใน key report เพื่อลด logic ใน view --}}
        <div class="text-sm font-medium text-gray-800 max-w-[250px]">
            {{ $assignmentRow['report']['title'] }}
        </div>
        @if ($assignmentRow['report']['description'])
            <div class="text-xs text-gray-500 mt-1 truncate max-w-[250px]">
                {{ Str::limit($assignmentRow['report']['description'], 60) }}
            </div>
        @endif
    </td>

    <td class="px-4 py-3 align-top">
        {{-- render reviewer ตาม flow ที่ controller resolve มาแล้ว --}}
        @if ($assignmentRow['reviewers']->isNotEmpty())
            <div class="space-y-2">
                @foreach ($assignmentRow['reviewers'] as $reviewer)
                    <div class="bg-blue-100 px-3 py-2 rounded-xl flex items-center">
                        <i class="fas fa-user-check text-blue-500 mr-2"></i>
                        <div>
                            <div class="text-xs font-semibold text-blue-600 leading-tight">
                                {{ $reviewer['label'] }}
                            </div>
                            <div class="text-sm font-semibold text-blue-700 leading-tight">
                                {{ $reviewer['name'] }}
                            </div>
                            <div class="text-xs text-blue-600">
                                {{ $reviewer['position'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <span class="text-sm text-gray-500">-</span>
        @endif
    </td>

    <td class="px-4 py-3 text-center align-top">
        <div class="flex flex-col items-center justify-center gap-1 rounded-xl bg-green-100 px-3 py-1.5">
            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-green-800">
                <i class="fas fa-users mr-1"></i>{{ $assignmentRow['evaluatee_count'] }} คน
            </span>

            @if ($assignmentRow['evaluatee_count'] > 0)
                <button
                    type="button"
                    data-show-evaluatees
                    data-assignment-id="{{ $assignmentRow['model']->id }}"
                    class="text-green-600 hover:text-green-800 text-xs underline mt-1"
                >
                    ดูรายละเอียด
                </button>
            @endif
        </div>
    </td>

    <td class="px-4 py-3 align-top whitespace-nowrap">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assignmentRow['status']['class'] }}">
            <i class="{{ $assignmentRow['status']['icon'] }} mr-1"></i>{{ $assignmentRow['status']['label'] }}
        </span>
    </td>

    <td class="px-4 py-3 align-top text-sm font-medium">
        <div class="flex items-center space-x-2">
            <a
                href="{{ route('assignment-data.edit', $assignmentRow['model']->id) }}"
                class="px-3 py-1.5 bg-yellow-500 text-white text-xs rounded-lg hover:bg-yellow-600 shadow-sm transition-all flex items-center"
            >
                <i class="fas fa-edit mr-1"></i>แก้ไข
            </a>
            <button
                type="button"
                data-delete-assignment
                data-assignment-id="{{ $assignmentRow['model']->id }}"
                class="px-3 py-1.5 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 shadow-sm transition-all flex items-center"
            >
                <i class="fas fa-trash mr-1"></i>ลบ
            </button>
        </div>
    </td>
</tr>
