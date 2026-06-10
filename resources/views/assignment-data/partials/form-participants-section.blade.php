{{-- ส่วนกำหนดผู้รับการประเมินและผู้ประเมินทุกบทบาท โดยรับ UI config จากหน้าแม่ --}}
<div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-3">
    <div class="flex items-center mb-6">
        <div class="flex items-center justify-center w-8 h-8 bg-purple-600 text-white rounded-full mr-3 text-sm font-semibold">
            3
        </div>
        <h2 class="text-xl font-semibold text-gray-800">กำหนดผู้ประเมิน / ผู้รับการประเมิน</h2>
    </div>

    <div class="mb-6 rounded-lg border border-purple-200 bg-purple-50 p-4 text-sm text-purple-800">
        กำหนดผู้ประเมิน กรรมการ และผู้บริหารแยกกันได้ โดยแต่ละช่องจะแสดงเฉพาะรายชื่อที่มี role นั้น ๆ
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-blue-50 rounded-lg p-6 position-card">
            <div class="flex items-center mb-4">
                <div class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full mr-2 text-xs font-semibold">
                    A
                </div>
                <h3 class="text-lg font-medium text-gray-700">ผู้รับการประเมิน</h3>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <div class="block text-sm font-medium text-gray-700">
                    รายชื่อผู้รับการประเมิน:
                </div>
                <div class="text-sm text-gray-500">
                    <span id="evaluatees-available-count">{{ $users->count() }}</span> คนที่แสดง จาก
                    <span id="evaluatees-total-count">{{ $users->count() }}</span> คนทั้งหมด
                </div>
            </div>

            <div class="mb-4" id="evaluatees-dropdown-wrapper">
                <button type="button" id="toggle-evaluatees-dropdown" class="{{ $evaluateesUi['dropdown_button_class'] }}">
                    <span id="evaluatees-dropdown-label">เลือกผู้รับการประเมิน</span>
                    <i class="{{ $evaluateesUi['dropdown_icon_class'] }}"></i>
                </button>

                <div id="evaluatees-dropdown-panel" class="{{ $evaluateesUi['dropdown_panel_class'] }}">
                    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label for="evaluatees-department-filter" class="mb-1 block text-sm font-medium text-gray-700">หน่วยงาน/คณะ</label>
                            <select
                                id="evaluatees-department-filter"
                                autocomplete="off"
                                class="form-select w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">ทั้งหมด</option>
                                @foreach ($departmentOptions as $departmentName)
                                    <option value="{{ $departmentName }}">{{ $departmentName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="evaluatees-position-filter" class="mb-1 block text-sm font-medium text-gray-700">ตำแหน่งงาน</label>
                            <select
                                id="evaluatees-position-filter"
                                autocomplete="off"
                                class="form-select w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">ทั้งหมด</option>
                                @foreach ($positionOptions as $positionName)
                                    <option value="{{ $positionName }}">{{ $positionName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="evaluatees-search-filter" class="mb-1 block text-sm font-medium text-gray-700">ค้นหารายชื่อ</label>
                        <input
                            type="text"
                            id="evaluatees-search-filter"
                            autocomplete="off"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="พิมพ์ชื่อหรือตำแหน่งงาน"
                        >
                    </div>

                    <label class="mb-3 inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-blue-700">
                        <input
                            type="checkbox"
                            id="evaluatees-select-all"
                            class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-500"
                        >
                        เลือกทั้งหมด
                    </label>

                    <div id="evaluatees-checkbox-list" class="{{ $evaluateesUi['checkbox_list_class'] }}"></div>
                </div>
            </div>

            {{-- select จริงถูกซ่อนไว้และใช้เป็น source of truth ของค่าที่จะ submit
                 ส่วน dropdown/checklist ด้านบนเป็น custom UI สำหรับค้นหาและเลือกได้สะดวกขึ้น --}}
            <select id="evaluatees" name="evaluatees[]" multiple class="hidden">
                @foreach ($users as $user)
                    <option
                        value="{{ $user->id }}"
                        data-user-name="{{ $user->name }}"
                        data-user-email="{{ $user->position->name }}"
                        data-personnel-type="{{ $user->personnel_type }}"
                        data-user-department="{{ $user->department->department_name ?? '' }}"
                        data-user-position="{{ $user->position->name ?? '' }}"
                        @selected(in_array($user->id, old('evaluatees', $selectedEvaluatees)))
                    >
                        {{ $user->name }} ({{ $user->position->name }})
                    </option>
                @endforeach
            </select>

            <div class="{{ $evaluateesUi['selected_wrapper_class'] }}">
                <p class="text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-check-circle mr-2 text-blue-500"></i>ผู้รับการประเมินที่เลือก:
                    <span id="evaluatees-selected-count" class="text-blue-600 font-semibold">{{ count($selectedEvaluatees) }}</span> คน
                </p>
                <div id="selected-evaluatees" class="{{ $evaluateesUi['selected_display_class'] }}">
                    @if (count($selectedEvaluatees) > 0)
                        @foreach ($selectedEvaluateeUsers as $selectedUser)
                            <span class="{{ $evaluateesUi['selected_item_class'] }}">
                                {{ $selectedUser->name }}
                            </span>
                        @endforeach
                    @else
                        <span class="{{ $evaluateesUi['empty_item_class'] }}">ยังไม่ได้เลือกผู้รับการประเมิน</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-5">
            @foreach ($reviewerCards as $card)
                @php
                    // แต่ละการ์ด reviewer สร้าง filter option ของตัวเอง
                    $cardDepartmentOptions = $card['options']->pluck('department.department_name')->filter()->unique()->sort()->values();
                    $cardPositionOptions = $card['options']->pluck('position.name')->filter()->unique()->sort()->values();
                    $selectedReviewerValue = old($card['id'], $card['value'] ?? null);
                    $selectedUser = $card['options']->firstWhere('id', $selectedReviewerValue);
                @endphp

                <div class="{{ $card['wrapper_class'] }} rounded-lg p-6 position-card border">
                    <div class="flex items-center justify-between mb-4 gap-3">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-6 h-6 {{ $card['badge_class'] }} text-white rounded-full mr-2 text-xs font-semibold">
                                {{ $card['badge'] }}
                            </div>
                            <h3 class="text-lg font-medium text-gray-700">{{ $card['title'] }}</h3>
                        </div>
                        <div class="w-32">
                            <label for="stage_order_{{ $card['key'] }}" class="block text-sm font-medium text-gray-700 mb-1">
                                ลำดับ
                            </label>
                            <select
                                id="stage_order_{{ $card['key'] }}"
                                name="stage_order[{{ $card['key'] }}]"
                                data-stage-order-select
                                data-stage-key="{{ $card['key'] }}"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}"
                            >
                                @for ($stageOrder = 1; $stageOrder <= 3; $stageOrder++)
                                    <option value="{{ $stageOrder }}" @selected((int) $card['order'] === $stageOrder)>
                                        ขั้นที่ {{ $stageOrder }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-4 gap-2">
                        <label for="{{ $card['id'] }}" class="block text-sm font-medium text-gray-700">
                            รายชื่อ{{ $card['title'] }}:
                        </label>
                        <div class="text-sm text-gray-500">
                            <span id="{{ $card['count_id'] }}-available-count">{{ $card['available_count'] }}</span> คนที่แสดง จาก
                            <span id="{{ $card['count_id'] }}-total-count">{{ $card['available_count'] }}</span> คนทั้งหมด
                        </div>
                    </div>

                    <div class="mb-4" id="{{ $card['count_id'] }}-dropdown-wrapper">
                        <button
                            type="button"
                            id="{{ $card['count_id'] }}-dropdown-toggle"
                            class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 text-left text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 focus:outline-none focus:ring-2 {{ $card['focus_class'] }}"
                        >
                            <span id="{{ $card['count_id'] }}-dropdown-label">{{ $card['placeholder'] }}</span>
                            <i class="fas fa-chevron-down text-xs text-slate-500"></i>
                        </button>

                        <div id="{{ $card['count_id'] }}-dropdown-panel" class="mt-3 hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div>
                                    <label for="{{ $card['count_id'] }}-department-filter" class="mb-1 block text-sm font-medium text-gray-700">หน่วยงาน/คณะ</label>
                                    <select
                                        id="{{ $card['count_id'] }}-department-filter"
                                        autocomplete="off"
                                        class="form-select w-full text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}"
                                    >
                                        <option value="">ทั้งหมด</option>
                                        @foreach ($cardDepartmentOptions as $departmentName)
                                            <option value="{{ $departmentName }}">{{ $departmentName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="{{ $card['count_id'] }}-position-filter" class="mb-1 block text-sm font-medium text-gray-700">ตำแหน่งงาน</label>
                                    <select
                                        id="{{ $card['count_id'] }}-position-filter"
                                        autocomplete="off"
                                        class="form-select w-full text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}"
                                    >
                                        <option value="">ทั้งหมด</option>
                                        @foreach ($cardPositionOptions as $positionName)
                                            <option value="{{ $positionName }}">{{ $positionName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="{{ $card['count_id'] }}-search-filter" class="mb-1 block text-sm font-medium text-gray-700">ค้นหารายชื่อ</label>
                                <input
                                    type="text"
                                    id="{{ $card['count_id'] }}-search-filter"
                                    autocomplete="off"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}"
                                    placeholder="พิมพ์ชื่อหรือตำแหน่งงาน"
                                >
                            </div>

                            <div id="{{ $card['count_id'] }}-checkbox-list" class="max-h-72 overflow-y-auto rounded-md border border-slate-200 bg-white px-3 py-2"></div>
                        </div>
                    </div>

                    {{-- select ที่ซ่อนไว้เก็บค่าจริงของ reviewer แต่ละบทบาท --}}
                    <select id="{{ $card['id'] }}" name="{{ $card['id'] }}" class="hidden">
                        <option value="">{{ $card['placeholder'] }}</option>
                        @foreach ($card['options'] as $user)
                            <option
                                value="{{ $user->id }}"
                                data-user-name="{{ $user->name }}"
                                data-user-email="{{ $user->position->name }}"
                                data-user-department="{{ $user->department->department_name ?? '' }}"
                                data-user-position="{{ $user->position->name ?? '' }}"
                                @selected($selectedReviewerValue == $user->id)
                            >
                                {{ $user->name }} ({{ $user->position->name }})
                            </option>
                        @endforeach
                    </select>

                    <div class="mt-4 p-4 bg-white rounded-lg min-h-[60px] border">
                        <p class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-check-circle mr-2 {{ $card['icon_class'] }}"></i>{{ $card['selected_label'] }}:
                            <span id="{{ $card['count_id'] }}-selected-count" class="{{ $card['text_class'] }} font-semibold">{{ $selectedReviewerValue ? 1 : 0 }}</span> คน
                        </p>
                        <div id="selected-{{ $card['count_id'] }}" class="{{ $reviewerSelectionDisplay === 'list' ? 'overflow-hidden rounded-md border border-slate-200 bg-white' : 'flex flex-col gap-2' }}">
                            @if ($selectedUser)
                                <span class="{{ $reviewerSelectionDisplay === 'list' ? 'block px-3 py-2 text-sm text-gray-700' : 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ' . $card['tag_class'] }}">
                                    {{ $selectedUser->name }}
                                </span>
                            @else
                                <span class="{{ $reviewerSelectionDisplay === 'list' ? 'block px-3 py-2 text-sm text-gray-500' : 'text-sm text-gray-500' }}">{{ $card['empty_text'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
