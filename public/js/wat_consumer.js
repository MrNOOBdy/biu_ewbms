function showAddConsumerModal() {
    const modal = document.getElementById('addConsumerModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);

    const barangaySelect = document.getElementById('barangay');
    const blockSelect = document.getElementById('block_id');

    barangaySelect.value = '';
    blockSelect.value = '';
    blockSelect.disabled = true;
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

    // Validate barangay is selected
    if (!barangay) {
        showConsumerResultModal(false, 'Please select a barangay.');
        return;
    }

    // Get block_id from the disabled select
    const blockSelect = document.getElementById('block_id');
    const blockId = blockSelect.value;

    // Validate block is set
    if (!blockId) {
        showConsumerResultModal(false, 'No block found for the selected barangay.');
        return;
    }

    formData.set('address', `${street}, ${barangay}`);
    formData.delete('street');
    formData.delete('barangay');
    formData.set('block_id', blockId);

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
                // Handle validation errors except contact number
                Object.keys(data.errors).forEach(key => {
                    if (key !== 'contact_no') {
                        const input = document.getElementById(key);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = data.errors[key][0];
                            }
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

async function filterConsumers() {
    const searchValue = document.getElementById('searchInput').value.trim();
    const blockValue = document.getElementById('blockFilter').value;
    const statusValue = document.getElementById('statusFilter').value;

    if (!blockValue && !searchValue && !statusValue) {
        window.location.reload();
        return;
    }

    const tbody = document.querySelector('.uni-table tbody');
    const paginationWrapper = document.querySelector('.pagination-wrapper');

    try {
        const response = await fetch(`/consumers/filter?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}&status=${encodeURIComponent(statusValue)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await response.json();

        if (data.success) {
            tbody.innerHTML = '';

            if (data.consumers.length === 0) {
                const colspan = document.querySelector('.uni-table thead tr').children.length;
                tbody.innerHTML = `
                    <tr>
                        <td colspan="${colspan}" class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <p>No consumers found</p>
                        </td>
                    </tr>
                `;
            } else {
                data.consumers.forEach(consumer => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>Block ${consumer.block_id}</td>
                        <td>${consumer.customer_id}</td>
                        <td>${consumer.firstname}</td>
                        <td>${consumer.middlename || ''}</td>
                        <td>${consumer.lastname}</td>
                        <td>${consumer.address}</td>
                        <td>${consumer.contact_no}</td>
                        <td>${consumer.consumer_type}</td>
                        <td>
                            <span class="status-badge ${consumer.status === 'Active' ? 'status-active' :
                            (consumer.status === 'Inactive' ? 'status-inactive' : 'status-pending')
                        }">
                                ${consumer.status}
                            </span>
                        </td>
                        ${(consumer.canEdit || consumer.canViewBillings || consumer.canDelete || consumer.canReconnect) ? `
                        <td>
                            <div class="action-buttons">
                                ${consumer.canEdit ? `
                                    <button class="btn_uni btn-view" title="View/Edit Consumer" onclick="viewConsumer('${consumer.customer_id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                ` : ''}

                                ${consumer.status === 'Active' && consumer.canViewBillings ? `
                                    <button class="btn_uni btn-billing" title="View Billings" onclick="viewBillings('${consumer.customer_id}')">
                                        <i class="fas fa-file-invoice"></i>
                                    </button>
                                ` : ''}

                                ${consumer.status === 'Inactive' && consumer.canReconnect ? `
                                    <button class="btn_uni btn-activate" title="Reconnect Consumer" onclick="showReconnectConfirmationModal('${consumer.customer_id}')">
                                        <i class="fas fa-plug"></i>
                                    </button>
                                ` : ''}

                                ${(consumer.status === 'Pending' || consumer.status === 'Inactive') && consumer.canDelete ? `
                                    <button class="btn_uni btn-deactivate" title="Delete Consumer" onclick="showDeleteConfirmationModal('${consumer.customer_id}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>` : ''}
                    `;
                    tbody.appendChild(row);
                });
            }

            if (paginationWrapper) {
                paginationWrapper.style.display = searchValue || blockValue || statusValue ? 'none' : 'flex';
            }
        } else {
            showConsumerResultModal(false, data.message);
        }
    } catch (error) {
        console.error('Failed to filter consumers:', error);
        showConsumerResultModal(false, 'Failed to filter consumers');
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

    // Validate barangay is selected
    if (!barangay) {
        showConsumerResultModal(false, 'Please select a barangay.');
        return;
    }

    // Get block_id from the disabled select
    const blockSelect = document.getElementById('edit_block_id');
    const blockId = blockSelect.value;

    // Validate block is set
    if (!blockId) {
        showConsumerResultModal(false, 'No block found for the selected barangay.');
        return;
    }

    formData.set('address', `${street}, ${barangay}`);
    formData.delete('street');
    formData.delete('barangay');

    // Ensure block_id is included in form data even though select is disabled
    formData.set('block_id', blockId);

    // Clear previous validation errors
    form.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });

    fetch(`/consumers/${customerId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.errors) {
                // Handle other validation errors except contact number
                Object.keys(data.errors).forEach(key => {
                    if (key !== 'contact_no') {
                        const input = document.getElementById('edit_' + key);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = data.errors[key][0];
                            }
                        }
                    }
                });
            } else if (data.keepModalOpen) {
                showConsumerResultModal(false, data.message);
            } else {
                closeModal('editConsumerModal');
                showConsumerResultModal(data.success, data.message);
                if (data.success) {
                    document.getElementById('consumerResultModal').setAttribute('data-refresh', 'true');
                }
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

                document.querySelectorAll('#editConsumerForm .is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });
                document.querySelectorAll('#editConsumerForm .invalid-feedback').forEach(element => {
                    element.textContent = '';
                });

                document.getElementById('edit_customer_id').value = consumer.customer_id;
                document.getElementById('edit_firstname').value = consumer.firstname;
                document.getElementById('edit_middlename').value = consumer.middlename || '';
                document.getElementById('edit_lastname').value = consumer.lastname;

                const addressParts = consumer.address.split(', ');
                document.getElementById('edit_street').value = addressParts[0] || '';

                const barangaySelect = document.getElementById('edit_barangay');
                const blockSelect = document.getElementById('edit_block_id');

                barangaySelect.value = addressParts[1] || '';

                if (addressParts[1]) {
                    Array.from(blockSelect.options).forEach(option => {
                        if (option.value) {
                            const barangays = JSON.parse(option.getAttribute('data-barangays'));
                            if (barangays.includes(addressParts[1])) {
                                blockSelect.value = option.value;
                                blockSelect.disabled = true;
                            }
                        }
                    });
                }

                document.getElementById('edit_contact_no').value = consumer.contact_no;
                document.getElementById('edit_consumer_type').value = consumer.consumer_type;
                document.getElementById('edit_status').value = consumer.status;

                const statusSelect = document.getElementById('edit_status');
                if (consumer.status === 'Pending') {
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

function updateBlockForBarangay() {
    const barangaySelect = document.getElementById('barangay');
    const blockSelect = document.getElementById('block_id');
    const selectedBarangay = barangaySelect.value;

    blockSelect.disabled = !selectedBarangay;
    blockSelect.value = '';

    if (selectedBarangay) {
        const blockOptions = Array.from(blockSelect.options);
        let foundBlock = false;

        blockOptions.forEach(option => {
            if (option.value) {
                const barangays = JSON.parse(option.getAttribute('data-barangays'));
                if (barangays.includes(selectedBarangay)) {
                    blockSelect.value = option.value;
                    foundBlock = true;
                    generateConsumerId(option.value);
                }
            }
        });

        if (foundBlock) {
            blockSelect.disabled = true;
        } else {
            blockSelect.disabled = false;
            showConsumerResultModal(false, 'No block found for the selected barangay. Please contact an administrator.');
        }
    }
}

function generateConsumerId(blockId) {
    if (!blockId) return;

    fetch(`/consumers/generate-id/${blockId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Generated ID:', data.consumer_id);
        })
        .catch(error => {
            console.error('Error generating consumer ID:', error);
        });
}

function updateBlockForEditBarangay() {
    const barangaySelect = document.getElementById('edit_barangay');
    const blockSelect = document.getElementById('edit_block_id');
    const selectedBarangay = barangaySelect.value;

    blockSelect.disabled = !selectedBarangay;
    blockSelect.value = '';

    if (selectedBarangay) {
        const blockOptions = Array.from(blockSelect.options);
        let foundBlock = false;

        blockOptions.forEach(option => {
            if (option.value) {
                const barangays = JSON.parse(option.getAttribute('data-barangays'));
                if (barangays.includes(selectedBarangay)) {
                    blockSelect.value = option.value;
                    foundBlock = true;
                }
            }
        });

        if (foundBlock) {
            blockSelect.disabled = true;
        } else {
            blockSelect.disabled = false;
            showConsumerResultModal(false, 'No block found for the selected barangay. Please contact an administrator.');
        }
    }
}