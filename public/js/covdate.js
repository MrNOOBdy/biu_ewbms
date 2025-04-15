const flatpickrConfig = {
    dateFormat: "Y-m-d",
    altFormat: "M d, Y",
    altInput: true,
    allowInput: false,
    disableMobile: true
};

function initDatePickers(selector, config) {
    document.querySelectorAll(selector).forEach(picker => {
        if (!picker._flatpickr) {
            flatpickr(picker, config);
        }
    });
}

function initModalPickers() {
    const addModal = document.getElementById('addCoverageDateModal');
    addModal.addEventListener('shown', () => {
        initDatePickers('#coverage_date_from, #coverage_date_to, #reading_date, #due_date', flatpickrConfig);
    });

    const editModal = document.getElementById('editCoverageDateModal');
    editModal.addEventListener('shown', () => {
        initDatePickers('#edit_coverage_date_from, #edit_coverage_date_to, #edit_reading_date, #edit_due_date', flatpickrConfig);
    });
}

function showAddModal() {
    const modal = document.getElementById('addCoverageDateModal');
    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('fade-in');
        initializeAllDatePickers();
    }, 10);
}

function filterTable() {
    const dateFromPicker = document.querySelector('#dateFrom')?._flatpickr;
    const dateToPicker = document.querySelector('#dateTo')?._flatpickr;

    if (!dateFromPicker || !dateToPicker) return;

    const dateFrom = dateFromPicker.selectedDates[0];
    const dateTo = dateToPicker.selectedDates[0];

    const tbody = document.querySelector('.table-container:not(.active-table) .uni-table tbody');
    const rows = tbody.querySelectorAll('tr:not(.empty-row)');
    let hasMatches = false;

    const existingEmptyRow = tbody.querySelector('.empty-row');
    if (existingEmptyRow) {
        existingEmptyRow.remove();
    }

    rows.forEach(row => {
        const covDateFrom = row.querySelector('td:first-child').textContent;
        const covDateValue = new Date(covDateFrom);

        const matchesFilter = (!dateFrom || covDateValue >= dateFrom) &&
            (!dateTo || covDateValue <= dateTo);

        if (matchesFilter) {
            row.style.display = '';
            hasMatches = true;
        } else {
            row.style.display = 'none';
        }
    });

    if (!hasMatches) {
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'empty-row';
        emptyRow.innerHTML = `
            <td colspan="6" class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No coverage dates found in selected date range</p>
            </td>`;
        tbody.appendChild(emptyRow);
    }
}

function clearFilter() {
    const dateFromPicker = document.getElementById('dateFrom')._flatpickr;
    const dateToPicker = document.getElementById('dateTo')._flatpickr;

    dateFromPicker.clear();
    dateToPicker.clear();
    filterTable();
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    if (modalId === 'resultModal' && modal.getAttribute('data-refresh') === 'true') {
        return;
    }

    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modalId === 'addCoverageDateModal') {
            document.getElementById('addCoverageDateForm').reset();
            clearFormErrors(document.getElementById('addCoverageDateForm'));
        }
    }, 300);
}

function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);


    validateCoverageDates(formData).then(validationResult => {
        if (!validationResult.success) {
            showResultModal(false, validationResult.message, 'warning', false);
            return;
        }

        if (formData.get('status') === 'Open') {
            const activeRow = document.querySelector('.active-table tbody tr:not(.empty-row)');
            if (activeRow) {
                pendingFormData = formData;
                const modal = document.getElementById('statusSwitchModal');
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);
                return;
            }
        }

        submitNewCoverageDate(formData);
    });
}

function validateCoverageDates(formData) {
    const dateData = {
        coverage_date_from: formData.get('coverage_date_from'),
        coverage_date_to: formData.get('coverage_date_to'),
        due_date: formData.get('due_date')
    };

    const coverageFrom = new Date(dateData.coverage_date_from);
    const coverageTo = new Date(dateData.coverage_date_to);
    const dueDate = new Date(dateData.due_date);

    if (coverageTo <= coverageFrom) {
        return Promise.resolve({
            success: false,
            message: 'Coverage Date To must be after Coverage Date From'
        });
    }

    if (dueDate <= coverageTo) {
        return Promise.resolve({
            success: false,
            message: 'Due Date must be after Coverage Date To'
        });
    }

    if (dueDate <= coverageFrom) {
        return Promise.resolve({
            success: false,
            message: 'Due Date must be after Coverage Date From'
        });
    }

    return Promise.resolve({ success: true });
}

