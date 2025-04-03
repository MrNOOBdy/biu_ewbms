function filterMeterReaderTable() {
    const searchInput = document.getElementById('meterReaderSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.uni-table tbody tr:not(.empty-state-row)');
    let visibleRows = 0;

    rows.forEach(row => {
        const textContent = row.textContent.toLowerCase();
        const matches = textContent.includes(searchInput);

        if (matches) {
            row.style.display = '';
            visibleRows++;
        } else {
            row.style.display = 'none';
        }
    });

    const existingEmptyRow = document.querySelector('.empty-state-row');
    if (existingEmptyRow) {
        existingEmptyRow.remove();
    }

    if (visibleRows === 0) {
        const tbody = document.querySelector('.uni-table tbody');
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'empty-state-row';
        emptyRow.innerHTML = `
            <td colspan="7" class="empty-state">
                <i class="fas fa-user-slash"></i>
                <p>No meter readers found</p>
            </td>
        `;
        tbody.appendChild(emptyRow);
    }
}

function showAssignBlockModal(readerId) {
    const modal = document.getElementById('assignBlockModal');
    document.getElementById('reader_id').value = readerId;

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);

    fetch(`/meter-readers/${readerId}/blocks`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.blocks.length > 0) {
                const selectElement = document.getElementById('block_id');
                selectElement.value = data.blocks[0];
            }
        })
        .catch(error => console.error('Error fetching blocks:', error));
}

document.getElementById('assignBlockForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const blockId = document.getElementById('block_id').value;
    const blockSelect = document.getElementById('block_id');
    const invalidFeedback = blockSelect.nextElementSibling;

    blockSelect.classList.remove('is-invalid');
    if (invalidFeedback) {
        invalidFeedback.style.display = 'none';
    }

    if (!blockId) {
        blockSelect.classList.add('is-invalid');
        if (invalidFeedback) {
            invalidFeedback.textContent = 'Please select a block';
            invalidFeedback.style.display = 'block';
        }
        return;
    }

    formData.set('blocks[]', blockId);

    fetch('/meter-readers/assign-blocks', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.errors) {
                blockSelect.classList.add('is-invalid');
                if (invalidFeedback) {
                    const errorMessage = Array.isArray(data.errors.blocks)
                        ? data.errors.blocks[0]
                        : data.errors.blocks;
                    invalidFeedback.textContent = errorMessage;
                    invalidFeedback.style.display = 'block';
                }
                return;
            }

            if (data.success) {
                closeModal('assignBlockModal');
                showResultModal('success', 'Success', data.message);
            } else {
                showResultModal('error', 'Error', data.message || 'Failed to assign block');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResultModal('error', 'Error', 'Failed to assign block');
        });
});

document.getElementById('block_id').addEventListener('change', function () {
    this.classList.remove('is-invalid');
    const feedback = this.nextElementSibling;
    feedback.style.display = 'none';
});

function showResultModal(type, title, message) {
    const modal = document.getElementById('resultModal');
    const icon = document.getElementById('resultIcon');
    const titleElement = document.getElementById('resultTitle');
    const messageElement = document.getElementById('resultMessage');

    icon.innerHTML = type === 'success'
        ? '<i class="fas fa-check-circle" style="color: #28a745;"></i>'
        : '<i class="fas fa-times-circle" style="color: #dc3545;"></i>';
    icon.className = type === 'success' ? 'success-icon' : 'error-icon';

    titleElement.textContent = title;
    messageElement.textContent = message;

    if (type === 'success') {
        modal.setAttribute('data-refresh', 'true');
    }

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeModal(modalId) {
    if (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('fade-in');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    }
}

function closeModalAndReload() {
    closeModal('resultModal');
    setTimeout(() => {
        window.location.reload();
    }, 300);
}

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        if (event.target.id === 'resultModal') {
            closeModalAndReload();
        } else {
            closeModal(event.target.id);
        }
    }
}

const flatpickrConfig = {
    dateFormat: "Y-m-d",
    altFormat: "M d, Y",
    altInput: true,
    allowInput: false,
    disableMobile: true,
    minDate: "today"
};

function handleSubstitutionSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    const startDateInput = document.querySelector('#start_date');
    const endDateInput = document.querySelector('#end_date');

    if (!startDateInput._flatpickr.selectedDates[0] || !endDateInput._flatpickr.selectedDates[0]) {
        showResultModal('error', 'Error', 'Please select both start and end dates');
        return;
    }

    fetch('/meter-readers/substitutions', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('substitutionModal');
                showResultModal('success', 'Success', data.message);
                loadActiveSubstitutions();
            } else {
                const message = data.errors
                    ? Object.values(data.errors).flat().join('\n')
                    : data.message;
                showResultModal('error', 'Error', message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResultModal('error', 'Error', 'Failed to create substitution');
        });
}

function showSubstitutionModal(readerId) {
    const modal = document.getElementById('substitutionModal');
    document.getElementById('absent_reader_id').value = readerId;

    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    startDateInput.min = today;
    endDateInput.min = today;

    const substituteSelect = document.getElementById('substitute_reader_id');
    Array.from(substituteSelect.options).forEach(option => {
        option.disabled = option.value === readerId;
    });

    const form = document.getElementById('substitutionForm');
    form.reset();

    const startDatePicker = document.getElementById('start_date')._flatpickr;
    const endDatePicker = document.getElementById('end_date')._flatpickr;
    startDatePicker.clear();
    endDatePicker.clear();

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function loadActiveSubstitutions() {
    fetch('/meter-readers/substitutions?status=active', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('activeSubstitutions');
            container.innerHTML = '';

            if (data.success) {
                if (data.data.length === 0) {
                    container.innerHTML = '<p class="no-data">No active substitutions</p>';
                    return;
                }

                data.data.forEach(sub => {
                    const element = document.createElement('div');
                    element.className = 'substitution-item';
                    element.innerHTML = `
                    <div class="sub-info">
                        <p><strong>Absent Reader:</strong> ${sub.absent_reader.firstname} ${sub.absent_reader.lastname}</p>
                        <p><strong>Substitute:</strong> ${sub.substitute_reader.firstname} ${sub.substitute_reader.lastname}</p>
                        <p><strong>Period:</strong> ${formatDate(sub.start_date)} to ${formatDate(sub.end_date)}</p>
                        <p><strong>Reason:</strong> ${sub.reason}</p>
                    </div>
                    <div class="sub-actions">
                        <button onclick="endSubstitution(${sub.id})" class="btn_uni btn-end">
                            <i class="fas fa-times-circle"></i> End Substitution
                        </button>
                    </div>
                `;
                    container.appendChild(element);
                });
            }
        })
        .catch(error => console.error('Error loading substitutions:', error));
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function endSubstitution(id) {
    if (!confirm('Are you sure you want to end this substitution?')) {
        return;
    }

    fetch(`/meter-readers/substitutions/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: 'inactive' })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResultModal('success', 'Success', data.message);
                loadActiveSubstitutions();
            } else {
                showResultModal('error', 'Error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResultModal('error', 'Error', 'Failed to end substitution');
        });
}

document.addEventListener('DOMContentLoaded', function () {
    loadActiveSubstitutions();
    initializeDatePickers();
});
