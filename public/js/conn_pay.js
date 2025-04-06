function showPaymentModal(customerId) {
    const consumer = document.querySelector(`tr[data-customer-id="${customerId}"]`);
    const applicationFee = consumer.querySelector('td:nth-child(6)').textContent.replace('₱', '').trim();

    const input = document.getElementById('amountTendered');
    const feedback = input.nextElementSibling;

    document.getElementById('paymentCustomerId').value = customerId;
    document.getElementById('applicationFee').value = applicationFee;
    input.value = '';
    input.classList.remove('is-invalid');
    if (feedback) feedback.textContent = '';

    const modal = document.getElementById('paymentModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

function showPaymentResultModal(success, message) {
    const modal = document.getElementById('paymentResultModal');
    const icon = document.getElementById('paymentResultIcon');
    const title = document.getElementById('paymentResultTitle');
    const messageEl = document.getElementById('paymentResultMessage');

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

function closePaymentResultModal() {
    const modal = document.getElementById('paymentResultModal');
    modal.classList.remove('fade-in');

    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.getAttribute('data-refresh') === 'true') {
            window.location.reload();
        }
    }, 300);
}

async function filterApplications() {
    const searchValue = document.getElementById('searchInput').value.trim();
    const blockValue = document.getElementById('blockFilter').value;
    const statusValue = document.getElementById('statusFilter').value;
    const tbody = document.querySelector('.uni-table tbody');
    const paginationContainer = document.querySelector('.pagination-wrapper');

    try {
        const response = await fetch(`/application-fee/search?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}&status=${encodeURIComponent(statusValue)}`, {
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
                            <i class="fas fa-file-invoice"></i>
                            <p>No application payments found</p>
                        </td>
                    </tr>
                `;
            } else {
                data.payments.forEach(payment => {
                    const row = document.createElement('tr');
                    row.dataset.customerId = payment.customer_id;
                    row.classList.toggle('unpaid-row', payment.status === 'unpaid');

                    row.innerHTML = `
                        <td>Block ${payment.block_id}</td>
                        <td>${payment.customer_id}</td>
                        <td>${payment.firstname}</td>
                        <td>${payment.middlename}</td>
                        <td>${payment.lastname}</td>
                        <td>₱${payment.application_fee}</td>
                        <td>₱${payment.amount_paid}</td>
                        <td>
                            <span class="status-badge ${payment.status === 'paid' ? 'status-active' : 'status-inactive'}">
                                ${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                ${payment.status === 'unpaid' ? `
                                    <button class="btn_uni btn-activate" title="Pay Application Fee" onclick="showPaymentModal('${payment.customer_id}')">
                                        <i class="fas fa-money-bill-wave"></i>Pay
                                    </button>
                                ` : `
                                    <button class="btn_uni btn-billing" title="Print Receipt" onclick="printReceipt('${payment.customer_id}')">
                                        <i class="fas fa-print"></i>Print
                                    </button>
                                `}
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            if (paginationContainer) {
                paginationContainer.style.display = searchValue || blockValue || statusValue ? 'none' : 'flex';
            }
        }
    } catch (error) {
        console.error('Failed to filter applications:', error);
    }
}

function showEmptyState(tbody) {
    const emptyRow = document.createElement('tr');
    emptyRow.classList.add('empty-state-row');
    const colspan = document.querySelector('.uni-table thead th:last-child').cellIndex + 1;
    emptyRow.innerHTML = `
        <td colspan="${colspan}" class="empty-state">
            <i class="fas fa-search"></i>
            <p>No application payments found for your search</p>
        </td>`;
    tbody.appendChild(emptyRow);
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
                    <button class="btn_uni btn-billing" title="Print Receipt" onclick="printReceipt('${customerId}')">
                        <i class="fas fa-print"></i>Print
                    </button>
                `;
            }
        }
    }
}

function printReceipt(customerId) {
    const printWindow = window.open(`/print-application-receipt/${customerId}`, '_blank');
    printWindow.addEventListener('load', () => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => {
            printWindow.printApplicationReceipt = function (customerId) {
                printWindow.print();
            };

            printWindow.downloadApplicationReceipt = function (customerId) {
                const element = printWindow.document.querySelector(`#receipt-${customerId}`);
                const opt = {
                    margin: 1,
                    filename: `application-fee-receipt-${customerId}.pdf`,
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

document.getElementById('paymentForm').addEventListener('submit', handlePayment);

function handlePayment(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const amountTendered = parseFloat(formData.get('amount_tendered'));
    const applicationFee = parseFloat(document.getElementById('applicationFee').value.replace(/,/g, '').replace('₱', ''));
    const input = document.getElementById('amountTendered');
    const feedback = input.nextElementSibling;

    input.classList.remove('is-invalid');
    if (feedback) feedback.textContent = '';

    if (!amountTendered || isNaN(amountTendered)) {
        input.classList.add('is-invalid');
        if (feedback) feedback.textContent = 'Please enter a valid amount';
        return false;
    }

    if (amountTendered !== applicationFee) {
        input.classList.add('is-invalid');
        if (feedback) {
            if (amountTendered < applicationFee) {
                feedback.textContent = `Amount tendered is insufficient. Required amount is ₱${applicationFee.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            } else {
                feedback.textContent = `Amount tendered is too high. Required amount is ₱${applicationFee.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }
        }
        return false;
    }

    fetch('/process-payment', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
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
                if (feedback) feedback.textContent = data.errors.amount_tendered[0];
            } else if (data.success) {
                closeModal('paymentModal');
                showPaymentResultModal(true, data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            input.classList.add('is-invalid');
            if (feedback) feedback.textContent = 'An error occurred while processing the payment';
        });

    return false;
}

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        if (event.target.id === 'paymentResultModal') {
            closePaymentResultModal();
        } else {
            closeModal(event.target.id);
        }
    }
};

