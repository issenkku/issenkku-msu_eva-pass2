{{-- ไฟล์มุมมอง: resources/views\vendor\mail\text\layout.blade.php --}}
{!! strip_tags($header ?? '') !!}

{!! strip_tags($slot) !!}
@isset($subcopy)

{!! strip_tags($subcopy) !!}
@endisset

{!! strip_tags($footer ?? '') !!}
