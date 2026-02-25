{{-- ไฟล์มุมมอง: resources/views\emails\assignment_Notify.blade.php --}}
<div style="font-family: Tahoma, Arial, sans-serif; font-size: 16px; color: #222;">
    <h2>แจ้งเตือน: คุณได้รับการมอบหมายจัดทำแบบประเมิน</h2>
    <p>เรียนคุณ {{ $name }},</p>
    <p>ระบบได้ทำการมอบหมายให้ท่านทำแบบประเมิน ชื่อการประเมิน: <strong>{{ $report_title }}</strong> ({{ $version_name ?? '-' }})</p>
    <p>ผู้ประเมินของท่านคือ : {{$evaluator_name}}</p>
    <p>กรุณาเข้าสู่ระบบเพื่อตรวจสอบรายละเอียดเพิ่มเติม</p>
    <p style="color: #888; font-size: 13px;">-- ระบบประเมินผล MSU-EVA --</p>
</div>
