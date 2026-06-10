@extends('layouts.app')
{{-- หน้าสร้างรอบการประเมิน ใช้ partial ร่วมกับหน้าแก้ไขเพื่อลดโค้ดซ้ำ --}}

@section('title', 'สร้างรอบการประเมินใหม่')

@section('content')
    @include('assignment-data.partials.flash-messages')

    <div class="bg-gray-50 min-h-screen py-8">
        <div class="py-12 max-w-6xl mx-auto px-4">
            @php
                $startTimeValue = old('start_time');
                $endTimeValue = old('end_time');
                $currentReportDataId = null;
            @endphp

            @include('assignment-data.partials.create-page-header')

            <form id="evaluation-form" action="{{ route('assignment-data.store') }}" method="POST" novalidate>
                @csrf

                @include('assignment-data.partials.form-period-section', compact('startTimeValue', 'endTimeValue'))
                @include('assignment-data.partials.form-criteria-section', compact('report_data', 'currentReportDataId'))

                @php
                    // รวมตัวเลือกหน่วยงาน/ตำแหน่งไว้ใช้ทั้ง filter ผู้รับการประเมินและ reviewer
                    $departmentOptions = $users->pluck('department.department_name')->filter()->unique()->sort()->values();
                    $positionOptions = $users->pluck('position.name')->filter()->unique()->sort()->values();
                    // เก็บค่าที่ผู้ใช้เลือกไว้ผ่าน old() เพื่อให้ฟอร์มไม่รีเซ็ตเมื่อ validation ไม่ผ่าน
                    $selectedEvaluatees = collect(old('evaluatees', []))->map(fn ($id) => (int) $id)->all();
                    $selectedEvaluateeUsers = $users->whereIn('id', $selectedEvaluatees);
                    // config นี้คุมเฉพาะหน้าตา/โครง UI ของ block ผู้รับการประเมิน
                    $evaluateesUi = [
                        'dropdown_button_class' => 'flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 text-left text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500',
                        'dropdown_icon_class' => 'fas fa-chevron-down text-xs text-slate-500',
                        'dropdown_panel_class' => 'mt-3 hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm',
                        'checkbox_list_class' => 'max-h-72 overflow-y-auto rounded-md border border-blue-100 bg-white px-3 py-2',
                        'selected_wrapper_class' => 'mt-4 p-4 bg-white rounded-lg min-h-[60px] border border-blue-200',
                        'selected_display_class' => 'flex flex-col gap-2',
                        'selected_item_class' => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
                        'empty_item_class' => 'text-sm text-gray-500',
                    ];
                    // หน้า create ใช้มุมมอง reviewer แบบ chip เพื่อให้เห็นผู้เลือกแบบกระชับ
                    $reviewerSelectionDisplay = 'chips';
                    // reviewerCards คือ schema กลางของการ์ดแต่ละบทบาท
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

                @include('assignment-data.partials.form-participants-section', [
                    'users' => $users,
                    'departmentOptions' => $departmentOptions,
                    'positionOptions' => $positionOptions,
                    'selectedEvaluatees' => $selectedEvaluatees,
                    'selectedEvaluateeUsers' => $selectedEvaluateeUsers,
                    'evaluateesUi' => $evaluateesUi,
                    'reviewerCards' => $reviewerCards,
                    'reviewerSelectionDisplay' => $reviewerSelectionDisplay,
                ])

                @include('assignment-data.partials.form-summary-actions')
            </form>
        </div>

        @php
            // config กลางสำหรับ JS ฝั่งฟอร์ม
            $formBehaviorConfig = [
                'evaluateeNameClass' => 'truncate font-semibold leading-5 text-slate-800',
                'evaluateeMetaClass' => 'truncate text-xs leading-4 text-slate-500',
                'evaluateeDropdownLabel' => 'เลือกผู้รับการประเมิน',
                'evaluateeNoResultsText' => 'ไม่พบรายชื่อผู้รับการประเมิน',
                'evaluateeEmptyText' => 'ยังไม่ได้เลือกผู้รับการประเมิน',
                'evaluateeSelectedItemClass' => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
                'evaluateeSelectedEmptyClass' => 'text-sm text-gray-500',
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
                'resetEvaluatorWithChangeEvent' => false,
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
                    'stageOrderDuplicate' => 'ลำดับการประเมินของแต่ละบทบาทต้องไม่ซ้ำกัน',
                ],
                'reviewerConfigs' => collect($reviewerCards)->map(fn ($card) => [
                    'stageKey' => $card['key'],
                    'stageOrderId' => 'stage_order_' . $card['key'],
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
                    'emptyClass' => 'text-sm text-gray-500',
                    'selectedItemClass' => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ' . $card['tag_class'],
                ])->values()->all(),
            ];
        @endphp

        @include('assignment-data.partials.form-behavior-script', ['formBehaviorConfig' => $formBehaviorConfig])
    </div>

    @include('assignment-data.partials.form-styles')
    @include('assignment-data.partials.loading-overlay')
@endsection
