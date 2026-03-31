{{-- ไฟล์มุมมอง: resources/views/emails/assignment_Notify.blade.php --}}
<div style="font-family: Tahoma, Arial, sans-serif; font-size: 16px; color: #222;">
    <h2>แจ้งเตือน: คุณได้รับการมอบหมายจัดทำแบบประเมิน</h2>
    <p>เรียนคุณ {{ $name }},</p>
    <p>ระบบได้ทำการมอบหมายให้ท่านจัดทำแบบประเมิน ชื่องานประเมิน: <strong>{{ $report_title }}</strong> ({{ $version_name ?? '-' }})</p>

    <p>ผู้เกี่ยวข้องในการประเมิน</p>
    @if (!empty($reviewer_contacts))
        <ul style="margin: 8px 0 16px 20px; padding: 0; color: #222;">
            @foreach ($reviewer_contacts as $contact)
                <li style="margin-bottom: 4px;">{{ $contact['label'] }}: {{ $contact['name'] }}</li>
            @endforeach
        </ul>
    @else
        <p>ผู้ประเมินของท่านคือ: {{ $evaluator_name }}</p>
    @endif

    <p>กรุณาเข้าสู่ระบบเพื่อตรวจสอบรายละเอียดและดำเนินการประเมิน</p>

    @if (!empty($action_url))
        <p style="margin: 24px 0;">
            <a href="{{ $action_url }}" style="display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600;">
                {{ $action_text ?? 'เข้าสู่แบบประเมิน' }}
            </a>
        </p>
        <p style="font-size: 13px; color: #666;">หากปุ่มใช้งานไม่ได้ สามารถเปิดลิงก์นี้ได้โดยตรง: {{ $action_url }}</p>
    @endif

    <p style="color: #888; font-size: 13px;">-- ระบบประเมินผล MSU-EVA --</p>
</div>
