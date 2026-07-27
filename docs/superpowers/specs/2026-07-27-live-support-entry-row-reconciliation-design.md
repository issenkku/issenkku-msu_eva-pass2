# Live Support Entry Row Reconciliation Design

## Goal

ทำให้ตารางแสดงผลเกณฑ์สายสนับสนุนบน desktop แยกแต่ละกิจกรรม/โครงการเป็นคนละแถวทันทีหลังผู้ใช้ยืนยันการแก้ไขใน Modal โดยไม่ต้องบันทึกแบบประเมินและรีเฟรชหน้าก่อน

## Problem

Blade สร้างแถว `[data-support-entry-row]` จากกิจกรรมที่บันทึกอยู่ในฐานข้อมูลตอนโหลดหน้า หากรายงานยังไม่มีกิจกรรม ตารางจะใช้แถวสรุปแบบเดิมแทน

เมื่อเพิ่มกิจกรรมผ่าน Modal ฟังก์ชัน `syncDesktopEntryRows` ปัจจุบันอัปเดตได้เฉพาะแถวที่ Blade สร้างไว้แล้ว แต่ไม่สร้างแถวเพิ่มหรือลบแถวส่วนเกิน ผลคือกิจกรรมใหม่ถูกแสดงเป็นรายการซ้อนในเซลล์เดิมและไม่มีเส้นคั่น จนกว่าจะบันทึกและรีเฟรชหน้า

## Scope

แก้เฉพาะ live preview ของตารางแสดงผลแบบ desktop สำหรับเกณฑ์ที่:

- อนุญาตให้ผู้ถูกประเมินเพิ่มกิจกรรม/โครงการ
- ไม่ได้แยกโครงการตามตัวชี้วัดย่อย
- ผู้ถูกประเมินอาจกรอกตัวชี้วัด น้ำหนัก และคะแนนแยกต่อโครงการ

ไม่เปลี่ยน:

- Modal กรอกข้อมูล
- payload และการบันทึกข้อมูล
- Validation
- ตาราง grouped-indicator
- การแสดงผลแบบ mobile
- ข้อมูลโครงการ หลักฐาน หรือความสัมพันธ์เดิม

## Design

### Server-rendered row shell

Blade จะสร้าง row shell สำหรับ layout แบบไม่ grouped แม้รายงานยังไม่มีกิจกรรม เพื่อให้หน้าเว็บมีโครงสร้างแถวแรกและ shared cells พร้อมใช้งาน

แถวแรกประกอบด้วย:

- shared cells: ลำดับ ค่าเป้าหมาย คะแนนถ่วงน้ำหนัก ประวัติ หลักฐาน และจัดการ
- entry cells: กิจกรรม ตัวชี้วัด น้ำหนัก และคะแนน ตาม flags ของเกณฑ์
- `rowspan` เริ่มต้นอย่างน้อย `1`

Blade จะมี `<template>` สำหรับ additional entry row ซึ่งมีเฉพาะ entry cells และไม่มี shared cells

### Client-side reconciliation

`syncDesktopEntryRows` จะเปลี่ยนจากการอัปเดตแถวที่มีอยู่เป็นการ reconcile จำนวนแถวกับจำนวนกิจกรรมใน Modal:

1. คำนวณจำนวนแถวที่ต้องแสดงเป็น `max(entryCount, 1)`
2. ใช้แถวแรกที่ Blade สร้างไว้เป็น base row
3. clone additional-row template จนจำนวนแถวพอดี
4. ลบ additional rows ที่เกินเมื่อผู้ใช้ลบกิจกรรม
5. ปรับ `rowspan` ของ shared cells ให้เท่ากับจำนวนแถวที่แสดง
6. ใส่ค่าและหมายเลขรายการใหม่ตามลำดับ
7. ใส่เส้น `border-t border-slate-100` ตั้งแต่แถวที่สอง
8. เมื่อไม่มีรายการ แถวแรกจะแสดง empty state และไม่แสดงเส้นคั่น

การ reconcile ทำงานหลังยืนยัน Modal ผ่าน flow เดิมของ `updateSupportRow` จึงไม่ส่ง request เพิ่มและไม่เปลี่ยนเวลาที่ข้อมูลถูกบันทึกจริง

### Visual behavior

- แต่ละโครงการเป็นคนละ `<tr>`
- เส้นนอนคั่นเฉพาะ entry cells
- เส้นไม่ตัดผ่าน shared cells ที่ใช้ `rowspan`
- เส้นตั้งและสีเดิมของตารางคงเดิม
- หลักฐานยังรวมอยู่ใน shared evidence cell และมี label `รายการ 1`, `รายการ 2`

## Edge Cases

- จากศูนย์รายการเป็นหนึ่งหรือหลายรายการ: สร้างแถวทันที
- เพิ่มรายการจากจำนวนเดิม: clone additional rows
- ลบรายการ: ลบแถวส่วนเกินและลด `rowspan`
- ลบจนเหลือศูนย์: คง base row หนึ่งแถวพร้อม empty state
- ยกเลิก Modal: restore snapshot และ reconcile ตารางกลับตามข้อมูลก่อนเปิด Modal
- grouped-indicator: ใช้ renderer เดิมและไม่ผ่าน reconciler นี้

## Testing

- Feature view test ยืนยันว่าเกณฑ์ non-grouped ที่ยังไม่มีกิจกรรมมี base row และ additional-row template
- JavaScript unit test ยืนยัน row plan สำหรับจำนวน `0`, `1`, `2+` และการลดจำนวนรายการ
- Existing evaluation view tests ยืนยันว่า server-rendered rows, shared cells และเส้นคั่นยังเหมือนเดิมเมื่อมีข้อมูลบันทึกอยู่
- JavaScript suite และ production build ต้องผ่านทั้งหมด

## Success Criteria

หลังเพิ่ม แก้ไข หรือลบกิจกรรมใน Modal แล้วกดยืนยัน:

- ตาราง desktop เปลี่ยนจำนวนแถวทันที
- แต่ละกิจกรรม ตัวชี้วัด น้ำหนัก และคะแนนอยู่ในแถวเดียวกัน
- เส้นคั่นปรากฏโดยไม่ต้องบันทึกหรือรีเฟรช
- การกดยกเลิก Modal คืนตารางเป็นสถานะเดิม
- การบันทึกและรีโหลดให้ผลเหมือน live preview
