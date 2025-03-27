function showAddConsumerModal() {
    const modal = document.getElementById('addConsumerModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);

    const barangaySelect = document.getElementById('barangay');
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    barangaySelect.disabled = true;
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

function handleConsumerSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    const street = formData.get('street');
    const barangay = formData.get('barangay');
    formData.set('address', `${street}, ${barangay}`);
    formData.delete('street');
    formData.delete('barangay');

    form.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });

    fetch('/consumers', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = data.errors[key][0];
                        }
                    }
                });
            } else {
                closeModal('addConsumerModal');
                showConsumerResultModal(true, data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showConsumerResultModal(false, 'An error occurred while saving the consumer.');
        });
}

function showConsumerResultModal(success, message) {
    const modal = document.getElementById('consumerResultModal');
    const icon = document.getElementById('consumerResultIcon');
    const title = document.getElementById('consumerResultTitle');
    const messageEl = document.getElementById('consumerResultMessage');

    if (!success) {
        icon.className = 'warning';
        icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        title.textContent = 'Warning';
    } else {
        icon.className = 'success';
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        title.textContent = 'Success';
        modal.setAttribute('data-refresh', 'true');
    }

    messageEl.textContent = message;
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeConsumerResultModal() {
    const modal = document.getElementById('consumerResultModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.getAttribute('data-refresh') === 'true') {
            location.reload();
        }
    }, 300);
}

function filterConsumers() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
    const blockValue = document.getElementById('blockFilter').value;
    const statusValue = document.getElementById('statusFilter').value;
    const tbody = document.querySelector('.uni-table tbody');
    const paginationWrapper = document.querySelector('.pagination-wrapper');
    
    // Hide pagination while filtering
    if (paginationWrapper) {
        paginationWrapper.style.display = 'none';
    }

    const rows = tbody.querySelectorAll('tr');
    let hasMatches = false;

    rows.forEach(row => {
        if (row.classList.contains('empty-state-row') || !row.querySelector('td')) {
            row.remove();
        }
    });

    const dataRows = tbody.querySelectorAll('tr');
    if (dataRows.length === 0) {
        showEmptyState(tbody);
        return;
    }

    dataRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const block = cells[0].textContent.split(' ')[1];
            const customerId = cells[1].textContent.toLowerCase();
            const name = cells[2].textContent.toLowerCase();
            const address = cells[3].textContent.toLowerCase();
            const contact = cells[4].textContent.toLowerCase();
            const consumerType = cells[5].textContent.toLowerCase();
            const statusBadge = row.querySelector('.status-badge');
            const status = statusBadge ? statusBadge.textContent.trim() : '';

            const matchesSearch = !searchValue ||
                customerId.includes(searchValue) ||
                name.includes(searchValue) ||
                address.includes(searchValue) ||
                contact.includes(searchValue) ||
                consumerType.includes(searchValue);

            const matchesBlock = !blockValue || block === blockValue;
            const matchesStatus = !statusValue || status === statusValue;

            const matches = matchesSearch && matchesBlock && matchesStatus;
            row.style.display = matches ? '' : 'none';
            if (matches) hasMatches = true;
        }
    });

    if (!hasMatches) {
        showEmptyState(tbody);
    }

    // Show pagination if not filtering
    if (!searchValue && !blockValue && !statusValue && paginationWrapper) {
        paginationWrapper.style.display = 'flex';
    }
}

function showEmptyState(tbody) {
    const emptyRow = document.createElement('tr');
    emptyRow.classList.add('empty-state-row');
    const colspan = document.querySelector('.uni-table thead th:last-child').cellIndex + 1;
    emptyRow.innerHTML = `
        <td colspan="${colspan}" class="empty-state">
            <i class="fas fa-search"></i>
            <p>No consumers found for your search</p>
        </td>`;
    tbody.appendChild(emptyRow);
}
window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        if (event.target.id === 'consumerResultModal') {
            closeConsumerResultModal();
        } else {
            closeModal(event.target.id);
        }
    }
};

