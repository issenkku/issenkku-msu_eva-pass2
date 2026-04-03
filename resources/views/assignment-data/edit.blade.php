@extends('layouts.app')
{{-- หน้าแก้ไขรอบการประเมิน ใช้โครงเดียวกับหน้า create แต่เติมค่าที่มีอยู่เดิม --}}

@section('title', 'แก้ไขรอบการประเมิน')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('assignment-data.partials.flash-messages')

    @php
        $flow = collect($assignmentData->evaluation_flow ?? [])->values();
        // แปลง evaluation_flow เดิมเป็นลำดับตัวเลขสำหรับ input แต่ละบทบาท
        // ถ้า role ใดไม่อยู่ใน flow จะ fallback เป็นลำดับมาตรฐานเพื่อให้ฟอร์มยังแสดงได้
        $stageOrders = [
            'evaluator' => $flow->search('evaluator') !== false ? $flow->search('evaluator') + 1 : 1,
            'director' => $flow->search('director') !== false ? $flow->search('director') + 1 : 2,
            'manager' => $flow->search('manager') !== false ? $flow->search('manager') + 1 : 3,
        ];
    @endphp

    <div class="bg-gray-50 min-h-screen py-8">
        <div class="py-12 max-w-6xl mx-auto px-4">
            @php
                $startTimeValue = old('start_time', $assignmentData->start_time ? $assignmentData->start_time->format('Y-m-d') : '');
                $endTimeValue = old('end_time', $assignmentData->end_time ? $assignmentData->end_time->format('Y-m-d') : '');
                $currentReportDataId = $assignmentData->assignments->first()?->report?->reportData?->id;
            @endphp

            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-edit mr-3 text-blue-600"></i>
                            แก้ไขรอบการประเมิน #{{ $assignmentData->id }}
                        </h1>
                        <p class="text-gray-600 mt-1">แก้ไขข้อมูลรอบการประเมิน</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('assignment-data.index') }}"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>กลับ
                        </a>
                    </div>
                </div>
            </div>

            <form id="evaluation-form" action="{{ route('assignment-data.update', $assignmentData->id) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                @include('assignment-data.partials.form-period-section', compact('startTimeValue', 'endTimeValue'))
                @include('assignment-data.partials.form-criteria-section', compact('report_data', 'currentReportDataId'))

                @php
                    // รวมตัวเลือกหน่วยงาน/ตำแหน่งไว้ให้ partial ใช้ filter แบบเดียวกับหน้า create
                    $departmentOptions = $users->pluck('department.department_name')->filter()->unique()->sort()->values();
                    $positionOptions = $users->pluck('position.name')->filter()->unique()->sort()->values();
                    // หน้าแก้ไขต้องรองรับทั้ง old() และค่าที่มีอยู่เดิมในฐานข้อมูล
                    $selectedEvaluatees = collect(old('evaluatees', $selectedEvaluatees ?? []))
                        ->map(fn ($id) => (int) $id)
                        ->all();
                    // ใช้ assignment เดิมเพื่อแสดงรายชื่อที่ถูกผูกไว้ก่อนเข้าแก้ไข
                    $selectedEvaluateeUsers = $assignmentData->assignments;
                    // หน้า edit ใช้ selected display แบบ list เพื่ออ่านค่าที่มีอยู่เดิมได้ชัดกว่าแบบ chip
                    $evaluateesUi = [
                        'dropdown_button_class' => 'flex w-full items-center justify-between rounded-md border border-blue-300 bg-white px-4 py-3 text-left text-sm text-gray-700 shadow-sm transition hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500',
                        'dropdown_icon_class' => 'fas fa-chevron-down text-xs text-gray-500',
                        'dropdown_panel_class' => 'mt-3 hidden rounded-lg border border-blue-200 bg-white p-4 shadow-sm',
                        'checkbox_list_class' => 'max-h-72 overflow-y-auto rounded-md border border-blue-100 bg-white px-3 py-2',
                        'selected_wrapper_class' => 'mt-4 p-4 bg-white rounded-lg min-h-[60px] border border-blue-200',
                        'selected_display_class' => 'overflow-hidden rounded-md border border-blue-100 bg-white',
                        'selected_item_class' => 'block border-b border-blue-50 px-3 py-2 text-sm text-gray-700 last:border-b-0',
                        'empty_item_class' => 'block px-3 py-2 text-sm text-gray-500',
                    ];
                    $reviewerSelectionDisplay = 'list';
                    // reviewerCards ทำหน้าที่เป็น data source เดียวของ UI reviewer ทั้ง 3 บทบาท
                    // ค่าที่ต่างจากหน้า create เช่น value เดิม และลำดับเดิม จะถูกกำหนดที่นี่
                    $reviewerCards = [
                        [
                            'key' => 'evaluator',
                            'id' => 'evaluator_id',
                            'badge' => 'B',
                            'title' => 'ผู้ประเมิน',
                            'count_id' => 'evaluators',
                            'placeholder' => '-- เลือกผู้ประเมิน --',
                            'empty_text' => 'ยังไม่ได้เลือกผู้ประเมิน',
                            'selected_label' => 'ผู้ประเมินที่เลือก',
                            'available_count' => $evaluatorUsers->count(),
                            'order' => old('stage_order.evaluator', $stageOrders['evaluator']),
                            'wrapper_class' => 'bg-green-50 border-green-200',
                            'badge_class' => 'bg-green-600',
                            'focus_class' => 'focus:ring-green-500',
                            'text_class' => 'text-green-600',
                            'icon_class' => 'text-green-500',
                            'tag_class' => 'bg-green-100 text-green-800',
                            'value' => $assignmentData->evaluator_id,
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
                            'order' => old('stage_order.director', $stageOrders['director']),
                            'wrapper_class' => 'bg-amber-50 border-amber-200',
                            'badge_class' => 'bg-amber-600',
                            'focus_class' => 'focus:ring-amber-500',
                            'text_class' => 'text-amber-600',
                            'icon_class' => 'text-amber-500',
                            'tag_class' => 'bg-amber-100 text-amber-800',
                            'value' => $assignmentData->director_id,
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
                            'order' => old('stage_order.manager', $stageOrders['manager']),
                            'wrapper_class' => 'bg-rose-50 border-rose-200',
                            'badge_class' => 'bg-rose-600',
                            'focus_class' => 'focus:ring-rose-500',
                            'text_class' => 'text-rose-600',
                            'icon_class' => 'text-rose-500',
                            'tag_class' => 'bg-rose-100 text-rose-800',
                            'value' => $assignmentData->manager_id,
                            'options' => $managerUsers,
                        ],
                    ];
                @endphp

                @include('assignment-data.partials.form-participants-section', [
                    'users' => $users,
                    'departmentOptions' => $departmentOptions,
                    'positionOptions' => $positionOptions,
                    'selectedEvaluatees' => $selectedEvaluatees,
                    'selectedEvaluateeUsers' => $selectedEvaluateeUsers->map(fn ($assignment) => $assignment->evaluateeUser),
                    'evaluateesUi' => $evaluateesUi,
                    'reviewerCards' => $reviewerCards,
                    'reviewerSelectionDisplay' => $reviewerSelectionDisplay,
                ])

                @include('assignment-data.partials.form-summary-actions')
            </form>
        </div>

        @php
            // config ฝั่ง JS ของหน้าแก้ไข
            // ส่วนใหญ่เหมือน create แต่มีบางค่าที่ต่างกัน เช่นรูปแบบ selected display และ reset behavior
            $formBehaviorConfig = [
                'evaluateeNameClass' => 'truncate font-medium text-gray-800',
                'evaluateeMetaClass' => 'truncate text-xs text-gray-500',
                'evaluateeDropdownLabel' => 'เลือกผู้รับการประเมิน',
                'evaluateeNoResultsText' => 'ไม่พบรายชื่อผู้รับการประเมิน',
                'evaluateeEmptyText' => 'ยังไม่ได้เลือกผู้รับการประเมิน',
                'evaluateeSelectedItemClass' => 'block border-b border-blue-50 px-3 py-2 text-sm text-gray-700 last:border-b-0',
                'evaluateeSelectedEmptyClass' => 'block px-3 py-2 text-sm text-gray-500',
                'reviewerNameClass' => 'truncate font-semibold leading-5 text-slate-800',
                'reviewerMetaClass' => 'truncate text-xs leading-4 text-slate-500',
                'reviewerNoResultsText' => 'ไม่พบรายชื่อ',
                'criteriaPlaceholderText' => '-- กรุณาเลือกเกณฑ์การประเมิน --',
                'criteriaSelectedDescription' => 'เกณฑ์ที่เลือกสำหรับการประเมินในครั้งนี้',
                'criteriaPromptDescription' => 'กรุณาเลือกเกณฑ์การประเมิน',
                'resetConfirmText' => 'คุณต้องการล้างข้อมูลในฟอร์มทั้งหมดใช่หรือไม่?',
                'resetSuccessText' => 'ล้างข้อมูลในฟอร์มเรียบร้อยแล้ว',
                'loadingHtml' => '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังบันทึก...',
                'notSpecifiedText' => 'ไม่ระบุ',
                'countUnit' => 'คน',
                'resetEvaluatorWithChangeEvent' => true,
                'personnelTypes' => [
                    'academic' => 'วิชาการ',
                    'support' => 'สนับสนุน',
                    'executive' => 'บริหาร',
                    'executiveAlt' => 'ผู้บริหาร',
                ],
                'validation' => [
                    'reportData' => 'กรุณาเลือกเกณฑ์การประเมิน',
                    'startTime' => 'กรุณาเลือกวันเริ่มต้นประเมิน',
                    'endTime' => 'กรุณาเลือกวันสิ้นสุดประเมิน',
                    'evaluatees' => 'กรุณาเลือกผู้รับการประเมินอย่างน้อย 1 คน',
                    'reviewers' => 'กรุณาเลือกผู้ประเมินอย่างน้อย 1 บทบาท',
                ],
                // แปลง reviewerCards ให้เหลือเฉพาะข้อมูลที่ JS ต้องใช้จริง
                'reviewerConfigs' => collect($reviewerCards)->map(fn ($card) => [
                    'selectId' => $card['id'],
                    'displayId' => 'selected-' . $card['count_id'],
                    'countId' => $card['count_id'] . '-selected-count',
                    'availableCountId' => $card['count_id'] . '-available-count',
                    'totalCountId' => $card['count_id'] . '-total-count',
                    'dropdownWrapperId' => $card['count_id'] . '-dropdown-wrapper',
                    'dropdownToggleId' => $card['count_id'] . '-dropdown-toggle',
                    'dropdownPanelId' => $card['count_id'] . '-dropdown-panel',
                    'dropdownLabelId' => $card['count_id'] . '-dropdown-label',
                    'checkboxListId' => $card['count_id'] . '-checkbox-list',
                    'departmentFilterId' => $card['count_id'] . '-department-filter',
                    'positionFilterId' => $card['count_id'] . '-position-filter',
                    'searchFilterId' => $card['count_id'] . '-search-filter',
                    'placeholder' => $card['placeholder'],
                    'emptyText' => $card['empty_text'],
                    'emptyClass' => 'block px-3 py-2 text-sm text-gray-500',
                    'selectedItemClass' => 'block px-3 py-2 text-sm text-gray-700',
                ])->values()->all(),
            ];
        @endphp

        @include('assignment-data.partials.form-behavior-script', ['formBehaviorConfig' => $formBehaviorConfig])
    </div>

    @include('assignment-data.partials.form-styles')
    @include('assignment-data.partials.loading-overlay')
@endsection
