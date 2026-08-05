{{-- script ของหน้า list: modal รายชื่อ, ลบรายการ, และการจัดการ flash message --}}
<script>
    let evaluateesModalItems = [];
    const evaluateeAvatarColors = [
        'bg-blue-100 text-blue-600',
        'bg-green-100 text-green-600',
        'bg-purple-100 text-purple-600',
        'bg-pink-100 text-pink-600',
        'bg-indigo-100 text-indigo-600'
    ];

    $(document).ready(function() {
        // ตั้ง CSRF token กลางสำหรับทุก request ของหน้านี้
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // flash message ฝั่งหน้า list ให้หายเองเมื่อผู้ใช้ไม่กดปิด
        setTimeout(function() {
            $('#successMessage, #errorMessage').fadeOut();
        }, 5000);
    });

    $(document).on('click', '[data-flash-close]', function() {
        $(this).closest('#successMessage, #errorMessage').remove();
    });

    $(document).on('click', '[data-show-evaluatees]', function() {
        showEvaluatees($(this).data('assignment-id'));
    });

    $(document).on('click', '[data-modal-close]', function() {
        closeModal();
    });

    $(document).on('click', '[data-delete-assignment]', function() {
        deleteAssignment($(this).data('assignment-id'), this);
    });

    $(document).on('input', '#evaluateesSearch', function() {
        renderEvaluateesList($(this).val());
    });

    function showEvaluatees(assignmentId) {
        // เปิด modal พร้อมสถานะ loading เพื่อให้ผู้ใช้เห็นว่าระบบกำลังทำงาน
        evaluateesModalItems = [];
        $('#evaluateesSearch').val('').prop('disabled', true);
        $('#evaluateesContent').html(
            '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="text-sm text-gray-500 mt-2">กำลังโหลดข้อมูล...</p></div>'
        );
        $('#evaluateesModal').removeClass('hidden');

        $.ajax({
            url: `/assignment-data/${assignmentId}`,
            type: 'GET',
            success: function(data) {
                // API ส่ง assignments กลับมาแล้ว หน้านี้มีหน้าที่จัดเป็นรายชื่อใน modal
                evaluateesModalItems = (data.assignments || [])
                    .filter(assignment => assignment.evaluatee_user)
                    .map((assignment, index) => ({
                        name: assignment.evaluatee_user.name || '',
                        email: assignment.evaluatee_user.email || '',
                        color: evaluateeAvatarColors[index % evaluateeAvatarColors.length]
                    }));

                $('#evaluateesSearch')
                    .val('')
                    .prop('disabled', evaluateesModalItems.length === 0);

                renderEvaluateesList('');
            },
            error: function(xhr) {
                console.error('Error loading evaluatees:', xhr);
                $('#evaluateesContent').html(
                    '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-3"></i><p class="text-sm text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</p></div>'
                );
            }
        });
    }

    function renderEvaluateesList(searchTerm) {
        const query = (searchTerm || '').trim().toLowerCase();
        const filteredItems = evaluateesModalItems.filter(item => {
            return item.name.toLowerCase().includes(query) || item.email.toLowerCase().includes(query);
        });

        let html = '<ol class="divide-y divide-gray-200">';

        if (evaluateesModalItems.length === 0) {
            html += '<li class="text-center py-8"><i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i><p class="text-sm text-gray-500">ไม่พบข้อมูลผู้รับการประเมิน</p></li>';
        } else if (filteredItems.length === 0) {
            html += '<li class="text-center py-8"><i class="fas fa-search text-4xl text-gray-300 mb-3"></i><p class="text-sm text-gray-500">ไม่พบรายชื่อที่ค้นหา</p></li>';
        } else {
            filteredItems.forEach((item, index) => {
                const safeName = escapeHtml(item.name);
                const safeEmail = escapeHtml(item.email);
                const initial = escapeHtml((item.name || '?').charAt(0).toUpperCase());

                html += `
                    <li class="flex items-center gap-3 px-1 py-3 hover:bg-gray-50 transition-colors">
                        <span class="w-6 flex-shrink-0 text-right text-xs text-gray-400">
                            ${index + 1}.
                        </span>
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-full ${item.color} flex items-center justify-center">
                                <span class="text-xs font-semibold">${initial}</span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900">
                                ${safeName}
                            </div>
                            <div class="truncate text-sm text-gray-500">
                                ${safeEmail}
                            </div>
                        </div>
                    </li>
                `;
            });
        }

        html += '</ol>';
        $('#evaluateesContent').html(html);
    }

    function escapeHtml(value) {
        return $('<div>').text(value).html();
    }

    function closeModal() {
        // แยก closeModal ไว้เป็นฟังก์ชันกลาง เพราะถูกเรียกจากทั้งปุ่มปิดและคลิก backdrop
        $('#evaluateesModal').addClass('hidden');
    }

    $(document).on('click', '#evaluateesModal', function(e) {
        if (e.target.id === 'evaluateesModal') {
            closeModal();
        }
    });

    function deleteAssignment(id, buttonElement) {
        // ลบข้อมูลเป็น action ที่กระทบหลายตาราง จึงยืนยันกับผู้ใช้ก่อนทุกครั้ง
        if (!confirm('คุณแน่ใจหรือไม่ที่ต้องการลบรอบการประเมินนี้?\n\nการดำเนินการนี้จะลบข้อมูลทั้งหมดที่เกี่ยวข้องและไม่สามารถย้อนกลับได้')) {
            return;
        }

        const button = buttonElement;
        const originalContent = button.innerHTML;

        // lock ปุ่มทันทีเพื่อลดการกดซ้ำระหว่าง request
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>กำลังลบ...';
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');

        $.ajax({
            url: `/assignment-data/${id}`,
            type: 'DELETE',
            dataType: 'json',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    window.AsyncResourceTable.applyResourceMutation(document, response);
                    const successHtml = `
                        <div id="deleteSuccessMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
                            <div class="flex items-center space-x-3">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>${response.message}</span>
                            </div>
                        </div>
                    `;
                    $('body').append(successHtml);

                    setTimeout(() => {
                        $('#deleteSuccessMessage').fadeOut(function () {
                            $(this).remove();
                        });
                    }, 1500);

                    if (!document.querySelector('[data-async-table-region] [data-resource-row]')) {
                        window.AsyncResourceTable.refreshTableRegion(
                            window.location.href,
                            '[data-async-table-region]',
                        );
                    }
                    return;
                }

                alert('เกิดข้อผิดพลาด: ' + response.message);
                button.innerHTML = originalContent;
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            },
            error: function(xhr) {
                // แปล error ที่พบบ่อยให้เป็นข้อความที่ผู้ใช้พอเข้าใจได้
                console.error('Delete error:', xhr);
                let errorMessage = 'เกิดข้อผิดพลาดในการลบข้อมูล';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 419) {
                    errorMessage = 'CSRF Token หมดอายุ กรุณารีเฟรชหน้าเว็บ';
                } else if (xhr.status === 500) {
                    errorMessage = 'เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์';
                }

                alert(errorMessage);
                button.innerHTML = originalContent;
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
    }
</script>
