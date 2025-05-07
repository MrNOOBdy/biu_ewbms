function showAddBillModal(consreadId) {
    fetch(`/billing/get-reading-details/${consreadId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('consread_id').value = consreadId;

            const customerId = data.consumer.customer_id;
            document.getElementById('customerId').textContent = customerId;

            const blockMatch = customerId.match(/B(\d+)/);
            const blockNumber = blockMatch ? `Block ${parseInt(blockMatch[1], 10)}` : 'N/A';
            document.getElementById('consumerBlock').textContent = blockNumber;

            document.getElementById('consumerFirstName').textContent = data.consumer.firstname;
            document.getElementById('consumerLastName').textContent = data.consumer.lastname;

            document.getElementById('coverageDateFrom').textContent = formatDate(data.coverage_date.coverage_date_from);
            document.getElementById('coverageDateTo').textContent = formatDate(data.coverage_date.coverage_date_to);
            document.getElementById('readingDate').textContent = formatDate(data.reading_date);
            document.getElementById('dueDate').textContent = formatDate(data.due_date);

            const consumption = data.consumption;
            const baseRate = 10;
            const excess = consumption > baseRate ? consumption - baseRate : 0;

            document.getElementById('previousReading').textContent = data.previous_reading;
            document.getElementById('presentReading').textContent = data.present_reading;
            document.getElementById('consumption').textContent = consumption;
            document.getElementById('baseRateValue').textContent = baseRate;
            document.getElementById('excessValue').textContent = excess;

            document.getElementById('currentBillAmount').textContent = formatAmount(data.current_bill_amount);

            const lastMonthSection = document.getElementById('lastMonthUnpaidSection');
            if (data.last_month_unpaid) {
                document.getElementById('lastMonthAmount').textContent = formatAmount(data.last_month_unpaid.total_amount);
                document.getElementById('lastMonthPenalty').textContent = formatAmount(data.penalty_amount);
                lastMonthSection.style.display = 'block';
            } else {
                lastMonthSection.style.display = 'none';
            }

            document.getElementById('totalAmount').textContent = formatAmount(data.total_amount);

            const modal = document.getElementById('addBillModal');
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('fade-in'), 10);
        })
        .catch(error => {
            console.error('Error:', error);
            showBillResultModal(false, 'Error fetching reading details');
        });
}

function formatAmount(amount) {
    return Number(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function closeModal(modalId) {
    if (!modalId) return;

    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

function addBill() {
    const consreadId = document.getElementById('consread_id').value;
    const totalAmount = document.getElementById('totalAmount').textContent;

    fetch('/billing/add-bill', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            consread_id: consreadId,
            total_amount: totalAmount
        })
    })
        .then(response => response.json())
        .then(data => {
            closeModal('addBillModal');
            showBillResultModal(data.success, data.message || 'Bill added successfully');
            if (data.success) {
                setTimeout(() => window.location.reload(), 1500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showBillResultModal(false, 'Error adding bill');
        });
}

function showBillResultModal(success, message) {
    const modal = document.getElementById('billResultModal');
    const icon = document.getElementById('billResultIcon');
    const title = document.getElementById('billResultTitle');
    const messageEl = document.getElementById('billResultMessage');

    icon.className = success ? 'success' : 'error';
    icon.innerHTML = success ?
        '<i class="fas fa-check-circle"></i>' :
        '<i class="fas fa-exclamation-triangle"></i>';
    title.textContent = success ? 'Success' : 'Error';
    messageEl.textContent = message;

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);

    if (success) {
        modal.setAttribute('data-refresh', 'true');
    }
}

function closeBillResultModal() {
    const modal = document.getElementById('billResultModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.getAttribute('data-refresh') === 'true') {
            location.reload();
        }
    }, 300);
}

function sendBill(consreadId) {
    fetch(`/billing/get-reading-details/${consreadId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.consumer) {
                showBillResultModal(false, 'Consumer details not found');
                return;
            }

            if (!data.consumer.contact_no) {
                showBillResultModal(false, 'Consumer has no contact number');
                return;
            }

            if (data.sms_sent) {
                showBillResultModal(false, 'SMS notification has already been sent for this bill');
                return;
            }

            document.getElementById('send_consread_id').value = consreadId;
            document.getElementById('sms_consumerName').textContent = `${data.consumer.firstname} ${data.consumer.lastname}`;
            document.getElementById('sms_contactNo').textContent = data.consumer.contact_no;
            document.getElementById('sms_presentReading').textContent = data.present_reading;
            document.getElementById('sms_consumption').textContent = data.consumption;

            let message = `Dear ${data.consumer.firstname},

Your water bill details:
Coverage Date From: ${formatDate(data.coverage_date.coverage_date_from)}
Coverage Date To: ${formatDate(data.coverage_date.coverage_date_to)}

Present Reading: ${data.present_reading}
Previous Reading: ${data.previous_reading}
Consumed: ${data.consumption} m³
Current Bill Amount: ₱${Number(data.current_bill_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

            if (data.last_month_unpaid) {
                message += `\n\nIMPORTANT: You have an unpaid bill from last month:
Reading Date: ${formatDate(data.last_month_unpaid.reading_date)}
Amount Due: ₱${Number(data.last_month_unpaid.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}
Penalty: ₱${Number(data.penalty_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
            }

            message += `\n\nTotal Amount Due: ₱${Number(data.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

            message += `\n\nReminder: 
Please pay your bill on or before due date to avoid penalty. 5 working days to settle your due/delinquent after due date to avoid water service cut-off.

Thank you,
BI-U: eWBS`;

            document.getElementById('sms_message').value = message;

            const modal = document.getElementById('sendBillModal');
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('fade-in'), 10);
        })
        .catch(error => {
            console.error('Error:', error);
            showBillResultModal(false, 'Error fetching bill details');
        });
}

function confirmSendBill() {
    const consreadId = document.getElementById('send_consread_id').value;
    const message = document.getElementById('sms_message').value;
    const sendButton = document.querySelector(`button[onclick="sendBill(${consreadId})"]`);

    if (!consreadId || !message) {
        showBillResultModal(false, 'Missing required information');
        return;
    }

    fetch('/billing/send-bill-sms', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            consread_id: consreadId,
            message: message
        })
    })
        .then(response => response.json())
        .then(data => {
            closeModal('sendBillModal');
            showBillResultModal(data.success, data.message);

            if (data.success && sendButton) {
                sendButton.disabled = true;
                sendButton.title = 'SMS already sent';
                sendButton.innerHTML = '<i class="fas fa-paper-plane"></i> SMS Sent';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showBillResultModal(false, 'Error sending bill notification');
        });
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
}

async function filterBills() {
    const searchValue = document.getElementById('searchInput').value.trim();
    const statusValue = document.getElementById('statusFilter').value;
    const tbody = document.querySelector('.uni-table tbody');
    const paginationContainer = document.querySelector('.pagination-wrapper');

    try {
        const response = await fetch(`/billing/search?query=${encodeURIComponent(searchValue)}&status=${encodeURIComponent(statusValue)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await response.json();

        if (data.success) {
            tbody.innerHTML = '';

            if (data.bills.length === 0) {
                showEmptyState(tbody);
            } else {
                data.bills.forEach(bill => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${bill.customer_id}</td>
                        <td>${bill.contact_no}</td>
                        <td>${bill.consumer_name}</td>
                        <td>${bill.reading_date}</td>
                        <td>${bill.due_date}</td>
                        <td>${bill.previous_reading}</td>
                        <td>${bill.present_reading}</td>
                        <td>${bill.consumption}</td>
                        <td>₱${bill.total_amount}</td>
                        <td>
                            <span class="status-badge ${bill.bill_status === 'Pending' ? 'status-pending' :
                            (bill.bill_status === 'paid' ? 'status-active' : 'status-inactive')}">
                                ${bill.bill_status}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                ${bill.bill_status === 'Pending' ? `
                                    <button class="btn_uni btn-view" onclick="showAddBillModal(${bill.consread_id})">
                                        <i class="fas fa-plus-circle"></i> Add Bill
                                    </button>
                                ` : `
                                    <button class="btn_uni btn-billing ${bill.sms_sent ? 'disabled' : ''}" 
                                            onclick="sendBill(${bill.consread_id})"
                                            ${bill.sms_sent ? 'disabled title="SMS already sent"' : ''}>
                                        <i class="fas fa-paper-plane"></i> ${bill.sms_sent ? 'SMS Sent' : 'Send Bill SMS'}
                                    </button>
                                `}
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            if (paginationContainer) {
                paginationContainer.style.display = searchValue || statusValue ? 'none' : 'flex';
            }
        } else {
            showBillResultModal(false, data.message);
        }
    } catch (error) {
        console.error('Failed to filter bills:', error);
        showBillResultModal(false, 'Failed to filter bills');
    }
}

function showEmptyState(tbody) {
    const emptyRow = document.createElement('tr');
    emptyRow.classList.add('empty-state-row');
    const colspan = document.querySelector('.uni-table thead th:last-child').cellIndex + 1;
    emptyRow.innerHTML = `
        <td colspan="${colspan}" class="empty-state">
            <i class="fas fa-search"></i>
            <p>No bills found for your search</p>
        </td>`;
    tbody.appendChild(emptyRow);
}

function editReading(readingId, presentReading) {
    document.getElementById('editReadingId').value = readingId;
    document.getElementById('editPresentReading').value = presentReading;
    const modal = document.getElementById('editReadingModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

async function updateReading(event) {
    event.preventDefault();
    const readingId = document.getElementById('editReadingId').value;
    const presentReading = document.getElementById('editPresentReading').value;

    try {
        const response = await fetch(`/billing/update-reading/${readingId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ present_reading: presentReading })
        });

        const data = await response.json();

        if (data.success) {
            const row = document.querySelector(`tr[data-reading-id="${readingId}"]`);
            if (row) {
                row.querySelector('.present-reading').textContent = data.reading.present_reading;
                row.querySelector('.consumption').textContent = data.reading.consumption;
                row.querySelector('.bill-amount').textContent = `₱${Number(data.reading.current_bill_amount).toFixed(2)}`;
            }

            closeModal('editReadingModal');
            showBillResultModal(true, 'Reading updated successfully');
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
        console.error('Error:', error);
        showBillResultModal(false, error.message || 'Failed to update reading');
    }
}

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
};
