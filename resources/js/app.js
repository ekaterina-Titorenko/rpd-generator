//
document.addEventListener('DOMContentLoaded', () => {
    const autosubmitTimers = new Map();

    document.querySelectorAll('[data-autosubmit]').forEach((field) => {
        const eventName = field.tagName === 'SELECT' || field.type === 'number'
            ? 'change'
            : 'input';

        field.addEventListener(eventName, () => {
            const formId = field.getAttribute('form');

            if (!formId) {
                return;
            }

            const form = document.getElementById(formId);

            if (!form) {
                return;
            }

            clearTimeout(autosubmitTimers.get(formId));

            const delay = eventName === 'input' ? 700 : 150;

            autosubmitTimers.set(formId, setTimeout(() => {
                form.requestSubmit();
            }, delay));
        });
    });
});

function resizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-autoresize]').forEach((textarea) => {
        resizeTextarea(textarea);

        textarea.addEventListener('input', () => {
            resizeTextarea(textarea);
        });
    });
});