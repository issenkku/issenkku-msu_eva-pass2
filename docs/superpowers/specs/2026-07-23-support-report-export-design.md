# Support Report Export Design

## Goal

ทำให้ Export รายงานภาพรวมและรายบุคคลแสดงคะแนนและรายละเอียดสายสนับสนุนครบถ้วน ใช้กติกาคะแนนเดียวกับหน้าจอ ป้องกันการ Export ข้อมูลนอกขอบเขตสิทธิ์ และมี regression tests ครอบคลุมข้อมูลในไฟล์จริง

## Scope

งานนี้ครอบคลุม:

- Export รายงานภาพรวมผ่าน `ReportsExport`
- Export รายงานรายบุคคลผ่าน `SingleReportExport`
- การตรวจสิทธิ์และสถานะใน `FileExportController`
- การคำนวณคะแนนกลางที่ใช้ร่วมกับ `EvaluationScoreSummary`
- รายละเอียดเกณฑ์สายสนับสนุน กิจกรรม/โครงการเพิ่มเติม และลิงก์หลักฐาน
- ความเห็นแยกตามบทบาท
- Audit log สำหรับ Export รายบุคคล
- Automated tests สำหรับสูตร เนื้อหา Export และ authorization

งานนี้ไม่เปลี่ยน:

- สูตร Quantity และ Quality
- จำนวนระดับค่าเป้าหมาย ซึ่งคงที่เป็น `5`
- โครงสร้างฐานข้อมูลคะแนนสายสนับสนุน
- ขั้นตอนการกรอกหรืออนุมัติคะแนน

## Score policy

สร้าง `App\Support\ReportScoreSummary` เป็นแหล่งกำหนดกติกาคะแนนสำหรับหน้าจอและ Export โดยรับ:

- คะแนน Quantity
- คะแนน Quality
- ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุนแบบ raw

และคืนค่า:

- `quantity`
- `quality`
- `support_raw`
- `support` ซึ่งจำกัดสูงสุดที่ `100`
- `support_achievement` ซึ่งคำนวณจาก `support_raw / 5`
- `total` ซึ่งคำนวณจาก `quantity + quality + support`

คะแนนผลสัมฤทธิ์ใช้ค่า raw ตามกติกาการบันทึกเดิม ส่วนคะแนนรวมใช้คะแนนสายสนับสนุนหลังจำกัดที่ 100 ตามที่ผู้ใช้ยืนยัน

`EvaluationScoreSummary` ต้องเรียกใช้ `ReportScoreSummary` แทนการกำหนดสูตรซ้ำ เพื่อให้หน้าจอและ Export ใช้ policy เดียวกัน

## Dashboard export

`ReportsExport` ต้องเพิ่มคอลัมน์:

1. ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน
2. คะแนนผลสัมฤทธิ์ของงาน

คอลัมน์คะแนนรวมต้องใช้ค่าจาก `ReportScoreSummary` และรวมคะแนนสายสนับสนุนหลังจำกัดที่ 100

เนื่องจากไฟล์ภาพรวมเป็นตารางหลายรายงาน หัวคอลัมน์สายสนับสนุนต้องคงอยู่ทุกแถว รายงานที่ไม่มีคะแนนสายสนับสนุนให้แสดง `0.00` เพื่อรักษา schema ของ workbook ให้คงที่

ต้องปรับ column widths และช่วง style/font ให้ครอบคลุมคอลัมน์ใหม่

Export ต้องคงคอลัมน์ความเห็นเดิมเพื่อ compatibility และเพิ่มความเห็นแยก:

- ความเห็นผู้ประเมิน
- ความเห็นกรรมการ
- ความเห็นผู้บริหาร

## Single-report export

### Summary sheet

เมื่อรายงานมีเกณฑ์สายสนับสนุน ให้เพิ่ม:

- ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน
- คะแนนผลสัมฤทธิ์ของงาน

คะแนนรวมใช้ค่าจาก `ReportScoreSummary`

เมื่อรายงานไม่มีเกณฑ์สายสนับสนุน ไม่เพิ่มแถวคะแนนสายสนับสนุนและคะแนนผลสัมฤทธิ์ เพื่อให้สอดคล้องกับกติกาซ่อน/แสดงของหน้าจอ

เพิ่มความเห็นแยกตามบทบาททั้งสามรายการ โดยคงความเห็นรวมเดิมไว้เพื่อ compatibility

### Category sheets

ใช้ `SupportCriteriaReadModel::forReport()` เป็นแหล่งข้อมูลสายสนับสนุน และเพิ่ม `support_items` เข้า evaluation list ที่ตรงกับ `evaluation_list_id`

