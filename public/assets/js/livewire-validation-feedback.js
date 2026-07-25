(function () {
    'use strict';

    if (window.__livewireValidationFeedbackLoaded) return;
    window.__livewireValidationFeedbackLoaded = true;

    const fingerprints = new Map();

    function errorMessages(errors) {
        return Object.values(errors || {}).flatMap(messages => Array.isArray(messages) ? messages : [messages]);
    }

    function boundProperty(element) {
        const attribute = Array.from(element.attributes || [])
            .find(item => item.name.startsWith('wire:model'));

        return attribute ? attribute.value : null;
    }

    function findField(root, key) {
        const boundField = Array.from(root.querySelectorAll('input, select, textarea, [contenteditable="true"]'))
            .find(element => boundProperty(element) === key);

        if (boundField) return boundField;

        const escapedKey = window.CSS && typeof window.CSS.escape === 'function'
            ? window.CSS.escape(key)
            : key.replace(/["\\]/g, '\\$&');
        const escapedId = window.CSS && typeof window.CSS.escape === 'function'
            ? window.CSS.escape(key.replace(/\./g, '_'))
            : key.replace(/\./g, '_').replace(/["\\]/g, '\\$&');

        return root.querySelector(
            `[name="${escapedKey}"], [name="${escapedKey}[]"], #${escapedId}, [data-validation-key="${escapedKey}"]`
        );
    }

    function clearGeneratedFeedback(root) {
        root.querySelectorAll('[data-livewire-validation-invalid="true"]').forEach(field => {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
            field.removeAttribute('data-livewire-validation-invalid');
        });

        root.querySelectorAll('[data-livewire-validation-generated="true"]').forEach(element => element.remove());
    }

    function addFieldFeedback(field, message) {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('data-livewire-validation-invalid', 'true');

        const container = field.closest('.input-group') || field;
        const parent = container.parentElement;
        const existingFeedback = parent
            ? Array.from(parent.children).find(element => element.classList.contains('invalid-feedback'))
            : null;

        if (existingFeedback) return;

        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.dataset.livewireValidationGenerated = 'true';
        feedback.textContent = message;
        container.insertAdjacentElement('afterend', feedback);
    }

    function addSummary(root, firstField, messages) {
        const form = firstField ? firstField.closest('form') : root.querySelector('form');
        if (!form) return;

        const target = firstField?.closest('.modal-body')
            || form.querySelector('.modal-body')
            || form.querySelector('.card-body')
            || form;

        if (target.querySelector('[data-validation-summary]')) return;

        const summary = document.createElement('div');
        summary.className = 'alert alert-danger border shadow-sm';
        summary.dataset.validationSummary = 'true';
        summary.dataset.livewireValidationGenerated = 'true';
        summary.setAttribute('role', 'alert');

        const heading = document.createElement('div');
        heading.className = 'fw-bold mb-2';
        heading.textContent = 'Chưa thể lưu dữ liệu. Vui lòng kiểm tra:';
        summary.appendChild(heading);

        const list = document.createElement('ul');
        list.className = 'mb-0 ps-3';
        messages.forEach(message => {
            const item = document.createElement('li');
            item.textContent = message;
            list.appendChild(item);
        });
        summary.appendChild(list);
        target.prepend(summary);
    }

    function revealField(field) {
        const modal = field.closest('.modal');
        if (modal && window.bootstrap?.Modal && !modal.classList.contains('show')) {
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        const collapse = field.closest('.collapse:not(.show)');
        if (collapse && window.bootstrap?.Collapse) {
            window.bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false }).show();
        }

        const tabPane = field.closest('.tab-pane:not(.active)');
        if (tabPane?.id && window.bootstrap?.Tab) {
            const trigger = document.querySelector(
                `[data-bs-toggle="tab"][data-bs-target="#${CSS.escape(tabPane.id)}"], `
                + `[data-bs-toggle="pill"][data-bs-target="#${CSS.escape(tabPane.id)}"]`
            );
            if (trigger) window.bootstrap.Tab.getOrCreateInstance(trigger).show();
        }

        window.setTimeout(() => {
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (typeof field.focus === 'function' && !field.disabled) {
                field.focus({ preventScroll: true });
            }
        }, 100);
    }

    function showToast(messages) {
        if (!messages.length) return;

        const remaining = messages.length - 1;
        const title = remaining > 0
            ? `${messages[0]} (và ${remaining} lỗi khác)`
            : messages[0];

        if (window.Toast && typeof window.Toast.fire === 'function') {
            window.Toast.fire({ icon: 'error', title, timer: 5000 });
        } else if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ icon: 'error', title: 'Dữ liệu chưa hợp lệ', text: title });
        }
    }

    function applyFeedback(component) {
        const root = component?.el;
        if (!root) return;

        clearGeneratedFeedback(root);

        const errors = component.snapshot?.memo?.errors || {};
        const entries = Object.entries(errors);
        const messages = errorMessages(errors);
        const fingerprint = JSON.stringify(errors);
        const previousFingerprint = fingerprints.get(component.id) || '';
        fingerprints.set(component.id, fingerprint);

        if (!entries.length) return;

        let firstField = null;
        entries.forEach(([key, fieldMessages]) => {
            const field = findField(root, key);
            if (!field) return;

            firstField = firstField || field;
            const message = Array.isArray(fieldMessages) ? fieldMessages[0] : fieldMessages;
            addFieldFeedback(field, message);
        });

        addSummary(root, firstField, messages);

        if (fingerprint !== previousFingerprint) {
            showToast(messages);
            if (firstField) revealField(firstField);
        }
    }

    function register() {
        if (!window.Livewire || window.__livewireValidationFeedbackRegistered) return;
        window.__livewireValidationFeedbackRegistered = true;

        window.Livewire.hook('component.init', ({ component }) => {
            window.setTimeout(() => applyFeedback(component), 0);
        });

        window.Livewire.hook('commit', ({ component, succeed }) => {
            succeed(() => window.setTimeout(() => applyFeedback(component), 0));
        });
    }

    document.addEventListener('livewire:init', register, { once: true });
    if (window.Livewire) register();
})();
