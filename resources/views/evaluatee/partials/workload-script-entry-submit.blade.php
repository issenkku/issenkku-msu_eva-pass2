    function initWorkloadEntrySubmit() {
        if (workloadForm) {
            workloadForm.addEventListener('submit', function (event) {
                const missingFields = getMissingWorkloadFields(true);
                if (missingFields.length === 0) {
                    return;
                }

                event.preventDefault();
                updateWorkloadSubmitState();
                alert('กรุณากรอกข้อมูลให้ครบก่อนบันทึก: ' + missingFields.join(', '));
            });
        }
    }

    function initWorkloadEntryDefaults() {
        // ซิงก์ state เริ่มต้นของ modal ให้ตรงกับค่าที่ render มาจาก server
        updateFormFields();
        updateSubjectRequirementState();
        updateSubjectTriggerText();
        filterSubjectOptions();
        updateWorkloadSubmitState();
    }
