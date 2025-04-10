async function filterServices() {
    const searchValue = document.getElementById('searchInput').value.trim();
    const blockValue = document.getElementById('blockFilter').value;
    const statusValue = document.getElementById('statusFilter').value;
    const tbody = document.querySelector('.uni-table tbody');
    const paginationContainer = document.querySelector('.pagination-wrapper');

    try {
        const response = await fetch(`/service/search?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}&status=${encodeURIComponent(statusValue)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await response.json();

        if (data.success) {
            tbody.innerHTML = '';

            if (data.payments.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i class="fas fa-tools"></i>
                            <p>No reconnection payments found</p>
                        </td>
                    </tr>
                `;
            } else {
                data.payments.forEach(payment => {
                    const hasPermissions = document.querySelector('th:last-child') !== null;
                    const row = document.createElement('tr');
                    row.dataset.customerId = payment.customer_id;
                    row.classList.toggle('unpaid-row', payment.status === 'unpaid');

                    row.innerHTML = `
                        <td>Block ${payment.block_id}</td>
                        <td>${payment.customer_id}</td>
                        <td>${payment.firstname}</td>
                        <td>${payment.middlename}</td>
                        <td>${payment.lastname}</td>
                        <td>₱${payment.reconnection_fee}</td>
                        <td>₱${payment.amount_paid}</td>
                        <td>
                            <span class="status-badge ${payment.status === 'paid' ? 'status-active' : 'status-inactive'}">
                                ${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                            </span>
                        </td>
                        ${hasPermissions ? `
                        <td>
                            <div class="action-buttons">
                                ${payment.status === 'unpaid' && payment.raw_fee > 0 ? `
                                    <button class="btn_uni btn-activate" title="Pay Reconnection Fee" onclick="showServicePaymentModal('${payment.customer_id}')">
                                        <i class="fas fa-money-bill-wave"></i>Pay
                                    </button>
                                ` : payment.status === 'paid' ? `
                                    <button class="btn_uni btn-billing" title="Print Receipt" onclick="printServiceReceipt('${payment.customer_id}')">
                                        <i class="fas fa-print"></i>Print
                                    </button>
                                ` : ''}
                            </div>
                        </td>` : ''}
                    `;
                    tbody.appendChild(row);
                });
            }

            if (paginationContainer) {
                paginationContainer.style.display = searchValue || blockValue || statusValue ? 'none' : 'flex';
            }
        } else {
            showServiceResultModal(false, data.message);
        }
    } catch (error) {
        console.error('Failed to filter services:', error);
        showServiceResultModal(false, 'Failed to filter services');
    }
}

function showEmptyState(tbody) {
    const emptyRow = document.createElement('tr');
    emptyRow.classList.add('empty-state-row');
    const colspan = document.querySelector('.uni-table thead th:last-child').cellIndex + 1;
    emptyRow.innerHTML = `
        <td colspan="${colspan}" class="empty-state">
            <i class="fas fa-tools"></i>
            <p>No reconnection payments found for your search</p>
        </td>`;
    tbody.appendChild(emptyRow);
}

function showServicePaymentModal(customerId) {
    const row = document.querySelector(`tr[data-customer-id="${customerId}"]`);
    const reconnectionFee = parseFloat(row.querySelector('td:nth-child(6)').textContent.replace('₱', '').replace(',', '').trim());

    document.getElementById('servicePaymentCustomerId').value = customerId;
    document.getElementById('reconnectionFee').value = reconnectionFee.toFixed(2);
    document.getElementById('serviceAmountTendered').value = '';

    const modal = document.getElementById('servicePaymentModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeServiceResultModal() {
    const modal = document.getElementById('serviceResultModal');
    modal.classList.remove('fade-in');

    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.getAttribute('data-refresh') === 'true') {
            window.location.reload();
        }
    }, 300);
}

