{{-- script ของหน้า list: modal รายชื่อ, ลบรายการ, และการจัดการ flash message --}}
<script>
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

    function showEvaluatees(assignmentId) {
        // เปิด modal ทันทีพร้อมสถานะ loading เพื่อให้ผู้ใช้เห็นว่าระบบกำลังทำงาน
        $('#evaluateesContent').html(
            '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="text-sm text-gray-500 mt-2">กำลังโหลดข้อมูล...</p></div>'
        );
        $('#evaluateesModal').removeClass('hidden');

        $.ajax({
            url: `/assignment-data/${assignmentId}`,
            type: 'GET',
            success: function(data) {
                // API ส่ง assignments กลับมาแล้ว หน้านี้มีหน้าที่จัดเป็น card list อย่างเดียว
                let html = '<div class="space-y-2">';

                if (data.assignments && data.assignments.length > 0) {
                    data.assignments.forEach((assignment, index) => {
                        if (assignment.evaluatee_user) {
                            const initial = assignment.evaluatee_user.name.charAt(0).toUpperCase();
                            const colors = [
                                'bg-blue-100 text-blue-600',
                                'bg-green-100 text-green-600',
                                'bg-purple-100 text-purple-600',
                                'bg-pink-100 text-pink-600',
                                'bg-indigo-100 text-indigo-600'
                            ];
                            const color = colors[index % colors.length];

                            html += `
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full ${color} flex items-center justify-center">
                                            <span class="font-semibold">${initial}</span>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <div class="text-sm font-medium text-gray-900">
                                            ${assignment.evaluatee_user.name}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ${assignment.evaluatee_user.email}
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            ผู้รับการประเมิน
                                        </span>
                                    </div>
                                </div>
                            `;
                        }
                    });
                } else {
                    html += '<div class="text-center py-8"><i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i><p class="text-sm text-gray-500">ไม่พบข้อมูลผู้รับการประเมิน</p></div>';
                }

                html += '</div>';
                $('#evaluateesContent').html(html);
            },
            error: function(xhr) {
                console.error('Error loading evaluatees:', xhr);
                $('#evaluateesContent').html(
                    '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-3"></i><p class="text-sm text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</p></div>'
                );
            }
        });
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
            success: function(response) {
                if (response.success) {
                    // ใช้ toast ชั่วคราวแล้ว reload เพื่อดึงข้อมูลล่าสุดจาก server กลับมา
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
                        $('#deleteSuccessMessage').fadeOut(() => {
                            location.reload();
                        });
                    }, 1500);
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
