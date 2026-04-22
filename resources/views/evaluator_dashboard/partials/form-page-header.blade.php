{{-- ส่วนหัวหน้าฟอร์มประเมิน --}}
<div class="page-header">
    <h1>แบบประเมินผลงาน</h1>
    <p class="version">เวอร์ชัน: {{ optional($assignment->report->reportData->criteriaVersion)->version_name ?? '-' }}</p>
</div>
