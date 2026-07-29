@php
    $loginUrl ??= route('login');
    $retryUrl ??= request()->fullUrl();
@endphp

<x-error-page
    title="ระบบยังไม่พร้อมใช้งาน"
    message="ระบบอาจอยู่ระหว่างการบำรุงรักษาหรือไม่พร้อมใช้งานชั่วคราว กรุณาลองใหม่ภายหลัง"
    primary-label="ลองอีกครั้ง"
    :primary-url="$retryUrl"
    secondary-label="ไปหน้าเข้าสู่ระบบ"
    :secondary-url="$loginUrl"
/>
