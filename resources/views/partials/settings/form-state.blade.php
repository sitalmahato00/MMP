<script>
document.addEventListener('DOMContentLoaded', () => {
    const applyValues = (formSelector, values) => {
        const form = document.querySelector(formSelector);

        if (!form || !values || typeof values !== 'object') {
            return;
        }

        Object.entries(values).forEach(([name, value]) => {
            const elements = form.querySelectorAll(`[name="${name}"]`);

            if (!elements.length) {
                return;
            }

            elements.forEach((element) => {
                if (element.type === 'checkbox') {
                    element.checked = Boolean(value);
                    return;
                }

                if (element.type === 'radio') {
                    element.checked = element.value === String(value);
                    return;
                }

                element.value = value ?? '';
            });
        });
    };

    applyValues('form[action*="/settings/preferences"]', @json($preferences ?? []));
    applyValues('form[action*="/settings/notifications"]', @json($notificationPreferences ?? []));
});
</script>
