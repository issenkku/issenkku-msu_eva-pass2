{{-- ไฟล์มุมมอง: resources/views\vendor\mail\html\footer.blade.php --}}
<tr>
<td>
{{-- ตารางข้อมูล --}}
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