function submitNewCoverageDate(formData) {
    clearFormErrors(document.getElementById('addCoverageDateForm'));

    fetch('/coverage-dates', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('addCoverageDateModal');
                closeModal('statusSwitchModal');
                pendingFormData = null;
                showResultModal(true, 'Coverage date created successfully');
            } else {
                pendingFormData = null;
                closeModal('statusSwitchModal');
                throw new Error(JSON.stringify({ message: data.message, type: 'warning' }));
            }
        })
        .catch(error => {
            let errorData;
            try {
                errorData = JSON.parse(error.message);
            } catch (e) {
                errorData = { message: error.message || 'An unexpected error occurred', type: 'warning' };
            }
            showResultModal(false, errorData.message, errorData.type);
        });
}

function showResultModal(success, message, type = null, shouldRefresh = true) {
    const modal = document.getElementById('resultModal');
    const icon = document.getElementById('resultIcon');
    const title = document.getElementById('resultTitle');
    const messageElement = document.getElementById('resultMessage');

    if (!success) {
        type = type || 'warning';
        shouldRefresh = false;
    }

    if (type === 'warning') {
        icon.className = 'warning';
        icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        title.textContent = 'Warning';
    } else {
        icon.className = 'success';
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        title.textContent = 'Success';
    }

    messageElement.textContent = message;
    modal.style.display = 'block';

    if (shouldRefresh) {
        modal.setAttribute('data-refresh', 'true');
    } else {
        modal.removeAttribute('data-refresh');
    }

    setTimeout(() => {
        modal.classList.add('fade-in');
    }, 10);

    document.addEventListener('click', handleResultModalClick);
}

function closeResultModal() {
    const modal = document.getElementById('resultModal');
    modal.classList.remove('fade-in');

    setTimeout(() => {
        modal.style.display = 'none';
        modal.removeAttribute('data-static');
        document.removeEventListener('click', handleResultModalClick);

        if (modal.getAttribute('data-refresh') === 'true') {
            window.location.reload();
        }
    }, 300);
}

function handleResultModalClick(event) {
    const modal = document.getElementById('resultModal');
    const modalContent = modal.querySelector('.modal-content');

    if (modal.style.display === 'block') {
        if (!modalContent.contains(event.target)) {
            closeResultModal();
        }
    }
}

function refreshTable() {
    window.location.reload();
}

function initializeAllDatePickers() {
    const filterConfig = {
        ...flatpickrConfig,
        onChange: function (selectedDates, dateStr, instance) {
            if (instance.element.id === 'dateFrom' || instance.element.id === 'dateTo') {
                filterTable();
            }
        }
    };

    ['#dateFrom', '#dateTo'].forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
            if (element._flatpickr) {
                element._flatpickr.destroy();
            }
            flatpickr(element, filterConfig);
        }
    });

    const modalSelectors = [
        '#coverage_date_from', '#coverage_date_to', '#reading_date', '#due_date',
        '#edit_coverage_date_from', '#edit_coverage_date_to', '#edit_reading_date', '#edit_due_date'
    ];

    modalSelectors.forEach(selector => {
        const element = document.querySelector(selector);
        if (element && !element._flatpickr) {
            flatpickr(element, flatpickrConfig);
        }
    });
}

window.initializeAllDatePickers = initializeAllDatePickers;

function clearFormErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(input => {
        input.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(errorDiv => {
        errorDiv.style.display = 'none';
    });
}

function handleValidationErrors(form, errors) {
    Object.keys(errors).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const errorDiv = input.parentElement.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.textContent = errors[field][0];
                errorDiv.style.display = 'block';
            }
        }
    });
}

let coverageDateToDelete = null;

