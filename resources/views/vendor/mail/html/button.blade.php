{{-- ไฟล์มุมมอง: resources/views\vendor\mail\html\button.blade.php --}}
@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
{{-- ตารางข้อมูล --}}
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
{{-- ตารางข้อมูล --}}
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
{{-- ตารางข้อมูล --}}
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