function handleServicePayment(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const amountTendered = parseFloat(formData.get('amount_tendered'));
    const reconnectionFee = parseFloat(document.getElementById('reconnectionFee').value);
    const input = document.getElementById('serviceAmountTendered');
    const feedback = input.nextElementSibling;

    input.classList.remove('is-invalid');
    if (feedback) feedback.textContent = '';

    if (!amountTendered || isNaN(amountTendered)) {
        input.classList.add('is-invalid');
        if (feedback) feedback.textContent = 'Please enter a valid amount';
        return false;
    }

    if (amountTendered !== reconnectionFee) {
        input.classList.add('is-invalid');
        if (feedback) {
            if (amountTendered < reconnectionFee) {
                feedback.textContent = `Amount tendered is insufficient. Required amount is ₱${reconnectionFee.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            } else {
                feedback.textContent = `Amount tendered is too high. Required amount is ₱${reconnectionFee.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }
        }
        return false;
    }

    fetch('/service/process-payment', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            customer_id: formData.get('customer_id'),
            amount_tendered: amountTendered
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.errors) {
                input.classList.add('is-invalid');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.textContent = data.errors.amount_tendered[0];
                }
            } else if (data.success) {
                closeModal('servicePaymentModal');
                showServiceResultModal(true, data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showServiceResultModal(false, 'An error occurred while processing the payment');
        });
}

function updateTableRow(customerId, amountPaid) {
    const row = document.querySelector(`tr[data-customer-id="${customerId}"]`);
    if (row) {
        row.querySelector('td:nth-child(7)').textContent = `₱${parseFloat(amountPaid).toFixed(2)}`;
        const statusBadge = row.querySelector('.status-badge');
        statusBadge.textContent = 'Paid';
        statusBadge.classList.remove('status-inactive');
        statusBadge.classList.add('status-active');

        const actionCell = row.querySelector('td:last-child');
        if (actionCell) {
            const actionButtons = actionCell.querySelector('.action-buttons');
            if (actionButtons) {
                actionButtons.innerHTML = `
                    <button class="btn_uni btn-billing" title="Print Receipt" onclick="printServiceReceipt('${customerId}')">
                        <i class="fas fa-print"></i>Print
                    </button>
                `;
            }
        }
    }
}

document.getElementById('servicePaymentForm').addEventListener('submit', handleServicePayment);

function showServiceResultModal(success, message) {
    const modal = document.getElementById('serviceResultModal');
    const icon = document.getElementById('serviceResultIcon');
    const title = document.getElementById('serviceResultTitle');
    const messageEl = document.getElementById('serviceResultMessage');

    icon.innerHTML = success ?
        '<i class="fas fa-check-circle"></i>' :
        '<i class="fas fa-exclamation-triangle"></i>';
    icon.className = success ? 'success' : 'error';
    title.textContent = success ? 'Success' : 'Error';
    messageEl.textContent = message;

    modal.setAttribute('data-refresh', success ? 'true' : 'false');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modalId === 'servicePaymentModal') {
            document.getElementById('serviceAmountTendered').value = '';
            document.getElementById('serviceAmountTendered').classList.remove('is-invalid');
        }
    }, 300);
}

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        if (event.target.id === 'serviceResultModal') {
            closeServiceResultModal();
        } else {
            closeModal(event.target.id);
        }
    }
};

function printServiceReceipt(customerId) {
    const printWindow = window.open(`/service/print-receipt/${customerId}`, '_blank');
    printWindow.addEventListener('load', () => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => {
            printWindow.printServiceReceipt = function (customerId) {
                printWindow.print();
            };

            printWindow.downloadServiceReceipt = function (customerId) {
                const element = printWindow.document.querySelector(`#receipt-${customerId}`);
                const opt = {
                    margin: 1,
                    filename: `service-fee-receipt-${customerId}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
                };

                const buttons = element.querySelector('.print-buttons');
                buttons.style.display = 'none';

                printWindow.html2pdf().set(opt).from(element).save().then(() => {
                    buttons.style.display = 'block';
                });
            };
        };
        printWindow.document.head.appendChild(script);
    });
}
