{{-- แถวเดียวของตาราง list โดยรับข้อมูลที่จัดรูปเสร็จแล้วจาก controller --}}
<tr class="assignment-data-row hover:bg-gray-50 transition-all" data-resource-row data-resource-id="{{ $assignmentRow['model']->id }}">
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

    <td class="px-4 py-3 align-top text-sm font-medium whitespace-nowrap">
        <div class="dropdown">
            <button
                type="button"
                class="inline-flex h-9 min-w-24 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                data-bs-toggle="dropdown"
                data-bs-boundary="viewport"
                aria-expanded="false"
            >
                <i class="fas fa-ellipsis-h"></i>
                <span>จัดการ</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end min-w-[10rem] rounded-lg border border-slate-200 p-1 shadow-lg">
                <li>
                    <a
                        href="{{ route('assignment-data.edit', $assignmentRow['model']->id) }}"
                        class="dropdown-item flex items-center gap-2 rounded-md px-3 py-2 text-sm text-slate-700"
                    >
                        <i class="fas fa-edit text-yellow-500"></i>
                        <span>แก้ไข</span>
                    </a>
                </li>
                <li>
                    <a
                        href="{{ route('assignment-data.copy', $assignmentRow['model']->id) }}"
                        class="dropdown-item flex items-center gap-2 rounded-md px-3 py-2 text-sm text-slate-700"
                    >
                        <i class="fas fa-copy text-blue-500"></i>
                        <span>คัดลอก</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <button
                        type="button"
                        data-delete-assignment
                        data-assignment-id="{{ $assignmentRow['model']->id }}"
                        class="dropdown-item flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-red-600"
                    >
                        <i class="fas fa-trash"></i>
                        <span>ลบ</span>
                    </button>
                </li>
            </ul>
        </div>
    </td>
</tr>
