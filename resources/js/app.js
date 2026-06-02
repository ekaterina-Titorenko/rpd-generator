

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

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-live-search-form]');
    const input = document.querySelector('[data-live-search-input]');

    if (!form || !input) {
        return;
    }

    let timer = null;
    let lastSubmittedValue = input.value;

    input.addEventListener('input', () => {
        window.clearTimeout(timer);

        timer = window.setTimeout(() => {
            const currentValue = input.value.trim();

            if (currentValue === lastSubmittedValue.trim()) {
                return;
            }

            lastSubmittedValue = currentValue;

            const url = new URL(form.action, window.location.origin);

            if (currentValue !== '') {
                url.searchParams.set('search', currentValue);
            }

            const sort = form.querySelector('[name="sort"]')?.value;
            const direction = form.querySelector('[name="direction"]')?.value;

            if (sort) {
                url.searchParams.set('sort', sort);
            }

            if (direction) {
                url.searchParams.set('direction', direction);
            }

            window.location.href = url.toString();
        }, 350);
    });

    input.addEventListener('search', () => {
        if (input.value !== '') {
            return;
        }

        const url = new URL(form.action, window.location.origin);

        const sort = form.querySelector('[name="sort"]')?.value;
        const direction = form.querySelector('[name="direction"]')?.value;

        if (sort) {
            url.searchParams.set('sort', sort);
        }

        if (direction) {
            url.searchParams.set('direction', direction);
        }

        window.location.href = url.toString();
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-live-search-form]');
    const input = document.querySelector('[data-live-search-input]');
    const results = document.querySelector('[data-live-search-results]');

    if (!form || !input || !results) {
        return;
    }

    let timer = null;
    let controller = null;

    const loadResults = () => {
        const url = new URL(form.action, window.location.origin);
        const value = input.value.trim();

        if (value !== '') {
            url.searchParams.set('search', value);
        }

        const sort = form.querySelector('[name="sort"]')?.value;
        const direction = form.querySelector('[name="direction"]')?.value;

        if (sort) {
            url.searchParams.set('sort', sort);
        }

        if (direction) {
            url.searchParams.set('direction', direction);
        }

        if (controller) {
            controller.abort();
        }

        controller = new AbortController();

        results.classList.add('is-loading');

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Search request failed');
                }

                return response.text();
            })
            .then((html) => {
                results.innerHTML = html;
                window.history.replaceState({}, '', url.toString());
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    console.error(error);
                }
            })
            .finally(() => {
                results.classList.remove('is-loading');
            });
    };

    input.addEventListener('input', () => {
        window.clearTimeout(timer);

        timer = window.setTimeout(() => {
            loadResults();
        }, 250);
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        window.clearTimeout(timer);
        loadResults();
    });

    form.addEventListener('reset', () => {
        window.setTimeout(() => {
            loadResults();
        }, 0);
    });

    results.addEventListener('click', (event) => {
        const link = event.target.closest('a');

        if (!link || !link.closest('th')) {
            return;
        }

        event.preventDefault();

        if (controller) {
            controller.abort();
        }

        controller = new AbortController();
        results.classList.add('is-loading');

        fetch(link.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Sort request failed');
                }

                return response.text();
            })
            .then((html) => {
                results.innerHTML = html;
                window.history.replaceState({}, '', link.href);

                const url = new URL(link.href);
                form.querySelector('[name="sort"]').value = url.searchParams.get('sort') || 'created_at';
                form.querySelector('[name="direction"]').value = url.searchParams.get('direction') || 'desc';
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    console.error(error);
                }
            })
            .finally(() => {
                results.classList.remove('is-loading');
            });
    });
});