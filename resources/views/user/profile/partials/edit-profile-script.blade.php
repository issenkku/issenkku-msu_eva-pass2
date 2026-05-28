<script>
const oldEducationHistory = @json(old('education_history', $user->education_history_entries));

function educationHistoryRowTemplate(index, entry = {}) {
    return `
        <div class="grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 md:grid-cols-[140px_1fr_1fr_auto]">
            <input type="text" name="education_history[${index}][graduation_year]" value="${entry.graduation_year ?? ''}" placeholder="ปีที่จบ" maxlength="4" class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black">
            <input type="text" name="education_history[${index}][degree]" value="${entry.degree ?? ''}" placeholder="วุฒิการศึกษา" class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black">
            <input type="text" name="education_history[${index}][university]" value="${entry.university ?? ''}" placeholder="มหาวิทยาลัยที่จบ" class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black">
            <button type="button" onclick="removeEducationHistoryRow(this)" class="rounded-md border border-red-300 px-3 py-2 text-sm text-red-600 hover:bg-red-50">ลบ</button>
        </div>
    `;
}

function renderEducationHistoryRows(entries = []) {
    const container = document.getElementById('educationHistoryRows');
    const normalizedEntries = Array.isArray(entries) && entries.length > 0 ? entries : [{}];
    container.innerHTML = normalizedEntries.map((entry, index) => educationHistoryRowTemplate(index, entry)).join('');
}

function removeEducationHistoryRow(button) {
    const container = document.getElementById('educationHistoryRows');
    button.closest('div.grid').remove();

    const rows = Array.from(container.children).map(row => ({
        graduation_year: row.querySelector('[name$="[graduation_year]"]')?.value ?? '',
        degree: row.querySelector('[name$="[degree]"]')?.value ?? '',
        university: row.querySelector('[name$="[university]"]')?.value ?? '',
    }));

    renderEducationHistoryRows(rows);
}

document.getElementById('addEducationHistoryRow').addEventListener('click', function() {
    const container = document.getElementById('educationHistoryRows');
    const index = container.children.length;
    container.insertAdjacentHTML('beforeend', educationHistoryRowTemplate(index, {}));
});

renderEducationHistoryRows(oldEducationHistory);

document.getElementById('profile_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('preview-photo').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('phone').addEventListener('input', function(e) {
    const value = e.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        e.target.value = value;
    }
});

document.getElementById('copy-profile-link').addEventListener('click', function() {
    const isEnabled = document.getElementById('enable-public-profile').checked;
    if (!isEnabled) {
        alert('กรุณาเปิดใช้โปรไฟล์สาธารณะก่อนคัดลอกลิงก์');
        return;
    }

    const publicUrl = "{{ $user->public_profile_url }}";
    navigator.clipboard.writeText(publicUrl).then(function() {
        const btn = document.getElementById('copy-profile-link');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> คัดลอกสำเร็จ!';
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-green-600');
        setTimeout(function() {
            btn.innerHTML = original;
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 1500);
    }, function() {
        alert('ไม่สามารถคัดลอกลิงก์ได้');
    });
});

document.getElementById('enable-public-profile').addEventListener('change', function() {
    const isEnabled = this.checked;
    const copyBtn = document.getElementById('copy-profile-link');
    const hiddenInput = document.getElementById('is_public_profile_enabled');

    hiddenInput.value = isEnabled ? '1' : '0';
    if (isEnabled) {
        copyBtn.disabled = false;
        copyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        copyBtn.disabled = true;
        copyBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
});
</script>