แต่ละเกณฑ์สายสนับสนุนต้อง Export:

- ชื่อกิจกรรม/โครงการ
- ตัวชี้วัด
- ค่าเป้าหมาย
- น้ำหนัก
- คะแนนที่ทำได้
- คะแนนถ่วงน้ำหนัก
- กิจกรรม/โครงการเพิ่มเติม
- URL หลักฐานจริง

ข้อความ rich text ต้องแปลงเป็น plain text ด้วย `SafeHtml::plainText()` ก่อนเขียนลง Excel

ยอดรวมของ evaluation list ต้องเป็น:

`Quantity + Quality + Support weighted score`

ถ้าไม่มีเกณฑ์สายสนับสนุน Category sheet ต้องคงผลลัพธ์เดิม

## Authorization and state

### Dashboard export

- `/admin/export/reports` อนุญาตเฉพาะ `admin` และ `ผู้บริหาร`
- `/export/reports` คงพฤติกรรม role-aware เดิม

### Single-report export

- `admin`, `ผู้บริหาร` และ `กรรมการ` Export รายงานที่อยู่ในขอบเขตข้อมูลภาพรวมได้
- `ผู้ประเมิน` Export ได้เฉพาะรายงานใน assignments ของตน
- ผู้ใช้ที่ไม่มีสิทธิ์ได้รับ HTTP `403`
- Report หรือ Assignment ที่ไม่มีอยู่ได้รับ HTTP `404`
- Export รายบุคคลได้เฉพาะรายงานสถานะ `Completed`
- รายงานที่ยังไม่ Completed ได้รับ HTTP `409`

ต้องตรวจสิทธิ์และสถานะก่อนสร้าง workbook และก่อนบันทึก audit log ว่าส่งออกสำเร็จ

## Audit logging

เพิ่ม audit log สำหรับ Export รายบุคคล โดยบันทึก:

- `export_type: single_report`
- `report_id`
- `assignment_data_id`
- `evaluatee_id`
- ผู้ใช้งานที่ดำเนินการ

การ Export ที่ถูกปฏิเสธด้วย 403, 404 หรือ 409 ต้องไม่สร้าง success audit log

## Data loading and performance

- Dashboard export ต้อง eager-load relation ที่ใช้ซ้ำและหลีกเลี่ยง query คะแนนแบบหนึ่ง query ต่อหนึ่งรายงาน
- Single-report export ต้อง reuse `SupportCriteriaReadModel` แทนการเขียน query เกณฑ์ คะแนน กิจกรรม และหลักฐานซ้ำ
- ไม่เพิ่ม dependency ภายนอก

## Testing

เพิ่ม tests อย่างน้อยดังนี้:

1. `ReportScoreSummary` จำกัดคะแนน Support ที่ 100 สำหรับ total
2. `support_achievement` ใช้ raw support score หาร 5
3. `EvaluationScoreSummary` และ export score summary ให้ค่าตรงกัน
4. Dashboard export มี headings และค่าคะแนนสายสนับสนุนใหม่
5. Dashboard export ใช้คะแนนรวมที่รวม Support หลังจำกัด
6. Single-report Summary แสดง Support เฉพาะเมื่อมีเกณฑ์
7. Category sheet แสดงข้อมูลเกณฑ์ กิจกรรมเพิ่มเติม และ URL หลักฐานจริง
8. ยอดรวมระดับ evaluation list รวม Support weighted score
9. ความเห็นทั้งสามบทบาทปรากฏใน Export
10. ผู้ประเมินไม่สามารถ Export report ที่ไม่ได้รับมอบหมาย
11. Admin export route ปฏิเสธบทบาทที่ไม่มีสิทธิ์
12. Single-report export ปฏิเสธรายงานที่ยังไม่ Completed
13. Single-report export ที่สำเร็จสร้าง audit log

ใช้ TDD แบบ red-green-refactor และรัน focused tests, formatter, JavaScript tests, production build และ full PHP suite ก่อนส่งมอบ

## Acceptance criteria

- คะแนนรวมใน Dashboard, confirmation UI และ Excel ใช้ policy เดียวกัน
- Export ภาพรวมมี Support raw total และ achievement score
- Export รายบุคคลมี Summary และรายละเอียดสายสนับสนุนครบ
- URL หลักฐานจริงปรากฏใน Excel
- รายงานที่ไม่มีเกณฑ์สายสนับสนุนไม่เกิด section ว่างใน Single-report export
- ไม่มีผู้ใช้ Export รายงานนอกขอบเขตสิทธิ์ได้
- ไม่มี regression ต่อ Quantity และ Quality
