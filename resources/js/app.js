

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