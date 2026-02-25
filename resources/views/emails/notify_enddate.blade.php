{{-- ไฟล์มุมมอง: resources/views\emails\notify_enddate.blade.php --}}
<div style="font-family: Tahoma, Arial, sans-serif; font-size: 16px; color: #222;">
    <h2>แจ้งเตือน: วันสิ้นสุดการประเมินใกล้ถึงกำหนด</h2>
    <p>เรียนคุณ {{ $name }},</p>
    <p>
        กำหนดสิ้นสุดการประเมินคือ <strong>{{ $endDateTh }}</strong>
        ({{ $daysLeftText }})
    </p>
    <p>กรุณาเข้าสู่ระบบเพื่อตรวจสอบและดำเนินการประเมินให้เรียบร้อยก่อนถึงกำหนด</p>
    <p style="color: #888; font-size: 13px;">-- ระบบประเมินผล MSU-EVA --</p>
</div>
