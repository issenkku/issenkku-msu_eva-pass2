# Support Criteria Rich Text Design

วันที่: 2026-07-20

สถานะ: อนุมัติแนวทางแล้ว รอตรวจทานเอกสารก่อนพัฒนา

## เป้าหมาย

เปลี่ยนช่อง `กิจกรรม/โครงการ/งาน` และ `ตัวชี้วัด/เกณฑ์การประเมิน` ในฟอร์มตั้งค่าเกณฑ์สายสนับสนุนจากช่องข้อความบรรทัดเดียวเป็น Summernote Rich Text Editor แบบเดียวกับช่องคำอธิบายเกณฑ์เดิม และแสดงรูปแบบ Rich Text ที่บันทึกไว้ในหน้าประเมินอย่างปลอดภัย

## ขอบเขต

- ใช้ Summernote 0.8.18 Lite และภาษาไทยที่ระบบโหลดอยู่แล้ว
- ใช้ toolbar ชุดเดียวกับช่องคำอธิบายเดิม: style, bold, italic, underline, clear, color, unordered/ordered list, paragraph, table, link, horizontal rule, fullscreen, code view และ help
- รองรับหน้าสร้างและแก้ไขโครงสร้างรายงาน รวมถึงการเพิ่ม ลบ clone และเรียงเกณฑ์สายสนับสนุนแบบไดนามิก
- เก็บ HTML ในฟิลด์ `support_criterias.activity_name` และ `support_criterias.indicator`
- แสดง Rich Text ในตาราง desktop, การ์ด mobile และหัวข้อ/รายละเอียดใน modal ของเกณฑ์สายสนับสนุน
- ไม่เปลี่ยนรูปแบบ payload, route, สิทธิ์, การคำนวณคะแนน หรือขั้นตอนการประเมินเดิม

## แนวทางที่เลือก

ใช้โครงสร้าง `.richtext-editor` และการตั้งค่า Summernote กลางที่มีอยู่ แทนการเพิ่ม editor ใหม่หรือสร้าง toolbar เฉพาะสองช่อง เพื่อให้หน้าตา พฤติกรรม ภาษา และการดูแลรักษาสอดคล้องกับช่องคำอธิบายเดิม

ทั้งสองฟิลด์จะเปลี่ยนจาก `<input type="text">` เป็น `<textarea>` แต่คง class เฉพาะฟิลด์ `support_activity_name` และ `support_indicator` ไว้ เพื่อไม่กระทบโค้ดเก็บข้อมูลและ populate ที่อ้างถึง selector เหล่านี้

## Editor Lifecycle

Summernote ต้องทำงานถูกต้องในทุก lifecycle ต่อไปนี้:

1. กล่องที่มีอยู่ตอนโหลดหน้าจะถูก initialize หนึ่งครั้ง
2. กล่องที่ populate จากข้อมูลเดิมจะรับ HTML ก่อน initialize เพื่อให้ toolbar แสดงค่าถูกต้อง
3. เมื่อเพิ่มหรือ clone เกณฑ์ใหม่ ระบบจะลบ Summernote wrapper, state, ID และ marker ที่ติดมาจากต้นแบบ ก่อน initialize textarea ใหม่
4. ก่อนลบเกณฑ์ ระบบจะ destroy instance ของ Summernote ใน block นั้น
5. callback `onChange` จะ sync HTML กลับสู่ textarea และเรียก dirty-state เดิม
6. ก่อนรวบรวม payload ระบบจะอ่าน HTML จาก Summernote instance เมื่อมี instance และ fallback ไปยังค่า textarea เมื่อยังไม่ได้ initialize

การ initialize ซ้ำต้องไม่สร้าง toolbar ซ้อนกัน และการ clone ต้องไม่คัดลอกเนื้อหาหรือสถานะของ editor ต้นแบบไปยังรายการใหม่

## การบันทึกและฐานข้อมูล

ฟิลด์ `indicator` เป็น `TEXT` อยู่แล้ว ส่วน `activity_name` เป็น `VARCHAR(255)` ซึ่งไม่เหมาะกับ HTML ที่มี markup เพิ่มจาก Summernote จึงเพิ่ม forward migration ใหม่เพื่อเปลี่ยน `activity_name` เป็น `TEXT` โดยไม่แก้ migration เดิมที่อาจถูกใช้งานไปแล้ว

validation ของ `activity_name` จะเลิกจำกัดที่ 255 ตัวอักษร แต่ทั้ง `activity_name` และ `indicator` ยังเป็น required string ระบบต้องตรวจเนื้อหาหลังแปลง HTML เป็น plain text เพื่อไม่ยอมรับค่าที่ว่างจริง เช่น `<p><br></p>`, whitespace หรือ markup ที่ไม่มีข้อความ

