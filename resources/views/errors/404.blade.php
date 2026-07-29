@php
    $homeUrl ??= route('login');
@endphp

<x-error-page
    title="ไม่พบหน้าที่ต้องการ"
    message="ลิงก์อาจไม่ถูกต้อง หมดอายุ ถูกย้าย หรือข้อมูลนี้ไม่มีอยู่แล้ว"
    primary-label="กลับหน้าหลัก"
    :primary-url="$homeUrl"
    secondary-label="ย้อนกลับ"
    :secondary-url="url()->previous()"
/>
