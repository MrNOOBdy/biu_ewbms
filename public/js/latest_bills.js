function showAddBillModal(consreadId) {
    fetch(`/billing/get-reading-details/${consreadId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('consread_id').value = consreadId;

            document.getElementById('customerId').textContent = data.consumer.customer_id;
            document.getElementById('consumerName').textContent = `${data.consumer.firstname} ${data.consumer.lastname}`;
            document.getElementById('contactNo').textContent = data.consumer.contact_no;
            document.getElementById('consumerType').textContent = data.consumer.consumer_type;

            const prevBillStatus = data.consumer.previous_bill_status || 'No previous bill';
            document.getElementById('prevBillStatus').textContent = prevBillStatus;

            const coverageDateFrom = data.coverage_date ? formatDate(data.coverage_date.coverage_date_from) : 'Not set';
            const coverageDateTo = data.coverage_date ? formatDate(data.coverage_date.coverage_date_to) : 'Not set';

            document.getElementById('coverageDateFrom').textContent = coverageDateFrom;
            document.getElementById('coverageDateTo').textContent = coverageDateTo;

            document.getElementById('readingDate').textContent = formatDate(data.reading_date);
            document.getElementById('dueDate').textContent = formatDate(data.due_date);
            document.getElementById('previousReading').textContent = data.previous_reading;
            document.getElementById('presentReading').textContent = data.present_reading;
            document.getElementById('consumption').textContent = data.consumption;
            document.getElementById('totalAmount').textContent = data.present_reading;

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
Amount Due: ₱${data.present_reading}
Due Date: ${formatDate(data.due_date)}
Meter Reader: ${data.meter_reader}

Reminder: 
Please pay your bill on or before due date to avoid penalty. 5 working day to settle your due/delinquent after due date to avoid water service cut-off.

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

function filterBills() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
    const statusValue = document.getElementById('statusFilter').value;
    const tbody = document.querySelector('.uni-table tbody');
    const paginationWrapper = document.querySelector('.pagination-wrapper');

    if (paginationWrapper) {
        paginationWrapper.style.display = 'none';
    }

    const rows = tbody.querySelectorAll('tr');
    let hasMatches = false;

    rows.forEach(row => {
        if (row.classList.contains('empty-state-row')) {
            row.remove();
            return;
        }

        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const consumerId = cells[0].textContent.toLowerCase();
            const contactNo = cells[1].textContent.toLowerCase();
            const name = cells[2].textContent.toLowerCase();
            const readingDate = cells[3].textContent.toLowerCase();
            const statusBadge = row.querySelector('.status-badge');
            const status = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';

            if (status === 'paid') {
                row.style.display = 'none';
                return;
            }

            const matchesSearch = !searchValue ||
                consumerId.includes(searchValue) ||
                contactNo.includes(searchValue) ||
                name.includes(searchValue) ||
                readingDate.includes(searchValue);

            const matchesStatus = !statusValue ||
                (statusValue === 'Pending' && status === 'pending') ||
                (statusValue === 'unpaid' && status === 'unpaid');

            const matches = matchesSearch && (matchesStatus || !statusValue);
            row.style.display = matches ? '' : 'none';
            if (matches) hasMatches = true;
        }
    });

    if (!hasMatches) {
        showEmptyState(tbody);
    }

    if (!searchValue && !statusValue && paginationWrapper) {
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
            <p>No bills found for your search</p>
        </td>`;
    tbody.appendChild(emptyRow);
}

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
};