function editCoverageDate(id) {
    fetch(`/coverage-dates/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_covdate_id').value = data.covdate_id;

            const modal = document.getElementById('editCoverageDateModal');
            const statusGroup = modal.querySelector('.inline-form-group:has(#edit_status)');

            if (data.status === 'Open') {
                statusGroup.style.display = 'none';
            } else {
                statusGroup.style.display = 'flex';
            }

            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('fade-in');

                ['coverage_date_from', 'coverage_date_to', 'due_date'].forEach(field => {
                    const element = document.getElementById(`edit_${field}`);
                    if (element && !element._flatpickr) {
                        flatpickr(element, flatpickrConfig);
                    }
                });

                const setPickerDate = (fieldId, date) => {
                    const element = document.getElementById(fieldId);
                    if (element && element._flatpickr) {
                        element._flatpickr.setDate(date);
                        element.dataset.originalValue = date;
                    }
                };

                setPickerDate('edit_coverage_date_from', data.coverage_date_from);
                setPickerDate('edit_coverage_date_to', data.coverage_date_to);
                setPickerDate('edit_due_date', data.due_date);

                const statusInput = document.getElementById('edit_status');
                if (statusInput) {
                    statusInput.value = data.status;
                    statusInput.dataset.originalValue = data.status;
                }
            }, 10);
        })
        .catch(error => {
            showResultModal(false, 'Error fetching coverage date data', 'warning', false);
        });
}

let pendingFormData = null;

function handleEditFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const id = document.getElementById('edit_covdate_id').value;
    const formData = new FormData(form);

    const hasChanges = ['coverage_date_from', 'coverage_date_to', 'due_date'].some(field => {
        const input = document.getElementById(`edit_${field}`);
        if (!input || !input._flatpickr) return false;
        const currentValue = input._flatpickr.formatDate(input._flatpickr.selectedDates[0], "Y-m-d");
        return currentValue !== input.dataset.originalValue;
    });

    const statusInput = document.getElementById('edit_status');
    const statusChanged = statusInput && statusInput.value !== statusInput.dataset.originalValue;

    if (!hasChanges && !statusChanged) {
        showResultModal(false, 'No changes were made to update', 'warning', false);
        return;
    }

    const coverageFrom = new Date(formData.get('coverage_date_from'));
    const coverageTo = new Date(formData.get('coverage_date_to'));
    const dueDate = new Date(formData.get('due_date'));

    if (dueDate <= coverageTo) {
        showResultModal(false, 'Due Date must be after Coverage Date To', 'warning', false);
        return;
    }

    if (dueDate <= coverageFrom) {
        showResultModal(false, 'Due Date must be after Coverage Date From', 'warning', false);
        return;
    }

    const tr = document.querySelector(`[data-covdate-id="${id}"]`);
    const currentStatus = tr?.dataset.status;

    if (currentStatus === 'Open') {
        formData.set('status', 'Open');
    }

    const newStatus = formData.get('status');
    if (currentStatus === 'Close' && newStatus === 'Open') {
        pendingFormData = formData;
        const modal = document.getElementById('statusSwitchModal');
        if (modal) {
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('fade-in'), 10);
            return;
        }
    }

    submitEditForm(id, formData);
}

function confirmStatusSwitch() {
    if (!pendingFormData) return;

    const form = document.getElementById('editCoverageDateForm');
    if (form.elements['covdate_id']) {
        const id = document.getElementById('edit_covdate_id').value;
        submitEditForm(id, pendingFormData);
    } else {
        validateCoverageDates(pendingFormData).then(validationResult => {
            if (!validationResult.success) {
                showResultModal(false, validationResult.message, 'warning', false);
                return;
            }
            submitNewCoverageDate(pendingFormData);
        });
    }
}

function submitEditForm(id, formData) {
    clearFormErrors(document.getElementById('editCoverageDateForm'));

    validateCoverageDates(formData).then(validationResult => {
        if (!validationResult.success) {
            showResultModal(false, validationResult.message, 'warning', false);
            return;
        }

        fetch(`/coverage-dates/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('editCoverageDateModal');
                    if (pendingFormData) {
                        closeModal('statusSwitchModal');
                        pendingFormData = null;
                    }
                    showResultModal(true, 'Coverage date updated successfully');
                } else {
                    throw new Error(JSON.stringify({ message: data.message || 'Failed to update coverage date', type: 'warning' }));
                }
            })
            .catch(error => {
                let errorData;
                try {
                    errorData = JSON.parse(error.message);
                } catch (e) {
                    errorData = { message: 'An unexpected error occurred', type: 'warning' };
                }
                showResultModal(false, errorData.message, errorData.type);
            });
    });
}

function deleteCoverageDate(id) {
    coverageDateToDelete = id;
    const modal = document.getElementById('deleteCoverageDateModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function confirmDelete() {
    if (!coverageDateToDelete) return;

    fetch(`/coverage-dates/${coverageDateToDelete}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            closeModal('deleteCoverageDateModal');
            if (data.success) {
                showResultModal(true, 'Coverage date deleted successfully', 'success', true);
            } else {
                throw { message: data.message, type: data.type || 'warning' };
            }
        })
        .catch(error => {
            closeModal('deleteCoverageDateModal');
            showResultModal(false, error.message || 'Error deleting coverage date', 'warning', true);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof flatpickr === 'function') {
        setTimeout(initializeAllDatePickers, 100);
    } else {
        console.error('Flatpickr not loaded');
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
    }
});

document.querySelectorAll('.modal-content').forEach(content => {
    content.addEventListener('click', (e) => {
        e.stopPropagation();
    });
});

function handleOutsideClick(event) {
    const modal = event.target.closest('.modal');
    if (modal && modal.getAttribute('data-static') === 'true') {
        event.stopPropagation();
        return;
    }
}
