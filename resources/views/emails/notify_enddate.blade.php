{{-- ไฟล์มุมมอง: resources/views/emails/notify_enddate.blade.php --}}
<div style="font-family: Tahoma, Arial, sans-serif; font-size: 16px; color: #222;">
    <h2>แจ้งเตือน: วันสิ้นสุดการประเมินใกล้ถึงกำหนด</h2>
    <p>เรียนคุณ {{ $name }},</p>
    <p>
        กำหนดสิ้นสุดการประเมินคือ <strong>{{ $endDateTh }}</strong>
        ({{ $daysLeftText }})
    </p>
    <p>กรุณาเข้าสู่ระบบเพื่อตรวจสอบและดำเนินการประเมินให้เรียบร้อยก่อนถึงกำหนด</p>
    @if (!empty($actionUrl))
        <p style="margin: 24px 0;">
            <a href="{{ $actionUrl }}" style="display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
                {{ $actionText ?? 'เข้าสู่รายการประเมิน' }}
            </a>
        </p>
        <p style="font-size: 13px; color: #666;">หากปุ่มใช้งานไม่ได้ สามารถเปิดลิงก์นี้ได้โดยตรง: {{ $actionUrl }}</p>
    @endif
    <p style="color: #888; font-size: 13px;">-- ระบบประเมินผล MSU-EVA --</p>
</div>
