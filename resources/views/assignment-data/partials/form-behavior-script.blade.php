{{-- script กลางของฟอร์ม create/edit โดยรับข้อความและรูปแบบผ่าน config จากหน้าแม่ --}}
<script>
    $(document).ready(function() {
@include('assignment-data.partials.form-script-helpers')
@include('assignment-data.partials.form-script-renderers')
@include('assignment-data.partials.form-script-interactions')
@include('assignment-data.partials.form-script-submit-init')
    });
</script>