function showDeleteConfirmationModal(customerId) {
    fetch(`/consumers/${customerId}/view`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const consumer = data.consumer;
                const modal = document.getElementById('deleteConfirmationModal');
                const warningMessage = document.querySelector('#deleteConfirmationModal .warning-message');
                const warningText = document.querySelector('#deleteConfirmationModal .warning-text');

                if (consumer.status === 'Pending') {
                    warningText.style.display = 'none';
                    warningMessage.style.display = 'none';
                    document.getElementById('deleteConsumerInfo').innerHTML = `
                        <div class="simple-delete-info">
                            <p>You are about to delete the following pending application:</p>
                            <p><strong>Consumer ID:</strong> ${consumer.customer_id}</p>
                            <p><strong>Name:</strong> ${consumer.firstname} ${consumer.middlename ? consumer.middlename + ' ' : ''}${consumer.lastname}</p>
                            <p class="simple-warning">Do you want to proceed with deletion?</p>
                        </div>
                    `;
                } else {
                    warningText.style.display = 'block';
                    warningMessage.style.display = 'block';
                    document.getElementById('deleteConsumerInfo').innerHTML = `
                        <p><strong>Consumer ID:</strong> ${consumer.customer_id}</p>
                        <p><strong>Name:</strong> ${consumer.firstname} ${consumer.middlename ? consumer.middlename + ' ' : ''}${consumer.lastname}</p>
                        <p><strong>Block:</strong> ${consumer.block_id}</p>
                        <p><strong>Address:</strong> ${consumer.address}</p>
                        <p><strong>Status:</strong> ${consumer.status}</p>
                        ${consumer.billings_count ? `<p><strong>Existing Records:</strong></p>
                        <ul>
                            <li>Billing Records: ${consumer.billings_count}</li>
                            <li>Payment Records: ${consumer.payments_count}</li>
                            ${consumer.pending_balance ? `<li class="warning">Pending Balance: ₱${consumer.pending_balance}</li>` : ''}
                        </ul>` : ''}
                    `;
                }

                const confirmBtn = document.getElementById('confirmDeleteBtn');
                confirmBtn.setAttribute('data-customer-id', customerId);
                confirmBtn.onclick = () => deleteConsumer(customerId);

                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);
            } else {
                showConsumerResultModal(false, 'Error fetching consumer details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showConsumerResultModal(false, 'Error fetching consumer details');
        });
}

function deleteConsumer(customerId) {
    fetch(`/consumers/${encodeURIComponent(customerId)}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            closeModal('deleteConfirmationModal');
            showConsumerResultModal(data.success, data.message);
            if (data.success) {
                document.getElementById('consumerResultModal').setAttribute('data-refresh', 'true');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeModal('deleteConfirmationModal');
            showConsumerResultModal(false, 'An error occurred while deleting the consumer.');
        });
}

function showEditConsumerModal(customerId) {
    fetch(`/consumers/${customerId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const consumer = data.consumer;
                document.getElementById('edit_customer_id').value = consumer.customer_id;
                document.getElementById('edit_block_id').value = consumer.block_id;
                document.getElementById('edit_firstname').value = consumer.firstname;
                document.getElementById('edit_middlename').value = consumer.middlename || '';
                document.getElementById('edit_lastname').value = consumer.lastname;

                const addressParts = consumer.address.split(', ');
                document.getElementById('edit_street').value = addressParts[0] || '';
                document.getElementById('edit_barangay').value = addressParts[1] || '';

                document.getElementById('edit_contact_no').value = consumer.contact_no;
                document.getElementById('edit_consumer_type').value = consumer.consumer_type;
                document.getElementById('edit_status').value = consumer.status;

                const statusSelect = document.getElementById('edit_status');
                if (consumer.status === 'Inactive') {
                    statusSelect.innerHTML = `
                        <option value="Inactive" selected>Inactive</option>
                        <option value="Active">Active</option>
                    `;
                    statusSelect.disabled = false;
                } else if (consumer.status === 'Pending') {
                    statusSelect.innerHTML = `
                        <option value="Pending" selected>Pending</option>
                        <option value="Active">Active</option>
                    `;
                    statusSelect.querySelector('option[value="Pending"]').disabled = true;
                } else {
                    statusSelect.innerHTML = `
                        <option value="Active" ${consumer.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Inactive" ${consumer.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                    `;
                    statusSelect.disabled = false;
                }

                const modal = document.getElementById('editConsumerModal');
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);
            } else {
                showConsumerResultModal(false, data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showConsumerResultModal(false, 'Error fetching consumer details');
        });
}

function handleEditConsumerSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const customerId = document.getElementById('edit_customer_id').value;
    const formData = new FormData(form);

    const street = formData.get('street');
    const barangay = formData.get('barangay');
    formData.set('address', `${street}, ${barangay}`);
    formData.delete('street');
    formData.delete('barangay');

    fetch(`/consumers/${customerId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.keepModalOpen) {
                showConsumerResultModal(false, data.message);
            } else {
                closeModal('editConsumerModal');
                showConsumerResultModal(data.success, data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showConsumerResultModal(false, 'Error updating consumer details');
        });
}

function viewConsumer(customerId) {
    fetch(`/consumers/${customerId}/view`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const consumer = data.consumer;
                document.getElementById('edit_customer_id').value = consumer.customer_id;
                document.getElementById('edit_block_id').value = consumer.block_id;
                document.getElementById('edit_firstname').value = consumer.firstname;
                document.getElementById('edit_middlename').value = consumer.middlename || '';
                document.getElementById('edit_lastname').value = consumer.lastname;
                const addressParts = consumer.address.split(', ');
                document.getElementById('edit_street').value = addressParts[0] || '';

                updateEditBarangays();
                setTimeout(() => {
                    const barangaySelect = document.getElementById('edit_barangay');
                    barangaySelect.value = addressParts[1] || '';
                }, 100);

                document.getElementById('edit_contact_no').value = consumer.contact_no;
                document.getElementById('edit_consumer_type').value = consumer.consumer_type;
                document.getElementById('edit_status').value = consumer.status;

                const statusSelect = document.getElementById('edit_status');
                if (consumer.status === 'Pending') {
                    statusSelect.innerHTML = `
                        <option value="Pending" selected>Pending</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    `;
                    statusSelect.querySelector('option[value="Pending"]').disabled = true;
                } else {
                    statusSelect.innerHTML = `
                        <option value="Active" ${consumer.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Inactive" ${consumer.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                    `;
                }

                const modal = document.getElementById('editConsumerModal');
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);
            } else {
                showConsumerResultModal(false, 'Error fetching consumer details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showConsumerResultModal(false, 'Error fetching consumer details');
        });
}

function showReconnectConfirmationModal(customerId) {
    fetch(`/consumers/check-reconnection-payment/${customerId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.isPaid) {
                reconnectConsumer(customerId);
            } else {
                showConsumerResultModal(false, 'Cannot activate consumer. Reconnection fee must be paid first.');
            }
        })
        .catch(error => {
            console.error('Error checking reconnection status:', error);
            showConsumerResultModal(false, 'Error checking reconnection status');
        });
}

function reconnectConsumer(customerId) {
    fetch(`/consumers/${encodeURIComponent(customerId)}/reconnect`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            closeModal('reconnectConfirmationModal');
            showConsumerResultModal(data.success, data.message);
            if (data.success) {
                document.getElementById('consumerResultModal').setAttribute('data-refresh', 'true');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeModal('reconnectConfirmationModal');
            showConsumerResultModal(false, 'An error occurred while reconnecting the consumer.');
        });
}

function viewBillings(customerId) {
    window.location.href = `/consumers/billings/${customerId}`;
}

function updateBarangays() {
    const blockSelect = document.getElementById('block_id');
    const barangaySelect = document.getElementById('barangay');

    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

    if (blockSelect.value) {
        const selectedOption = blockSelect.options[blockSelect.selectedIndex];
        const barangays = JSON.parse(selectedOption.getAttribute('data-barangays'));

        barangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay;
            option.textContent = barangay;
            barangaySelect.appendChild(option);
        });
    }

    barangaySelect.disabled = !blockSelect.value;
}

function updateEditBarangays() {
    const blockSelect = document.getElementById('edit_block_id');
    const barangaySelect = document.getElementById('edit_barangay');

    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

    if (blockSelect.value) {
        const selectedOption = blockSelect.options[blockSelect.selectedIndex];
        const barangays = JSON.parse(selectedOption.getAttribute('data-barangays'));

        barangays.forEach(barangay => {
            const option = document.createElement('option');
            option.value = barangay;
            option.textContent = barangay;
            barangaySelect.appendChild(option);
        });
    }

    barangaySelect.disabled = !blockSelect.value;
}