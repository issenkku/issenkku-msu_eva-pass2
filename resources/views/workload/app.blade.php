@extends('layouts.app')

@section('title', 'ตั้งค่าเกณฑ์ด้านปริมาณ')

@section('content')
    {{-- หน้าตั้งค่าเกณฑ์ด้านปริมาณ --}}
    <div class="workload-page">
        {{-- ส่วนหัวหน้า --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">ตั้งค่าเกณฑ์ภารงานด้านปริมาณ</h1>
                <p class="page-subtitle">กรุณากรอกข้อมูลเกณฑ์ภารงานการประเมินให้ครบถ้วนเพื่อจัดทำเกณฑ์ที่สมบูรณ์</p>
            </div>
        </div>

        {{-- พื้นที่หลัก: เมนูซ้าย + เนื้อหาขวา --}}
        <div class="workload-grid">
            {{-- เมนูรายการหลัก --}}
            <aside class="workload-nav">
                <div class="nav-title">ด้านปริมาณผลงาน</div>
                <div class="nav-list" id="workload-nav-list" aria-busy="true">
                    <button class="nav-item2 is-active" type="button" disabled tabindex="-1">
                        กำลังโหลดรายการ...
                    </button>
                </div>
            </aside>

            {{-- เนื้อหาการตั้งค่า --}}
            <section class="workload-content">
                <div class="section-heading">
                    <span class="section-badge" id="workload-section-badge">1</span>
                    <h2 class="section-title" id="workload-section-title"></h2>
                </div>

                {{-- รายการการ์ดหลัก (หมวดหลัก) --}}
                <div class="workload-card-list">
                    {{-- การ์ดแม่แบบของหมวดหลัก --}}
                    <div class="card workload-card">
                        <div class="card-body">
                        <div class="card-head">
                            <div>
                                <h3 class="card-title">หมวดหมู่การประเมิน</h3>
                                <div class="workload-main-summary">หมวดหมู่หลัก</div>
                            </div>
                            <div class="card-actions">
                                <button class="icon-btn is-drag workload-drag-handle workload-drag-handle-text" type="button" aria-label="ลากเพื่อจัดอันดับ" title="ลากเพื่อจัดอันดับ">
                                    <span class="workload-drag-icon" aria-hidden="true">⋮⋮</span>
                                    <span class="workload-drag-label">ลากจัดลำดับ</span>
                                </button>
                                <button class="icon-btn workload-collapse-toggle" title="ยุบ" type="button" aria-label="ยุบหมวดหลัก">
                                    <span>˅</span>
                                </button>
                                <button type="button" class="delete_category_btn text-red-600 hover:text-red-800 transition duration-200" title="ลบ" aria-label="ลบหมวดหลัก">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 workload-delete-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- กำหนดข้อมูลหมวดหลัก --}}
                        <div class="main-criteria-block mt-4">
                            <div class="main-criteria-labels">
                                <div class="form-label">ลำดับ</div>
                                <div class="form-label">หมวดหมู่หลัก <span class="req">*</span></div>
                            </div>
                            <div class="main-criteria-fields">
                                <div class="sequence-display workload-main-sequence-display" id="workload-main-sequence-display">1</div>
                                <input type="hidden" class="workload-main-sequence" id="workload-main-sequence" value="1">
                                <input id="workload-main-category" type="text" class="form-control workload-main-category" value=""
                                    aria-label="หมวดหมู่หลัก"
                                    placeholder="กรุณากรอกหมวดหมู่หลัก">
                            </div>
                        </div>

                        {{-- กลุ่มหมวดย่อย (รายการย่อยหลายรายการ) --}}
                        <div class="sub-block mt-4">
                            <div class="sub-header">
                                <div class="sub-title">
                                    <span class="sub-icon">📋</span>
                                    รายการหมวดหมู่ย่อย
                                </div>
                            </div>

                            {{-- การ์ดแม่แบบของหมวดย่อย --}}
                            <div class="sub-card workload-sub-card">
                                <div class="sub-card-head">
                                    <div>
                                        <div class="sub-card-title">รายการประเมิน</div>
                                        <div class="workload-sub-summary">หมวดย่อย</div>
                                    </div>
                                    <div class="card-actions">
                                        <button class="icon-btn is-drag workload-drag-handle workload-drag-handle-text" type="button" aria-label="ลากเพื่อจัดอันดับ" title="ลากเพื่อจัดอันดับ">
                                            <span class="workload-drag-icon" aria-hidden="true">⋮⋮</span>
                                            <span class="workload-drag-label">ลากจัดลำดับ</span>
                                        </button>
                                        <button class="icon-btn workload-collapse-toggle" title="ยุบ" type="button" aria-label="ยุบหมวดย่อย">
                                            <span>˅</span>
                                        </button>
                                        <button type="button" class="delete_category_btn text-red-600 hover:text-red-800 transition duration-200" title="ลบ" aria-label="ลบหมวดย่อย">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 workload-delete-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- กำหนดข้อมูลหมวดย่อย --}}
                                <div class="sub-criteria-block mt-3">
                                    <div class="sub-criteria-labels">
                                        <div class="form-label">ลำดับ</div>
                                        <div class="form-label">หมวดหมู่ย่อย <span class="req">*</span></div>
                                    </div>
                                    <div class="sub-criteria-fields">
                                        <div class="sequence-display workload-sub-sequence-display" id="workload-sub-sequence-display">1</div>
                                        <input type="hidden" class="workload-sub-sequence" id="workload-sub-sequence" value="1">
                                        <input id="workload-sub-category" type="text" class="form-control workload-sub-category" value=""
                                            aria-label="หมวดหมู่ย่อย"
                                            placeholder="กรุณากรอกหมวดหมู่ย่อย">
                                    </div>
                                    <label class="workload-require-subject-option">
                                        <input id="workload-require-subject" type="checkbox" class="workload-require-subject">
                                        <span>หัวข้อนี้ต้องเลือกรายวิชาและใช้ค่าหน่วยกิต</span>
                                    </label>
                                </div>

                                {{-- ตารางรายการภาระงาน + คะแนน --}}
                                <div class="subitem-table mt-4">
                                    <div class="subitem-header">
                                        <div>ค่าภาระงาน</div>
                                        <div>ชื่อภาระงาน <span class="req">*</span></div>
                                        <div>คะแนน <span class="req">*</span></div>
                                        <div></div>
                                    </div>
                                    <div class="subitem-row workload-item-row">
                                        <div class="subitem-label">
                                            <button class="icon-btn is-drag workload-drag-handle workload-item-drag-handle" type="button" aria-label="ลากเพื่อจัดอันดับ" title="ลากเพื่อจัดอันดับ">⋮⋮</button>
                                            <span class="subitem-sequence-text">1</span>
                                        </div>
                                        <input id="workload-item-name" type="text" class="form-control workload-item-name" value=""
                                            aria-label="ชื่อภาระงาน">
                                        <input id="workload-item-score" type="number" class="form-control workload-item-score" value=""
                                            min="0" step="0.01" aria-label="คะแนนภาระงาน">
                                        <button class="icon-btn is-danger workload-item-remove" type="button" aria-label="ลบรายการภาระงาน">×</button>
                                    </div>

                                    <div class="subitem-actions">
                                        <button class="btn btn-outline-primary btn-sm workload-add-item" id="workload-add-item"
                                            type="button">เพิ่มภาระงาน</button>
                                    </div>
                                </div>

                                {{-- ส่วนกำหนดสูตรการคำนวณ --}}
                                <div class="formula-block mt-4">
                                    <div class="sub-title">
                                        <span class="sub-icon">🧮</span>
                                        สูตรการคำนวณ
                                    </div>

                                    <div class="formula-panel">
                                        <div class="formula-row">
                                            <div>
                                                <label for="workload-variable-label" class="form-label">ชื่อตัวแปร</label>
                                                <input id="workload-variable-label" type="text" class="form-control workload-variable-label"
                                                    placeholder="กรอกชื่อตัวแปร">
                                            </div>
                                            <div>
                                                <label for="workload-variable-note" class="form-label">หมายเหตุ</label>
                                                <input id="workload-variable-note" type="text" class="form-control workload-variable-note"
                                                    placeholder="อธิบายว่าฟิลด์นี้ใช้กรอกอะไร">
                                            </div>
                                            <div>
                                                <label for="workload-variable-default-value" class="form-label">ค่าเริ่มต้น</label>
                                                <input id="workload-variable-default-value" type="text" class="form-control workload-variable-default-value"
                                                    placeholder="ใส่ค่าที่ต้องการให้แสดงไว้ก่อน">
                                            </div>
                                            <div>
                                                <label for="workload-variable-type" class="form-label">ประเภทอินพุต</label>
                                                <select id="workload-variable-type" class="form-select workload-variable-type">
                                                    <option value="">กรุณาเลือกประเภทอินพุต</option>
                                                    <option value="number" >Number (ตัวเลข)</option>
                                                    <option value="text">Text (ข้อความ)</option>
                                                  
                                                </select>
                                            </div>
                                            <div class="formula-action">
                                                <button class="btn btn-outline-primary btn-sm workload-add-variable"
                                                    type="button">เพิ่มตัวแปร</button>
                                                <button class="btn btn-outline-secondary btn-sm workload-cancel-variable-edit"
                                                    type="button" style="display:none;">ยกเลิก</button>
                                            </div>
                                        </div>

                                        <div class="formula-list workload-formula-list" id="workload-formula-list"></div>

                                        <div class="mt-3">
                                            <label for="workload-formula-text" class="form-label">สร้างการคำนวณ</label>

                                            <textarea class="form-control formula-text workload-formula-text" rows="4" id="workload-formula-text"></textarea>
                                        </div>

                                        {{-- แถบเครื่องมือช่วยเขียนสูตร --}}
                                        <div class="formula-toolbar">
                                            <div class="toolbar-group">
                                                <div class="toolbar-label">เครื่องหมาย</div>
                                                <div class="toolbar-buttons">
                                                    <button class="chip" type="button">+</button>
                                                    <button class="chip" type="button">-</button>
                                                    <button class="chip" type="button">*</button>
                                                    <button class="chip" type="button">/</button>
                                                    <button class="chip" type="button">(</button>
                                                    <button class="chip" type="button">)</button>
                                                    <button class="chip" type="button">&lt;</button>
                                                    <button class="chip" type="button">&gt;</button>
                                                    <button class="chip" type="button">&lt;=</button>
                                                    <button class="chip" type="button">&gt;=</button>
                                                    <button class="chip" type="button">IF</button>
                                                    <button class="chip" type="button">==</button>
                                                    <button class="chip" type="button">!=</button>
                                                </div>
                                            </div>
                                            <div class="toolbar-group">
                                                <div class="toolbar-label">ตัวแปร</div>
                                                <div class="toolbar-buttons workload-variable-chips" id="workload-variable-chips">
                                                    <button class="chip chip-muted" type="button"
                                                        disabled>ยังไม่มีตัวแปร</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-footer">
                                <button class="btn btn-outline-primary btn-sm workload-add-sub" type="button">เพิ่มหมวดย่อย</button>
                            </div>

                        </div>
                        <div class="sub-footer">
                            <button class="btn btn-outline-secondary btn-sm workload-add-main" type="button">เพิ่มหมวดหลัก</button>
                        </div>
                    </div>
                </div>
            </div>

                {{-- ปุ่มคำสั่งของหน้า --}}
                <div class="page-actions">
                    <a class="btn btn-outline-secondary" id="workload-back-link" href="/criteria-config/{{ request()->query('quant_sub_criteria_id') }}/edit">ย้อนกลับ</a>
                    <button class="btn btn-outline-primary" type="button" id="workload-reset">รีเซ็ตค่า</button>
                    <button class="btn btn-primary" type="button" id="workload-save">บันทึกการตั้งค่า</button>
                </div>
            </section>
        </div>
    </div>

    {{-- Toast แจ้งผลการทำงาน --}}
    <div class="workload-toast" id="workload-toast" aria-live="polite" aria-atomic="true">
        <div class="workload-toast-content">
            <span class="workload-toast-icon" aria-hidden="true">✓</span>
            <span class="workload-toast-text" id="workload-toast-text"></span>
            <button class="workload-toast-close" type="button" aria-label="ปิดข้อความแจ้งผล">×</button>
        </div>
    </div>
    <div id="floating_save_button" class="fixed bottom-6 right-6 z-40 hidden">
        <div class="flex items-center gap-3 rounded-2xl bg-blue-600 px-4 py-3 text-white shadow-2xl ring-1 ring-blue-500/40">
            <div class="hidden sm:block">
                <p class="text-sm font-semibold">มีการแก้ไขที่ยังไม่บันทึก</p>
                <p class="text-xs text-blue-100">กรุณากดบันทึกก่อนออกจากหน้านี้</p>
            </div>
            <button type="button" id="floating_save_submit" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                บันทึก
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- สไตล์และสคริปต์ของหน้าตั้งค่า workload --}}
    @include('workload.partials.app-styles')
    @include('workload.partials.app-script')
@endpush
