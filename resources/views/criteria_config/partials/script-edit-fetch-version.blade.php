        function fetchVersionDetails() {
            isInitialDataLoaded = false;
            suppressDirtyTracking = true;
            resetDirtyState();
            showLoading();
            const url = "{{ route('report-structure.show', ['id' => $id ?? '']) }}?t=" + Date.now();
            
            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                cache: 'no-store',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                isInitialDataLoaded = false;
                isSubmitting = false;
                updateFloatingSaveButton();
                if (data.data) {
                    originalData = data.data;
                    populateForm(data.data);
                    isInitialDataLoaded = true;
                    suppressDirtyTracking = false;
                    resetDirtyState();
                    requestAnimationFrame(() => observeVisibleSummernote());
                } else {
                    suppressDirtyTracking = false;
                    showError('ไม่พบข้อมูลเวอร์ชัน');
                }
            })
            .catch(error => {
                hideLoading();
                isInitialDataLoaded = false;
                isSubmitting = false;
                updateFloatingSaveButton();
                suppressDirtyTracking = false;
                console.error('Error:', error);
                showError('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            });
        }
