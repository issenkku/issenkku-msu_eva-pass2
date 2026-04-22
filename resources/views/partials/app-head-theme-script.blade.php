{{-- ตรวจสอบ theme ของระบบก่อนโหลดหน้าเพื่อเติม class `dark` ให้ทันที --}}
<script>
    (function () {
        const appearance = '{{ $appearance ?? "system" }}';

        if (appearance === 'system') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (prefersDark) {
                document.documentElement.classList.add('dark');
            }
        }
    })();
</script>
