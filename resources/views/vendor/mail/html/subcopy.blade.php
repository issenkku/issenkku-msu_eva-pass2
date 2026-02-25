{{-- ไฟล์มุมมอง: resources/views\vendor\mail\html\subcopy.blade.php --}}
<table class="subcopy" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
