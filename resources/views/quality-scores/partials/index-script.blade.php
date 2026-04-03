{{-- script ของหน้าคะแนนคุณภาพ สำหรับกรองหรือเปิดดูรายละเอียดรายเกณฑ์ --}}
<script>
    function showScoreDetails(subCriteriaId) {
        const url = `{{ route('quality-scores.index') }}?filter_criteria=${subCriteriaId}`;
        window.location.href = url;
    }
</script>
