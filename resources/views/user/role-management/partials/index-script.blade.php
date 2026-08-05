{{-- script ของหน้าบทบาท ใช้เปิดและปิด modal สร้างบทบาทใหม่ --}}
<script>
    @include('user.role-management.partials.index-script-open-modal')
    @include('user.role-management.partials.index-script-close-modal')

    document.getElementById('roleForm')?.addEventListener('submit', async function (event) {
        event.preventDefault();

        const form = event.currentTarget;
        const coordinator = window.MasterDataPage?.createMasterDataSubmitCoordinator({
            applyMutation: async () => {
                await window.AsyncResourceTable.refreshTableRegion(
                    window.location.href,
                    '[data-async-table-region]',
                );
                window.MasterDataPage.initializeAsyncDeleteForms(document);
            },
            applyValidationErrors: (errors) => {
                const message = Object.values(errors || {}).flat()[0];
                if (message) {
                    window.MasterDataPage.showMasterDataMessage(message, true);
                }
            },
            button: form.querySelector('[type="submit"]'),
            form,
            hideModal: closeModal,
            resetForm: () => form.reset(),
        });

        if (coordinator) {
            await coordinator({ preventDefault() {} });
        } else {
            form.submit();
        }
    });
</script>
