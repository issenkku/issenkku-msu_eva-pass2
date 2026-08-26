# Epic F — UI State และ Accessibility

## Workflow ที่เกี่ยวข้อง

1. ผู้ใช้เปิด dashboard/settings/evaluation form
2. ผู้ใช้เปิด dropdown/modal/confirmation dialog
3. ผู้ใช้ submit form
4. ระบบแสดง loading/disabled state
5. ระบบแสดง flash/validation message
6. ผู้ใช้ keyboard/screen-reader ใช้งาน flow เดียวกัน

## Module / Interface ที่เกี่ยวข้อง

- Blade layout partials
- evaluation confirmation modal partials
- subject/settings page scripts
- flash message partials
- loading overlay partials
- support criteria table UI
- global CSS/tokens

## F-01 — Duplicate DOM IDs ทำให้ selector ชี้ผิด element

Severity: `P2`

### จุดที่พบ

- `resources/views/layouts/app.blade.php`
- `resources/views/partials/evaluation-form-confirmation-modal.blade.php`
- `resources/views/partials/evaluatee-confirmation-modal.blade.php`
- flash message partials หลายหน้า
- loading overlay partials หลายหน้า

### IDs ที่พบว่าซ้ำหรือเสี่ยงชน

- `settingDropdown`
- `confirmationModal`
- `modal-content`
- `confirmSubmitBtn`
- `cancelModalBtn`
- `loading_overlay`
- `successMessage`
- `warningMessage`
- `errorMessage`

### สาเหตุของ defect

แต่ละ partial assume ว่าตัวเองเป็น instance เดียวใน DOM แต่ layout/page composition อาจ include partial หลายตัว หรือมี global script ที่ query DOM-wide

### Failure mode

- `getElementById` ได้ element ตัวแรก ไม่ใช่ flow ปัจจุบัน
- dropdown aria-labelledby ผิด
- modal เปิด/ปิดผิดตัว
- overlay ผิดตัว
- flash message ถูก remove/dismiss ผิดตัว

### Coverage gap

ไม่มี duplicate-ID safety test แบบ render composed page แล้ว assert ID uniqueness

### แนวทางแก้

- เปลี่ยนเป็น component-scoped data attributes
- ส่ง unique id prefix ผ่าน partial props
- ลด DOM-wide `getElementById`
- ใช้ event delegation บน container เฉพาะ Module

## F-02 — Confirmation modal ไม่ครบ accessible dialog contract

Severity: `P2`

### จุดที่พบ

- `evaluation-form-confirmation-modal.blade.php`
- `evaluatee-confirmation-modal.blade.php`
- related scripts

### การทำงานปัจจุบัน

custom modal เปิด/ปิดด้วย class/ID แต่ไม่พบ contract ครบ:

- `role="dialog"`
- `aria-modal="true"`
- labelled title
- focus trap
- focus restore
- background inert หรือ tab order control

### สาเหตุของ defect

Modal Implementation เป็น visual overlay แต่ Interface สำหรับ assistive technology ยังไม่ถูกกำหนด

### Failure mode

keyboard/screen-reader users ไม่รู้ว่ามี dialog เปิดอยู่ หรือ tab หลุดไป background

### Coverage gap

support criteria modal มี test บางส่วน แต่ confirmation modal ยังไม่มี contract test

### แนวทางแก้

ใช้ shared `AccessibleDialog` partial/script หรือ native `<dialog>` พร้อม focus management

## F-03 — Loading/disabled state ผูกกับ timer ไม่ผูกกับ request lifecycle

Severity: `P2`

### จุดที่พบ

- `resources/views/subjects/partials/index-script.blade.php`
- patterns เดียวกันใน departments/positions/job-level scripts
- evaluator form scripts

### การทำงานปัจจุบัน

button disable/re-enable และ overlay lifecycle ใช้ `setTimeout(..., 5000)` หรือ submit แล้วหวัง navigation

### สาเหตุของ defect

UI state Interface ไม่ได้ผูกกับ request lifecycle จริง เช่น `submit:start`, `submit:success`, `submit:error`, `submit:finally`

### Failure mode

- request ช้ากว่า 5s แล้วปุ่มเปิดให้กดซ้ำ
- validation fail แล้ว state ไม่ reset ถูกจังหวะ
- overlay ค้าง
- duplicate submit

### Coverage gap

ไม่มี JS behavior test สำหรับ loading lifecycle

### แนวทางแก้

สร้าง form submit helper กลาง:

- disable on submit start
- restore on validation error
- avoid fixed timer
- idempotency guard ป้องกัน double submit

## F-04 — Flash messages ไม่ประกาศสถานะอย่างสม่ำเสมอ

Severity: `P2`

### จุดที่พบ

- flash message partials หลายหน้า
- `layout-app-shell-script.blade.php`

### การทำงานปัจจุบัน

มี DOM-wide selectors ของ `#successMessage`, `#warningMessage`, `#errorMessage` และ auto-hide scripts แต่หลาย variant ไม่มี `aria-live`

### สาเหตุของ defect

Flash message เป็น repeated shallow partial ไม่มี Interface กลางสำหรับ live region, close button, role, timeout

### Failure mode

- screen reader ไม่ announce message
- script ลบ toast ผิดตัว
- message หายเร็วเกินก่อนผู้ใช้รับรู้

### Coverage gap

มี test close button บางส่วน แต่ยังไม่มี live region และ duplicate ID test ครบ

### แนวทางแก้

สร้าง `FlashMessage` Module/partial กลาง:

- class-based selectors
- `role="status"` หรือ `role="alert"` ตาม severity
- `aria-live`
- no duplicate id

## F-05 — Motion/contrast policy ยังไม่ verify

Severity: `P2`

### จุดที่พบ

- CSS/layout partials มี transition/animation
- ไม่พบ policy `prefers-reduced-motion` ครอบคลุม
- ไม่มี automated contrast tests

### สาเหตุของ defect

Design system มี tokens แต่ยังไม่มี accessibility verification Interface

### Failure mode

- ผู้ใช้ที่ sensitive ต่อ motion ได้รับ motion ที่ปิดไม่ได้
- text/background บาง state อาจ contrast ต่ำโดยไม่มี test จับ

### Coverage gap

ไม่มี browser/visual/accessibility audit สำหรับ contrast และ reduced motion

### แนวทางแก้

- เพิ่ม CSS reduced-motion base rule
- เพิ่ม test หรือ lint สำหรับ contrast critical components
- ทำ browser E2E audit สำหรับ modal/table/form states

