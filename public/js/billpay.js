function handlePayment(consreadId) {
    fetch(`/billing/get-bill-details/${consreadId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const presentReading = parseFloat(data.present_reading) || 0;

                document.getElementById('billId').value = consreadId;
                document.getElementById('present_reading').value = `₱${presentReading.toFixed(2)}`;
                document.getElementById('penalty_amount').value = '0';
                document.getElementById('total_amount').value = `₱${presentReading.toFixed(2)}`;
                document.getElementById('bill_tendered_amount').value = '';

                const modal = document.getElementById('paymentModal');
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);

                document.getElementById('penalty_amount').addEventListener('input', updateTotalAmount);
                document.getElementById('bill_tendered_amount').addEventListener('input', validateTendered);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPaymentResultModal(false, 'Error fetching bill details');
        });
}

function updateTotalAmount() {
    const presentReading = parseFloat(document.getElementById('present_reading').value.replace('₱', '')) || 0;
    const penaltyAmount = parseFloat(document.getElementById('penalty_amount').value) || 0;
    const totalAmount = presentReading + penaltyAmount;
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
        feedback.textContent = `Amount tendered is too high. Required amount is ₱${totalAmount.toFixed(2)}`;
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
    const penalty = parseFloat(document.getElementById('penalty_amount').value) || 0;

    if (tendered !== totalAmount) {
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

    icon.className = success ? 'success' : 'error';
    icon.innerHTML = success ?
        '<i class="fas fa-check-circle"></i>' :
        '<i class="fas fa-exclamation-triangle"></i>';
    title.textContent = success ? 'Success' : 'Error';
    messageEl.textContent = message;

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
        // Add the html2pdf script dynamically
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => {
            // Define the print and download functions in the new window
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

                // Hide the buttons before generating PDF
                const buttons = element.querySelector('.print-buttons');
                buttons.style.display = 'none';

                // Generate PDF
                printWindow.html2pdf().set(opt).from(element).save().then(() => {
                    // Show the buttons again after PDF generation
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
    const tbody = document.querySelector('.uni-table tbody');
    const paginationContainer = document.querySelector('.pagination-container');

    try {
        const response = await fetch(`/billing/payments/search?query=${encodeURIComponent(searchValue)}&status=${encodeURIComponent(statusValue)}`, {
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
