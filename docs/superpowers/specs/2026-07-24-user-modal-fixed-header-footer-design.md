# User Modal Fixed Header and Footer Design

## Goal

ปรับ modal เพิ่มและแก้ไขผู้ใช้งานให้ผู้ใช้มองเห็นชื่อ modal ปุ่มปิด และปุ่มดำเนินการได้ตลอดเวลา ขณะที่เลื่อนดูแบบฟอร์มยาว โดยรองรับทั้ง desktop และ mobile และไม่เปลี่ยนพฤติกรรมการบันทึกหรือ validation เดิม

## Scope

การเปลี่ยนแปลงครอบคลุมเฉพาะ modal ใน `resources/views/user/management/user-form-modal.blade.php` และ partial ของ header กับ action area ที่ modal นี้เรียกใช้

ไม่เปลี่ยนแปลง:

- ฟิลด์และลำดับข้อมูลในแบบฟอร์ม
- request payload และ route สำหรับสร้างหรือแก้ไขผู้ใช้
- client-side และ server-side validation
- การเพิ่มหรือลบรายการประวัติการศึกษา
- พฤติกรรมปิด modal ด้วยปุ่มปิด ปุ่มย้อนกลับ หรือการคลิก backdrop

## Layout

Modal panel ใช้ Flexbox แนวตั้งและจำกัดความสูงตาม viewport:

1. Header เป็นส่วน `flex-none` อยู่ด้านบนของ panel
2. Form เป็น flex container แนวตั้งที่ใช้พื้นที่ที่เหลือและกำหนด `min-height: 0`
3. Form body เป็นส่วน `flex-1` และเป็น scroll container เพียงส่วนเดียวด้วย `overflow-y: auto`
4. Action footer เป็นส่วน `flex-none` อยู่ด้านล่างของ form

Panel ใช้ `overflow: hidden` เพื่อให้เนื้อหาไม่เลื่อนผ่านขอบมน ส่วน overlay ไม่เป็น scroll container หลักอีกต่อไป

โครงสร้างโดยสรุป:

```text
Overlay
└── Modal panel (vertical flex, viewport-bounded)
    ├── Header (fixed within panel)
    └── Form (vertical flex, remaining height)
        ├── Scrollable form body
        └── Action footer (fixed within panel)
```

## Visual Treatment

- Header และ footer ใช้พื้นหลังสีขาวทึบ เพื่อไม่ให้ข้อความที่เลื่อนผ่านมองเห็นซ้อนด้านหลัง
- Header มีเส้นแบ่งด้านล่าง และ footer มีเส้นแบ่งด้านบน
- Padding อยู่ในแต่ละส่วนแทนการใส่ padding รอบ panel เพื่อให้เส้นแบ่งเต็มความกว้าง
- รูปลักษณ์เดิมของสีม่วง ปุ่ม และช่องกรอกข้อมูลยังคงเดิม
- Desktop เว้นระยะจากขอบ viewport และใช้ความกว้างสูงสุดเดิม
- Mobile ลดระยะรอบ overlay และให้ panel ใช้พื้นที่หน้าจอเกือบทั้งหมด โดยยังคงขอบมนและพื้นที่กดปุ่มที่เพียงพอ

## Viewport and Scrolling Behavior

- ใช้ dynamic viewport height (`dvh`) เพื่อให้เหมาะกับ browser chrome บน mobile
- Modal panel สูงไม่เกิน viewport หลังหักระยะขอบรอบ modal
- เมื่อเนื้อหาสั้น panel สูงตามเนื้อหา
- เมื่อเนื้อหายาว เฉพาะ form body เลื่อน
- Header, close button, back button และ save button ต้องมองเห็นอยู่ตลอดเวลาที่ modal เปิด
- พื้นหลังหน้าหลักไม่ควรรับ scroll gesture ขณะ pointer อยู่เหนือ scrollable form body

## Accessibility

- ปุ่มปิดยังเป็น `<button type="button">` และใช้ data hook เดิม
- เพิ่ม accessible label ภาษาไทยให้ปุ่มปิด เนื่องจากตัวปุ่มแสดงเพียงสัญลักษณ์
- ลำดับ tab ยังคงเป็น header close button ตามด้วย fields และ action buttons
- แสดง focus style ของปุ่มปิดให้เห็นชัด
- ไม่เปลี่ยน semantic ของ `<form>` หรือปุ่ม submit

## Implementation Boundaries

- ใช้ utility classes ที่มีอยู่ในโปรเจกต์ ไม่เพิ่ม dependency หรือ JavaScript สำหรับคำนวณความสูง
- คง data hooks และ element IDs เดิมทั้งหมด เพื่อไม่ให้สคริปต์ modal และ tests เดิมเสีย
- ย้ายเฉพาะ wrapper ที่จำเป็นต่อการแยก scrollable body ออกจาก footer
- ไม่ refactor partial หรือ JavaScript ที่ไม่เกี่ยวข้อง

## Testing

เพิ่มหรือขยาย feature test ของ view เพื่อตรวจว่า:

- modal panel มี hook ที่ระบุ viewport-bounded shell
- form body มี hook ที่ระบุ scroll container
- action footer มี hook แยกจาก scroll container
- close control ยังคงใช้ `data-user-modal-close` และมี accessible label
- data hooks เดิมสำหรับประวัติการศึกษาและ modal controls ยังคงอยู่

ตรวจด้วย browser ที่ desktop และ mobile viewport ว่า:

- header และ footer ไม่เลื่อนออกจาก panel
- ไม่มี scroll ซ้อนระหว่าง overlay กับ form body
- ช่องกรอกสุดท้ายและ validation message เลื่อนเข้ามามองเห็นได้
- ปุ่ม action ไม่บังเนื้อหาส่วนท้าย
- modal สร้างและแก้ไขผู้ใช้ยังเปิด ปิด และ submit ได้ตามเดิม

## Acceptance Criteria

1. เมื่อ modal สูงกว่า viewport ผู้ใช้เลื่อนได้เฉพาะเนื้อหาฟอร์ม
2. ชื่อ modal และปุ่มปิดมองเห็นตลอดเวลาที่เลื่อน
3. ปุ่มย้อนกลับและบันทึกมองเห็นตลอดเวลาที่เลื่อน
4. Modal ใช้งานได้โดยไม่มีเนื้อหาหรือปุ่มหลุดออกนอก viewport ทั้ง desktop และ mobile
5. Validation, education rows, create flow และ edit flow ทำงานเหมือนก่อนแก้ไข
6. Automated tests ที่เกี่ยวข้องผ่านทั้งหมด
