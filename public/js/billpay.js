function handlePayment(consreadId) {
    fetch(`/billing/get-bill-details/${consreadId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const billAmount = parseFloat(data.total_amount) || 0;
                const lastUnpaidAmount = parseFloat(data.last_unpaid_amount) || 0;
                const penaltyAmount = parseFloat(data.penalty_amount) || 0;
                const totalAmount = billAmount + lastUnpaidAmount;

                document.getElementById('billId').value = consreadId;
                document.getElementById('present_reading').value = `₱${billAmount.toFixed(2)}`;
                document.getElementById('penalty_amount').value = `₱${penaltyAmount.toFixed(2)}`;

                const existingLastUnpaid = document.getElementById('last_unpaid_amount');
                if (existingLastUnpaid) {
                    existingLastUnpaid.closest('.form-group').remove();
                }

                if (lastUnpaidAmount > 0) {
                    const lastUnpaidElement = document.createElement('div');
                    lastUnpaidElement.className = 'form-group';
                    lastUnpaidElement.innerHTML = `
                        <label>Last Month Unpaid Amount (Including ₱${penaltyAmount.toFixed(2)} Penalty)</label>
                        <input type="text" id="last_unpaid_amount" class="form-control" readonly value="₱${lastUnpaidAmount.toFixed(2)}">
                        <small class="text-muted penalty-info">
                            <i class="fas fa-info-circle"></i> Includes ₱${penaltyAmount.toFixed(2)} penalty for unpaid bill
                        </small>
                    `;
                    document.getElementById('present_reading').parentElement.after(lastUnpaidElement);

                    document.getElementById('penalty_amount').parentElement.style.display = 'none';
                } else {
                    document.getElementById('penalty_amount').parentElement.style.display = 'none';
                }

                document.getElementById('total_amount').value = `₱${totalAmount.toFixed(2)}`;
                document.getElementById('bill_tendered_amount').value = '';

                const modal = document.getElementById('paymentModal');
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);

                document.getElementById('bill_tendered_amount').addEventListener('input', validateTendered);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPaymentResultModal(false, 'Error fetching bill details');
        });
}

function updateTotalAmount() {
    const baseAmount = parseFloat(document.getElementById('present_reading').value.replace('₱', '')) || 0;
    const lastUnpaidAmount = parseFloat(document.getElementById('last_unpaid_amount')?.value?.replace('₱', '') || 0);
    const penaltyAmount = parseFloat(document.getElementById('penalty_amount').value.replace('₱', '')) || 0;
    const totalAmount = baseAmount + lastUnpaidAmount + penaltyAmount;
    document.getElementById('total_amount').value = `₱${totalAmount.toFixed(2)}`;

    const tenderedInput = document.getElementById('bill_tendered_amount');
    if (tenderedInput.value) {
        validateTendered();
    }
}

function validateTendered() {
    const totalAmount = parseFloat(document.getElementById('total_amount').value.replace('₱', ''));
    const tendered = parseFloat(document.getElementById('bill_tendered_amount').value) || 0;

    const input = document.getElementById('bill_tendered_amount');
    const feedback = input.nextElementSibling;
    const submitButton = document.querySelector('#paymentForm button[type="submit"]');

    if (tendered < totalAmount) {
        input.classList.add('is-invalid');
        feedback.textContent = `Amount tendered is insufficient. Required amount is ₱${totalAmount.toFixed(2)}`;
        submitButton.disabled = true;
    } else if (tendered > totalAmount) {
        input.classList.add('is-invalid');
        feedback.textContent = `Amount tendered exceeds the total amount. Please enter a valid amount.`;
        submitButton.disabled = true;
    } else {
        input.classList.remove('is-invalid');
        feedback.textContent = '';
        submitButton.disabled = false;
    }
}

async function processPayment(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const totalAmount = parseFloat(document.getElementById('total_amount').value.replace('₱', ''));
    const tendered = parseFloat(document.getElementById('bill_tendered_amount').value);
    const penalty = parseFloat(document.getElementById('penalty_amount').value.replace('₱', '')) || 0;

    if (tendered < totalAmount) {
        return;
    }

    fetch('/billing/process-payment', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            bill_id: formData.get('bill_id'),
            bill_tendered_amount: tendered,
            penalty_amount: penalty
        })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closeModal('paymentModal');
                showPaymentResultModal(true, 'Payment processed successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                showPaymentResultModal(false, result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPaymentResultModal(false, 'Payment processing failed');
        });
}

function showPaymentResultModal(success, message) {
    const modal = document.getElementById('paymentResultModal');
    const icon = document.getElementById('paymentResultIcon');
    const title = document.getElementById('paymentResultTitle');
    const messageEl = document.getElementById('paymentResultMessage');
    const okButton = modal.querySelector('.btn_verify');

    icon.className = success ? 'success' : 'error';
    icon.innerHTML = success ?
        '<i class="fas fa-check-circle"></i>' :
        '<i class="fas fa-exclamation-triangle"></i>';
    title.textContent = success ? 'Success' : 'Error';
    messageEl.textContent = message;

    okButton.onclick = () => {
        closeModal('paymentResultModal');
        if (success) location.reload();
    };

    modal.onclick = (event) => {
        if (event.target === modal) {
            closeModal('paymentResultModal');
            if (success) location.reload();
        }
    };

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

function printBill(consreadId) {
    const printWindow = window.open(`/billing/print-receipt/${consreadId}`, '_blank');

    printWindow.addEventListener('load', () => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => {
            printWindow.printBillReceipt = function () {
                printWindow.print();
            };

            printWindow.downloadBillReceipt = function () {
                const element = printWindow.document.querySelector('#bill-receipt');
                const opt = {
                    margin: 1,
                    filename: `water-bill-${consreadId}.pdf`,
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

function printBillReceipt(billId) {
    const printWindow = window.open(`/billing/print-receipt/${billId}`, '_blank');
    printWindow.addEventListener('load', () => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => {
            printWindow.printBillReceipt = function (billId) {
                printWindow.print();
            };

            printWindow.downloadBillReceipt = function (billId) {
                const element = printWindow.document.querySelector(`#receipt-${billId}`);
                const opt = {
                    margin: 1,
                    filename: `bill-receipt-${billId}.pdf`,
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

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

async function filterBills() {
    const searchValue = document.getElementById('searchInput').value.trim();
    const statusValue = document.getElementById('statusFilter').value.toLowerCase();
    const currentCoverageId = document.getElementById('currentCoverageId')?.value;
    const tbody = document.querySelector('.uni-table tbody');
    const paginationContainer = document.querySelector('.pagination-container');

    if (!currentCoverageId) {
        showPaymentResultModal(false, 'No active coverage period found');
        return;
    }

    try {
        const queryParams = new URLSearchParams();
        if (searchValue) queryParams.append('query', searchValue);
        if (statusValue) queryParams.append('status', statusValue);
        queryParams.append('covdate_id', currentCoverageId);

        const response = await fetch(`/billing/payments/search?${queryParams.toString()}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await response.json();

        if (data.success) {
            tbody.innerHTML = '';

            if (data.bills.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i class="fas fa-file-invoice"></i>
                            <p>No bills found</p>
                        </td>
                    </tr>
                `;
            } else {
                data.bills.forEach(bill => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${bill.customer_id}</td>
                        <td>${bill.consumer_name}</td>
                        <td>${bill.due_date}</td>
                        <td>${bill.previous_reading}</td>
                        <td>${bill.present_reading}</td>
                        <td>${bill.consumption}</td>
                        <td>₱${bill.total_amount}</td>
                        <td>
                            <span class="status-badge ${bill.status.toLowerCase() === 'paid' ? 'status-active' : 'status-pending'}">
                                ${bill.status.charAt(0).toUpperCase() + bill.status.slice(1)}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                ${bill.status.toLowerCase() === 'unpaid' ? `
                                    <button class="btn_uni btn-billing" onclick="handlePayment('${bill.consread_id}')">
                                        <i class="fas fa-money-bill-wave"></i> Pay
                                    </button>
                                ` : `
                                    <button class="btn_uni btn-billing" title="Print Bill" onclick="printBill('${bill.consread_id}')">
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
                paginationContainer.style.display = searchValue || statusValue ? 'none' : 'flex';
            }
        } else {
            showPaymentResultModal(false, data.message);
        }
    } catch (error) {
        console.error('Failed to filter bills:', error);
        showPaymentResultModal(false, 'Failed to filter bills');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target.id);
        }
    };
});
