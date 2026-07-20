# Support Score Confirmation Summary

## Goal

แสดงคะแนนสายสนับสนุนและชื่อกิจกรรม/โครงการสายสนับสนุนใน modal ยืนยันการส่งแบบประเมินของผู้ประเมิน โดยไม่เปลี่ยนค่าคะแนนรวมเดิมของ modal

## Scope

- เพิ่มการ์ดสรุปใบที่ 4 ชื่อ `คะแนนสายสนับสนุน` ใน `evaluatee-confirmation-modal`.
- เพิ่มรายการ Support ภายในแต่ละ evaluation list โดยใช้ `support_items` ที่มีอยู่ใน `$categoryItems`.
- แสดงชื่อ `activity_name` ของแต่ละ support criterion และคะแนนที่กรอก/คะแนนถ่วงน้ำหนักที่คำนวณได้.
- คง `คะแนนรวม` เดิม ไม่รวม support score เข้าไปคำนวณใหม่.

## Data flow

1. Blade อ่าน `support_items` จากแต่ละ evaluation list และสร้าง summary rows พร้อม data attributes ของ support id, activity name และ weight.
2. `evaluatee-evaluation-script` อ่านค่า `data-support-score` จาก support editor store ที่อยู่ในหน้าเดียวกัน.
3. JavaScript คำนวณคะแนนถ่วงน้ำหนักต่อรายการ (`weight * achieved / 100`) และยอดรวมสายสนับสนุน.
4. JavaScript อัปเดตการ์ดใบที่ 4, สถานะของแต่ละ support row และยอดคะแนนของ evaluation list โดยไม่แตะ `modal-total-summary`.

## Empty and invalid states

- ค่าว่างหรือค่าที่ไม่ใช่ตัวเลขถือเป็น 0 และแสดงสถานะ `ยังไม่มีข้อมูล`.
- ค่าคะแนนที่เป็นตัวเลขตั้งแต่ 0 ขึ้นไปถือว่ามีข้อมูล และแสดงสถานะพร้อมคะแนน 2 ตำแหน่ง.
- รายการที่ไม่มี support criteria ไม่สร้าง section Support.

## Testing

- เพิ่ม view contract test ตรวจการ์ดใบที่ 4, support section, ชื่อกิจกรรม และ data attributes.
- เพิ่ม script contract test ตรวจการอ่าน support inputs, การคำนวณ weighted score และการอัปเดต modal support summary.
- รัน focused Pest tests และ `git diff --check` ก่อนสรุปงาน.
