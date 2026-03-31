@extends('layouts.app')
@section('content')
    @if(session('success'))
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <body class="bg-gray-50 min-h-screen py-8">
        <div class="py-12 max-w-6xl mx-auto px-4">
            <form id="evaluation-form" action="{{ route('assignment-data.store') }}" method="POST">
                @csrf
                
                <!-- กำหนดกรอบการประเมิน Section -->
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-1">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full mr-3 text-sm font-semibold">
                            1
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">กำหนดกรอบการประเมิน</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>วันเริ่มต้นประเมิน:
                            </label>
                            <input type="text" name="start_time" id="start_time" value="{{ old('start_time') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr-date" required>
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>วันสิ้นสุดประเมิน:
                            </label>
                            <input type="text" name="end_time" id="end_time" value="{{ old('end_time') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr-date" required>
                        </div>
                    </div>
                </div>

                <!-- เกณฑ์การประเมิน Section -->
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-2">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-green-600 text-white rounded-full mr-3 text-sm font-semibold">
                            2
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">เลือกเกณฑ์การประเมิน</h2>
                    </div>
                    <div>
                        <label for="report_data_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clipboard-list mr-2 text-green-500"></i>เกณฑ์การประเมิน:
                        </label>
                        <select id="report_data_id" name="report_data_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- กรุณาเลือกเกณฑ์การประเมิน --</option>
                            @foreach ($report_data as $item)
                                <option value="{{ $item->id }}" data-assessment-type="{{ $item->assessment_type }}"
                                    {{ old('report_data_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->report_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- กำหนดผู้ประเมิน / ผู้รับการประเมิน Section -->
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
                        <!-- ผู้รับการประเมิน Section -->
                        <div class="bg-blue-50 rounded-lg p-6 position-card">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full mr-2 text-xs font-semibold">
                                    A
                                </div>
                                <h3 class="text-lg font-medium text-gray-700">ผู้รับการประเมิน</h3>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    รายชื่อผู้รับการประเมิน:
                                </label>
                                <div class="text-sm text-gray-500">
                                    <span id="evaluatees-available-count">{{ $users->count() }}</span> คนที่แสดง จาก
                                    <span id="evaluatees-total-count">{{ $users->count() }}</span> คนทั้งหมด
                                </div>
                            </div>
                            @php
                                $departmentOptions = $users->pluck('department.department_name')->filter()->unique()->sort()->values();
                                $positionOptions = $users->pluck('position.name')->filter()->unique()->sort()->values();
                                $selectedEvaluatees = collect(old('evaluatees', []))->map(fn ($id) => (int) $id)->all();
                            @endphp
                            <div class="mb-4" id="evaluatees-dropdown-wrapper">
                                <button type="button" id="toggle-evaluatees-dropdown"
                                    class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 text-left text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span id="evaluatees-dropdown-label">เลือกผู้รับการประเมิน</span>
                                    <i class="fas fa-chevron-down text-xs text-slate-500"></i>
                                </button>

                                <div id="evaluatees-dropdown-panel" class="mt-3 hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <div>
                                            <label for="evaluatees-department-filter" class="mb-1 block text-sm font-medium text-gray-700">หน่วยงาน/คณะ</label>
                                            <select id="evaluatees-department-filter"
                                                class="form-select w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">ทั้งหมด</option>
                                                @foreach ($departmentOptions as $departmentName)
                                                    <option value="{{ $departmentName }}">{{ $departmentName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="evaluatees-position-filter" class="mb-1 block text-sm font-medium text-gray-700">ตำแหน่งงาน</label>
                                            <select id="evaluatees-position-filter"
                                                class="form-select w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">ทั้งหมด</option>
                                                @foreach ($positionOptions as $positionName)
                                                    <option value="{{ $positionName }}">{{ $positionName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="evaluatees-search-filter" class="mb-1 block text-sm font-medium text-gray-700">ค้นหารายชื่อ</label>
                                        <input type="text" id="evaluatees-search-filter"
                                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="พิมพ์ชื่อหรือตำแหน่งงาน">
                                    </div>

                                    <label class="mb-3 inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-blue-700">
                                        <input type="checkbox" id="evaluatees-select-all"
                                            class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                                        เลือกทั้งหมด
                                    </label>

                                    <div id="evaluatees-checkbox-list" class="max-h-72 overflow-y-auto rounded-md border border-blue-100 bg-white px-3 py-2"></div>
                                </div>
                            </div>
                            <select id="evaluatees" name="evaluatees[]" multiple required class="hidden">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->position->name }}"
                                        data-personnel-type="{{ $user->personnel_type }}"
                                        data-user-department="{{ $user->department->department_name ?? '' }}"
                                        data-user-position="{{ $user->position->name ?? '' }}"
                                        @selected(in_array($user->id, $selectedEvaluatees))>
                                        {{ $user->name }} ({{ $user->position->name }})
                                    </option>
                                @endforeach
                            </select>

                            <!-- Selected Display for Evaluatees -->
                            <div class="mt-4 p-4 bg-white rounded-lg min-h-[60px] border border-blue-200">
                                <p class="text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-check-circle mr-2 text-blue-500"></i>ผู้รับการประเมินที่เลือก:
                                    <span id="evaluatees-selected-count" class="text-blue-600 font-semibold">{{ count($selectedEvaluatees) }}</span> คน
                                </p>
                                <div id="selected-evaluatees" class="flex flex-col gap-2">
                                    @if(count($selectedEvaluatees) > 0)
                                        @foreach($users->whereIn('id', $selectedEvaluatees) as $selectedUser)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $selectedUser->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-sm text-gray-500">ยังไม่ได้เลือกผู้รับการประเมิน</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            @php
                                $reviewerCards = [
                                    [
                                        'key' => 'evaluator',
                                        'id' => 'evaluator_id',
                                        'badge' => 'B',
                                        'title' => 'ผู้ประเมิน/หัวหน้างาน',
                                        'count_id' => 'evaluators',
                                        'placeholder' => '-- เลือกผู้ประเมิน/หัวหน้างาน --',
                                        'empty_text' => 'ยังไม่ได้เลือกผู้ประเมิน/หัวหน้างาน',
                                        'selected_label' => 'ผู้ประเมิน/หัวหน้างานที่เลือก',
                                        'available_count' => $evaluatorUsers->count(),
                                        'order' => old('stage_order.evaluator', 1),
                                        'wrapper_class' => 'bg-green-50 border-green-200',
                                        'badge_class' => 'bg-green-600',
                                        'focus_class' => 'focus:ring-green-500',
                                        'text_class' => 'text-green-600',
                                        'icon_class' => 'text-green-500',
                                        'tag_class' => 'bg-green-100 text-green-800',
                                        'options' => $evaluatorUsers,
                                    ],
                                    [
                                        'key' => 'director',
                                        'id' => 'director_id',
                                        'badge' => 'C',
                                        'title' => 'กรรมการ',
                                        'count_id' => 'directors',
                                        'placeholder' => '-- เลือกกรรมการ --',
                                        'empty_text' => 'ยังไม่ได้เลือกกรรมการ',
                                        'selected_label' => 'กรรมการที่เลือก',
                                        'available_count' => $directorUsers->count(),
                                        'order' => old('stage_order.director', 2),
                                        'wrapper_class' => 'bg-amber-50 border-amber-200',
                                        'badge_class' => 'bg-amber-600',
                                        'focus_class' => 'focus:ring-amber-500',
                                        'text_class' => 'text-amber-600',
                                        'icon_class' => 'text-amber-500',
                                        'tag_class' => 'bg-amber-100 text-amber-800',
                                        'options' => $directorUsers,
                                    ],
                                    [
                                        'key' => 'manager',
                                        'id' => 'manager_id',
                                        'badge' => 'D',
                                        'title' => 'ผู้บริหาร',
                                        'count_id' => 'managers',
                                        'placeholder' => '-- เลือกผู้บริหาร --',
                                        'empty_text' => 'ยังไม่ได้เลือกผู้บริหาร',
                                        'selected_label' => 'ผู้บริหารที่เลือก',
                                        'available_count' => $managerUsers->count(),
                                        'order' => old('stage_order.manager', 3),
                                        'wrapper_class' => 'bg-rose-50 border-rose-200',
                                        'badge_class' => 'bg-rose-600',
                                        'focus_class' => 'focus:ring-rose-500',
                                        'text_class' => 'text-rose-600',
                                        'icon_class' => 'text-rose-500',
                                        'tag_class' => 'bg-rose-100 text-rose-800',
                                        'options' => $managerUsers,
                                    ],
                                ];
                            @endphp

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
                                                ลำดับ
                                            </label>
                                            <input type="number" id="stage_order_{{ $card['key'] }}" name="stage_order[{{ $card['key'] }}]"
                                                min="1" max="3" value="{{ $card['order'] }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 {{ $card['focus_class'] }}">
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
                                    @php
                                        $cardDepartmentOptions = $card['options']->pluck('department.department_name')->filter()->unique()->sort()->values();
                                        $cardPositionOptions = $card['options']->pluck('position.name')->filter()->unique()->sort()->values();
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
                                                    <label for="{{ $card['count_id'] }}-department-filter" class="mb-1 block text-sm font-medium text-gray-700">หน่วยงาน/คณะ</label>
                                                    <select id="{{ $card['count_id'] }}-department-filter"
                                                        class="form-select w-full text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}">
                                                        <option value="">ทั้งหมด</option>
                                                        @foreach ($cardDepartmentOptions as $departmentName)
                                                            <option value="{{ $departmentName }}">{{ $departmentName }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="{{ $card['count_id'] }}-position-filter" class="mb-1 block text-sm font-medium text-gray-700">ตำแหน่งงาน</label>
                                                    <select id="{{ $card['count_id'] }}-position-filter"
                                                        class="form-select w-full text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}">
                                                        <option value="">ทั้งหมด</option>
                                                        @foreach ($cardPositionOptions as $positionName)
                                                            <option value="{{ $positionName }}">{{ $positionName }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label for="{{ $card['count_id'] }}-search-filter" class="mb-1 block text-sm font-medium text-gray-700">ค้นหารายชื่อ</label>
                                                <input type="text" id="{{ $card['count_id'] }}-search-filter"
                                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $card['focus_class'] }}"
                                                    placeholder="พิมพ์ชื่อหรือตำแหน่งงาน">
                                            </div>

                                            <div id="{{ $card['count_id'] }}-checkbox-list" class="max-h-72 overflow-y-auto rounded-md border border-slate-200 bg-white px-3 py-2"></div>
                                        </div>
                                    </div>
                                    <select id="{{ $card['id'] }}" name="{{ $card['id'] }}" class="hidden">
                                        <option value="">{{ $card['placeholder'] }}</option>
                                        @foreach ($card['options'] as $user)
                                            <option value="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-email="{{ $user->position->name }}"
                                                data-user-department="{{ $user->department->department_name ?? '' }}"
                                                data-user-position="{{ $user->position->name ?? '' }}"
                                                @selected(old($card['id']) == $user->id)>
                                                {{ $user->name }} ({{ $user->position->name }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="mt-4 p-4 bg-white rounded-lg min-h-[60px] border">
                                        <p class="text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-check-circle mr-2 {{ $card['icon_class'] }}"></i>{{ $card['selected_label'] }}:
                                            <span id="{{ $card['count_id'] }}-selected-count" class="{{ $card['text_class'] }} font-semibold">{{ old($card['id']) ? 1 : 0 }}</span> คน
                                        </p>
                                        <div id="selected-{{ $card['count_id'] }}" class="flex flex-col gap-2">
                                            @php
                                                $selectedUser = $card['options']->firstWhere('id', old($card['id']));
                                            @endphp
                                            @if ($selectedUser)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $card['tag_class'] }}">
                                                    {{ $selectedUser->name }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-500">{{ $card['empty_text'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- สรุปและปุ่มควบคุม Section -->
                <div class="bg-white shadow-sm rounded-lg p-6 form-section step-card step-4">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-orange-600 text-white rounded-full mr-3 text-sm font-semibold">
                            4
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">สรุปและยืนยันการตั้งค่า</h2>
                    </div>
                    <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 items-stretch">
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-md summary-card flex min-h-[220px] flex-col">
                                <div class="mb-4 text-center">
                                    <div class="text-lg font-bold text-blue-600">ระยะเวลาประเมิน</div>
                                </div>
                                <div class="bg-blue-50 rounded-lg p-4 min-h-[120px] flex flex-col items-center justify-center text-center">
                                    <div class="text-3xl font-extrabold text-blue-600 leading-none" id="summary-period">-</div>
                                    <div class="mt-3 text-sm font-medium text-gray-600">จำนวนวันของรอบประเมิน</div>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-md summary-card flex min-h-[220px] flex-col">
                                <div class="mb-4 text-center">
                                    <div class="text-lg font-bold text-green-600">เกณฑ์การประเมินที่เลือก</div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-4 min-h-[120px] flex flex-col justify-center">
                                    <div class="text-sm text-gray-600 font-medium mb-2">ชื่อเกณฑ์:</div>
                                    <div class="text-base font-semibold text-green-700" id="summary-criteria-full">-</div>
                                    <div class="text-xs text-gray-500 mt-2" id="summary-criteria-description">กรุณาเลือกเกณฑ์การประเมิน</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex flex-column flex-md-row justify-between items-start md:items-center mb-4 gap-3">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span class="font-medium">หมายเหตุ:</span>
                            กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก
                        </div>
                        <div class="flex justify-center items-center gap-2 flex-wrap">
                            <a href="/assignment-data"
                                class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>ย้อนกลับ
                            </a>
                            <button type="button" id="reset-btn"
                                class="px-6 py-2 bg-white text-blue-800 border-2 border-blue-500 font-semibold rounded-md hover:bg-blue-50 transition-colors">
                                <i class="fas fa-undo mr-2"></i>ล้างค่า
                            </button>
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-save mr-2"></i>บันทึกการตั้งค่า
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <script>
            $(document).ready(function() {
                const evaluateeOptionTemplate = $('#evaluatees option').map(function() {
                    return $(this).clone();
                }).get();

                const reviewerConfigs = [
                    {
                        selectId: 'evaluator_id',
                        displayId: 'selected-evaluators',
                        countId: 'evaluators-selected-count',
                        availableCountId: 'evaluators-available-count',
                        totalCountId: 'evaluators-total-count',
                        dropdownWrapperId: 'evaluators-dropdown-wrapper',
                        dropdownToggleId: 'evaluators-dropdown-toggle',
                        dropdownPanelId: 'evaluators-dropdown-panel',
                        dropdownLabelId: 'evaluators-dropdown-label',
                        checkboxListId: 'evaluators-checkbox-list',
                        departmentFilterId: 'evaluators-department-filter',
                        positionFilterId: 'evaluators-position-filter',
                        searchFilterId: 'evaluators-search-filter',
                        placeholder: '-- เลือกผู้ประเมิน/หัวหน้างาน --',
                        emptyText: 'ยังไม่ได้เลือกผู้ประเมิน/หัวหน้างาน',
                        className: 'bg-green-100 text-green-800',
                    },
                    {
                        selectId: 'director_id',
                        displayId: 'selected-directors',
                        countId: 'directors-selected-count',
                        availableCountId: 'directors-available-count',
                        totalCountId: 'directors-total-count',
                        dropdownWrapperId: 'directors-dropdown-wrapper',
                        dropdownToggleId: 'directors-dropdown-toggle',
                        dropdownPanelId: 'directors-dropdown-panel',
                        dropdownLabelId: 'directors-dropdown-label',
                        checkboxListId: 'directors-checkbox-list',
                        departmentFilterId: 'directors-department-filter',
                        positionFilterId: 'directors-position-filter',
                        searchFilterId: 'directors-search-filter',
                        placeholder: '-- เลือกกรรมการ --',
                        emptyText: 'ยังไม่ได้เลือกกรรมการ',
                        className: 'bg-amber-100 text-amber-800',
                    },
                    {
                        selectId: 'manager_id',
                        displayId: 'selected-managers',
                        countId: 'managers-selected-count',
                        availableCountId: 'managers-available-count',
                        totalCountId: 'managers-total-count',
                        dropdownWrapperId: 'managers-dropdown-wrapper',
                        dropdownToggleId: 'managers-dropdown-toggle',
                        dropdownPanelId: 'managers-dropdown-panel',
                        dropdownLabelId: 'managers-dropdown-label',
                        checkboxListId: 'managers-checkbox-list',
                        departmentFilterId: 'managers-department-filter',
                        positionFilterId: 'managers-position-filter',
                        searchFilterId: 'managers-search-filter',
                        placeholder: '-- เลือกผู้บริหาร --',
                        emptyText: 'ยังไม่ได้เลือกผู้บริหาร',
                        className: 'bg-rose-100 text-rose-800',
                    }
                ];
                const reviewerOptionTemplates = Object.fromEntries(
                    reviewerConfigs.map(config => [
                        config.selectId,
                        $(`#${config.selectId} option`).map(function() {
                            return $(this).clone();
                        }).get()
                    ])
                );

                function normalizePersonnelType(value) {
                    const text = String(value || '').trim();
                    if (!text) return '';
                    if (text.includes('วิชาการ')) return 'วิชาการ';
                    if (text.includes('สนับสนุน')) return 'สนับสนุน';
                    if (text.includes('บริหาร') || text.includes('ผู้บริหาร')) return 'บริหาร';
                    return text;
                }
                let filteredEvaluateeOptions = [];

                function updateEvaluateesDropdownLabel() {
                    const selectedCount = ($('#evaluatees').val() || []).length;
                    const label = selectedCount > 0
                        ? `เลือกผู้รับการประเมิน (${selectedCount} คน)`
                        : 'เลือกผู้รับการประเมิน';
                    $('#evaluatees-dropdown-label').text(label);
                }

                function renderEvaluateeCheckboxList() {
                    const selectedValues = new Set(($('#evaluatees').val() || []).map(String));
                    const $list = $('#evaluatees-checkbox-list');

                    if (!$list.length) {
                        return;
                    }

                    if (filteredEvaluateeOptions.length === 0) {
                        $list.html('<div class="text-sm text-gray-500">ไม่พบรายชื่อผู้รับการประเมิน</div>');
                        return;
                    }

                    let html = '';
                    filteredEvaluateeOptions.forEach(option => {
                        const $option = $(option);
                        const value = String($option.val());
                        const userName = $option.data('user-name') || $option.text() || 'ไม่ระบุ';
                        const userEmail = $option.data('user-email') || '';
                        const checked = selectedValues.has(value) ? 'checked' : '';

                        html += `
                            <label class="grid cursor-pointer grid-cols-[18px_minmax(0,180px)_minmax(0,1fr)] items-center gap-x-3 border-b border-blue-50 px-1 py-2 text-sm text-gray-700 transition last:border-b-0 hover:bg-blue-50/50">
                                <input type="checkbox" class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-500 evaluatee-checkbox" value="${value}" ${checked}>
                                <span class="truncate font-semibold leading-5 text-slate-800">${userName}</span>
                                <span class="truncate text-xs leading-4 text-slate-500">${userEmail}</span>
                            </label>
                        `;
                    });

                    $list.html(html);
                }

                function filterEvaluateesByCriteria() {
                    const selectedAssessmentType = normalizePersonnelType($('#report_data_id').find('option:selected').data('assessment-type'));
                    const selectedDepartment = String($('#evaluatees-department-filter').val() || '').trim();
                    const selectedPosition = String($('#evaluatees-position-filter').val() || '').trim();
                    const searchKeyword = String($('#evaluatees-search-filter').val() || '').trim().toLowerCase();
                    const $evaluatees = $('#evaluatees');
                    const currentSelected = $evaluatees.val() || [];

                    filteredEvaluateeOptions = evaluateeOptionTemplate.filter(option => {
                        const $option = $(option);
                        const userType = normalizePersonnelType($(option).data('personnel-type'));
                        const userName = String($option.data('user-name') || '').trim();
                        const userDepartment = String($option.data('user-department') || '').trim();
                        const userPosition = String($option.data('user-position') || '').trim();
                        const matchedAssessmentType = !selectedAssessmentType || userType === selectedAssessmentType;
                        const matchedDepartment = !selectedDepartment || userDepartment === selectedDepartment;
                        const matchedPosition = !selectedPosition || userPosition === selectedPosition;
                        const searchHaystack = `${userName} ${userPosition}`.toLowerCase();
                        const matchedSearch = !searchKeyword || searchHaystack.includes(searchKeyword);

                        return matchedAssessmentType && matchedDepartment && matchedPosition && matchedSearch;
                    }).map(option => $(option).clone());

                    const nextSelected = filteredEvaluateeOptions
                        .map(option => String(option.val()))
                        .filter(value => currentSelected.includes(value));

                    $evaluatees.empty().append(filteredEvaluateeOptions);
                    $evaluatees.val(nextSelected);
                    $('#evaluatees-available-count').text(filteredEvaluateeOptions.length);
                    $('#evaluatees-total-count').text(evaluateeOptionTemplate.length);
                    renderEvaluateeCheckboxList();
                    updateEvaluateesSelectAllState();
                }

                function filterReviewerOptions(config) {
                    const $select = $(`#${config.selectId}`);
                    const currentSelected = $select.val();
                    const selectedDepartment = String($(`#${config.departmentFilterId}`).val() || '').trim();
                    const selectedPosition = String($(`#${config.positionFilterId}`).val() || '').trim();
                    const searchKeyword = String($(`#${config.searchFilterId}`).val() || '').trim().toLowerCase();
                    const optionTemplate = reviewerOptionTemplates[config.selectId] || [];

                    const matchedOptions = optionTemplate.filter(option => {
                        const $option = $(option);
                        const value = String($option.val() || '').trim();
                        if (!value) {
                            return true;
                        }

                        const userName = String($option.data('user-name') || '').trim();
                        const userDepartment = String($option.data('user-department') || '').trim();
                        const userPosition = String($option.data('user-position') || '').trim();
                        const matchedDepartment = !selectedDepartment || userDepartment === selectedDepartment;
                        const matchedPosition = !selectedPosition || userPosition === selectedPosition;
                        const searchHaystack = `${userName} ${userPosition}`.toLowerCase();
                        const matchedSearch = !searchKeyword || searchHaystack.includes(searchKeyword);

                        return matchedDepartment && matchedPosition && matchedSearch;
                    }).map(option => $(option).clone());

                    const hasSelected = matchedOptions.some(option => String(option.val()) === String(currentSelected || ''));
                    const nextSelected = hasSelected ? currentSelected : '';

                    $select.empty().append(matchedOptions);
                    $select.val(nextSelected);

                    const availableCount = Math.max(matchedOptions.length - 1, 0);
                    $(`#${config.availableCountId}`).text(availableCount);
                    $(`#${config.totalCountId}`).text(Math.max(optionTemplate.length - 1, 0));
                    renderReviewerCheckboxList(config, matchedOptions);
                    updateReviewerDropdownLabel(config);
                }

                function renderReviewerCheckboxList(config, matchedOptions) {
                    const $list = $(`#${config.checkboxListId}`);
                    const selectedValue = String($(`#${config.selectId}`).val() || '');

                    if (!$list.length) {
                        return;
                    }

                    if (matchedOptions.length <= 1) {
                        $list.html('<div class="text-sm text-gray-500">ไม่พบรายชื่อ</div>');
                        return;
                    }

                    let html = '';
                    matchedOptions.forEach(option => {
                        const $option = $(option);
                        const value = String($option.val() || '').trim();
                        if (!value) {
                            return;
                        }

                        const checked = selectedValue === value ? 'checked' : '';
                        const userName = $option.data('user-name') || $option.text() || 'ไม่ระบุ';
                        const userEmail = $option.data('user-email') || '';

                        html += `
                            <label class="grid cursor-pointer grid-cols-[18px_minmax(0,180px)_minmax(0,1fr)] items-center gap-x-3 border-b border-slate-100 px-1 py-2 text-sm text-gray-700 transition last:border-b-0 hover:bg-slate-50">
                                <input type="checkbox" class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-500 reviewer-checkbox" data-select-id="${config.selectId}" value="${value}" ${checked}>
                                <span class="truncate font-semibold leading-5 text-slate-800">${userName}</span>
                                <span class="truncate text-xs leading-4 text-slate-500">${userEmail}</span>
                            </label>
                        `;
                    });

                    $list.html(html || '<div class="text-sm text-gray-500">ไม่พบรายชื่อ</div>');
                }

                function updateReviewerDropdownLabel(config) {
                    const $select = $(`#${config.selectId}`);
                    const selectedValue = $select.val();

                    if (!selectedValue) {
                        $(`#${config.dropdownLabelId}`).text(config.placeholder);
                        return;
                    }

                    const selectedOption = $select.find(`option[value="${selectedValue}"]`);
                    const userName = selectedOption.data('user-name') || selectedOption.text() || config.placeholder;
                    $(`#${config.dropdownLabelId}`).text(userName);
                }

                function updateDisplayAndCounts() {
                    // Update evaluatees
                    const $evaluatees = $('#evaluatees');
                    const selectedEvaluatees = $evaluatees.val() || [];
                    const evaluateesCount = selectedEvaluatees.length;
                    
                    $('#evaluatees-selected-count').text(evaluateesCount);

                    if (evaluateesCount === 0) {
                        $('#selected-evaluatees').html(
                            '<span class="text-sm text-gray-500">ยังไม่ได้เลือกผู้รับการประเมิน</span>');
                    } else {
                        let html = '';
                        selectedEvaluatees.forEach(val => {
                            const option = $evaluatees.find(`option[value="${val}"]`);
                            const userName = option.data('user-name') || option.text() || 'ไม่ระบุ';
                            html += `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${userName}
                            </span>`;
                        });
                        $('#selected-evaluatees').html(html);
                    }

                    updateEvaluateesDropdownLabel();
                    updateEvaluateesSelectAllState();

                    reviewerConfigs.forEach(config => {
                        const $select = $(`#${config.selectId}`);
                        const selectedValue = $select.val();
                        $(`#${config.countId}`).text(selectedValue ? 1 : 0);
                        updateReviewerDropdownLabel(config);

                        if (!selectedValue) {
                            $(`#${config.displayId}`).html(`<span class="text-sm text-gray-500">${config.emptyText}</span>`);
                            return;
                        }

                        const selectedOption = $select.find(':selected');
                        const userName = selectedOption.data('user-name') || selectedOption.text() || 'ไม่ระบุ';
                        const tag = `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${config.className}">
                            ${userName}
                        </span>`;
                        $(`#${config.displayId}`).html(tag);
                    });

                    // Update summary
                    updateSummary();
                }

                function updateEvaluateesSelectAllState() {
                    const $selectAll = $('#evaluatees-select-all');
                    const selectedValues = new Set(($('#evaluatees').val() || []).map(String));
                    const totalVisible = filteredEvaluateeOptions.length;
                    const selectedVisibleCount = filteredEvaluateeOptions.filter(option => selectedValues.has(String($(option).val()))).length;

                    if (!$selectAll.length) {
                        return;
                    }

                    if (totalVisible === 0) {
                        $selectAll.prop({
                            checked: false,
                            indeterminate: false,
                            disabled: true,
                        });
                        return;
                    }

                    const allSelected = selectedVisibleCount === totalVisible;
                    const partiallySelected = selectedVisibleCount > 0 && selectedVisibleCount < totalVisible;

                    $selectAll.prop({
                        checked: allSelected,
                        indeterminate: partiallySelected,
                        disabled: false,
                    });
                }

                function updateSummary() {
                    try {
                        // Update period
                        const startTime = $('#start_time').val();
                        const endTime = $('#end_time').val();
                        const $summaryPeriod = $('#summary-period');
                        
                        if (startTime && endTime && $summaryPeriod.length) {
                            const start = new Date(startTime);
                            const end = new Date(endTime);
                            const diffTime = Math.abs(end - start);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                            $summaryPeriod.text(diffDays);
                        } else if ($summaryPeriod.length) {
                            $summaryPeriod.text('-');
                        }

                        // Update criteria
                        const selectedCriteria = $('#report_data_id option:selected').text();
                        const $summaryCriteriaFull = $('#summary-criteria-full');
                        const $summaryCriteriaDescription = $('#summary-criteria-description');
                        
                        if ($summaryCriteriaFull.length) {
                            if (selectedCriteria && selectedCriteria !== '-- กรุณาเลือกเกณฑ์การประเมิน --') {
                                $summaryCriteriaFull.text(selectedCriteria);
                                $summaryCriteriaDescription.text('เกณฑ์ที่เลือกสำหรับการประเมินในครั้งนี้');
                            } else {
                                $summaryCriteriaFull.text('-');
                                $summaryCriteriaDescription.text('กรุณาเลือกเกณฑ์การประเมิน');
                            }
                        }
                    } catch (error) {
                        console.error('Error updating summary:', error);
                    }
                }

                filterEvaluateesByCriteria();
                updateDisplayAndCounts();

                // Event listeners
                $('#start_time, #end_time').on('change', updateSummary);
                $('#report_data_id').on('change', function() {
                    filterEvaluateesByCriteria();
                    updateDisplayAndCounts();
                });

                $('#evaluatees-department-filter, #evaluatees-position-filter').on('change', function() {
                    filterEvaluateesByCriteria();
                    updateDisplayAndCounts();
                });

                $('#evaluatees-search-filter').on('input', function() {
                    filterEvaluateesByCriteria();
                    updateDisplayAndCounts();
                });

                $('#toggle-evaluatees-dropdown').on('click', function() {
                    $('#evaluatees-dropdown-panel').toggleClass('hidden');
                    $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
                });

                reviewerConfigs.forEach(config => {
                    $(`#${config.dropdownToggleId}`).on('click', function() {
                        const $panel = $(`#${config.dropdownPanelId}`);
                        $panel.toggleClass('hidden');
                        $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
                    });
                });

                $(document).on('click', function(event) {
                    const $wrapper = $('#evaluatees-dropdown-wrapper');
                    if ($wrapper.length && !$wrapper.is(event.target) && !$wrapper.has(event.target).length) {
                        $('#evaluatees-dropdown-panel').addClass('hidden');
                        $('#toggle-evaluatees-dropdown').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }

                    reviewerConfigs.forEach(config => {
                        const $reviewerWrapper = $(`#${config.dropdownWrapperId}`);
                        if ($reviewerWrapper.length && !$reviewerWrapper.is(event.target) && !$reviewerWrapper.has(event.target).length) {
                            $(`#${config.dropdownPanelId}`).addClass('hidden');
                            $(`#${config.dropdownToggleId}`).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        }
                    });
                });

                reviewerConfigs.forEach(config => {
                    $(`#${config.departmentFilterId}, #${config.positionFilterId}`).on('change', function() {
                        filterReviewerOptions(config);
                        updateDisplayAndCounts();
                    });

                    $(`#${config.searchFilterId}`).on('input', function() {
                        filterReviewerOptions(config);
                        updateDisplayAndCounts();
                    });
                });

                $('#evaluatees').on('change', function() {
                    updateDisplayAndCounts();
                });

                $(document).on('change', '.evaluatee-checkbox', function() {
                    const selectedValues = $('.evaluatee-checkbox:checked').map(function() {
                        return String($(this).val());
                    }).get();

                    $('#evaluatees').val(selectedValues);
                    updateDisplayAndCounts();
                });

                $(document).on('change', '.reviewer-checkbox', function() {
                    const selectId = $(this).data('select-id');
                    const config = reviewerConfigs.find(item => item.selectId === selectId);
                    if (!config) {
                        return;
                    }

                    const selectedValue = $(this).is(':checked') ? String($(this).val()) : '';
                    $(`#${config.selectId}`).val(selectedValue);
                    renderReviewerCheckboxList(config, ($(`#${config.selectId} option`).map(function() {
                        return $(this).clone();
                    }).get()));
                    updateDisplayAndCounts();
                });

                $('#evaluatees-select-all').on('change', function() {
                    const selectedValues = new Set(($('#evaluatees').val() || []).map(String));
                    const visibleValues = filteredEvaluateeOptions.map(option => String($(option).val()));

                    if ($(this).is(':checked')) {
                        visibleValues.forEach(value => selectedValues.add(value));
                    } else {
                        visibleValues.forEach(value => selectedValues.delete(value));
                    }

                    $('#evaluatees').val(Array.from(selectedValues));
                    renderEvaluateeCheckboxList();
                    updateDisplayAndCounts();
                });

                reviewerConfigs.forEach(config => {
                    filterReviewerOptions(config);
                });

                // Update summary initially
                updateSummary();

                // Reset button
                $('#reset-btn').on('click', function() {
                    if (confirm('คุณต้องการล้างข้อมูลในฟอร์มทั้งหมดใช่หรือไม่?')) {
                        $('#evaluation-form')[0].reset();
                        $('#evaluatees').val(null);
                        $('#evaluatees-search-filter').val('');
                        $('#evaluator_id').val(null);
                        $('#director_id').val(null);
                        $('#manager_id').val(null);
                        reviewerConfigs.forEach(config => {
                            $(`#${config.departmentFilterId}`).val('');
                            $(`#${config.positionFilterId}`).val('');
                            $(`#${config.searchFilterId}`).val('');
                            filterReviewerOptions(config);
                            $(`#${config.dropdownPanelId}`).addClass('hidden');
                            $(`#${config.dropdownToggleId}`).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        });
                        $('#evaluatees-dropdown-panel').addClass('hidden');
                        $('#toggle-evaluatees-dropdown').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        filterEvaluateesByCriteria();
                        updateDisplayAndCounts();
                        updateSummary();
                        alert('ล้างข้อมูลในฟอร์มเรียบร้อยแล้ว');
                    }
                });

                // Form validation on submit
                $('#evaluation-form').on('submit', function(e) {
                    const submitButton = $(this).find('button[type="submit"]');
                    const loadingOverlay = $('#loading-overlay');

                    const evaluateesSelected = $('#evaluatees').val() || [];
                    const selectedReviewers = ['#evaluator_id', '#director_id', '#manager_id']
                        .map(id => $(id).val())
                        .filter(Boolean);
                    const reportDataId = $('#report_data_id').val();
                    const startTime = $('#start_time').val();
                    const endTime = $('#end_time').val();

                    // Validation
                    if (!reportDataId) {
                        e.preventDefault();
                        alert('กรุณาเลือกเกณฑ์การประเมิน');
                        return false;
                    }

                    if (!startTime) {
                        e.preventDefault();
                        alert('กรุณาเลือกวันเริ่มต้นประเมิน');
                        return false;
                    }

                    if (!endTime) {
                        e.preventDefault();
                        alert('กรุณาเลือกวันสิ้นสุดประเมิน');
                        return false;
                    }

                    if (evaluateesSelected.length === 0) {
                        e.preventDefault();
                        alert('กรุณาเลือกผู้รับการประเมินอย่างน้อย 1 คน');
                        return false;
                    }

                    if (selectedReviewers.length === 0) {
                        e.preventDefault();
                        alert('กรุณาเลือกผู้ประเมินอย่างน้อย 1 บทบาท');
                        return false;
                    }

                    submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังบันทึก...');
                    if (loadingOverlay && loadingOverlay.length) {
                        loadingOverlay.removeClass('hidden');
                    }
                });

                // Initialize Flatpickr
                flatpickr(".flatpickr-date", {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d/m/Y",
                    locale: "th",
                    allowInput: true
                });

                // Auto hide alerts
                window.setTimeout(function() {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(alert => {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                            bsAlert.close();
                        }
                    });
                }, 5000);
            });
        </script>
    </body>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        /* Step indicators */
        .step-card {
            transition: all 0.3s ease;
            position: relative;
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 8px 8px 0 0;
        }

        .step-card.step-1::before { background: linear-gradient(90deg, #2563eb, #3b82f6); }
        .step-card.step-2::before { background: linear-gradient(90deg, #059669, #10b981); }
        .step-card.step-3::before { background: linear-gradient(90deg, #7c3aed, #8b5cf6); }
        .step-card.step-4::before { background: linear-gradient(90deg, #ea580c, #f97316); }

        /* Form sections hover effects */
        .form-section {
            transition: all 0.3s ease;
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Position selection cards */
        .position-card {
            transition: all 0.3s ease;
        }

        .position-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Summary cards animation */
        .summary-card {
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        /* Gradient backgrounds */
        .bg-gradient-blue { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
        .bg-gradient-green { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); }
        .bg-gradient-purple { background: linear-gradient(135deg, #e9d5ff 0%, #ddd6fe 100%); }
        .bg-gradient-orange { background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%); }

        /* Animation for step completion */
        .step-completed {
            animation: stepComplete 0.5s ease-in-out;
        }

        @keyframes stepComplete {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>

    <div id="loading-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden z-50">
        <div class="flex items-center justify-center h-full">
            <div class="text-center text-white">
                <!-- Spinner -->
                <svg class="animate-spin h-10 w-10 text-white mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-lg font-semibold">กำลังบันทึกข้อมูล...</p>
                <p class="text-sm">กรุณารอสักครู่</p>
            </div>
        </div>
    </div>
@endsection
