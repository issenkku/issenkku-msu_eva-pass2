{{-- ไฟล์มุมมอง: resources/views\emails\evaluation_completed.blade.php --}}
<div style="font-family: Tahoma, Arial, sans-serif; font-size: 16px; color: #222;">
    <h2>แจ้งเตือน: ผลการประเมินของคุณเสร็จสมบูรณ์</h2>
    <p>เรียนคุณ {{ $name }},</p>
    <p>ผลการประเมินหัวข้อ <strong>{{ $report_title }}</strong> ({{ $version_name ?? '-' }}) ได้รับการประเมินผ่านเป็นที่เรียบร้อยแล้ว</p>
    <p>กรุณาเข้าสู่ระบบเพื่อตรวจสอบรายละเอียดเพิ่มเติม</p>
    <p style="color: #888; font-size: 13px;">-- ระบบประเมินผล MSU-EVA --</p>
</div>