HTML จะถูกเก็บตามที่ editor ส่งมาเพื่อให้แก้ไขซ้ำได้โดยไม่สูญเสียรูปแบบ การกรองความปลอดภัยจะทำตอน render ผ่าน `SafeHtml::richText()` ตามรูปแบบที่ระบบใช้อยู่

## การแสดงผลและความปลอดภัย

ทุกตำแหน่งที่แสดงสองฟิลด์แก่ผู้ใช้ใน component เกณฑ์สายสนับสนุนจะใช้ `SafeHtml::richText()` เพื่อคงรูปแบบที่อนุญาตและกรอง element/attribute ที่เป็นอันตรายด้วย HTMLPurifier

ข้อมูลที่นำไปใช้เป็นข้อความล้วน เช่น `data-support-activity`, `aria-label`, ข้อความ validation และชื่อรายการใน JavaScript ต้องไม่ใช้ HTML ดิบ ระบบจะเพิ่มวิธีแปลง Rich Text เป็น plain text ที่ decode entity, ตัด tag และจัด whitespace ให้เหมาะสม เพื่อไม่ให้ผู้ใช้ได้ยินชื่อ tag จาก screen reader หรือเห็น markup ในข้อความผิดพลาด

ห้ามนำ HTML ดิบไปต่อเป็น `innerHTML` ใน JavaScript และห้าม render ด้วย Blade raw output โดยไม่ผ่าน `SafeHtml::richText()`

## การจัดวาง

สอง editor ยังคงอยู่ใน grid สองคอลัมน์บนจอขนาดกลางขึ้นไปและเรียงหนึ่งคอลัมน์บนมือถือ ความสูง editor เริ่มต้น 250px ตามช่องคำอธิบายเดิม แต่ต้องยืดตามเนื้อหา/resize handle ของ Summernote ได้โดยไม่ดัน block อื่นซ้อนกัน

label และเครื่องหมาย required เดิมยังคงอยู่ และ textarea ต้องเชื่อมกับ label ในลักษณะที่ใช้งานด้วยคีย์บอร์ดและ screen reader ได้

## การจัดการข้อผิดพลาด

- ถ้าฟิลด์ใดไม่มีข้อความจริง ให้หยุดการบันทึกและแสดงข้อความว่าต้องกรอกข้อมูลเกณฑ์สายสนับสนุนให้ครบ
- การตรวจฝั่ง browser ช่วยชี้รายการที่ผิด แต่ server-side validation เป็นแหล่งตัดสินสุดท้าย
- หาก Summernote ยังไม่ initialize หรือโหลดไม่ได้ textarea ต้องยังรับ แก้ไข และส่งค่าได้
- ข้อมูลเก่าที่เป็น plain text ต้องเปิดแก้ไขและแสดงผลได้เหมือนเดิมโดยไม่ต้อง migrate เนื้อหา

## การทดสอบ

พัฒนาแบบ test-first และครอบคลุมอย่างน้อย:

- template แสดง textarea Rich Text ทั้งสองฟิลด์ พร้อม class/selector ที่โค้ดเดิมต้องใช้
- toolbar และภาษาเหมือน Summernote ของช่องคำอธิบายเดิม
- การเพิ่มและ populate เกณฑ์ใหม่ initialize editor โดยไม่เกิด toolbar ซ้ำหรือค่าติดจากรายการต้นแบบ
- form collector ส่ง HTML ของ editor ทั้งหน้า create และ edit
- server ปฏิเสธ Rich Text ที่ไม่มีข้อความจริง
- API สร้างและแก้ไขเก็บ HTML ได้ รวมถึง `activity_name` ที่ยาวเกินขีดจำกัดเดิม 255 ตัวอักษร
- ตาราง desktop, mobile และ modal แสดง tag ที่ปลอดภัย เช่น `<strong>` และ `<ul>` เป็น Rich Text
- `<script>`, event handler และ HTML อันตรายถูกกรองจากผลลัพธ์
- `aria-label`, data attribute และข้อความ validation ใช้ plain text โดยไม่มี tag HTML
- ชุดทดสอบเกณฑ์สายสนับสนุนและ flow การประเมินเดิมยังผ่าน

## เกณฑ์สำเร็จ

- ผู้ดูแลระบบจัดรูปแบบข้อความในทั้งสองช่องด้วย Summernote ได้ทั้งตอนสร้างและแก้ไข
- รายการที่เพิ่มแบบไดนามิกมี editor ที่ทำงานครบและไม่มี toolbar ซ้ำ
- รูปแบบที่บันทึกแสดงในทุกมุมมองของหน้าประเมินอย่างถูกต้อง
- plain text เดิมยังรองรับโดยไม่ต้องแปลงข้อมูลย้อนหลัง
- HTML อันตรายไม่ถูก render และข้อความสำหรับ accessibility ไม่มี markup ปะปน
- ไม่มีการเปลี่ยนแปลงกติกาคะแนน payload หรือสิทธิ์เดิม
