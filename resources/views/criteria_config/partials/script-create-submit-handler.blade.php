        document.getElementById('confirm_submit_btn').addEventListener('click', async function handleSubmit() {
            hideConfirmModal();
            isSubmitting = true;
            updateFloatingSaveButton();
            showLoading();

            try {
                const response = await fetch("{{ route('report-structure.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value ||
                            document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(finalData)
                });

                const data = await response.json();

                hideLoading();

                if (response.ok && data.success) {
                    // Success case (HTTP 201)
                    showSuccessModal();
                } else if (response.status === 422) {
                    isSubmitting = false;
                    updateFloatingSaveButton();
                    // Validation error (HTTP 422)
                    let errorMessage = 'เน€เธเธดเธ”เธเนเธญเธเธดเธ”เธเธฅเธฒเธ”เนเธเธเธฒเธฃเธ•เธฃเธงเธเธชเธญเธเธเนเธญเธกเธนเธฅ:\n';

                    // Check for both 'error' and 'errors' to handle potential response variations
                    const errors = data.error || data.errors || {};

                    if (Object.keys(errors).length > 0) {
                        // Process validation errors if present
                        for (const [field, messages] of Object.entries(errors)) {
                            errorMessage +=
                                `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}\n`;
                        }
                    } else {
                        // Fallback if no specific errors are provided
                        errorMessage += data.message || 'เนเธกเนเธเธเธฃเธฒเธขเธฅเธฐเน€เธญเธตเธขเธ”เธเนเธญเธเธดเธ”เธเธฅเธฒเธ”';
                    }

                    alert(errorMessage);
                } else {
                    isSubmitting = false;
                    updateFloatingSaveButton();
                    // Other errors (e.g., HTTP 500)
                    alert('เน€เธเธดเธ”เธเนเธญเธเธดเธ”เธเธฅเธฒเธ”: ' + (data.message || 'เนเธกเนเธชเธฒเธกเธฒเธฃเธ–เธเธฑเธเธ—เธถเธเธเนเธญเธกเธนเธฅเนเธ”เน'));
                }
            } catch (error) {
                // Network or unexpected errors
                isSubmitting = false;
                updateFloatingSaveButton();
                hideLoading();
                console.error('Fetch error:', error);
                alert('เน€เธเธดเธ”เธเนเธญเธเธดเธ”เธเธฅเธฒเธ”เนเธเธเธฒเธฃเน€เธเธทเนเธญเธกเธ•เนเธญ: ' + error.message);
            }
        });
