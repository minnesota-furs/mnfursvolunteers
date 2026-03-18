import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('custom-fields-tbody');
    if (!tbody) return;

    const reorderUrl = tbody.dataset.reorderUrl;
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'bg-blue-50',
        onEnd() {
            const order = [...tbody.querySelectorAll('tr[data-id]')]
                .map(row => row.dataset.id);

            fetch(reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ order }),
            });
        },
    });
});
