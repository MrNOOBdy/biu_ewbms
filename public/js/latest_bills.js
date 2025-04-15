function showAddBillModal(consreadId) {
    fetch(`/billing/get-reading-details/${consreadId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('consread_id').value = consreadId;

            // Consumer Info
            document.getElementById('customerId').textContent = data.consumer.customer_id;
            document.getElementById('consumerName').textContent = `${data.consumer.firstname} ${data.consumer.lastname}`;
            document.getElementById('contactNo').textContent = data.consumer.contact_no;
            document.getElementById('consumerType').textContent = data.consumer.consumer_type;
            document.getElementById('prevBillStatus').textContent = data.consumer.previous_bill_status || 'No previous bill';

            // Coverage Dates
            document.getElementById('coverageDateFrom').textContent = data.coverage_date ? formatDate(data.coverage_date.coverage_date_from) : 'Not set';
            document.getElementById('coverageDateTo').textContent = data.coverage_date ? formatDate(data.coverage_date.coverage_date_to) : 'Not set';

            // Bill Details
            document.getElementById('readingDate').textContent = formatDate(data.reading_date);
            document.getElementById('dueDate').textContent = formatDate(data.due_date);
            document.getElementById('previousReading').textContent = data.previous_reading;
            document.getElementById('presentReading').textContent = data.present_reading;
            document.getElementById('consumption').textContent = data.consumption;
            document.getElementById('totalAmount').textContent = Number(data.total_amount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            const modal = document.getElementById('addBillModal');
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('fade-in'), 10);
        })
        .catch(error => {
            console.error('Error:', error);
            showBillResultModal(false, 'Error fetching reading details');
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

            document.getElementById('send_consread_id').value = consreadId;
            document.getElementById('sms_consumerName').textContent = `${data.consumer.firstname} ${data.consumer.lastname}`;
            document.getElementById('sms_contactNo').textContent = data.consumer.contact_no;
            document.getElementById('sms_presentReading').textContent = data.present_reading;
            document.getElementById('sms_consumption').textContent = data.consumption;

            const message = `Dear ${data.consumer.firstname},

Your water bill details:
Coverage Date From: ${formatDate(data.coverage_date.coverage_date_from)}
Coverage Date To: ${formatDate(data.coverage_date.coverage_date_to)}

Previous Reading: ${data.previous_reading}
Present Reading: ${data.present_reading}
Consumed: ${data.consumption} m³
Amount Due: ₱${Number(data.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
Due Date: ${formatDate(data.due_date)}
Meter Reader: ${data.meter_reader}

Reminder: 
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
                                    <button class="btn_uni btn-billing" onclick="sendBill(${bill.consread_id})">
                                        <i class="fas fa-paper-plane"></i> Send Bill SMS
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

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
};
