@extends('layouts.app')

@section('title', 'ตั้งค่าเกณฑ์ด้านปริมาณ')

@section('content')
    {{-- หน้าตั้งค่าเกณฑ์ด้านปริมาณ --}}
    <div class="workload-page">
        {{-- ส่วนหัวหน้า --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">ตั้งค่าเกณฑ์ด้านปริมาณ</h1>
                <p class="page-subtitle">กรุณากรอกข้อมูลเกณฑ์การประเมินให้ครบถ้วนเพื่อจัดทำเกณฑ์ที่สมบูรณ์</p>
            </div>
        </div>

        {{-- พื้นที่หลัก: เมนูซ้าย + เนื้อหาขวา --}}
        <div class="workload-grid">
            {{-- เมนูรายการหลัก --}}
            <aside class="workload-nav">
                <div class="nav-title">ด้านปริมาณผลงาน</div>
                <div class="nav-list" id="workload-nav-list">
                    <button class="nav-item2 is-active" type="button">

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
                            </div>
                            <div class="card-actions">
                                <button class="icon-btn is-muted" title="ยุบ" type="button">
                                    <span>˅</span>
                                </button>
                                <button class="icon-btn is-primary" title="ขยาย" type="button">
                                    <span>˄</span>
                                </button>
                                <button class="icon-btn is-danger" title="ลบ" type="button">
                                    <span>🗑</span>
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
                                <input type="text" class="form-control workload-main-category" value=""
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
                                    <div class="sub-card-title">รายการประเมิน</div>
                                    <div class="card-actions">
                                        <button class="icon-btn is-muted" title="ยุบ" type="button">
                                            <span>˅</span>
                                        </button>
                                        <button class="icon-btn is-primary" title="ขยาย" type="button">
                                            <span>˄</span>
                                        </button>
                                        <button class="icon-btn is-danger" title="ลบ" type="button">
                                            <span>×</span>
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
                                        <input type="text" class="form-control workload-sub-category" value=""
                                            placeholder="กรุณากรอกหมวดหมู่ย่อย">
                                    </div>
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
                                        <div class="subitem-label">1</div>
                                        <input type="text" class="form-control workload-item-name" value="">
                                        <input type="number" class="form-control workload-item-score" value=""
                                            min="0" step="0.01">
                                        <button class="icon-btn is-danger workload-item-remove" type="button">×</button>
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
                                                <label class="form-label">ชื่อตัวแปร</label>
                                                <input type="text" class="form-control workload-variable-label"
                                                    placeholder="กรอกชื่อตัวแปร">
                                            </div>
                                            <div>
                                                <label class="form-label">หมายเหตุ</label>
                                                <input type="text" class="form-control workload-variable-note"
                                                    placeholder="อธิบายว่าฟิลด์นี้ใช้กรอกอะไร">
                                            </div>
                                            <div>
                                                <label class="form-label">ประเภทอินพุต</label>
                                                <select class="form-select workload-variable-type">
                                                    <option value="">กรุณาเลือกประเภทอินพุต</option>
                                                    <option value="number" >Number (ตัวเลข)</option>
                                                    <option value="text">Text (ข้อความ)</option>
                                                    <option value="item">Item (รายการภาระงาน)</option>
                                                </select>
                                            </div>
                                            <div class="formula-action">
                                                <button class="btn btn-outline-primary btn-sm workload-add-variable"
                                                    type="button">เพิ่มตัวแปร</button>
                                            </div>
                                        </div>

                                        <div class="formula-list workload-formula-list" id="workload-formula-list"></div>

                                        <div class="mt-3">
                                            <label class="form-label">สร้างการคำนวณ</label>

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
                                            {{-- <div class="toolbar-group">
                                                <div class="toolbar-label">คำสั่ง</div>
                                                <div class="toolbar-buttons">
                                                    <button class="chip" type="button">AND</button>
                                                    <button class="chip" type="button">OR</button>
                                                    <button class="chip" type="button">NOR</button>
                                                    <button class="chip" type="button">XOR</button>
                                                    <button class="chip" type="button">XNOR</button>
                                                    <button class="chip" type="button">NAND</button>
                                                    <button class="chip" type="button">NOT</button>
                                                </div>
                                            </div> --}}
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
                                {{-- <button class="btn btn-outline-primary btn-sm" id="workload-add-sub" type="button">เพิ่มหมวดย่อย</button> --}}
                                <button class="btn btn-outline-primary btn-sm workload-add-sub" type="button">เพิ่มหมวดย่อย</button>
                            </div>

                        </div>
                        <div class="sub-footer">
                            {{-- <button class="btn btn-outline-primary btn-sm workload-add-sub" type="button">เพิ่มหมวดย่อย</button> --}}
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
        {{--  --}}
        <div class="workload-toast-content">
            <span class="workload-toast-icon" aria-hidden="true">✓</span>
            <span class="workload-toast-text" id="workload-toast-text"></span>
            <button class="workload-toast-close" type="button" aria-label="Close">×</button>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- สไตล์เฉพาะหน้า --}}
    <style>
        .workload-page {
            padding: 20px 24px 40px;
            background: #f4f6f9;
            min-height: 80vh;
        }

        .page-header {
            margin-bottom: 12px;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .page-subtitle {
            color: #6b7280;
            margin: 8px 0 0;
            font-size: 0.95rem;
        }

        .workload-grid {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 24px;
            margin-top: 24px;
        }

        .workload-nav {
            background: #fff;
            border-radius: 16px;
            padding: 16px 14px;
            border: 1px solid #e5e7eb;
            height: fit-content;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        }

        .nav-title {
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
        }

        .nav-list {
            display: grid;
            gap: 8px;
            margin-top: 16px;
        }

        .workload-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            background: #16a34a;
            color: #ffffff;
            border-radius: 12px;
            padding: 12px 16px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
            transform: translateX(120%);
            transition: transform 0.3s ease;
            max-width: 360px;
            width: calc(100% - 40px);
        }

        .workload-toast.is-visible {
            transform: translateX(0);
        }

        .workload-toast.is-danger {
            background: #dc2626;
        }

        .workload-toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .workload-toast-icon {
            font-weight: 700;
        }

        .workload-toast-text {
            flex: 1;
            font-size: 0.95rem;
        }

        .workload-toast-close {
            background: transparent;
            border: none;
            color: inherit;
            font-size: 1.1rem;
            line-height: 1;
            cursor: pointer;
        }

        .nav-item2 {
            border: 1px solid transparent;
            background: #f3f4f6;
            padding: 10px 12px;
            border-radius: 10px;
            text-align: left;
            font-size: 0.9rem;
            color: #4b5563;
            transition: 0.2s ease;
            width: 100%;
        }

        .nav-item2:hover {
            background: #e5e7eb;
        }

        .nav-item2.is-active {
            border-color: #2563eb;
            background: #e0edff;
            color: #1d4ed8;
            font-weight: 600;
        }

        .workload-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .workload-content > .section-heading {
            order: 1;
        }

        .workload-content > .workload-card-list {
            order: 2;
        }

        .workload-content > .page-actions {
            order: 3;
        }

        .workload-card-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .section-title {
            margin: 0;
            font-weight: 600;
            color: #111827;
        }

        .workload-card {
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            position: relative;
            overflow: hidden;
        }

        .workload-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: #2563eb;
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }

        .card-actions {
            display: flex;
            gap: 8px;
        }

        .workload-card.is-collapsed .card-body > :not(.card-head) {
            display: none;
        }

        .workload-sub-card.is-collapsed > :not(.sub-card-head) {
            display: none;
        }

        .icon-btn {
            border: 1px solid #e5e7eb;
            background: #fff;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 0.85rem;
            color: #6b7280;
            line-height: 1;
        }

        .workload-card .card-actions .icon-btn {
            border-color: transparent;
            background: transparent;
            padding: 2px 6px;
            font-size: 1rem;
            color: #94a3b8;
        }

        .workload-card .card-actions .icon-btn:hover {
            color: #2563eb;
        }

        .workload-card .card-actions .icon-btn.is-danger:hover {
            color: #dc2626;
        }

        .workload-card .card-actions .icon-btn.is-primary {
            color: #2563eb;
        }

        .workload-card .card-actions .icon-btn.is-muted {
            color: #cbd5e1;
        }

        .icon-btn.is-danger {
            color: #dc2626;
            border-color: #fecaca;
            background: #fff5f5;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 12px 16px;
            align-items: center;
        }

        .form-label {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0;
        }

        .main-criteria-block {
            display: grid;
            gap: 8px;
        }

        .main-criteria-labels,
        .main-criteria-fields {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 16px;
            align-items: center;
        }

        .main-criteria-labels .form-label {
            color: #111827;
            font-weight: 600;
        }

        .sub-criteria-block {
            display: grid;
            gap: 8px;
            background: #f9fafb;
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        .sub-criteria-labels,
        .sub-criteria-fields {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 16px;
            align-items: center;
        }

        .sub-criteria-labels .form-label {
            color: #111827;
            font-weight: 600;
        }

        .main-criteria-fields .form-control {
            max-width: 100%;
        }

        .req {
            color: #ef4444;
        }

        .sub-block {
            background: #f8fafc;
            border-radius: 16px;
            padding: 16px;
            border: 1px solid #e5e7eb;
        }

        .sub-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sub-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #1f2937;
        }

        .sub-icon {
            font-size: 1.1rem;
        }

        .sub-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            padding: 16px;
            margin-top: 12px;
        }

        .sub-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sub-card-title {
            font-weight: 600;
            color: #111827;
        }

        .subitem-table {
            display: grid;
            gap: 10px;
        }

        .subitem-header {
            display: grid;
            grid-template-columns: 100px 1fr 140px 40px;
            gap: 12px;
            font-weight: 600;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .subitem-row {
            display: grid;
            grid-template-columns: 100px 1fr 140px 40px;
            gap: 12px;
            align-items: center;
        }

        .subitem-label {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
            color: #6b7280;
            font-weight: 600;
        }

        .subitem-actions {
            display: flex;
            justify-content: flex-start;
            margin-top: 8px;
        }

        .formula-block {
            margin-top: 16px;
        }

        .formula-panel {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            padding: 16px;
            margin-top: 12px;
        }

        .formula-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .formula-action {
            display: flex;
            justify-content: flex-end;
        }

        .formula-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .formula-item {
            display: grid;
            grid-template-columns: 1fr 120px 40px;
            gap: 10px;
            align-items: center;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            border-radius: 10px;
            color: #374151;
        }

        .formula-item-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .formula-item-label {
            font-weight: 600;
            color: #111827;
        }

        .formula-item-note {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .formula-value {
            color: #2563eb;
            font-weight: 600;
            text-align: right;
        }

        .formula-text {
            border-radius: 12px;
        }

        .formula-builder-bar {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .formula-toolbar {
            margin-top: 16px;
            display: grid;
            gap: 12px;
        }

        .toolbar-group {
            display: grid;
            gap: 8px;
        }

        .toolbar-label {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .toolbar-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .workload-variable-chips {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .workload-variable-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .chip {
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #2563eb;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .chip-muted {
            border-color: #e5e7eb;
            background: #f3f4f6;
            color: #9ca3af;
        }

        .sub-footer {
            margin-top: 12px;
        }

        .page-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 12px;
            padding-bottom: 8px;
        }

        @media (max-width: 992px) {
            .workload-grid {
                grid-template-columns: 1fr;
            }

            .subitem-header,
            .subitem-row {
                grid-template-columns: 1fr;
            }

            .formula-row {
                grid-template-columns: 1fr;
            }

            .formula-item {
                grid-template-columns: 1fr 1fr auto;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const quantSubCriteriaId = params.get('quant_sub_criteria_id');
            const backLink = document.getElementById('workload-back-link');

            const updateBackLink = (id) => {
                if (!backLink || !id) {
                    return;
                }
                backLink.href = `/criteria-config/${encodeURIComponent(id)}/edit`;
            };
            if (!quantSubCriteriaId) {
                return;
            }

            const navList = document.getElementById('workload-nav-list');
            const sectionBadge = document.getElementById('workload-section-badge');
            const sectionTitle = document.getElementById('workload-section-title');
            const saveButton = document.getElementById('workload-save');
            const resetButton = document.getElementById('workload-reset');
            const mainCard = document.querySelector('.workload-card');
            // อ้างอิง DOM หลักที่ใช้บ่อย
            const mainContainer = document.querySelector('.workload-card-list');
            const pageActions = document.querySelector('.page-actions');

            // ฟังก์ชันย่อย: renderNav
            const renderNav = (items, activeId) => {
                if (!navList) {
                    return;
                }

                navList.innerHTML = '';
                items.forEach((item) => {
                    const button = document.createElement('button');
                    button.className = `nav-item2${item.id === activeId ? ' is-active' : ''}`;
                    button.type = 'button';
                    button.addEventListener('click', () => {
                        window.location.href =
                            `/workload-config?quant_sub_criteria_id=${encodeURIComponent(item.id)}`;
                    });

                    const text = document.createElement('span');
                    text.className = 'nav-text';
                    text.textContent = item.name || `รายการ ${item.sequence || ''}`.trim();

                    button.appendChild(text);
                    navList.appendChild(button);
                });

                if (sectionTitle) {
                    const activeButton = navList.querySelector('.nav-item2.is-active .nav-text');
                    if (activeButton) {
                        sectionTitle.textContent = activeButton.textContent.trim();
                    }
                }
                if (sectionBadge) {
                    const activeIndex = items.findIndex((item) => item.id === activeId);
                    if (activeIndex >= 0) {
                        sectionBadge.textContent = items[activeIndex].sequence || (activeIndex + 1);
                    }
                }
                updateBackLink(activeId);
            };

            // ฟังก์ชันย่อย: insertToken
            const insertToken = (textarea, token) => {
                if (!textarea) {
                    return;
                }
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                const before = textarea.value.slice(0, start);
                const after = textarea.value.slice(end);
                textarea.value = `${before}${token}${after}`;
                const nextPos = start + token.length;
                textarea.setSelectionRange(nextPos, nextPos);
                textarea.focus();
            };

            // ฟังก์ชันย่อย: toggleCollapsed
            const toggleCollapsed = (card, collapsed) => {
                if (!card) {
                    return;
                }
                if (collapsed) {
                    card.classList.add('is-collapsed');
                } else {
                    card.classList.remove('is-collapsed');
                }
            };

            // ฟังก์ชันย่อย: bindCardActions
            const bindCardActions = (card, actionsSelector, options = {}) => {
                if (!card) {
                    return;
                }
                const actions = actionsSelector ? card.querySelector(actionsSelector) : null;
                const scope = actions || card;
                const collapseBtn = scope.querySelector('.icon-btn.is-muted');
                const expandBtn = scope.querySelector('.icon-btn.is-primary');
                const deleteBtn = scope.querySelector('.icon-btn.is-danger');

                if (collapseBtn && collapseBtn.dataset.bound !== 'true') {
                    collapseBtn.dataset.bound = 'true';
                    collapseBtn.addEventListener('click', () => {
                        toggleCollapsed(card, true);
                    });
                }
                if (expandBtn && expandBtn.dataset.bound !== 'true') {
                    expandBtn.dataset.bound = 'true';
                    expandBtn.addEventListener('click', () => {
                        toggleCollapsed(card, false);
                    });
                }
                if (deleteBtn && typeof options.onDelete === 'function' && deleteBtn.dataset.bound !== 'true') {
                    deleteBtn.dataset.bound = 'true';
                    deleteBtn.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();
                        if (options.confirm !== false) {
                            const message = options.confirmMessage || 'ยืนยันการลบรายการนี้หรือไม่?';
                            if (!window.confirm(message)) {
                                return;
                            }
                        }
                        const target = options.closestSelector
                            ? deleteBtn.closest(options.closestSelector)
                            : card;
                        options.onDelete(target || card);
                    });
                }
            };

            // ฟังก์ชันย่อย: ensurePageActionsPosition
            const ensurePageActionsPosition = () => {
                if (!mainContainer || !pageActions) {
                    return;
                }
                const content = pageActions.parentElement;
                if (!content || mainContainer.parentElement !== content) {
                    return;
                }
                if (pageActions.previousElementSibling !== mainContainer) {
                    content.insertBefore(mainContainer, pageActions);
                }
                if (content.lastElementChild !== pageActions) {
                    content.appendChild(pageActions);
                }
            };

            // อัปเดตลำดับของหมวดหลักทั้งหมด
            // ฟังก์ชันย่อย: updateMainSequences
            const updateMainSequences = () => {
                const cards = mainContainer
                    ? mainContainer.querySelectorAll('.workload-card')
                    : document.querySelectorAll('.workload-card');
                cards.forEach((card, index) => {
                    const display = card.querySelector('.workload-main-sequence-display');
                    const input = card.querySelector('.workload-main-sequence');
                    if (display) {
                        display.textContent = index + 1;
                    }
                    if (input) {
                        input.value = index + 1;
                    }
                });
                ensurePageActionsPosition();
            };

            // ฟังก์ชันย่อย: updateSubSequences
            const updateSubSequences = (scope) => {
                const root = scope || document;
                root.querySelectorAll('.workload-sub-card').forEach((card, index) => {
                    const display = card.querySelector('.workload-sub-sequence-display');
                    const input = card.querySelector('.workload-sub-sequence');
                    if (display) {
                        display.textContent = index + 1;
                    }
                    if (input) {
                        input.value = index + 1;
                    }
                });
            };

            // ฟังก์ชันย่อย: initSubCard
            const initSubCard = (card) => {
                if (card.dataset.initialized === 'true') {
                    return;
                }
                bindCardActions(card, '.sub-card-head .card-actions', {
                    closestSelector: '.workload-sub-card',
                    onDelete: (target) => {
                        target.remove();
                        const parentCard = target.closest('.workload-card');
                        updateSubSequences(parentCard || document);
                    },
                });
                const formulaList = card.querySelector('.workload-formula-list');
                const formulaText = card.querySelector('.workload-formula-text');
                const addItemButton = card.querySelector('.workload-add-item');
                const addVariableButton = card.querySelector('.workload-add-variable');
                const variableLabelInput = card.querySelector('.workload-variable-label');
                const variableNoteInput = card.querySelector('.workload-variable-note');
                const variableTypeSelect = card.querySelector('.workload-variable-type');
                const variableChips = card.querySelector('.workload-variable-chips');
                const subitemTable = card.querySelector('.subitem-table');

                // ฟังก์ชันย่อย: syncVariableChips
                const syncVariableChips = () => {
                    if (!variableChips) {
                        return;
                    }
                    variableChips.innerHTML = '';

                    const creditsRow = document.createElement('div');
                    creditsRow.className = 'workload-variable-chip-row';
                    variableChips.appendChild(creditsRow);

                    const workloadRow = document.createElement('div');
                    workloadRow.className = 'workload-variable-chip-row';
                    variableChips.appendChild(workloadRow);

                    const builtInVariables = [
                        { label: 'หน่วยกิตรวม', value: 'credits' },
                        { label: 'หน่วยกิตบรรยาย', value: 'lecture_credits' },
                        { label: 'หน่วยกิตปฏิบัติ', value: 'lab_credits' },
                        { label: 'หน่วยกิตศึกษาด้วยตนเอง', value: 'self_study_credits' },
                    ];

                    builtInVariables.forEach((variable) => {
                        const chip = document.createElement('button');
                        chip.className = 'chip';
                        chip.type = 'button';
                        chip.textContent = variable.label;
                        chip.dataset.value = variable.value;
                        chip.addEventListener('click', () => {
                            if (formulaText) {
                                insertToken(formulaText, variable.value);
                            }
                        });
                        creditsRow.appendChild(chip);
                    });

                    const itemRows = card.querySelectorAll('.workload-item-row');
                    if (!itemRows.length) {
                        if (creditsRow.children.length > 0) {
                            return;
                        }
                        const emptyChip = document.createElement('button');
                        emptyChip.className = 'chip chip-muted';
                        emptyChip.type = 'button';
                        emptyChip.disabled = true;
                        emptyChip.textContent = 'ยังไม่มีตัวแปร';
                        workloadRow.appendChild(emptyChip);
                        return;
                    }

                    const valueText = 'item_star';
                    const valueLabel = 'ค่าภารงาน';
                    const chip = document.createElement('button');
                    chip.className = 'chip';
                    chip.type = 'button';
                    chip.textContent = valueLabel;
                    chip.dataset.value = valueText;
                    chip.addEventListener('click', () => {
                        if (formulaText) {
                            insertToken(formulaText, valueText);
                        }
                    });
                    workloadRow.appendChild(chip);
                };

                // ฟังก์ชันย่อย: addFormulaItem
                const addFormulaItem = (label, variableName, fieldType, note = '') => {
                    if (!formulaList) {
                        return;
                    }
                    const row = document.createElement('div');
                    row.className = 'formula-item';
                    row.dataset.fieldType = fieldType || 'input';
                    row.dataset.note = note || '';

                    const meta = document.createElement('div');
                    meta.className = 'formula-item-meta';

                    const labelSpan = document.createElement('span');
                    labelSpan.textContent = label || 'ตัวแปร';

                    labelSpan.className = 'formula-item-label';
                    meta.appendChild(labelSpan);

                    if (note) {
                        const noteSpan = document.createElement('span');
                        noteSpan.className = 'formula-item-note';
                        noteSpan.textContent = note;
                        meta.appendChild(noteSpan);
                    }

                    const valueSpan = document.createElement('span');
                    valueSpan.className = 'formula-value';
                    valueSpan.textContent = variableName || '';

                    const button = document.createElement('button');
                    button.className = 'icon-btn is-danger workload-variable-remove';
                    button.type = 'button';
                    button.textContent = '×';
                    button.addEventListener('click', () => {
                        row.remove();
                        syncVariableChips();
                    });

                    row.appendChild(meta);
                    row.appendChild(valueSpan);
                    row.appendChild(button);
                    row.addEventListener('click', (event) => {
                        if (event.target.closest('.workload-variable-remove')) {
                            return;
                        }
                        if (formulaText) {
                            const token = valueSpan.textContent.trim();
                            insertToken(formulaText, token);
                        }
                    });
                    formulaList.appendChild(row);
                    syncVariableChips();
                };

                // ฟังก์ชันย่อย: updateItemSequence
                const updateItemSequence = (scope) => {
                    scope.querySelectorAll('.workload-item-row').forEach((row, index) => {
                        const label = row.querySelector('.subitem-label');
                        if (label) {
                            label.textContent = index + 1;
                        }
                    });
                    syncVariableChips();
                };

                // ฟังก์ชันย่อย: getNextItemSequence
                const getNextItemSequence = () => {
                    const existingSequences = new Set();
                    if (formulaList) {
                        formulaList.querySelectorAll('.formula-item').forEach((row) => {
                            const valueEl = row.querySelector('.formula-value');
                            const valueText = valueEl ? valueEl.textContent.trim() : '';
                            const match = valueText.match(/^item_(\d+)$/i);
                            if (match) {
                                existingSequences.add(Number(match[1]));
                            }
                        });
                    }

                    const itemRows = card.querySelectorAll('.workload-item-row');
                    for (let i = 0; i < itemRows.length; i++) {
                        const sequence = i + 1;
                        if (!existingSequences.has(sequence)) {
                            return sequence;
                        }
                    }

                    return existingSequences.size + 1;
                };

                // ฟังก์ชันย่อย: getNextVariableIndex
                const getNextVariableIndex = (prefix) => {
                    let maxIndex = 0;
                    if (!formulaList) {
                        return 1;
                    }
                    const pattern = new RegExp(`^${prefix}_(\\d+)$`, 'i');
                    formulaList.querySelectorAll('.formula-item').forEach((row) => {
                        const valueEl = row.querySelector('.formula-value');
                        const valueText = valueEl ? valueEl.textContent.trim() : '';
                        const match = valueText.match(pattern);
                        if (match) {
                            const num = Number(match[1]);
                            if (!Number.isNaN(num) && num > maxIndex) {
                                maxIndex = num;
                            }
                        }
                    });
                    return maxIndex + 1;
                };

                // ฟังก์ชันย่อย: attachItemRowHandlers
                if (subitemTable && subitemTable.dataset.removeBound !== 'true') {
                    subitemTable.dataset.removeBound = 'true';
                    subitemTable.addEventListener('click', (event) => {
                        const removeBtn = event.target.closest('.workload-item-remove');
                        if (!removeBtn || !subitemTable.contains(removeBtn)) {
                            return;
                        }
                        const row = removeBtn.closest('.workload-item-row');
                        if (!row) {
                            return;
                        }
                        const message = 'Confirm deleting this item?';
                        if (!window.confirm(message)) {
                            return;
                        }
                        row.remove();
                        updateItemSequence(card);
                    });
                }

                if (addItemButton && subitemTable) {
                    addItemButton.addEventListener('click', () => {
                        const firstRow = card.querySelector('.workload-item-row');
                        if (!firstRow) {
                            return;
                        }
                        const newRow = firstRow.cloneNode(true);
                        newRow.querySelectorAll('input').forEach((input) => {
                            input.value = '';
                        });
                        subitemTable.insertBefore(newRow, subitemTable.querySelector('.subitem-actions'));
                        updateItemSequence(card);
                    });
                }

                if (addVariableButton) {
                    addVariableButton.addEventListener('click', () => {
                        const currentLabelInput = card.querySelector('.workload-variable-label');
                        const currentNoteInput = card.querySelector('.workload-variable-note');
                        const currentTypeSelect = card.querySelector('.workload-variable-type');
                        const label = currentLabelInput ? currentLabelInput.value.trim() : '';
                        const note = currentNoteInput ? currentNoteInput.value.trim() : '';
                        let fieldType = currentTypeSelect ? currentTypeSelect.value : '';
                        if (!label) {
                            alert('กรุณากรอกชื่อตัวแปร');
                            return;
                        }
                        if (!fieldType) {
                            fieldType = 'number';
                        }

                        const variableName = fieldType === 'number'
                            ? `num_${getNextVariableIndex('num')}`
                            : fieldType === 'item'
                                ? `item_${getNextItemSequence()}`
                                : `text_${getNextVariableIndex('text')}`;
                        addFormulaItem(label, variableName, fieldType, note);

                        if (currentLabelInput) {
                            currentLabelInput.value = '';
                        }
                        if (currentNoteInput) {
                            currentNoteInput.value = '';
                        }
                        if (currentTypeSelect) {
                            currentTypeSelect.value = '';
                        }
                    });
                }

                card.querySelectorAll('.workload-variable-remove').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const row = btn.closest('.formula-item');
                        if (row) {
                            row.remove();
                            syncVariableChips();
                        }
                    });
                });

                card.querySelectorAll('.formula-toolbar .chip').forEach((chip) => {
                    chip.addEventListener('click', () => {
                        if (!formulaText) {
                            return;
                        }
                        const token = chip.textContent.trim();
                        insertToken(formulaText, token);
                    });
                });

                updateItemSequence(card);
                syncVariableChips();

                card.__workload = {
                    addFormulaItem,
                    syncVariableChips,
                    updateItemSequence,
                };
                card.dataset.initialized = 'true';
            };

            // ฟังก์ชันย่อย: sanitizeClonedSubCard
            const sanitizeClonedSubCard = (card, resetInit = false) => {
                card.querySelectorAll('[id]').forEach((node) => node.removeAttribute('id'));
                card.querySelectorAll('[data-bound]').forEach((node) => delete node.dataset.bound);
                card.querySelectorAll('[data-remove-bound]').forEach((node) => delete node.dataset.removeBound);
                if (resetInit) {
                    card.dataset.initialized = 'false';
                }
                card.dataset.itemId = '';
                card.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
                card.querySelectorAll('textarea').forEach((textarea) => {
                    textarea.value = '';
                });
                const formulaList = card.querySelector('.formula-list');
                if (formulaList) {
                    formulaList.innerHTML = '';
                }
                const variableChips = card.querySelector('.workload-variable-chips');
                if (variableChips) {
                    variableChips.innerHTML = '';
                    const emptyChip = document.createElement('button');
                    emptyChip.className = 'chip chip-muted';
                    emptyChip.type = 'button';
                    emptyChip.disabled = true;
                    emptyChip.textContent = 'ยังไม่มีตัวแปร';
                    variableChips.appendChild(emptyChip);
                }
            };

            // ฟังก์ชันย่อย: sanitizeClonedMainCard
            const sanitizeClonedMainCard = (card, resetInit = false) => {
                if (!card) {
                    return;
                }
                card.classList.remove('is-collapsed');
                card.querySelectorAll('[id]').forEach((node) => node.removeAttribute('id'));
                card.querySelectorAll('[data-bound]').forEach((node) => delete node.dataset.bound);
                card.querySelectorAll('[data-remove-bound]').forEach((node) => delete node.dataset.removeBound);
                if (resetInit) {
                    card.dataset.mainInitialized = 'false';
                }
                card.dataset.groupId = '';
                card.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
                card.querySelectorAll('textarea').forEach((textarea) => {
                    textarea.value = '';
                });
                card.querySelectorAll('.workload-sub-card').forEach((subCard, index) => {
                    if (index === 0) {
                        sanitizeClonedSubCard(subCard, true);
                    } else {
                        subCard.remove();
                    }
                });
            };

            // ฟังก์ชันย่อย: addMainCard
            const addMainCard = () => {
                if (!mainContainer) {
                    return;
                }
                const template = document.querySelector('.workload-card');
                if (!template) {
                    return;
                }
                const clone = template.cloneNode(true);
                sanitizeClonedMainCard(clone, true);
                if (mainContainer) {
                    mainContainer.appendChild(clone);
                }
                initMainCard(clone);
                updateMainSequences();
                updateSubSequences(clone);
            };

            // ผูกพฤติกรรมให้การ์ดหมวดหลัก
            // ฟังก์ชันย่อย: initMainCard
            const initMainCard = (card) => {
                if (!card) {
                    return;
                }
                if (card.dataset.mainInitialized === 'true') {
                    return;
                }
                bindCardActions(card, '.card-head .card-actions', {
                    closestSelector: '.workload-card',
                    onDelete: (target) => {
                        const cards = mainContainer
                            ? mainContainer.querySelectorAll('.workload-card')
                            : document.querySelectorAll('.workload-card');
                        if (cards.length > 1) {
                            target.remove();
                        } else {
                            sanitizeClonedMainCard(target);
                        }
                        updateMainSequences();
                        updateSubSequences(target);
                        toggleCollapsed(target, false);
                    },
                });

                const subBlock = card.querySelector('.sub-block');
                const subFooter = card.querySelector('.sub-footer');
                const addSubButton = card.querySelector('.workload-add-sub');
                const addMainButton = card.querySelector('.workload-add-main');

                card.querySelectorAll('.workload-sub-card').forEach((subCard) => {
                    initSubCard(subCard);
                });

                if (addSubButton) {
                    addSubButton.addEventListener('click', () => {
                        if (!subBlock || !subFooter) {
                            return;
                        }
                        const firstCard = subBlock.querySelector('.workload-sub-card');
                        if (!firstCard) {
                            return;
                        }
                        const clone = firstCard.cloneNode(true);
                        sanitizeClonedSubCard(clone, true);
                        subBlock.insertBefore(clone, subFooter);
                        initSubCard(clone);
                        updateSubSequences(card);
                    });
                }

                if (addMainButton) {
                    addMainButton.addEventListener('click', () => {
                        addMainCard();
                    });
                }

                card.dataset.mainInitialized = 'true';
            };

            document.querySelectorAll('.workload-card').forEach((card) => {
                initMainCard(card);
            });
            updateMainSequences();
            if (mainCard) {
                updateSubSequences(mainCard);
            }

            // เติมข้อมูลหมวดย่อยและรายการภาระงานจาก API
            // ฟังก์ชันย่อย: populateItems
            const populateItems = (card, items) => {
                const subBlock = card ? card.querySelector('.sub-block') : null;
                const subFooter = card ? card.querySelector('.sub-footer') : null;
                if (!subBlock || !subFooter || !Array.isArray(items)) {
                    return;
                }
                const template = subBlock.querySelector('.workload-sub-card');
                if (!template) {
                    return;
                }
                subBlock.querySelectorAll('.workload-sub-card').forEach((subCard, index) => {
                    if (index > 0) {
                        subCard.remove();
                    }
                });

                items.forEach((block, index) => {
                    const subCard = index === 0 ? template : template.cloneNode(true);
                    sanitizeClonedSubCard(subCard, index > 0);
                    if (index > 0) {
                        subBlock.insertBefore(subCard, subFooter);
                    }
                    if (!subCard.__workload) {
                        initSubCard(subCard);
                    }

                    if (block?.item?.id) {
                        subCard.dataset.itemId = block.item.id;
                    }
                    const seqDisplay = subCard.querySelector('.workload-sub-sequence-display');
                    const seqInput = subCard.querySelector('.workload-sub-sequence');
                    if (seqDisplay && block?.item?.sequence) {
                        seqDisplay.textContent = block.item.sequence;
                    }
                    if (seqInput && block?.item?.sequence) {
                        seqInput.value = block.item.sequence;
                    }
                    const subNameInput = subCard.querySelector('.workload-sub-category');
                    if (subNameInput && block?.item?.name) {
                        subNameInput.value = block.item.name;
                    }

                    const form = block?.form;
                    const api = subCard.__workload;
                    const cardFormulaText = subCard.querySelector('.workload-formula-text');
                    const cardFormulaList = subCard.querySelector('.workload-formula-list');
                    const cardSubitemTable = subCard.querySelector('.subitem-table');
                    if (cardFormulaText && form?.formula_logic) {
                        cardFormulaText.value = form.formula_logic;
                    }
                    if (cardFormulaList && Array.isArray(form?.fields) && api?.addFormulaItem) {
                        cardFormulaList.innerHTML = '';
                        form.fields.forEach((field, idx) => {
                            api.addFormulaItem(
                                field.label || `ตัวแปร ${idx + 1}`,
                                field.variable_name || `input_${idx + 1}`,
                                field.field_type || 'input',
                                field.note || ''
                            );
                        });
                        api.syncVariableChips();
                    }
                    if (cardSubitemTable && Array.isArray(form?.items)) {
                        const firstRow = subCard.querySelector('.workload-item-row');
                        if (firstRow) {
                            subCard.querySelectorAll('.workload-item-row').forEach((row, idx) => {
                                if (idx > 0) {
                                    row.remove();
                                }
                            });
                            form.items.forEach((item, idx) => {
                                const row = idx === 0 ? firstRow : firstRow.cloneNode(true);
                                row.querySelector('.workload-item-name').value = item.label || '';
                                const scoreValue = item.score ?? '';
                                row.querySelector('.workload-item-score').value = scoreValue === ''
                                    ? ''
                                    : Number(scoreValue);
                                if (idx > 0) {
                                    cardSubitemTable.insertBefore(row, cardSubitemTable.querySelector('.subitem-actions'));
                                }
                            });
                            api?.updateItemSequence(subCard);
                        }
                    }
                });
                updateSubSequences(card);
            };

            // เติมข้อมูลหมวดหลักจาก API
            // ฟังก์ชันย่อย: populateGroups
            const populateGroups = (groups) => {
                if (!mainContainer || !Array.isArray(groups)) {
                    return;
                }
                const template = document.querySelector('.workload-card');
                if (!template) {
                    return;
                }

                mainContainer.querySelectorAll('.workload-card').forEach((card, index) => {
                    if (index > 0) {
                        card.remove();
                    }
                });

                groups.forEach((groupBlock, index) => {
                    const card = index === 0 ? template : template.cloneNode(true);
                    sanitizeClonedMainCard(card, index > 0);
                    if (index > 0) {
                        mainContainer.appendChild(card);
                    }
                    initMainCard(card);

                    if (groupBlock?.group?.id) {
                        card.dataset.groupId = groupBlock.group.id;
                    }
                    const mainNameInput = card.querySelector('.workload-main-category');
                    if (mainNameInput && groupBlock?.group?.name) {
                        mainNameInput.value = groupBlock.group.name;
                    }
                    const mainSeqDisplay = card.querySelector('.workload-main-sequence-display');
                    const mainSeqInput = card.querySelector('.workload-main-sequence');
                    if (mainSeqDisplay && groupBlock?.group?.sequence) {
                        mainSeqDisplay.textContent = groupBlock.group.sequence;
                    }
                    if (mainSeqInput && groupBlock?.group?.sequence) {
                        mainSeqInput.value = groupBlock.group.sequence;
                    }

                    populateItems(card, groupBlock.items || []);
                });

                updateMainSequences();
            };

            // โหลดข้อมูลหัวข้อ/เมนูซ้าย
            fetch(`/workload-quantity-sub-criterias?quant_sub_criteria_id=${encodeURIComponent(quantSubCriteriaId)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then((response) => response.json())
                .then((data) => {
                    if (!data || !data.active) {
                        return;
                    }

                    renderNav(data.items || [], data.active.id);
                    if (data.active) {
                        updateBackLink(data.active.criteria_version_id || data.active.id);
                    }

                    const mainTitle = data.active.main_criteria_name || data.active.name || '';
                    if (sectionTitle && !sectionTitle.textContent && mainTitle) {
                        sectionTitle.textContent = mainTitle;
                    }
                })
                .catch(() => {
                    // Ignore load errors for now.
                });

            // โหลดโครงสร้างหมวดหลักและหมวดย่อย
            fetch(`/workload-sub-blocks?quant_sub_criteria_id=${encodeURIComponent(quantSubCriteriaId)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data && Array.isArray(data.groups)) {
                        populateGroups(data.groups);
                    }
                })
                .catch(() => {
                    // Ignore load errors for now.
                });

            const toastEl = document.getElementById('workload-toast');
            const toastTextEl = document.getElementById('workload-toast-text');

            // แสดง toast แจ้งสถานะการทำงาน
            // ฟังก์ชันย่อย: showWorkloadToast
            const showWorkloadToast = (message, type = 'success') => {
                if (!toastEl || !toastTextEl) {
                    return;
                }
                toastTextEl.textContent = message;
                toastEl.classList.remove('is-danger');
                if (type === 'danger') {
                    toastEl.classList.add('is-danger');
                }
                toastEl.classList.add('is-visible');
                clearTimeout(showWorkloadToast._timer);
                showWorkloadToast._timer = setTimeout(() => {
                    toastEl.classList.remove('is-visible');
                }, 4000);
            };

            if (toastEl) {
                const closeBtn = toastEl.querySelector('.workload-toast-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        toastEl.classList.remove('is-visible');
                    });
                }
            }

            // ปุ่มรีเซ็ตค่า
            if (resetButton) {
                resetButton.addEventListener('click', () => {
                    if (!mainContainer) {
                        return;
                    }
                    const cards = mainContainer.querySelectorAll('.workload-card');
                    if (!cards.length) {
                        return;
                    }
                    cards.forEach((card, index) => {
                        if (index === 0) {
                            sanitizeClonedMainCard(card, true);
                            initMainCard(card);
                            updateSubSequences(card);
                        } else {
                            card.remove();
                        }
                    });
                    updateMainSequences();
                    showWorkloadToast('รีเซ็ตค่าเรียบร้อย');
                });
            }

            // ปุ่มบันทึกข้อมูล
            if (saveButton) {
                saveButton.addEventListener('click', () => {
                    const groups = [];
                    document.querySelectorAll('.workload-card').forEach((card, groupIndex) => {
                        const items = [];
                        card.querySelectorAll('.workload-sub-card').forEach((subCard, index) => {
                            const formItems = [];
                            subCard.querySelectorAll('.workload-item-row').forEach((row, rowIndex) => {
                                const nameInput = row.querySelector('.workload-item-name');
                                const scoreInput = row.querySelector('.workload-item-score');
                                const label = nameInput ? nameInput.value.trim() : '';
                                const score = scoreInput ? scoreInput.value : '';
                                if (label) {
                                    formItems.push({
                                        label,
                                        score: score === '' ? null : Number(score),
                                        sequence: rowIndex + 1,
                                    });
                                }
                            });

                            const fields = [];
                            subCard.querySelectorAll('.formula-item').forEach((row) => {
                                const label = row.querySelector('.formula-item-label');
                                const value = row.querySelector('.formula-value');
                                fields.push({
                                    label: label ? label.textContent.trim() : '',
                                    variable_name: value ? value.textContent.trim() : '',
                                    field_type: row.dataset.fieldType || 'input',
                                    note: row.dataset.note || '',
                                });
                            });

                            const subNameInput = subCard.querySelector('.workload-sub-category');
                            const sequenceDisplay = subCard.querySelector('.workload-sub-sequence-display');
                            const formulaText = subCard.querySelector('.workload-formula-text');

                            items.push({
                                id: subCard.dataset.itemId ? Number(subCard.dataset.itemId) : null,
                                item_name: subNameInput ? subNameInput.value.trim() : '',
                                sequence: sequenceDisplay ? Number(sequenceDisplay.textContent || 0) : (index + 1),
                                formula_logic: formulaText ? formulaText.value.trim() : '',
                                fields,
                                form_items: formItems,
                            });
                        });

                        const mainNameInput = card.querySelector('.workload-main-category');
                        const mainSequenceDisplay = card.querySelector('.workload-main-sequence-display');

                        groups.push({
                            id: card.dataset.groupId ? Number(card.dataset.groupId) : null,
                            group_name: mainNameInput ? mainNameInput.value.trim() : '',
                            sequence: mainSequenceDisplay
                                ? Number(mainSequenceDisplay.textContent || 0)
                                : (groupIndex + 1),
                            items,
                        });
                    });

                    fetch('/workload-config/save', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                quant_sub_criteria_id: quantSubCriteriaId,
                                groups,
                            }),
                        })
                        .then(async (response) => {
                            const data = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                throw data;
                            }
                            return data;
                        })
                        .then((data) => {
                            if (!data || data.success === false) {
                                showWorkloadToast(
                                    data && data.message ? data.message : 'บันทึกไม่สำเร็จ',
                                    'danger'
                                );
                                return;
                            }
                            showWorkloadToast('บันทึกสำเร็จ');
                        })
                        .catch((error) => {
                            if (error && error.errors && error.errors.formula_logic) {
                                showWorkloadToast(error.errors.formula_logic.join('\n'), 'danger');
                                return;
                            }
                            showWorkloadToast(
                                error && error.message ? error.message : 'บันทึกไม่สำเร็จ',
                                'danger'
                            );
                        });
                });
            }
        });
    </script>
@endpush
