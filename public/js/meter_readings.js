const MeterReadings = {
    async filter() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const blockValue = document.getElementById('blockFilter').value;
        const currentCoverageId = document.getElementById('currentCoverageId')?.value;
        const tbody = document.querySelector('.uni-table tbody');
        const paginationContainer = document.querySelector('.pagination-container');

        if (!currentCoverageId) {
            console.error('No active coverage period found');
            return;
        }

        if (!searchValue && !blockValue) {
            window.location.reload();
            return;
        }

        try {
            const queryParams = new URLSearchParams();
            if (searchValue) queryParams.append('query', searchValue);
            if (blockValue) queryParams.append('block', blockValue);

            const response = await fetch(`/meter-readings/search?${queryParams.toString()}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();

            if (data.success) {
                tbody.innerHTML = '';

                if (data.readings.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>No readings found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    data.readings.forEach(reading => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>Block ${reading.block_id}</td>
                            <td>${reading.customer_id}</td>
                            <td>${reading.consumer_name}</td>
                            <td>${reading.consumer_type}</td>
                            <td>${reading.previous_reading}</td>
                            <td class="present-reading">${reading.present_reading}</td>
                            <td class="consumption">${reading.consumption}</td>
                            <td>${reading.meter_reader}</td>
                            <td>
                                <button onclick="MeterReadings.showEditModal(${reading.id}, ${reading.present_reading})">Edit</button>
                            </td>
                        `;
                        row.setAttribute('data-reading-id', reading.id);
                        tbody.appendChild(row);
                    });
                }

                if (paginationContainer) {
                    paginationContainer.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Failed to filter readings:', error);
        }
    },

    showEditModal(readingId, presentReading) {
        document.getElementById('editReadingId').value = readingId;
        document.getElementById('editPresentReading').value = presentReading;
        const modal = document.getElementById('editReadingModal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeEditModal() {
        const modal = document.getElementById('editReadingModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            document.getElementById('editReadingForm').reset();
            this.clearValidationErrors();
        }, 300);
    },

    clearValidationErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(element => {
            element.textContent = '';
            element.style.display = 'none';
        });
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
    },

    showResultModal(success, message) {
        const modal = document.getElementById('meterReadingResultModal');
        const icon = document.getElementById('meterReadingResultIcon');
        const title = document.getElementById('meterReadingResultTitle');
        const messageEl = document.getElementById('meterReadingResultMessage');

        icon.className = success ? 'success' : 'error';
        icon.innerHTML = success ?
            '<i class="fas fa-check-circle"></i>' :
            '<i class="fas fa-exclamation-circle"></i>';
        title.textContent = success ? 'Success' : 'Error';
        messageEl.textContent = message;

        modal.setAttribute('data-should-refresh', 'false');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeResultModal() {
        const modal = document.getElementById('meterReadingResultModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            if (modal.getAttribute('data-should-refresh') === 'true') {
                window.location.reload();
            }
        }, 300);
    },

    async updateReading(event) {
        event.preventDefault();
        this.clearValidationErrors();

        const readingId = document.getElementById('editReadingId').value;
        const presentReading = document.getElementById('editPresentReading').value;

        try {
            const response = await fetch(`/meter-readings/${readingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ present_reading: presentReading })
            });

            const data = await response.json();

            if (data.success) {
                this.closeEditModal();
                this.showResultModal(true, 'Meter reading updated successfully');
            } else if (response.status === 422) {
                const input = document.getElementById('editPresentReading');
                input.classList.add('is-invalid');
                const feedback = input.nextElementSibling;
                feedback.textContent = data.errors.present_reading[0];
                feedback.style.display = 'block';
            } else {
                throw new Error(data.message || 'Failed to update reading');
            }
        } catch (error) {
            console.error('Error updating reading:', error);
            this.showResultModal(false, error.message || 'Failed to update meter reading');
        }
    },

    async showAddReadingModal() {
        const modal = document.getElementById('addReadingModal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
        await this.filterConsumers();
    },

    async filterConsumers() {
        const blockFilter = document.getElementById('modalBlockFilter').value;
        const searchInput = document.getElementById('modalSearchInput').value;
        const tableBody = document.getElementById('consumerTableBody');

        try {
            const response = await fetch(`/meter-readings/consumers?block=${blockFilter}&search=${searchInput}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();

            if (data.success) {
                tableBody.innerHTML = '';

                if (data.consumers.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>No consumers found</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                data.consumers.forEach(consumer => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${consumer.customer_id}</td>
                        <td>${consumer.consumer_name}</td>
                        <td>${consumer.consumer_type}</td>
                        <td>${consumer.previous_reading}</td>
                        <td>
                            <input type="number" 
                                class="form-control present-reading-input" 
                                value="${consumer.present_reading || ''}"
                                min="${consumer.previous_reading}"
                                ${consumer.has_reading ? 'readonly' : ''}
                                data-customer-id="${consumer.customer_id}"
                                style="width: 100px; margin: 0 auto;">
                        </td>
                        <td>
                            ${consumer.has_reading ?
                            '<span class="status-badge status-active">Reading Recorded</span>' :
                            '<button class="btn_uni btn-view" onclick="MeterReadings.saveReading(\'' + consumer.customer_id + '\')">Save</button>'}
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }
        } catch (error) {
            console.error('Error fetching consumers:', error);
        }
    },

    async saveReading(customerId) {
        const input = document.querySelector(`input[data-customer-id="${customerId}"]`);
        const presentReading = input.value;

        if (!presentReading) {
            this.showResultModal(false, 'Please enter a present reading');
            return;
        }

        const currentCoverageId = document.getElementById('currentCoverageId').value;

        try {
            const response = await fetch('/meter-readings/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    customer_id: customerId,
                    covdate_id: currentCoverageId,
                    present_reading: presentReading,
                })
            });

            const data = await response.json();

            if (data.success) {
                input.readOnly = true;
                const actionCell = input.closest('tr').querySelector('td:last-child');
                actionCell.innerHTML = '<span class="status-badge status-active">Reading Recorded</span>';

                const blockFilter = document.getElementById('blockFilter').value;
                const searchInput = document.getElementById('searchInput').value;
                const queryParams = new URLSearchParams();
                if (searchInput) queryParams.append('query', searchInput);
                if (blockFilter) queryParams.append('block', blockFilter);

                const mainTableResponse = await fetch(`/meter-readings/search?${queryParams.toString()}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });
                const mainTableData = await mainTableResponse.json();

                if (mainTableData.success) {
                    const mainTableBody = document.querySelector('.content-wrapper .uni-table tbody');
                    if (mainTableData.readings.length === 0) {
                        mainTableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <p>No readings found</p>
                                </td>
                            </tr>
                        `;
                    } else {
                        const updatedRows = mainTableData.readings.map(reading => `
                            <tr>
                                <td>${reading.customer_id}</td>
                                <td>${reading.consumer_name}</td>
                                <td>${reading.consumer_type}</td>
                                <td>${reading.previous_reading}</td>
                                <td>${reading.present_reading}</td>
                                <td>${reading.consumption}</td>
                                <td>${reading.meter_reader}</td>
                            </tr>
                        `).join('');
                        mainTableBody.innerHTML = updatedRows;
                    }
                }

                this.showResultModal(true, 'Reading saved successfully');
            } else {
                this.showResultModal(false, data.message || 'Failed to save reading');
            }
        } catch (error) {
            console.error('Error saving reading:', error);
            this.showResultModal(false, 'Failed to save reading');
        }
    },

    async saveAllReadings() {
        const inputs = document.querySelectorAll('.present-reading-input:not([readonly])');
        const currentCoverageId = document.getElementById('currentCoverageId').value;
        let successCount = 0;
        let errorCount = 0;

        for (const input of inputs) {
            if (!input.value) continue;

            try {
                const response = await fetch('/meter-readings/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        customer_id: input.dataset.customerId,
                        covdate_id: currentCoverageId,
                        present_reading: input.value,
                    })
                });

                const data = await response.json();

                if (data.success) {
                    successCount++;
                    input.readOnly = true;
                    const actionCell = input.closest('tr').querySelector('td:last-child');
                    actionCell.innerHTML = '<span class="status-badge status-active">Reading Recorded</span>';
                } else {
                    errorCount++;
                }
            } catch (error) {
                console.error('Error saving reading:', error);
                errorCount++;
            }
        }

        this.showResultModal(
            errorCount === 0,
            `${successCount} readings saved successfully${errorCount > 0 ? `, ${errorCount} failed` : ''}`
        );

        const modal = document.getElementById('meterReadingResultModal');
        modal.setAttribute('data-should-refresh', 'true');
    },

    closeAddReadingModal() {
        const modal = document.getElementById('addReadingModal');
        if (modal) {
            modal.classList.remove('fade-in');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    },

    addReading(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('addReadingForm'));

        fetch('/meter-readings', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closeAddReadingModal();
                    this.showResultModal(true, 'Meter reading added successfully');
                } else {
                    console.error('Error adding reading:', data);
                }
            })
            .catch(error => console.error('Error:', error));
    }
};

window.addEventListener('click', function (event) {
    if (event.target.classList.contains('modal')) {
        if (event.target.id === 'meterReadingResultModal') {
            const modal = event.target;
            if (modal.getAttribute('data-should-refresh') === 'true') {
                MeterReadings.filter();
            }
            MeterReadings.closeResultModal();
        } else if (event.target.id === 'editReadingModal') {
            MeterReadings.closeEditModal();
        }
    }
});
