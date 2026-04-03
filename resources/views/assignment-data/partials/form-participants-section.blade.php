{{-- ส่วนกำหนดผู้รับการประเมินและผู้ประเมินทุกบทบาท โดยรับ UI config จากหน้าแม่ --}}
<div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-3">
    <div class="flex items-center mb-6">
        <div class="flex items-center justify-center w-8 h-8 bg-purple-600 text-white rounded-full mr-3 text-sm font-semibold">
            3
        </div>
        <h2 class="text-xl font-semibold text-gray-800">&#x0E01;&#x0E33;&#x0E2B;&#x0E19;&#x0E14;&#x0E1C;&#x0E39;&#x0E49;&#x0E1B;&#x0E23;&#x0E30;&#x0E40;&#x0E21;&#x0E34;&#x0E19;&#x0020;&#x002F;&#x0020;&#x0E1C;&#x0E39;&#x0E49;&#x0E23;&#x0E31;&#x0E1A;&#x0E01;&#x0E32;&#x0E23;&#x0E1B;&#x0E23;&#x0E30;&#x0E40;&#x0E21;&#x0E34;&#x0E19;</h2>
    </div>

    <div class="mb-6 rounded-lg border border-purple-200 bg-purple-50 p-4 text-sm text-purple-800">
        &#x0E01;&#x0E33;&#x0E2B;&#x0E19;&#x0E14;&#x0E1C;&#x0E39;&#x0E49;&#x0E1B;&#x0E23;&#x0E30;&#x0E40;&#x0E21;&#x0E34;&#x0E19;&#x0020;&#x0E01;&#x0E23;&#x0E23;&#x0E21;&#x0E01;&#x0E32;&#x0E23;&#x0020;&#x0E41;&#x0E25;&#x0E30;&#x0E1C;&#x0E39;&#x0E49;&#x0E1A;&#x0E23;&#x0E34;&#x0E2B;&#x0E32;&#x0E23;&#x0E41;&#x0E22;&#x0E01;&#x0E01;&#x0E31;&#x0E19;&#x0E44;&#x0E14;&#x0E49;&#x0020;&#x0E42;&#x0E14;&#x0E22;&#x0E41;&#x0E15;&#x0E48;&#x0E25;&#x0E30;&#x0E0A;&#x0E48;&#x0E2D;&#x0E07;&#x0E08;&#x0E30;&#x0E41;&#x0E2A;&#x0E14;&#x0E07;&#x0E40;&#x0E09;&#x0E1E;&#x0E32;&#x0E30;&#x0E23;&#x0E32;&#x0E22;&#x0E0A;&#x0E37;&#x0E48;&#x0E2D;&#x0E17;&#x0E35;&#x0E48;&#x0E21;&#x0E35;&#x0020;role&#x0020;&#x0E19;&#x0E31;&#x0E49;&#x0E19;&#x0020;&#x0E46;
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-blue-50 rounded-lg p-6 position-card">
            <div class="flex items-center mb-4">
                <div class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full mr-2 text-xs font-semibold">
                    A
                </div>
                <h3 class="text-lg font-medium text-gray-700">&#x0E1C;&#x0E39;&#x0E49;&#x0E23;&#x0E31;&#x0E1A;&#x0E01;&#x0E32;&#x0E23;&#x0E1B;&#x0E23;&#x0E30;&#x0E40;&#x0E21;&#x0E34;&#x0E19;</h3>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <label class="block text-sm font-medium text-gray-700">
                    &#x0E23;&#x0E32;&#x0E22;&#x0E0A;&#x0E37;&#x0E48;&#x0E2D;&#x0E1C;&#x0E39;&#x0E49;&#x0E23;&#x0E31;&#x0E1A;&#x0E01;&#x0E32;&#x0E23;&#x0E1B;&#x0E23;&#x0E30;&#x0E40;&#x0E21;&#x0E34;&#x0E19;&#x003A;
                </label>
                <div class="text-sm text-gray-500">
                    <span id="evaluatees-available-count">{{ $users->count() }}</span> &#x0E04;&#x0E19;&#x0E17;&#x0E35;&#x0E48;&#x0E41;&#x0E2A;&#x0E14;&#x0E07;&#x0020;&#x0E08;&#x0E32;&#x0E01;
                    <span id="evaluatees-total-count">{{ $users->count() }}</span> &#x0E04;&#x0E19;&#x0E17;&#x0E31;&#x0E49;&#x0E07;&#x0E2B;&#x0E21;&#x0E14;
                </div>
            </div>

            <div class="mb-4" id="evaluatees-dropdown-wrapper">
                <button type="button" id="toggle-evaluatees-dropdown" class="{{ $evaluateesUi['dropdown_button_class'] }}">
                    <span id="evaluatees-dropdown-label">&#x0E40;&#x0E25;&#x0E37;&#x0E2D;&#x0E01;&#x0E1C;&#x0E39;&#x0E49;&#x0E23;&#x0E31;&#x0E1A;&#x0E01;&#x0E32;&#x0E23;&#x0E1B;&#x0E23;&#x0E30;&#x0E40;&#x0E21;&#x0E34;&#x0E19;</span>
                    <i class="{{ $evaluateesUi['dropdown_icon_class'] }}"></i>
                </button>

                <div id="evaluatees-dropdown-panel" class="{{ $evaluateesUi['dropdown_panel_class'] }}">
                    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label for="evaluatees-department-filter" class="mb-1 block text-sm font-medium text-gray-700">&#x0E2B;&#x0E19;&#x0E48;&#x0E27;&#x0E22;&#x0E07;&#x0E32;&#x0E19;&#x002F;&#x0E04;&#x0E13;&#x0E30;</label>
                            <select id="evaluatees-department-filter"
                                class="form-select w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">&#x0E17;&#x0E31;&#x0E49;&#x0E07;&#x0E2B;&#x0E21;&#x0E14;</option>
                                @foreach ($departmentOptions as $departmentName)
                                    <option value="{{ $departmentName }}">{{ $departmentName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="evaluatees-position-filter" class="mb-1 block text-sm font-medium text-gray-700">&#x0E15;&#x0E33;&#x0E41;&#x0E2B;&#x0E19;&#x0E48;&#x0E07;&#x0E07;&#x0E32;&#x0E19;</label>
                            <select id="evaluatees-position-filter"
                                class="form-select w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">&#x0E17;&#x0E31;&#x0E49;&#x0E07;&#x0E2B;&#x0E21;&#x0E14;</option>
                                @foreach ($positionOptions as $positionName)
                                    <option value="{{ $positionName }}">{{ $positionName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="evaluatees-search-filter" class="mb-1 block text-sm font-medium text-gray-700">&#x0E04;&#x0E49;&#x0E19;&#x0E2B;&#x0E32;&#x0E23;&#x0E32;&#x0E22;&#x0E0A;&#x0E37;&#x0E48;&#x0E2D;</label>
                        <input type="text" id="evaluatees-search-filter"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="&#x0E1E;&#x0E34;&#x0E21;&#x0E1E;&#x0E4C;&#x0E0A;&#x0E37;&#x0E48;&#x0E2D;&#x0E2B;&#x0E23;&#x0E37;&#x0E2D;&#x0E15;&#x0E33;&#x0E41;&#x0E2B;&#x0E19;&#x0E48;&#x0E07;&#x0E07;&#x0E32;&#x0E19;">
                    </div>

                    <label class="mb-3 inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-blue-700">
                        <input type="checkbox" id="evaluatees-select-all"
                            class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                        &#x0E40;&#x0E25;&#x0E37;&#x0E2D;&#x0E01;&#x0E17;&#x0E31;&#x0E49;&#x0E07;&#x0E2B;&#x0E21;&#x0E14;
                    </label>

                    <div id="evaluatees-checkbox-list" class="{{ $evaluateesUi['checkbox_list_class'] }}"></div>
                </div>
            </div>
            {{-- select จริงถูกซ่อนไว้และใช้เป็น source of truth ของค่าที่จะ submit
                 ส่วน dropdown/checklist ด้านบนเป็นเพียง custom UI สำหรับค้นหาและเลือกได้สะดวกขึ้น --}}
            <select id="evaluatees" name="evaluatees[]" multiple class="hidden">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}"
                        data-user-name="{{ $user->name }}"
                        data-user-email="{{ $user->position->name }}"
                        data-personnel-type="{{ $user->personnel_type }}"
                        data-user-department="{{ $user->department->department_name ?? '' }}"
                        data-user-position="{{ $user->position->name ?? '' }}"
                        @selected(in_array($user->id, old('evaluatees', $selectedEvaluatees)))>
                        {{ $user->name }} ({{ $user->position->name }})
                    </option>
                @endforeach
            </select>

            <div class="{{ $evaluateesUi['selected_wrapper_class'] }}">
                <p class="text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-check-circle mr-2 text-blue-500"></i>&#x0E1C;&#x0E39;&#x0E49;&#x0E23;&#x0E31;&#x0E1A;&#x0E01;&#x0E32;&#x0E23;&#x0E1B;&#x0E23;&#x0E30;&#x0E40;&#x0E21;&#x0E34;&#x0E19;&#x0E17;&#x0E35;&#x0E48;&#x0E40;&#x0E25;&#x0E37;&#x0E2D;&#x0E01;&#x003A;
                    <span id="evaluatees-selected-count" class="text-blue-600 font-semibold">{{ count($selectedEvaluatees) }}</span> &#x0E04;&#x0E19;
                </p>
                <div id="selected-evaluatees" class="{{ $evaluateesUi['selected_display_class'] }}">
                    @if (count($selectedEvaluatees) > 0)
                        @foreach ($selectedEvaluateeUsers as $selectedUser)
                            <span class="{{ $evaluateesUi['selected_item_class'] }}">
                                {{ $selectedUser->name }}
                            </span>
                        @endforeach
                    @else
                        <span class="{{ $evaluateesUi['empty_item_class'] }}">&#x0E22;&#x0E31;&#x0E07;&#x0E44;&#x0E21;&#x0E48;&#x0E44;&#x0E14;&#x0E49;&#x0E40;&#x0E25;&#x0E37;&#x0E2D;&#x0E01;&#x0E1C;&#x0E39;&#x0E49;&#x0E23;&#x0E31;&#x0E1A;&#x0E01;&#x0E32;&#x0E23;&#x0E1B;&#x0E23;&#x0E30;&#x0E40;&#x0E21;&#x0E34;&#x0E19;</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-5">
            @foreach ($reviewerCards as $card)
                <div class="{{ $card['wrapper_class'] }} rounded-lg p-6 position-card border">
                    <div class="flex items-center justify-between mb-4 gap-3">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-6 h-6 {{ $card['badge_class'] }} text-white rounded-full mr-2 text-xs font-semibold">
                                {{ $card['badge'] }}
                            </div>
                            <h3 class="text-lg font-medium text-gray-700">{{ $card['title'] }}</h3>
                        </div>
                        <div class="w-24">
                            <label for="stage_order_{{ $card['key'] }}" class="block text-sm font-medium text-gray-700 mb-1">
                                &#x0E25;&#x0E33;&#x0E14;&#x0E31;&#x0E1A;
                            </label>
                            <input type="number" id="stage_order_{{ $card['key'] }}" name="stage_order[{{ $card['key'] }}]"
                                min="1" max="3" value="{{ $card['order'] }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 {{ $card['focus_class'] }}">
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-4 gap-2">
                        <label for="{{ $card['id'] }}" class="block text-sm font-medium text-gray-700">
                            &#x0E23;&#x0E32;&#x0E22;&#x0E0A;&#x0E37;&#x0E48;&#x0E2D;{{ $card['title'] }}:
                        </label>
                        <div class="text-sm text-gray-500">
                            <span id="{{ $card['count_id'] }}-available-count">{{ $card['available_count'] }}</span> &#x0E04;&#x0E19;&#x0E17;&#x0E35;&#x0E48;&#x0E41;&#x0E2A;&#x0E14;&#x0E07;&#x0020;&#x0E08;&#x0E32;&#x0E01;
                            <span id="{{ $card['count_id'] }}-total-count">{{ $card['available_count'] }}</span> &#x0E04;&#x0E19;&#x0E17;&#x0E31;&#x0E49;&#x0E07;&#x0E2B;&#x0E21;&#x0E14;
                        </div>
                    </div>
                    @php
                        // แต่ละการ์ด reviewer สร้าง filter option ของตัวเอง
                        // เพื่อให้หน่วยงาน/ตำแหน่งที่แสดง สะท้อนเฉพาะชุดผู้ใช้ในบทบาทนั้น
                        $cardDepartmentOptions = $card['options']->pluck('department.department_name')->filter()->unique()->sort()->values();
                        $cardPositionOptions = $card['options']->pluck('position.name')->filter()->unique()->sort()->values();
                        $selectedReviewerValue = old($card['id'], $card['value'] ?? null);
                        $selectedUser = $card['options']->firstWhere('id', $selectedReviewerValue);
                    @endphp
                    <div class="mb-4" id="{{ $card['count_id'] }}-dropdown-wrapper">
                        <button type="button" id="{{ $card['count_id'] }}-dropdown-toggle"
                            class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 text-left text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 focus:outline-none focus:ring-2 {{ $card['focus_class'] }}">
                            <span id="{{ $card['count_id'] }}-dropdown-label">{{ $card['placeholder'] }}</span>
                            <i class="fas fa-chevron-down text-xs text-slate-500"></i>
                        </button>

                        <div id="{{ $card['count_id'] }}-dropdown-panel" class="mt-3 hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div>
                                    <label for="{{ $card['count_id'] }}-department-filter" class="mb-1 block text-sm font-medium text-gray-700">&#x0E2B;&#x0E19;&#x0E48;&#x0E27;&#x0E22;&#x0E07;&#x0E32;&#x0E19;&#x002F;&#x0E04;&#x0E13;&#x0E30;</label>
                                    <select id="{{ $card['count_id'] }}-department-filter"
                                        class="form-select w-full text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}">
                                        <option value="">&#x0E17;&#x0E31;&#x0E49;&#x0E07;&#x0E2B;&#x0E21;&#x0E14;</option>
                                        @foreach ($cardDepartmentOptions as $departmentName)
                                            <option value="{{ $departmentName }}">{{ $departmentName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="{{ $card['count_id'] }}-position-filter" class="mb-1 block text-sm font-medium text-gray-700">&#x0E15;&#x0E33;&#x0E41;&#x0E2B;&#x0E19;&#x0E48;&#x0E07;&#x0E07;&#x0E32;&#x0E19;</label>
                                    <select id="{{ $card['count_id'] }}-position-filter"
                                        class="form-select w-full text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}">
                                        <option value="">&#x0E17;&#x0E31;&#x0E49;&#x0E07;&#x0E2B;&#x0E21;&#x0E14;</option>
                                        @foreach ($cardPositionOptions as $positionName)
                                            <option value="{{ $positionName }}">{{ $positionName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="{{ $card['count_id'] }}-search-filter" class="mb-1 block text-sm font-medium text-gray-700">&#x0E04;&#x0E49;&#x0E19;&#x0E2B;&#x0E32;&#x0E23;&#x0E32;&#x0E22;&#x0E0A;&#x0E37;&#x0E48;&#x0E2D;</label>
                                <input type="text" id="{{ $card['count_id'] }}-search-filter"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}"
                                    placeholder="&#x0E1E;&#x0E34;&#x0E21;&#x0E1E;&#x0E4C;&#x0E0A;&#x0E37;&#x0E48;&#x0E2D;&#x0E2B;&#x0E23;&#x0E37;&#x0E2D;&#x0E15;&#x0E33;&#x0E41;&#x0E2B;&#x0E19;&#x0E48;&#x0E07;&#x0E07;&#x0E32;&#x0E19;">
                            </div>

                            <div id="{{ $card['count_id'] }}-checkbox-list" class="max-h-72 overflow-y-auto rounded-md border border-slate-200 bg-white px-3 py-2"></div>
                        </div>
                    </div>
                    {{-- select ที่ซ่อนอยู่เก็บค่าจริงของ reviewer แต่ละบทบาท
                         JS จะ sync ค่า checkbox/dropdown กลับมาที่ element นี้ก่อน submit ทุกครั้ง --}}
                    <select id="{{ $card['id'] }}" name="{{ $card['id'] }}" class="hidden">
                        <option value="">{{ $card['placeholder'] }}</option>
                        @foreach ($card['options'] as $user)
                            <option value="{{ $user->id }}"
                                data-user-name="{{ $user->name }}"
                                data-user-email="{{ $user->position->name }}"
                                data-user-department="{{ $user->department->department_name ?? '' }}"
                                data-user-position="{{ $user->position->name ?? '' }}"
                                @selected($selectedReviewerValue == $user->id)>
                                {{ $user->name }} ({{ $user->position->name }})
                            </option>
                        @endforeach
                    </select>

                    <div class="mt-4 p-4 bg-white rounded-lg min-h-[60px] border">
                        <p class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-check-circle mr-2 {{ $card['icon_class'] }}"></i>{{ $card['selected_label'] }}:
                            <span id="{{ $card['count_id'] }}-selected-count" class="{{ $card['text_class'] }} font-semibold">{{ $selectedReviewerValue ? 1 : 0 }}</span> &#x0E04;&#x0E19;
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
