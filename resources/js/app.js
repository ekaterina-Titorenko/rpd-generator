

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.querySelector('#type');
    const parentField = document.querySelector('[data-topic-parent-field]');
    const parentSelect = document.querySelector('#parent_id');

    if (!typeSelect || !parentField || !parentSelect) {
        return;
    }

    const toggleParentField = () => {
        const isTopic = typeSelect.value === 'topic';

        parentField.hidden = !isTopic;
        parentSelect.disabled = !isTopic;
        parentField.closest('form')?.classList.toggle('has-topic-parent', isTopic);

        if (!isTopic) {
            parentSelect.value = '';
        }
    };

    typeSelect.addEventListener('change', toggleParentField);

    toggleParentField();
});

document.addEventListener('DOMContentLoaded', () => {
    const reviewTextarea = document.querySelector('#review_comment');
    const reviewCopies = document.querySelectorAll('[data-review-comment-copy]');

    if (!reviewTextarea || reviewCopies.length === 0) {
        return;
    }

    const syncReviewComment = () => {
        reviewCopies.forEach((input) => {
            input.value = reviewTextarea.value;
        });
    };

    reviewTextarea.addEventListener('input', syncReviewComment);
    syncReviewComment();
});

document.addEventListener('DOMContentLoaded', () => {
    const autosubmitTimers = new Map();

    document.querySelectorAll('[data-autosubmit]').forEach((field) => {
        const eventName = field.matches('input[type="number"], select')
            ? 'change'
            : 'input';

        field.addEventListener(eventName, () => {
            const formId = field.getAttribute('form');
            const form = formId
                ? document.getElementById(formId)
                : field.closest('form');

            if (!form) {
                return;
            }

            clearTimeout(autosubmitTimers.get(form));

            const delay = eventName === 'input' ? 900 : 150;

            sessionStorage.setItem('rpdScrollY', String(window.scrollY));
            sessionStorage.setItem('rpdPathname', window.location.pathname);

            autosubmitTimers.set(form, setTimeout(() => {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {

                    form.submit();
                }
            }, delay));
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const sourceTypeSelect = document.querySelector('[data-source-type-select]');
    const sourceFields = document.querySelectorAll('[data-source-field]');

    if (!sourceTypeSelect || sourceFields.length === 0) {
        return;
    }

    const toggleSourceFields = () => {
        const selectedType = sourceTypeSelect.value;

        sourceFields.forEach((field) => {
            const allowedTypes = field.dataset.sourceField.split(' ');
            const isVisible = allowedTypes.includes(selectedType);

            field.hidden = !isVisible;

            field.querySelectorAll('input, textarea, select').forEach((input) => {
                input.disabled = !isVisible;
            });
        });
    };

    sourceTypeSelect.addEventListener('change', toggleSourceFields);
    toggleSourceFields();
});

document.addEventListener('DOMContentLoaded', () => {
    const savedScrollY = sessionStorage.getItem('rpdScrollY');
    const savedPathname = sessionStorage.getItem('rpdPathname');

    if (!savedScrollY || savedPathname !== window.location.pathname) {
        return;
    }

    sessionStorage.removeItem('rpdScrollY');
    sessionStorage.removeItem('rpdPathname');

    window.requestAnimationFrame(() => {
        window.scrollTo({
            top: Number(savedScrollY),
            behavior: 'instant',
        });
    });
});