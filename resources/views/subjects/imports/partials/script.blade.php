<script>
document.addEventListener('DOMContentLoaded', function () {
    const conflicts = Array.from(document.querySelectorAll('[data-subject-import-conflict]'));
    document.querySelector('[data-subject-import-select-all]')?.addEventListener('click', function () {
        conflicts.forEach(function (checkbox) { checkbox.checked = true; });
    });
    document.querySelector('[data-subject-import-select-none]')?.addEventListener('click', function () {
        conflicts.forEach(function (checkbox) { checkbox.checked = false; });
    });
});
</script>
