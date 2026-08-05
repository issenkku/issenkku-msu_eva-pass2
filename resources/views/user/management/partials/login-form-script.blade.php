<script>
    const form = document.getElementById('loginForm');
    const errorDiv = document.getElementById('error');
    const fallbackMessage = 'เข้าสู่ระบบไม่สำเร็จ';

    if (form && window.fetch) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            errorDiv?.classList.add('hidden');

            let message = fallbackMessage;

            try {
                const response = await window.fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();

                if (!response.ok) {
                    message = data.message || fallbackMessage;
                    throw new Error(message);
                }

                if (typeof data.redirect !== 'string') {
                    throw new Error(fallbackMessage);
                }

                window.location.assign(data.redirect);
            } catch {
                if (errorDiv) {
                    errorDiv.textContent = message;
                    errorDiv.classList.remove('hidden');
                }
            }
        });
    }
</script>
