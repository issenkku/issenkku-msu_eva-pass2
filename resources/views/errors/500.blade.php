@php
    $homeUrl ??= route('login');
    $retryUrl ??= request()->fullUrl();
@endphp

<x-error-page
    title="ระบบขัดข้องชั่วคราว"
    message="ระบบยังดำเนินการคำขอนี้ไม่ได้ กรุณาลองอีกครั้ง หรือกลับไปหน้าหลัก"
    primary-label="ลองอีกครั้ง"
    :primary-url="$retryUrl"
    secondary-label="กลับหน้าหลัก"
    :secondary-url="$homeUrl"
    tone="danger"
/>
