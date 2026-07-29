@php
    $homeUrl ??= route('login');
@endphp

<x-error-page
    title="ไม่สามารถเข้าถึงหน้านี้ได้"
    message="บัญชีของคุณไม่มีสิทธิ์ดูหน้าที่ร้องขอ หากคิดว่าเป็นข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ"
    primary-label="กลับหน้าหลัก"
    :primary-url="$homeUrl"
    secondary-label="ย้อนกลับ"
    :secondary-url="url()->previous()"
/>
