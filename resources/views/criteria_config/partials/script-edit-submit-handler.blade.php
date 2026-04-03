        // Form submission with full data structure
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!isInitialDataLoaded) {
                showError('ยังโหลดข้อมูลเดิมไม่สำเร็จ ระบบจะไม่บันทึกเพื่อป้องกันการสร้างข้อมูลซ้ำ');
                return;
            }
            
            $('.richtext-editor').each(function() {
                if ($(this).next('.note-editor').length > 0) {
                    const content = $(this).summernote('code');
                    this.value = content;
                }
            });
            
            const versionName = document.getElementById('version_name').value.trim();
            const reportTitle = document.getElementById('report_title').value.trim();
            const reportDescription = document.getElementById('report_description').value.trim();
            const assessmentType = document.getElementById('assessment_type').value.trim();

            if (!versionName || !reportTitle || !assessmentType) {
                alert('กรุณากรอกข้อมูลพื้นฐานให้ครบถ้วน');
                return;
            }

            isSubmitting = true;
            updateFloatingSaveButton();
            showLoading();

            let formData;
            try {
                formData = collectFormData();
            } catch (error) {
                isSubmitting = false;
                updateFloatingSaveButton();
                hideLoading();
                alert(error.message);
                return;
            }
            
            fetch(this.action, {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData),
                cache: 'no-store',
                credentials: 'same-origin'
            })
            .then(async response => {
                let data = {};
                try {
                    data = await response.json();
                } catch (err) {
                    // keep default object if response is not JSON
                }
                return { ok: response.ok, status: response.status, data };
            })
            .then(({ ok, status, data }) => {
                if (ok && data.success === true) {
                    isSubmitting = false;
                    resetDirtyState();
                    fetchVersionDetails();
                    hideLoading();
                    showSuccess();
                } else {
                    isSubmitting = false;
                    updateFloatingSaveButton();
                    hideLoading();
                    let errorMessage = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                    if (data.message) {
                        errorMessage = data.message;
                    }
                    if (data.error) {
                        errorMessage += "\\n" + Object.values(data.error).flat().join("\\n");
                    }
                    showError(errorMessage);
                }
            })
            .catch(error => {
                isSubmitting = false;
                updateFloatingSaveButton();
                hideLoading();
                console.error('Error:', error);
                showError('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            });
        });
